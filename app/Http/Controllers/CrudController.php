<?php

namespace App\Http\Controllers;

use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

abstract class CrudController extends Controller
{
    /**
     * @return class-string<Model>
     */
    abstract protected function modelClass(): string;

    abstract protected function routePrefix(): string;

    abstract protected function pageTitle(): string;

    /**
     * @return array<int, array<string, mixed>>
     */
    abstract protected function columns(): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    abstract protected function fields(): array;

    /**
     * @return array<int, string>
     */
    protected function relations(): array
    {
        return [];
    }

    protected function perPage(): int
    {
        return 15;
    }

    public function index(): View
    {
        $class = $this->modelClass();

        $query = $class::query();

        if ($this->relations() !== []) {
            $query->with($this->relations());
        }

        $items = $query->orderByDesc('id')->paginate($this->perPage())->withQueryString();
        $columns = $this->columns();

        $headers = array_map(
            static fn (array $column): string => $column['label'],
            $columns
        );

        $rows = [];

        foreach ($items as $item) {
            $rows[] = [
                'id' => $item->getKey(),
                'cells' => array_map(
                    fn (array $column): string => $this->columnValue($item, $column),
                    $columns
                ),
                'images' => array_map(
                    fn (array $column): ?string => $this->columnImage($item, $column),
                    $columns
                ),
            ];
        }

        return view('crud.index', [
            'title' => $this->pageTitle(),
            'routePrefix' => $this->routePrefix(),
            'headers' => $headers,
            'items' => $items,
            'rows' => $rows,
        ]);
    }

    public function create(): View
    {
        $class = $this->modelClass();

        return view('crud.create', $this->formViewData(new $class(), 'Tambah '.$this->pageTitle()));
    }

    public function store(Request $request): RedirectResponse
    {
        $fields = $this->resolvedFields();

        $validated = $request->validate(
            $this->rules($fields),
            [],
            $this->validationAttributes($fields)
        );

        $validated = $this->prepareFileFields($request, $validated, $fields);

        $class = $this->modelClass();
        $item = new $class();

        $this->fillModel($item, $validated, $fields);
        $this->beforeSave($item);
        $item->save();

        return redirect()
            ->route($this->routePrefix().'.show', $item->getKey())
            ->with('success', $this->pageTitle().' berhasil ditambahkan.');
    }

    public function show(int|string $id): View
    {
        $item = $this->findItem($id);

        $rows = array_map(
            fn (array $column): array => [
                'label' => $column['label'],
                'value' => $this->columnValue($item, $column),
            ],
            $this->columns()
        );

        return view('crud.show', [
            'title' => $this->pageTitle(),
            'routePrefix' => $this->routePrefix(),
            'item' => $item,
            'rows' => $rows,
        ]);
    }

    public function edit(int|string $id): View
    {
        return view('crud.edit', $this->formViewData(
            $this->findItem($id),
            'Edit '.$this->pageTitle()
        ));
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $item = $this->findItem($id);
        $fields = $this->resolvedFields();

        $validated = $request->validate(
            $this->rules($fields, $item),
            [],
            $this->validationAttributes($fields)
        );

        $validated = $this->prepareFileFields($request, $validated, $fields, $item);

        $this->fillModel($item, $validated, $fields);
        $this->beforeSave($item);
        $item->save();

        return redirect()
            ->route($this->routePrefix().'.show', $item->getKey())
            ->with('success', $this->pageTitle().' berhasil diperbarui.');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $item = $this->findItem($id);

        try {
            $item->delete();
        } catch (QueryException) {
            return redirect()
                ->route($this->routePrefix().'.index')
                ->with('error', 'Data tidak dapat dihapus karena masih digunakan oleh data lain.');
        }

        return redirect()
            ->route($this->routePrefix().'.index')
            ->with('success', $this->pageTitle().' berhasil dihapus.');
    }

