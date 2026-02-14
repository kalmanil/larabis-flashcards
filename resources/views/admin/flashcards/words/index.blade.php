@extends('layouts.app')

@section('title', 'Browse Words - Flashcards')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Browse Words</h1>
        <a href="{{ route('flashcards.words.create') }}"
           class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
            Add word
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif

    <form method="GET" class="mb-6 flex gap-4 flex-wrap">
        <select name="shoresh_id" class="border rounded px-3 py-2">
            <option value="">All roots</option>
            @foreach ($shoreshim as $s)
                <option value="{{ $s->id }}" {{ request('shoresh_id') == $s->id ? 'selected' : '' }}>{{ $s->root }}</option>
            @endforeach
        </select>
        <select name="language" class="border rounded px-3 py-2">
            <option value="">All languages</option>
            <option value="ru" {{ request('language') == 'ru' ? 'selected' : '' }}>Russian</option>
            <option value="en" {{ request('language') == 'en' ? 'selected' : '' }}>English</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Filter</button>
    </form>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Hebrew</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Root</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Transcription</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Translations</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Freq</th>
                    <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($words as $word)
                    <tr>
                        <td class="px-4 py-2 text-lg" dir="rtl">{{ $word->form_text }}</td>
                        <td class="px-4 py-2">{{ $word->shoresh?->root ?? '—' }}</td>
                        <td class="px-4 py-2 text-sm">{{ $word->transcription_ru ?? $word->transcription_en ?? '—' }}</td>
                        <td class="px-4 py-2 text-sm">
                            @foreach ($word->translations as $t)
                                <span class="inline-block bg-gray-100 px-2 py-0.5 rounded text-xs mr-1">{{ $t->text }}</span>
                            @endforeach
                            @if ($word->translations->isEmpty()) — @endif
                        </td>
                        <td class="px-4 py-2 text-sm">
                            @if ($word->frequency_rank) #{{ $word->frequency_rank }} @endif
                            @if ($word->frequency_per_million) {{ number_format($word->frequency_per_million, 1) }}/M @endif
                            @if (!$word->frequency_rank && !$word->frequency_per_million) — @endif
                        </td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('flashcards.words.edit', $word) }}" class="text-indigo-600 hover:underline text-sm">Edit</a>
                            <form action="{{ route('flashcards.words.add-to-deck', $word) }}" method="POST" class="inline ml-2">
                                @csrf
                                <button type="submit" class="text-green-600 hover:underline text-sm">Add to deck</button>
                            </form>
                            <form action="{{ route('flashcards.words.destroy', $word) }}" method="POST" class="inline ml-2"
                                  onsubmit="return confirm('Delete this word?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No words yet. <a href="{{ route('flashcards.words.create') }}" class="text-indigo-600">Add one</a>.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($words->hasPages())
        <div class="mt-4">{{ $words->links() }}</div>
    @endif
</div>
@endsection
