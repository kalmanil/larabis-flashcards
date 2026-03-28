@extends('layouts.app')

@section('title', 'Flashcards - Dashboard')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    @include('default.partials.nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-4xl font-bold mb-4 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Flashcards</h1>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">{{ session('success') }}</div>
            @endif
            @if (session('info'))
                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl">{{ session('info') }}</div>
            @endif

            <nav class="mb-8 p-4 bg-white/90 border border-gray-100 rounded-2xl shadow-sm" aria-label="App menu">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Menu</p>
                <div class="flex flex-wrap gap-x-4 gap-y-2 text-sm font-medium">
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
                   class="block w-full p-6 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-2xl shadow-lg text-center font-semibold text-lg transition-all duration-200 hover:shadow-xl transform hover:-translate-y-0.5">
                    Start learning
                </a>

                <a href="{{ route('flashcards.words.create') }}"
                   class="block w-full p-6 bg-white hover:bg-gray-50 border border-gray-100 rounded-2xl shadow-lg text-center font-semibold text-lg transition-all duration-200 hover:shadow-xl transform hover:-translate-y-0.5">
                    Add words
                </a>

                <a href="{{ route('flashcards.words.bulk-create') }}"
                   class="block w-full p-6 bg-white hover:bg-gray-50 border border-gray-100 rounded-2xl shadow-lg text-center font-semibold text-lg transition-all duration-200 hover:shadow-xl transform hover:-translate-y-0.5">
                    Bulk add words
                </a>

                <a href="{{ route('flashcards.words.process-pending') }}"
                   class="block w-full p-6 bg-white hover:bg-gray-50 border border-gray-100 rounded-2xl shadow-lg text-center font-semibold text-lg transition-all duration-200 hover:shadow-xl transform hover:-translate-y-0.5">
                    Process new words @if(($pendingEnrichmentCount ?? 0) > 0)
                        <span class="text-indigo-600">({{ $pendingEnrichmentCount }} pending)</span>
                    @endif
                </a>

                <a href="{{ route('flashcards.words.index') }}"
                   class="block w-full p-6 bg-white hover:bg-gray-50 border border-gray-100 rounded-2xl shadow-lg text-center font-semibold text-lg transition-all duration-200 hover:shadow-xl transform hover:-translate-y-0.5">
                    Browse words
                </a>

                <a href="{{ route('flashcards.decks.index') }}"
                   class="block w-full p-6 bg-white hover:bg-gray-50 border border-gray-100 rounded-2xl shadow-lg text-center font-semibold text-lg transition-all duration-200 hover:shadow-xl transform hover:-translate-y-0.5">
                    My cards ({{ $cardCount ?? 0 }})
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
