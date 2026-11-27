@foreach ($fields as $field)
    @php
        $name = $field['name'];
        $type = $field['type'] ?? 'text';
        $id = 'field-'.$name;
        $value = old($name, $values[$name] ?? null);
        $required = in_array('required', $field['rules'] ?? [], true);
        $fullWidth = in_array($type, ['textarea', 'file'], true);
        $currentFile = $values[$name] ?? null;
    @endphp

    <div class="form-group {{ $fullWidth ? 'full' : '' }}">
        <label for="{{ $id }}">
            {{ $field['label'] }}
            @if ($required)
                <span class="req">*</span>
            @endif
        </label>

        @if ($type === 'textarea')
            <textarea
                id="{{ $id }}"
                name="{{ $name }}"
                rows="{{ $field['rows'] ?? 4 }}"
                @if (isset($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif
            >{{ $value }}</textarea>
        @elseif ($type === 'select')
            <select id="{{ $id }}" name="{{ $name }}">
                @if ($field['nullable'] ?? false)
                    <option value="">- Pilih -</option>
                @endif
                @foreach ($field['options'] ?? [] as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                @endforeach
            </select>
        @elseif ($type === 'search-select')
            @php
                $searchLabel = '';

                foreach ($field['options'] ?? [] as $optionValue => $optionLabel) {
                    if ((string) $optionValue === (string) $value) {
                        $searchLabel = $optionLabel;
                        break;
                    }
                }

                $listId = $id.'-options';
            @endphp
            <div class="search-select" data-search-select>
                <input
                    type="text"
                    id="{{ $id }}"
                    value="{{ $searchLabel }}"
                    list="{{ $listId }}"
                    autocomplete="off"
                    placeholder="{{ $field['placeholder'] ?? 'Ketik untuk mencari...' }}"
                    data-search-input
                    @if ($required) required @endif
                >
                <datalist id="{{ $listId }}">
                    @foreach ($field['options'] ?? [] as $optionValue => $optionLabel)
                        <option value="{{ $optionLabel }}" data-value="{{ $optionValue }}"></option>
                    @endforeach
                </datalist>
                <input type="hidden" name="{{ $name }}" value="{{ $value }}" data-search-value>
            </div>
        @elseif ($type === 'checkbox')
            <input type="hidden" name="{{ $name }}" value="0">
            <label class="check">
                <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" @checked((bool) $value)>
                Ya
            </label>
        @elseif ($type === 'file')
            @if ($currentFile)
                <div class="file-preview">
                    <img
                        src="{{ \Illuminate\Support\Str::startsWith($currentFile, ['http://', 'https://', '//']) ? $currentFile : asset($currentFile) }}"
                        alt="Berkas saat ini"
                        loading="lazy"
                    >
                    <span>Berkas saat ini. Pilih berkas baru untuk menggantinya.</span>
                </div>
            @endif
            <input
                type="file"
                id="{{ $id }}"
                name="{{ $name }}"
                accept="image/*"
            >
        @else
            <input
                type="{{ $type }}"
                id="{{ $id }}"
                name="{{ $name }}"
                value="{{ $value }}"
                @if (isset($field['step'])) step="{{ $field['step'] }}" @endif
                @if (isset($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif
            >
        @endif

        @error($name)
            <div class="error">{{ $message }}</div>
        @enderror

        @if (isset($field['help']))
            <small>{{ $field['help'] }}</small>
        @endif
    </div>
@endforeach

@once
    @push('scripts')
        <script>
            (() => {
                const normalize = (value) => String(value || '').trim().toLowerCase();

                document.querySelectorAll('[data-search-select]').forEach((wrapper) => {
                    const input = wrapper.querySelector('[data-search-input]');
                    const hidden = wrapper.querySelector('[data-search-value]');
                    const list = wrapper.querySelector('datalist');

                    if (!input || !hidden || !list) {
                        return;
                    }

                    const options = [...list.options];

                    const resolve = () => {
                        const value = normalize(input.value);

                        if (value === '') {
                            hidden.value = '';

                            return;
                        }

                        const exact = options.find((option) => normalize(option.value) === value);

                        if (exact) {
                            hidden.value = exact.dataset.value;

                            return;
                        }

                        const matches = options.filter((option) => normalize(option.value).includes(value));
                        hidden.value = matches.length === 1 ? matches[0].dataset.value : '';
                    };

                    input.addEventListener('input', resolve);
                    input.addEventListener('change', resolve);
                    input.addEventListener('blur', () => {
                        if (hidden.value === '') {
                            return;
                        }

                        const match = options.find((option) => option.dataset.value === hidden.value);

                        if (match) {
                            input.value = match.value;
                        }
                    });
                });
            })();
        </script>
    @endpush
@endonce
