@props(['name'])

@error($name)
    <span class="field__error" role="alert">{{ $message }}</span>
@enderror
