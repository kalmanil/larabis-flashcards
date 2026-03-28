@extends('layouts.app')

@section('title', 'Bulk add words - Flashcards')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-2">Bulk add words</h1>
    <p class="text-gray-600 mb-6 text-sm">One Hebrew word per line. Words that already exist are skipped. New words are <strong>saved to the database immediately</strong> (Hebrew form only). Use <strong>Process new words</strong> to add at least one <strong>Russian</strong> translation (and other fields).</p>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('flashcards.words.bulk-queue') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block font-medium text-gray-700 mb-1">Words (one per line)</label>
            <textarea name="lines" rows="14" dir="rtl"
                      class="w-full border rounded px-3 py-2 font-mono text-lg"
                      placeholder="שלום&#10;בית">{{ old('lines') }}</textarea>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save words</button>
            <a href="{{ route('flashcards.words.index') }}" class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
