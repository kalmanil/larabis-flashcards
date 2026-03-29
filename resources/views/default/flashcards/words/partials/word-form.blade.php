@php
    extract(\App\Features\Flashcards\View\WordFormTheme::variables($theme ?? 'default'));
@endphp

<form action="{{ route('flashcards.words.store') }}" method="POST" class="{{ $wordFormSpacing }}">
    @csrf

    @include('default.flashcards.words.partials.word-form-fields', [
        'word' => null,
        'formTextReadonly' => false,
        'geminiImportLabel' => 'Import from Gemini',
    ])

    <div>
        <label class="inline-flex items-center">
            <input type="checkbox" name="add_to_deck" value="1" {{ old('add_to_deck') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="ml-2 text-gray-700">Add to my deck</span>
        </label>
    </div>

    <div class="flex gap-4 pt-2">
        <button type="submit" class="{{ $wordFormBtnSubmit }}">Save</button>
        <a href="{{ route('flashcards.words.index') }}" class="{{ $wordFormBtnSecondary }}">Cancel</a>
    </div>
</form>

@include('default.flashcards.words.partials.word-form-scripts')
