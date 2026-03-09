@extends('layouts.app')

@section('title', 'Learning - Flashcards')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    @include('default.partials.nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl mx-auto">
            <div class="text-sm text-gray-500 mb-4">Card {{ $position }} of {{ $total }}</div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-6 min-h-[220px]">
                <div id="front" class="text-center">
                    <p class="text-3xl font-bold mb-6" dir="{{ $frontType === 'hebrew' ? 'rtl' : 'ltr' }}">{{ $front }}</p>
                    <button type="button" onclick="document.getElementById('front').classList.add('hidden'); document.getElementById('reverse').classList.remove('hidden');"
                            class="px-6 py-3 bg-gray-100 hover:bg-gray-200 rounded-xl font-medium transition-colors">
                        Show answer
                    </button>
                </div>

                <div id="reverse" class="hidden">
                    <div class="space-y-3 text-center mb-8">
                        @if ($frontType !== 'hebrew')
                            <p class="text-2xl font-semibold" dir="rtl">{{ $reverse['hebrew'] }}</p>
                        @endif
                        @if ($frontType !== 'transcription')
                            <p class="text-lg text-gray-600">{{ $reverse['transcription'] }}</p>
                        @endif
                        @if ($frontType !== 'translation')
                            <p class="text-lg text-gray-700">{{ $reverse['translation'] }}</p>
                        @endif
                    </div>

                    <form action="{{ route('flashcards.learn.answer') }}" method="POST" class="flex gap-4 justify-center">
                        @csrf
                        <button type="submit" name="known" value="0" class="px-6 py-3 bg-red-50 text-red-800 rounded-xl hover:bg-red-100 font-medium border border-red-200 transition-colors">
                            Not known
                        </button>
                        <button type="submit" name="known" value="1" class="px-6 py-3 bg-green-50 text-green-800 rounded-xl hover:bg-green-100 font-medium border border-green-200 transition-colors">
                            Known
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center"><a href="{{ route('flashcards.learn.config') }}" class="text-indigo-600 hover:underline text-sm font-medium">End session</a></p>
        </div>
    </div>
</div>
@endsection
