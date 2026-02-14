@extends('layouts.app')

@section('title', 'Session Complete - Flashcards')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-xl text-center">
    <h1 class="text-3xl font-bold mb-4">Session complete!</h1>
    <p class="text-gray-600 mb-6">You've gone through all cards in this session.</p>
    <a href="{{ route('flashcards.learn.config') }}" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">
        Start another session
    </a>
    <p class="mt-4"><a href="{{ route('flashcards.dashboard') }}" class="text-indigo-600 hover:underline">← Dashboard</a></p>
</div>
@endsection
