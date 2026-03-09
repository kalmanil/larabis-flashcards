@extends('layouts.app')

@section('title', 'Add Word - Flashcards')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    @include('default.partials.nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Add Word</h1>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('flashcards.words.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Hebrew form *</label>
                        <input type="text" name="form_text" id="form_text" value="{{ old('form_text') }}" required dir="rtl"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Root (shoresh)</label>
                        <select name="shoresh_id" id="shoresh_id" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">— None —</option>
                            @foreach ($shoreshim as $s)
                                <option value="{{ $s->id }}" {{ old('shoresh_id') == $s->id ? 'selected' : '' }}>{{ $s->root }}</option>
                            @endforeach
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Or add new:</p>
                        <input type="text" name="new_shoresh" id="new_shoresh" value="{{ old('new_shoresh') }}" placeholder="e.g. כ־ת־ב" dir="rtl" class="w-full border border-gray-200 rounded-xl px-3 py-2 mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Form type (e.g. binyan)</label>
                        <input type="text" name="form_type" id="form_type" value="{{ old('form_type') }}" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Transcription (Russian)</label>
                        <input type="text" name="transcription_ru" id="transcription_ru" value="{{ old('transcription_ru') }}" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Frequency rank</label>
                            <input type="number" name="frequency_rank" value="{{ old('frequency_rank') }}" min="1" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Frequency per million</label>
                            <input type="number" name="frequency_per_million" value="{{ old('frequency_per_million') }}" step="0.01" min="0" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Translations (Russian)</label>
                        <p class="text-sm text-gray-500 mb-1">Select existing or add new below (one per line)</p>
                        <select name="translation_ids[]" multiple class="w-full border border-gray-200 rounded-xl px-3 py-2 h-24 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach ($translationsRu as $t)
                                <option value="{{ $t->id }}" {{ in_array($t->id, old('translation_ids', [])) ? 'selected' : '' }}>{{ $t->text }}</option>
                            @endforeach
                        </select>
                        <textarea name="new_translations_ru" id="new_translations_ru" rows="2" class="w-full border border-gray-200 rounded-xl px-3 py-2 mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="New RU translations, one per line">{{ is_array(old('new_translations_ru')) ? implode("\n", old('new_translations_ru')) : old('new_translations_ru') }}</textarea>
                    </div>

                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="add_to_deck" value="1" {{ old('add_to_deck') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-gray-700">Add to my deck</span>
                        </label>
                    </div>

                    <div class="flex gap-4 pt-2">
                        <button type="submit" class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 shadow-md transition-all duration-200">Save</button>
                        <a href="{{ route('flashcards.words.index') }}" class="px-6 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-medium transition-colors">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
