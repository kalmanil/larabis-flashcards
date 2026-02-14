@extends('layouts.app')

@section('title', 'My Cards - Flashcards')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">My Cards ({{ $deck->deckCards->count() }})</h1>
        <a href="{{ route('flashcards.dashboard') }}" class="text-indigo-600 hover:underline">← Dashboard</a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="space-y-4">
        @forelse ($deck->deckCards as $deckCard)
            @php $word = $deckCard->hebrewForm; @endphp
            <div class="bg-white rounded-lg shadow p-4 flex justify-between items-center">
                <div>
                    <span class="text-xl font-semibold" dir="rtl">{{ $word->form_text }}</span>
                    @if ($word->shoresh)
                        <span class="text-gray-500 text-sm ml-2">({{ $word->shoresh->root }})</span>
                    @endif
                    <div class="text-sm text-gray-600 mt-1">
                        @foreach ($word->translations as $t)
                            <span class="inline-block bg-gray-100 px-2 py-0.5 rounded mr-1">{{ $t->text }}</span>
                        @endforeach
                    </div>
                </div>
                <form action="{{ route('flashcards.decks.remove-card', [$deck, $word]) }}" method="POST"
                      onsubmit="return confirm('Remove from deck?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:underline text-sm">Remove</button>
                </form>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                No cards in your deck yet. <a href="{{ route('flashcards.words.index') }}" class="text-indigo-600">Browse words</a> and add some.
            </div>
        @endforelse
    </div>
</div>
@endsection
