@php
    extract(app(\App\Features\Flashcards\View\Services\WordFormThemeFactory::class)->variables($theme ?? 'default'));
@endphp

<form action="{{ route('flashcards.words.store') }}" method="POST" class="{{ $wordFormSpacing }}">
    @csrf

    @include('default.flashcards.words.partials.word-form-fields', [
        'word' => null,
        'formTextReadonly' => false,
    ])

    @include('default.flashcards.words.partials.word-form-add-to-deck')

    <div class="flex gap-4 pt-2">
        <button type="submit" class="{{ $wordFormBtnSubmit }}">Save</button>
        <a href="{{ route('flashcards.words.index') }}" class="{{ $wordFormBtnSecondary }}">Cancel</a>
    </div>
</form>

@include('default.flashcards.words.partials.word-form-scripts')
