{{-- Composant de sélection des niveaux avec traductions --}}
@props(['name' => 'level', 'selected' => null, 'required' => true, 'placeholder' => '-- Sélectionnez un niveau --'])

<select 
    class="form-control @error($name) is-invalid @enderror" 
    name="{{ $name }}"
    {{ $required ? 'required' : '' }}
    {{ $attributes }}
>
    <option value="">{{ $placeholder }}</option>
    
    {{-- Niveaux Francophones --}}
    <optgroup label="{{ __('levels.system.francophone') }}">
        @foreach(__('levels.francophone') as $key => $label)
            @if($key !== 'label')
                <option value="{{ $key }}" 
                    {{ (isset($selected) && $selected == $key) || old($name) == $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endif
        @endforeach
    </optgroup>
    
    {{-- Niveaux Anglophones --}}
    <optgroup label="{{ __('levels.system.anglophone') }}">
        @foreach(__('levels.anglophone') as $key => $label)
            @if($key !== 'label')
                <option value="{{ $key }}" 
                    {{ (isset($selected) && $selected == $key) || old($name) == $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endif
        @endforeach
    </optgroup>
</select>

@error($name)
    <div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>
@enderror
