@extends('layouts.app')

@section('title', 'Admin Login - Flashcards')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900 relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>
    </div>
    
    <!-- Animated Background Elements -->
    <div class="absolute top-0 left-0 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
    <div class="absolute top-0 right-0 w-96 h-96 bg-pink-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
    <div class="absolute bottom-0 left-1/2 w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>

    <div class="max-w-md w-full space-y-8 relative z-10">
        <!-- Logo and Header -->
        <div class="text-center">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-indigo-400 to-purple-600 rounded-2xl flex items-center justify-center shadow-2xl mb-6 transform hover:scale-105 transition-transform duration-200">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h2 class="text-4xl font-extrabold text-white mb-2">
                Admin CMS
            </h2>
            <p class="text-indigo-200 text-sm font-medium">
                {{ $config['name'] ?? 'Flashcards' }} Administration Panel
            </p>
            @if(isset($flashcardsConfig))
            <p class="text-indigo-300 text-xs mt-1">Version {{ $flashcardsConfig['version'] ?? '1.0' }}</p>
            @endif
        </div>

        <!-- Login Form Card -->
        <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl p-8 border border-white/20">
            <form class="space-y-6" action="#" method="POST">
                @csrf
                
                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required 
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 text-gray-900 placeholder-gray-400"
                               placeholder="admin@flashcards.test" value="admin@flashcards.test">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password" required 
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 text-gray-900 placeholder-gray-400"
                               placeholder="Enter your password" value="password">
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" 
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>
                    <div class="text-sm">
                        <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors duration-200">
                            Forgot password?
                        </a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center items-center py-3 px-4 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <span>Sign in</span>
                        <svg class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </button>
                </div>

                <!-- Database Connection Status -->
                @if(isset($dbConnection))
                <div class="mt-6 p-4 rounded-xl border {{ $dbConnection['status'] === 'connected' ? 'bg-green-50 border-green-200' : ($dbConnection['status'] === 'error' ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200') }}">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold {{ $dbConnection['status'] === 'connected' ? 'text-green-900' : ($dbConnection['status'] === 'error' ? 'text-red-900' : 'text-yellow-900') }}">
                            Database Status
                        </h3>
                        @if($dbConnection['status'] === 'connected')
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-1 animate-pulse"></span>
                                Connected
                            </span>
                        @elseif($dbConnection['status'] === 'error')
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Error
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Warning
                            </span>
                        @endif
                    </div>
                    @if($dbConnection['status'] === 'connected')
                        <div class="text-xs text-green-700 space-y-1">
                            <div class="flex justify-between">
                                <span>Database:</span>
                                <span class="font-mono font-semibold">{{ $dbConnection['database'] ?? 'N/A' }}</span>
                            </div>
                            @if(isset($dbConnection['timestamp']))
                            <div class="flex justify-between text-green-600">
                                <span>Checked:</span>
                                <span>{{ $dbConnection['timestamp'] }}</span>
                            </div>
                            @endif
                        </div>
                    @elseif($dbConnection['status'] === 'error')
                        <p class="text-xs text-red-700">{{ $dbConnection['message'] ?? 'Unknown error' }}</p>
                    @else
                        <p class="text-xs text-yellow-700">{{ $dbConnection['message'] ?? 'Tenancy not initialized' }}</p>
                    @endif
                </div>
                @endif

                <!-- Demo Notice -->
                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-xl">
                    <p class="text-xs text-blue-800 text-center">
                        <strong>Demo Mode:</strong> This is a demonstration login page. Authentication not implemented.
                    </p>
                </div>
            </form>
        </div>

        <!-- Back to Home Link -->
        <div class="text-center">
            <a href="http://flashcards.test:8000" 
               class="inline-flex items-center text-sm font-medium text-white/80 hover:text-white transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to {{ $config['name'] ?? 'Flashcards' }} Home
            </a>
        </div>
    </div>

    <!-- Console Log Script -->
    @if(isset($dbConnection) && isset($dbConnection['log_to_console']) && $dbConnection['log_to_console'])
    <script>
        console.log('%cFlashcards Admin - Database Connection Status', 'color: #6366f1; font-weight: bold; font-size: 14px;');
        console.log({
            status: '{{ $dbConnection['status'] ?? 'unknown' }}',
            database: '{{ $dbConnection['database'] ?? 'N/A' }}',
            message: '{{ $dbConnection['message'] ?? 'N/A' }}',
            timestamp: '{{ $dbConnection['timestamp'] ?? 'N/A' }}',
            view: '{{ $dbConnection['view'] ?? 'admin' }}'
        });
    </script>
    @endif

    <!-- Animation Styles -->
    <style>
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }
    </style>
</div>
@endsection

