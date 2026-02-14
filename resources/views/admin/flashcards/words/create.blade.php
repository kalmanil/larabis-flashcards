@extends('layouts.app')

@section('title', 'Add Word - Flashcards')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">Add Word</h1>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('flashcards.words.store') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block font-medium text-gray-700 mb-1">Hebrew form *</label>
            <input type="text" name="form_text" value="{{ old('form_text') }}" required dir="rtl"
                   class="w-full border rounded px-3 py-2 text-lg">
        </div>

        <div>
            <label class="block font-medium text-gray-700 mb-1">Root (shoresh)</label>
            <select name="shoresh_id" id="shoresh_id" class="w-full border rounded px-3 py-2">
                <option value="">— None —</option>
                @foreach ($shoreshim as $s)
                    <option value="{{ $s->id }}" {{ old('shoresh_id') == $s->id ? 'selected' : '' }}>{{ $s->root }}</option>
                @endforeach
            </select>
            <p class="text-sm text-gray-500 mt-1">Or add new:</p>
            <input type="text" name="new_shoresh" value="{{ old('new_shoresh') }}" placeholder="e.g. כ־ת־ב" dir="rtl" class="w-full border rounded px-3 py-2 mt-1">
        </div>

        <div>
            <label class="block font-medium text-gray-700 mb-1">Form type (e.g. binyan)</label>
            <input type="text" name="form_type" value="{{ old('form_type') }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-medium text-gray-700 mb-1">Transcription (Russian)</label>
                <input type="text" name="transcription_ru" value="{{ old('transcription_ru') }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Transcription (English)</label>
                <input type="text" name="transcription_en" value="{{ old('transcription_en') }}" class="w-full border rounded px-3 py-2">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-medium text-gray-700 mb-1">Frequency rank</label>
                <input type="number" name="frequency_rank" value="{{ old('frequency_rank') }}" min="1" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Frequency per million</label>
                <input type="number" name="frequency_per_million" value="{{ old('frequency_per_million') }}" step="0.01" min="0" class="w-full border rounded px-3 py-2">
            </div>
        </div>

        <div>
            <label class="block font-medium text-gray-700 mb-1">Translations (Russian)</label>
            <p class="text-sm text-gray-500 mb-1">Select existing or add new below (one per line)</p>
            <select name="translation_ids[]" multiple class="w-full border rounded px-3 py-2 h-24">
                @foreach ($translationsRu as $t)
                    <option value="{{ $t->id }}" {{ in_array($t->id, old('translation_ids', [])) ? 'selected' : '' }}>{{ $t->text }}</option>
                @endforeach
            </select>
            <textarea name="new_translations_ru" rows="2" class="w-full border rounded px-3 py-2 mt-1" placeholder="New RU translations, one per line">{{ is_array(old('new_translations_ru')) ? implode("\n", old('new_translations_ru')) : old('new_translations_ru') }}</textarea>
        </div>

        <div>
            <label class="block font-medium text-gray-700 mb-1">Translations (English)</label>
            <select name="translation_ids[]" multiple class="w-full border rounded px-3 py-2 h-24">
                @foreach ($translationsEn as $t)
                    <option value="{{ $t->id }}" {{ in_array($t->id, old('translation_ids', [])) ? 'selected' : '' }}>{{ $t->text }}</option>
                @endforeach
            </select>
            <textarea name="new_translations_en" rows="2" class="w-full border rounded px-3 py-2 mt-1" placeholder="New EN translations, one per line">{{ is_array(old('new_translations_en')) ? implode("\n", old('new_translations_en')) : old('new_translations_en') }}</textarea>
        </div>

        <div>
            <label class="inline-flex items-center">
                <input type="checkbox" name="add_to_deck" value="1" {{ old('add_to_deck') ? 'checked' : '' }}>
                <span class="ml-2">Add to my deck</span>
            </label>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save</button>
            <a href="{{ route('flashcards.words.index') }}" class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
