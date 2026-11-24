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
