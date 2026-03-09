@extends('layouts.app')

@section('title', 'Session Complete - Flashcards')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    @include('default.partials.nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-xl mx-auto text-center">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-12">
                <h1 class="text-3xl font-bold mb-4 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Session complete!</h1>
                <p class="text-gray-600 mb-8">You've gone through all cards in this session.</p>
                <a href="{{ route('flashcards.learn.config') }}" class="inline-block px-8 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-semibold hover:from-indigo-600 hover:to-purple-700 shadow-md transition-all duration-200">
                    Start another session
                </a>
            </div>
            <p class="mt-6"><a href="{{ route('flashcards.dashboard') }}" class="text-indigo-600 hover:underline font-medium">← Dashboard</a></p>
        </div>
    </div>
</div>
@endsection
