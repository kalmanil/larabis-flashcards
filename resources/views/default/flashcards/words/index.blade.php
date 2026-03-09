@extends('layouts.app')

@section('title', 'Browse Words - Flashcards')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    @include('default.partials.nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Browse Words</h1>
            <a href="{{ route('flashcards.words.create') }}"
               class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 shadow-md hover:shadow-lg transition-all duration-200">
                Add word
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 mb-6">
            <form method="GET" class="flex gap-4 flex-wrap">
                <select name="shoresh_id" class="border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All roots</option>
                    @foreach ($shoreshim as $s)
                        <option value="{{ $s->id }}" {{ request('shoresh_id') == $s->id ? 'selected' : '' }}>{{ $s->root }}</option>
                    @endforeach
                </select>
                <select name="language" class="border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All languages</option>
                    <option value="ru" {{ request('language') == 'ru' ? 'selected' : '' }}>Russian</option>
                    <option value="en" {{ request('language') == 'en' ? 'selected' : '' }}>English</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl font-medium transition-colors">Filter</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Hebrew</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Root</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Transcription</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Translations</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Freq</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($words as $word)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 text-lg" dir="rtl">{{ $word->form_text }}</td>
                            <td class="px-4 py-3">{{ $word->shoresh?->root ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $word->transcription_ru ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">
                                @foreach ($word->translations as $t)
                                    <span class="inline-block bg-indigo-50 text-indigo-800 px-2 py-0.5 rounded text-xs mr-1">{{ $t->text }}</span>
                                @endforeach
                                @if ($word->translations->isEmpty()) — @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if ($word->frequency_rank) #{{ $word->frequency_rank }} @endif
                                @if ($word->frequency_per_million) {{ number_format($word->frequency_per_million, 1) }}/M @endif
                                @if (!$word->frequency_rank && !$word->frequency_per_million) — @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex flex-wrap items-center gap-x-3 gap-y-1 justify-end">
                                    <a href="{{ route('flashcards.words.edit', $word) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">Edit</a>
                                    <form action="{{ route('flashcards.words.add-to-deck', $word) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-800 font-medium text-sm">Add to deck</button>
                                    </form>
                                    <form action="{{ route('flashcards.words.destroy', $word) }}" method="POST" class="inline" onsubmit="return confirm('Delete this word?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-sm">Delete</button>
                                    </form>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-500">No words yet. <a href="{{ route('flashcards.words.create') }}" class="text-indigo-600 hover:underline font-medium">Add one</a>.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($words->hasPages())
            <div class="mt-6">{{ $words->links() }}</div>
        @endif
    </div>
</div>
@endsection
