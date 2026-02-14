@extends('layouts.app')

@section('title', 'Start Learning - Flashcards')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-xl">
    <h1 class="text-3xl font-bold mb-6">Start Learning</h1>
    <p class="text-gray-600 mb-6">You have {{ $cardCount }} cards in your deck.</p>

    <form action="{{ route('flashcards.learn.start') }}" method="POST" class="space-y-6">
        @csrf

        <div>
            <label class="block font-medium text-gray-700 mb-2">Translation language</label>
            <div class="flex gap-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="lang" value="ru" {{ old('lang', 'ru') === 'ru' ? 'checked' : '' }}>
                    <span class="ml-2">Russian</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="lang" value="en" {{ old('lang') === 'en' ? 'checked' : '' }}>
                    <span class="ml-2">English</span>
                </label>
            </div>
        </div>

        <div>
            <label class="block font-medium text-gray-700 mb-2">Question on front</label>
            <div class="space-y-2">
                <label class="block">
                    <input type="radio" name="front_type" value="hebrew" {{ old('front_type', 'hebrew') === 'hebrew' ? 'checked' : '' }}>
                    <span class="ml-2">Hebrew</span>
                </label>
                <label class="block">
                    <input type="radio" name="front_type" value="transcription" {{ old('front_type') === 'transcription' ? 'checked' : '' }}>
                    <span class="ml-2">Transcription</span>
                </label>
                <label class="block">
                    <input type="radio" name="front_type" value="translation" {{ old('front_type') === 'translation' ? 'checked' : '' }}>
                    <span class="ml-2">Translation</span>
                </label>
                <label class="block">
                    <input type="radio" name="front_type" value="random" {{ old('front_type') === 'random' ? 'checked' : '' }}>
                    <span class="ml-2">Random</span>
                </label>
            </div>
        </div>

        <button type="submit" class="w-full py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">
            Start session
        </button>
    </form>

    <div class="mt-8 pt-6 border-t">
        <form action="{{ route('flashcards.progress.reset') }}" method="POST" onsubmit="return confirm('Reset all your progress?');">
            @csrf
            <button type="submit" class="text-red-600 hover:underline text-sm">Reset progress</button>
        </form>
    </div>

    <p class="mt-4"><a href="{{ route('flashcards.dashboard') }}" class="text-indigo-600 hover:underline">← Dashboard</a></p>
</div>
@endsection
