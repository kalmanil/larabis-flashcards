@extends('layouts.app')

@section('title', 'Start Learning - Flashcards')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    @include('default.partials.nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-xl mx-auto">
            <h1 class="text-3xl font-bold mb-2 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Start Learning</h1>
            <p class="text-gray-600 mb-8">You have {{ $cardCount }} cards in your deck.</p>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                <form action="{{ route('flashcards.learn.start') }}" method="POST" class="space-y-6">
                    @csrf

                    <input type="hidden" name="lang" value="ru">

                    <div>
                        <label class="block font-medium text-gray-700 mb-3">Question on front</label>
                        <div class="space-y-3">
                            <label class="flex items-center p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                                <input type="radio" name="front_type" value="hebrew" {{ old('front_type', 'hebrew') === 'hebrew' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-3">Hebrew</span>
                            </label>
                            <label class="flex items-center p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                                <input type="radio" name="front_type" value="transcription" {{ old('front_type') === 'transcription' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-3">Transcription</span>
                            </label>
                            <label class="flex items-center p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                                <input type="radio" name="front_type" value="translation" {{ old('front_type') === 'translation' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-3">Translation</span>
                            </label>
                            <label class="flex items-center p-3 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                                <input type="radio" name="front_type" value="random" {{ old('front_type') === 'random' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-3">Random</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-semibold hover:from-indigo-600 hover:to-purple-700 shadow-md transition-all duration-200">
                        Start session
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <form action="{{ route('flashcards.progress.reset') }}" method="POST" onsubmit="return confirm('Reset all your progress?');">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Reset progress</button>
                    </form>
                </div>
            </div>

            <p class="mt-6"><a href="{{ route('flashcards.dashboard') }}" class="text-indigo-600 hover:underline font-medium">← Dashboard</a></p>
        </div>
    </div>
</div>
@endsection
