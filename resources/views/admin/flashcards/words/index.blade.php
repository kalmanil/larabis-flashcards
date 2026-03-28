@extends('layouts.app')

@section('title', 'Browse Words - Flashcards')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-6">
        <h1 class="text-3xl font-bold">Browse Words</h1>
        <div class="flex flex-col sm:flex-row gap-2 sm:justify-end shrink-0">
            <a href="{{ route('flashcards.words.bulk-create') }}"
               class="px-4 py-2 border border-gray-300 bg-white text-gray-800 rounded-lg hover:bg-gray-50 font-medium text-center">
                Bulk add
            </a>
            <a href="{{ route('flashcards.words.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium text-center">
                Add word
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if (session('info'))
        <div class="mb-4 p-4 bg-blue-100 text-blue-800 rounded-lg">{{ session('info') }}</div>
    @endif

    <form method="GET" class="mb-6 flex gap-4 flex-wrap">
        <select name="shoresh_id" class="border rounded px-3 py-2 w-full sm:w-auto min-w-0">
            <option value="">All roots</option>
            @foreach ($shoreshim as $s)
                <option value="{{ $s->id }}" {{ request('shoresh_id') == $s->id ? 'selected' : '' }}>{{ $s->root }}</option>
            @endforeach
        </select>
        <select name="language" class="border rounded px-3 py-2 w-full sm:w-auto min-w-0">
            <option value="">All languages</option>
            <option value="ru" {{ request('language') == 'ru' ? 'selected' : '' }}>Russian</option>
            <option value="en" {{ request('language') == 'en' ? 'selected' : '' }}>English</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 w-full sm:w-auto">Filter</button>
    </form>

    <div class="space-y-4">
        @forelse ($words as $word)
            <div class="bg-white rounded-lg shadow p-4 flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-start">
                <div class="min-w-0 flex-1 space-y-2">
                    <div>
                        <span class="text-xl font-semibold" dir="rtl">{{ $word->form_text }}</span>
                        <span class="text-gray-500 text-sm ml-2">{{ $word->shoresh?->root ?? '—' }}</span>
                    </div>
                    <p class="text-sm text-gray-600">{{ $word->transcription_ru ?? '—' }}</p>
                    <div class="text-sm text-gray-600 mt-1">
                        @foreach ($word->translations as $t)
                            <span class="inline-block bg-gray-100 px-2 py-0.5 rounded text-xs mr-1">{{ $t->text }}</span>
                        @endforeach
                        @if ($word->translations->isEmpty())
                            <span class="text-gray-400">—</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500">
                        @if ($word->frequency_rank) #{{ $word->frequency_rank }} @endif
                        @if ($word->frequency_per_million) {{ number_format($word->frequency_per_million, 1) }}/M @endif
                        @if (!$word->frequency_rank && !$word->frequency_per_million) — @endif
                    </p>
                </div>
                <div class="flex flex-wrap gap-x-4 gap-y-2 shrink-0 sm:flex-col sm:items-end sm:gap-2 border-t border-gray-100 pt-3 sm:border-t-0 sm:pt-0">
                    <a href="{{ route('flashcards.words.edit', $word) }}" class="text-indigo-600 hover:underline text-sm">Edit</a>
                    <form action="{{ route('flashcards.words.add-to-deck', $word) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-green-600 hover:underline text-sm">Add to deck</button>
                    </form>
                    <form action="{{ route('flashcards.words.destroy', $word) }}" method="POST" class="inline"
                          onsubmit="return confirm('Delete this word?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline text-sm">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                No words yet. <a href="{{ route('flashcards.words.create') }}" class="text-indigo-600">Add one</a> or <a href="{{ route('flashcards.words.bulk-create') }}" class="text-indigo-600">bulk add</a>.
            </div>
        @endforelse
    </div>

    @if ($words->hasPages())
        <div class="mt-4">{{ $words->links() }}</div>
    @endif
</div>
@endsection