    protected function findItem(int|string $id): Model
    {
        $class = $this->modelClass();

        $query = $class::query();

        if ($this->relations() !== []) {
            $query->with($this->relations());
        }

        return $query->findOrFail($id);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function resolvedFields(): array
    {
        return array_map(function (array $field): array {
            if (isset($field['options']) && $field['options'] instanceof Closure) {
                $field['options'] = ($field['options'])();
            }

            return $field;
        }, $this->fields());
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<string, array<int, mixed>>
     */
    protected function rules(array $fields, ?Model $item = null): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $fieldRules = $field['rules'] ?? [];

            if (! empty($field['unique'])) {
                if ($item !== null) {
                    $table = $item->getTable();
                    $ignore = $item->getKey();
                } else {
                    $class = $this->modelClass();
                    $table = (new $class())->getTable();
                    $ignore = null;
                }

                $fieldRules[] = Rule::unique($table, $field['name'])->ignore($ignore);
            }

            $rules[$field['name']] = $fieldRules;
        }

        return $rules;
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<string, string>
     */
    protected function validationAttributes(array $fields): array
    {
        $attributes = [];

        foreach ($fields as $field) {
            $attributes[$field['name']] = $field['label'] ?? $field['name'];
        }

        return $attributes;
    }

    /**
     * Set atribut satu per satu agar tetap bekerja meskipun $fillable model
     * tidak sama dengan kolom tabel yang sebenarnya.
     *
     * @param  array<int, array<string, mixed>>  $fields
     */
    protected function fillModel(Model $item, array $validated, array $fields): void
    {
        foreach ($fields as $field) {
            $name = $field['name'];
            $type = $field['type'] ?? 'text';

            if ($type === 'checkbox') {
                $item->setAttribute($name, (bool) ($validated[$name] ?? false));

                continue;
            }

            if (! array_key_exists($name, $validated)) {
                continue;
            }

            $value = $validated[$name];

            if ($value === null && ! ($field['nullable'] ?? false) && array_key_exists('default', $field)) {
                $value = $field['default'];
            }

            if (($field['json'] ?? false) && is_string($value) && trim($value) !== '') {
                $decoded = json_decode($value, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
            }

            $item->setAttribute($name, $value);
        }
    }

    /**
     * Hook untuk menyesuaikan model sebelum disimpan tanpa mengubah model.
     */
    protected function beforeSave(Model $item): void
    {
        //
    }

    /**
     * @return array<string, mixed>
     */
    protected function formViewData(Model $item, string $heading): array
    {
        $fields = $this->resolvedFields();
        $values = [];

        foreach ($fields as $field) {
            $name = $field['name'];
            $type = $field['type'] ?? 'text';
            $value = $item->getAttribute($name);

            if ($value === null && ! $item->exists && array_key_exists('default', $field)) {
                $value = $field['default'];
            }

            if ($type === 'checkbox') {
                $value = (bool) ($value ?? ($field['default'] ?? false));
            } elseif ($value instanceof \DateTimeInterface) {
                $value = $type === 'date'
                    ? $value->format('Y-m-d')
                    : $value->format('Y-m-d\TH:i');
            } elseif (($field['json'] ?? false) && is_array($value)) {
                $value = json_encode(
                    $value,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            }

            $values[$name] = $value;
        }

        return [
            'heading' => $heading,
            'title' => $this->pageTitle(),
            'routePrefix' => $this->routePrefix(),
            'fields' => $fields,
            'values' => $values,
            'item' => $item,
        ];
    }

    /**
     * Simpan unggahan gambar ke public/uploads/{modul} dan pertahankan
     * berkas lama jika form edit dikirim tanpa memilih berkas baru.
     *
     * @param  array<string, mixed>  $validated
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    protected function prepareFileFields(Request $request, array $validated, array $fields, ?Model $item = null): array
    {
        foreach ($fields as $field) {
            if (($field['type'] ?? 'text') !== 'file') {
                continue;
            }

            $name = $field['name'];

            if ($request->hasFile($name)) {
                $directory = public_path('uploads/'.$this->routePrefix());
                File::ensureDirectoryExists($directory);

                $file = $request->file($name);
                $filename = Str::random(16).'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $file->move($directory, $filename);

                $validated[$name] = 'uploads/'.$this->routePrefix().'/'.$filename;

                continue;
            }

            $existing = $item?->getAttribute($name);

            if ($existing) {
                unset($validated[$name]);

                continue;
            }

            if ($field['required'] ?? false) {
                throw ValidationException::withMessages([
                    $name => 'Pilih berkas '.$field['label'].' terlebih dahulu.',
                ]);
            }
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $column
     */
    protected function columnImage(Model $item, array $column): ?string
    {
        if (! ($column['image'] ?? false)) {
            return null;
        }

        $value = data_get($item, $column['key'] ?? '');

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return Str::startsWith($value, ['http://', 'https://', '//'])
            ? $value
            : asset($value);
    }

    /**
     * @param  array<string, mixed>  $column
     */
    protected function columnValue(Model $item, array $column): string
    {
        if (isset($column['value']) && is_callable($column['value'])) {
            $value = ($column['value'])($item);
        } elseif (isset($column['relation'])) {
            $value = data_get($item, $column['relation'].'.'.($column['label_key'] ?? 'name'));
        } else {
            $value = data_get($item, $column['key'] ?? '');
        }

        return $this->stringify($value, $column);
    }

    /**
     * @param  array<string, mixed>  $column
     */
    protected function stringify(mixed $value, array $column): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(($column['date'] ?? false) ? 'd-m-Y' : 'd-m-Y H:i');
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '-';
        }

        if (($column['humanize'] ?? false) && is_string($value)) {
            return ucfirst(str_replace('_', ' ', $value));
        }

        return (string) $value;
    }

    /**
     * @return array<int, string>
     */
    protected function userOptions(): array
    {
        return User::query()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(static fn (User $user): array => [
                $user->id => $user->name.' ('.$user->role.')',
            ])
            ->all();
    }

    private ?array $userNames = null;

    protected function userName(int|string|null $id): string
    {
        if ($id === null || $id === '') {
            return '-';
        }

        if ($this->userNames === null) {
            $this->userNames = User::query()->pluck('name', 'id')->all();
        }

        return $this->userNames[(int) $id] ?? 'Pengguna #'.$id;
    }
}
