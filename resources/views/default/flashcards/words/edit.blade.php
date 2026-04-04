@extends('layouts.app')

@section('title', 'Edit Word - Flashcards')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    @include('default.partials.nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Edit Word</h1>

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

                @php
                    extract(\App\Features\Flashcards\View\WordFormTheme::variables('default'));
                @endphp

                <form action="{{ route('flashcards.words.update', $word) }}" method="POST" class="{{ $wordFormSpacing }}">
                    @csrf
                    @method('PUT')

                    @include('default.flashcards.words.partials.word-form-fields', [
                        'word' => $word,
                        'formTextReadonly' => false,
                    ])

                    @include('default.flashcards.words.partials.word-form-add-to-deck')

                    <div class="flex gap-4 pt-2">
                        <button type="submit" class="{{ $wordFormBtnSubmit }}">Update</button>
                        <a href="{{ route('flashcards.words.index') }}" class="{{ $wordFormBtnSecondary }}">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('default.flashcards.words.partials.word-form-scripts')
@endsection
