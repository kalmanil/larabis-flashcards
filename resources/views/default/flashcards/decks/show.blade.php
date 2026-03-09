@extends('layouts.app')

@section('title', 'My Cards - Flashcards')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    @include('default.partials.nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">My Cards ({{ $deck->deckCards->count() }})</h1>
            <a href="{{ route('flashcards.dashboard') }}" class="px-4 py-2 text-indigo-600 hover:text-indigo-800 font-medium transition-colors">← Dashboard</a>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">{{ session('success') }}</div>
        @endif

        <div class="space-y-4">
            @forelse ($deck->deckCards as $deckCard)
                @php $word = $deckCard->hebrewForm; @endphp
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5 flex justify-between items-center hover:shadow-xl transition-shadow">
                    <div>
                        <span class="text-xl font-semibold" dir="rtl">{{ $word->form_text }}</span>
                        @if ($word->shoresh)
                            <span class="text-gray-500 text-sm ml-2">({{ $word->shoresh->root }})</span>
                        @endif
                        <div class="text-sm text-gray-600 mt-1">
                            @foreach ($word->translations as $t)
                                <span class="inline-block bg-indigo-50 text-indigo-800 px-2 py-0.5 rounded text-xs mr-1">{{ $t->text }}</span>
                            @endforeach
                        </div>
                    </div>
                    <form action="{{ route('flashcards.decks.remove-card', [$deck, $word]) }}" method="POST"
                          onsubmit="return confirm('Remove from deck?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 text-red-600 hover:bg-red-50 rounded-lg text-sm font-medium transition-colors">Remove</button>
                    </form>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-12 text-center text-gray-500">
                    No cards in your deck yet. <a href="{{ route('flashcards.words.index') }}" class="text-indigo-600 hover:underline font-medium">Browse words</a> and add some.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
