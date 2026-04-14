@extends('layouts.app')

@section('title', 'Staff — Flashcards')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-2">Staff</h1>
        <p class="text-gray-600 mb-6">Tenant management (subadmin tools). Superadmin-only actions are listed separately.</p>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('status') }}</div>
        @endif

        <ul class="space-y-3 mb-8">
            <li>
                <a href="{{ route('flashcards.staff.decks.index') }}" class="text-indigo-600 hover:underline font-medium">All users’ decks</a>
                <span class="text-gray-500 text-sm"> — overview with card counts</span>
            </li>
            <li>
                <a href="{{ route('flashcards.staff.words.export.ndjson') }}" class="text-indigo-600 hover:underline font-medium">Export word pool (NDJSON)</a>
            </li>
            @if ($isSuperAdmin)
                <li>
                    <a href="{{ route('flashcards.staff.subadmins.index') }}" class="text-indigo-600 hover:underline font-medium">Manage subadmins</a>
                    <span class="text-gray-500 text-sm"> — {{ $subadminCount }} subadmin(s)</span>
                </li>
            @endif
        </ul>

        <p class="text-sm text-gray-500">
            <a href="{{ route('flashcards.dashboard') }}" class="text-indigo-600 hover:underline">Back to app dashboard</a>
        </p>
    </div>
</div>
@endsection
