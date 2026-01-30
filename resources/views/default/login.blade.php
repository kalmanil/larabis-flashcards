@extends('layouts.app')

@section('title', 'Login - Flashcards')

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
            <h2 class="text-4xl font-extrabold text-white mb-2">
                Welcome Back
            </h2>
            <p class="text-indigo-200 text-sm font-medium">
                Sign in to {{ $config['name'] ?? 'Flashcards' }}
            </p>
        </div>

        <!-- Login Form Card -->
        <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl p-8 border border-white/20">
            <!-- Error Messages -->
            @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form class="space-y-6" action="{{ route('login') }}" method="POST">
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
                               placeholder="you@example.com" value="{{ old('email') }}">
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
                               placeholder="Enter your password">
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

                <!-- Register Link -->
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors duration-200">
                            Sign up
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Back to Home Link -->
        <div class="text-center">
            <a href="/" 
               class="inline-flex items-center text-sm font-medium text-white/80 hover:text-white transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to {{ $config['name'] ?? 'Flashcards' }} Home
            </a>
        </div>
    </div>

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
