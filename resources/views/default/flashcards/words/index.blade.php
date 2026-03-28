@extends('layouts.app')

@section('title', 'Browse Words - Flashcards')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    @include('default.partials.nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-6">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Browse Words</h1>
            <div class="flex flex-col sm:flex-row gap-2 sm:justify-end shrink-0">
                <a href="{{ route('flashcards.words.bulk-create') }}"
                   class="px-4 py-2 border border-gray-200 bg-white text-gray-800 rounded-xl font-medium hover:bg-gray-50 shadow-sm transition-all duration-200 text-center">
                    Bulk add
                </a>
                <a href="{{ route('flashcards.words.create') }}"
                   class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 shadow-md hover:shadow-lg transition-all duration-200 text-center">
                    Add word
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl">{{ session('info') }}</div>
        @endif

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 mb-6">
            <form method="GET" class="flex gap-4 flex-wrap">
                <select name="shoresh_id" class="border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-full sm:w-auto min-w-0">
                    <option value="">All roots</option>
                    @foreach ($shoreshim as $s)
                        <option value="{{ $s->id }}" {{ request('shoresh_id') == $s->id ? 'selected' : '' }}>{{ $s->root }}</option>
                    @endforeach
                </select>
                <select name="language" class="border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-full sm:w-auto min-w-0">
                    <option value="">All languages</option>
                    <option value="ru" {{ request('language') == 'ru' ? 'selected' : '' }}>Russian</option>
                    <option value="en" {{ request('language') == 'en' ? 'selected' : '' }}>English</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl font-medium transition-colors w-full sm:w-auto">Filter</button>
            </form>
        </div>

        <div class="space-y-4">
            @forelse ($words as $word)
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5 flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-start lg:flex-nowrap lg:items-center lg:gap-6 hover:shadow-xl transition-shadow">
                    <div class="min-w-0 flex-1 flex flex-col gap-2 lg:flex-row lg:items-center lg:gap-4 xl:gap-6">
                        <div class="shrink-0">
                            <span class="text-xl font-semibold" dir="rtl">{{ $word->form_text }}</span>
                            @if ($word->shoresh)
                                <span class="text-gray-500 text-sm ml-2">({{ $word->shoresh->root }})</span>
                            @else
                                <span class="text-gray-500 text-sm ml-2">(—)</span>
                            @endif
                        </div>
                        <p class="shrink-0 text-sm text-gray-600">{{ $word->transcription_ru ?? '—' }}</p>
                        <div class="text-sm text-gray-600 min-w-0 flex-1">
                            @foreach ($word->translations as $t)
                                <span class="inline-block bg-indigo-50 text-indigo-800 px-2 py-0.5 rounded text-xs mr-1 mb-1">{{ $t->text }}</span>
                            @endforeach
                            @if ($word->translations->isEmpty())
                                <span class="text-gray-400">—</span>
                            @endif
                        </div>
                        <p class="shrink-0 text-sm text-gray-500 whitespace-nowrap tabular-nums">
                            @if ($word->frequency_rank) #{{ $word->frequency_rank }} @endif
                            @if ($word->frequency_per_million) {{ number_format($word->frequency_per_million, 1) }}/M @endif
                            @if (!$word->frequency_rank && !$word->frequency_per_million) — @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-2 shrink-0 sm:flex-col sm:items-end sm:gap-2 lg:flex-row lg:items-center lg:justify-end lg:gap-3 border-t border-gray-100 pt-4 sm:border-t-0 sm:pt-0">
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
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-12 text-center text-gray-500">
                    No words yet. <a href="{{ route('flashcards.words.create') }}" class="text-indigo-600 hover:underline font-medium">Add one</a> or <a href="{{ route('flashcards.words.bulk-create') }}" class="text-indigo-600 hover:underline font-medium">bulk add</a>.
                </div>
            @endforelse
        </div>

        @if ($words->hasPages())
            <div class="mt-6">{{ $words->links() }}</div>
        @endif
    </div>
</div>
@endsection
