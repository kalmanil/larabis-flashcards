@extends('layouts.app')

@section('title', 'Flashcards - Dashboard')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    @include('default.partials.nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-4xl font-bold mb-8 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Flashcards</h1>

            <div class="space-y-4">
                <a href="{{ route('flashcards.learn.config') }}"
                   class="block w-full p-6 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-2xl shadow-lg text-center font-semibold text-lg transition-all duration-200 hover:shadow-xl transform hover:-translate-y-0.5">
                    Start learning
                </a>

                <a href="{{ route('flashcards.words.create') }}"
                   class="block w-full p-6 bg-white hover:bg-gray-50 border border-gray-100 rounded-2xl shadow-lg text-center font-semibold text-lg transition-all duration-200 hover:shadow-xl transform hover:-translate-y-0.5">
                    Add words
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
