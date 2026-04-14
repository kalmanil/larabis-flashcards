@extends('layouts.app')

@section('title', 'Flashcards - Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-4xl font-bold mb-4">Flashcards</h1>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="mb-4 p-4 bg-blue-100 text-blue-800 rounded-lg">{{ session('info') }}</div>
        @endif

        <nav class="mb-8 p-4 bg-gray-50 border-2 border-gray-200 rounded-xl" aria-label="App menu">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Menu</p>
            <div class="flex flex-wrap gap-x-4 gap-y-2 text-sm font-medium">
                @if(auth()->user()?->isStaff())
                    <a href="{{ route('flashcards.staff.dashboard') }}" class="text-indigo-600 hover:text-indigo-800 hover:underline font-semibold">Staff</a>
                    <span class="text-gray-300" aria-hidden="true">|</span>
                @endif
                <a href="{{ route('flashcards.learn.config') }}" class="text-indigo-600 hover:text-indigo-800 hover:underline">Learn</a>
                <span class="text-gray-300" aria-hidden="true">|</span>
                <a href="{{ route('flashcards.words.index') }}" class="text-indigo-600 hover:text-indigo-800 hover:underline">Words</a>
                <span class="text-gray-300" aria-hidden="true">|</span>
                <a href="{{ route('flashcards.words.bulk-create') }}" class="text-indigo-600 hover:text-indigo-800 hover:underline">Bulk add</a>
                <span class="text-gray-300" aria-hidden="true">|</span>
                <a href="{{ route('flashcards.words.process-pending') }}" class="text-indigo-600 hover:text-indigo-800 hover:underline">Process new words{{ ($pendingEnrichmentCount ?? 0) > 0 ? ' ('.$pendingEnrichmentCount.')' : '' }}</a>
                <span class="text-gray-300" aria-hidden="true">|</span>
                <a href="{{ route('flashcards.words.create') }}" class="text-indigo-600 hover:text-indigo-800 hover:underline">Add word</a>
                <span class="text-gray-300" aria-hidden="true">|</span>
                <a href="{{ route('flashcards.decks.index') }}" class="text-indigo-600 hover:text-indigo-800 hover:underline">Decks</a>
            </div>
        </nav>

        <div class="space-y-4">
            <a href="{{ route('flashcards.learn.config') }}"
               class="block w-full p-6 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-lg text-center font-semibold text-lg transition-colors">
                Start learning
            </a>

            <a href="{{ route('flashcards.words.create') }}"
               class="block w-full p-6 bg-white hover:bg-gray-50 border-2 border-gray-200 rounded-xl shadow text-center font-semibold text-lg transition-colors">
                Add words
            </a>

            <a href="{{ route('flashcards.words.bulk-create') }}"
               class="block w-full p-6 bg-white hover:bg-gray-50 border-2 border-gray-200 rounded-xl shadow text-center font-semibold text-lg transition-colors">
                Bulk add words
            </a>

            <a href="{{ route('flashcards.words.process-pending') }}"
               class="block w-full p-6 bg-white hover:bg-gray-50 border-2 border-gray-200 rounded-xl shadow text-center font-semibold text-lg transition-colors">
                Process new words @if(($pendingEnrichmentCount ?? 0) > 0)
                    <span class="text-indigo-600">({{ $pendingEnrichmentCount }} pending)</span>
                @endif
            </a>

            <a href="{{ route('flashcards.words.index') }}"
               class="block w-full p-6 bg-white hover:bg-gray-50 border-2 border-gray-200 rounded-xl shadow text-center font-semibold text-lg transition-colors">
                Browse words
            </a>

            <a href="{{ route('flashcards.decks.index') }}"
               class="block w-full p-6 bg-white hover:bg-gray-50 border-2 border-gray-200 rounded-xl shadow text-center font-semibold text-lg transition-colors">
                My cards ({{ $cardCount ?? 0 }})
            </a>
        </div>
    </div>
</div>
@endsection
