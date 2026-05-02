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

    <div class="flex flex-wrap gap-3 pt-2">
        <button type="submit" name="save_continue" value="1" class="{{ $wordFormBtnSubmit }}">Save and continue</button>
        <button type="submit" class="{{ $wordFormBtnSecondary }}">Save and exit</button>
        <a href="{{ route('flashcards.words.index') }}" class="{{ $wordFormBtnSecondary }}">Cancel</a>
    </div>
</form>

@include('default.flashcards.words.partials.word-form-scripts')
