{{-- Shared suggestions for per-sense form_type (must match FormTypeCatalog::allowed()). --}}
@php
    $formTypeDatalistId = $formTypeDatalistId ?? 'flashcards-form-type-options';
@endphp
<datalist id="{{ $formTypeDatalistId }}">
    @foreach (\App\Features\Flashcards\Support\FormTypeCatalog::allowed() as $opt)
        <option value="{{ $opt }}">
    @endforeach
</datalist>
