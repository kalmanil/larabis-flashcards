@extends('layouts.app')

@section('title', 'Flashcards - Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-4xl font-bold mb-6">Flashcards</h1>

        <div class="space-y-4">
            <a href="{{ route('flashcards.learn.config') }}"
               class="block w-full p-6 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-lg text-center font-semibold text-lg transition-colors">
                Start learning
            </a>

            <a href="{{ route('flashcards.words.create') }}"
               class="block w-full p-6 bg-white hover:bg-gray-50 border-2 border-gray-200 rounded-xl shadow text-center font-semibold text-lg transition-colors">
                Add words
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
