@extends('layouts.app')

@section('title', 'Learning - Flashcards')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="text-sm text-gray-500 mb-4">Card {{ $position }} of {{ $total }}</div>

    <div class="bg-white rounded-xl shadow-lg p-8 mb-6 min-h-[200px]">
        <div id="front" class="text-center">
            <p class="text-3xl font-bold mb-4" dir="{{ $frontType === 'hebrew' ? 'rtl' : 'ltr' }}">{{ $front }}</p>
            <button type="button" onclick="document.getElementById('front').classList.add('hidden'); document.getElementById('reverse').classList.remove('hidden');"
                    class="px-6 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 font-medium">
                Show answer
            </button>
        </div>

        <div id="reverse" class="hidden">
            <div class="space-y-3 text-center mb-6">
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
                <button type="submit" name="known" value="0" class="px-6 py-3 bg-red-100 text-red-800 rounded-lg hover:bg-red-200 font-medium">
                    Not known
                </button>
                <button type="submit" name="known" value="1" class="px-6 py-3 bg-green-100 text-green-800 rounded-lg hover:bg-green-200 font-medium">
                    Known
                </button>
            </form>
        </div>
    </div>

    <p class="text-center"><a href="{{ route('flashcards.learn.config') }}" class="text-gray-500 hover:underline text-sm">End session</a></p>
</div>
@endsection
