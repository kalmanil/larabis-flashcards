@extends('layouts.app')

@section('title', 'Process new words - Flashcards')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    @include('default.partials.nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold mb-2 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Process new words</h1>
            <p class="text-gray-600 mb-6">
                Word <span class="font-semibold">{{ $pendingPosition }}</span> of <span class="font-semibold">{{ $pendingTotal }}</span> pending enrichment
                <span class="mx-2 text-gray-400">·</span>
                <span dir="rtl" class="font-medium">{{ $word->form_text }}</span>
            </p>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">{{ session('success') }}</div>
                @endif

                @php
                    extract(\App\Features\Flashcards\View\WordFormTheme::variables('default'));
                @endphp

                <form action="{{ route('flashcards.words.update', $word) }}" method="POST" class="{{ $wordFormSpacing }}">
                    @csrf
                    @method('PUT')

                    @include('default.flashcards.words.partials.word-form-fields', [
                        'word' => $word,
                        'formTextReadonly' => true,
                    ])

                    @include('default.flashcards.words.partials.word-form-add-to-deck')

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit" name="enrichment_flow" value="1" class="{{ $wordFormBtnSubmit }}">Save and continue</button>
                        <button type="submit" class="{{ $wordFormBtnSecondary }}">Save and exit</button>
                        <a href="{{ route('flashcards.dashboard') }}" class="{{ $wordFormBtnSecondary }}">Exit without saving</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('default.flashcards.words.partials.word-form-scripts')
@endsection
