@extends('layouts.app')

@section('title', 'Home - Flashcards')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-10 border-b border-indigo-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Flashcards
                        </h1>
                        <p class="text-xs text-gray-500">Learning Platform</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('register') }}" 
                       class="px-4 py-2 text-indigo-600 hover:text-indigo-800 font-medium transition-colors duration-200">
                        Sign up
                    </a>
                    <a href="{{ route('login') }}" 
                       class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg font-medium hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h1 class="text-5xl md:text-6xl font-extrabold mb-4 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
                Welcome to Flashcards
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Master your learning with our intelligent flashcard system. Create, study, and track your progress.
            </p>
        </div>

        <!-- Feature Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <!-- System Status Card -->
            @if(isset($dbConnection))
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">System Status</h3>
                    @if($dbConnection['status'] === 'connected')
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    @elseif($dbConnection['status'] === 'error')
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    @else
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    @endif
                </div>
                
                @if($dbConnection['status'] === 'connected')
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2 text-green-700">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">Database Connected</span>
                        </div>
                        <div class="bg-green-50 rounded-lg p-3 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Database:</span>
                                <span class="font-mono text-green-700 font-semibold">{{ $dbConnection['database'] ?? 'N/A' }}</span>
                            </div>
                            @if(isset($dbConnection['timestamp']))
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>Checked:</span>
                                <span>{{ $dbConnection['timestamp'] }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                @elseif($dbConnection['status'] === 'error')
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2 text-red-700">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">Connection Error</span>
                        </div>
                        <div class="bg-red-50 rounded-lg p-3">
                            <p class="text-sm text-red-700">{{ $dbConnection['message'] ?? 'Unknown error' }}</p>
                        </div>
                    </div>
                @else
                    <div class="flex items-center space-x-2 text-yellow-700">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">{{ $dbConnection['message'] ?? 'Not Initialized' }}</span>
                    </div>
                @endif
            </div>
            @endif

            <!-- Context Card -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Current Context</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Tenant:</span>
                        <span class="font-semibold text-indigo-600">{{ $tenant->id ?? 'flashcards' }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">View:</span>
                        <span class="font-semibold text-purple-600">{{ $view->name ?? 'default' }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Code:</span>
                        <span class="font-mono text-xs text-gray-700 bg-gray-50 px-2 py-1 rounded">{{ $view->code ?? 'default' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Domain:</span>
                        <span class="font-mono text-xs text-gray-700">{{ $view->domain ?? request()->getHost() }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Card -->
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <h3 class="text-lg font-semibold mb-4">Quick Stats</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-indigo-100">Status</span>
                        <span class="font-bold text-lg">{{ isset($flashcardsConfig) ? $flashcardsConfig['version'] ?? '1.0' : '1.0' }}</span>
                    </div>
                    <div class="h-px bg-white/20"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-indigo-100">Platform</span>
                        <span class="font-semibold">Active</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-12">
            <a href="{{ route('register') }}" 
               class="w-full sm:w-auto px-8 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                <span>Create Account</span>
            </a>
            <a href="{{ route('login') }}" 
               class="w-full sm:w-auto px-8 py-3 bg-white text-indigo-600 border-2 border-indigo-500 rounded-xl font-semibold hover:bg-indigo-50 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
                <span>Sign In</span>
            </a>
        </div>
    </div>
</div>
@endsection