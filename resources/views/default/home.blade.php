@extends('layouts.app')

@section('title', 'Home - ' . ucfirst('flashcards'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-4">Welcome to {{ ucfirst($tenant->id ?? 'flashcards') }}</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-semibold mb-4">{{ ucfirst($view->name ?? 'default') }} View</h2>
            <p class="text-gray-600 mb-4">
                This is the <strong>{{ $view->name ?? 'default' }}</strong> view for the <strong>{{ $tenant->id ?? 'flashcards' }}</strong> tenant.
            </p>
            
            <div class="bg-blue-50 border border-blue-200 rounded p-4">
                <h3 class="font-semibold text-blue-900 mb-2">Current Context:</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li><strong>Tenant:</strong> {{ $tenant->id ?? 'flashcards' }}</li>
                    <li><strong>View:</strong> {{ $view->name ?? 'default' }}</li>
                    <li><strong>Code:</strong> {{ $view->code ?? 'default' }}</li>
                    <li><strong>Domain:</strong> {{ $view->domain ?? request()->getHost() }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection