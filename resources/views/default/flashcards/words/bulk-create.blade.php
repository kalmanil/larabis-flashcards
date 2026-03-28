@extends('layouts.app')

@section('title', 'Bulk add words - Flashcards')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    @include('default.partials.nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold mb-2 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Bulk add words</h1>
            <p class="text-gray-600 mb-6 text-sm">One Hebrew word per line. Words that already exist are skipped. New words are <strong>saved to the database immediately</strong> (Hebrew form only). Use <strong>Process new words</strong> on the dashboard to add at least one <strong>Russian</strong> translation (and other fields)—until then they stay in the processing queue.</p>

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

                <form action="{{ route('flashcards.words.bulk-queue') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Words (one per line)</label>
                        <textarea name="lines" rows="14" dir="rtl"
                                  class="w-full border border-gray-200 rounded-xl px-3 py-2 font-mono text-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="שלום&#10;בית">{{ old('lines') }}</textarea>
                    </div>
                    <div class="flex gap-4 pt-2">
                        <button type="submit" class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 shadow-md transition-all duration-200">Save words</button>
                        <a href="{{ route('flashcards.words.index') }}" class="px-6 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-medium transition-colors">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
