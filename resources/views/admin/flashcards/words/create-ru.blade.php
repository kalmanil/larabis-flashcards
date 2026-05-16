@extends('layouts.app')

@section('title', 'Добавить слово - Flashcards')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">Добавить слово</h1>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include(\App\Helpers\TenancyHelper::getViewPathForCode('default', 'flashcards.words.partials.word-form'), [
        'theme' => 'admin',
        'wordFormInputMode' => 'russian',
    ])
</div>
@endsection
