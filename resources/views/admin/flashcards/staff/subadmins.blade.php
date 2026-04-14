@extends('layouts.app')

@section('title', 'Subadmins — Flashcards')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Subadmins</h1>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('status') }}</div>
        @endif

        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8 shadow-sm">
            <h2 class="text-lg font-semibold mb-4">Create subadmin</h2>
            <form method="POST" action="{{ route('flashcards.staff.subadmins.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('password')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Create</button>
            </form>
        </div>

        <h2 class="text-lg font-semibold mb-3">Existing subadmins</h2>
        @if ($subadmins->isEmpty())
            <p class="text-gray-600">None yet.</p>
        @else
            <ul class="divide-y divide-gray-200 border border-gray-200 rounded-lg bg-white">
                @foreach ($subadmins as $u)
                    <li class="flex items-center justify-between px-4 py-3">
                        <div>
                            <span class="font-medium">{{ $u->name }}</span>
                            <span class="text-gray-500 text-sm ml-2">{{ $u->email }}</span>
                        </div>
                        <form method="POST" action="{{ route('flashcards.staff.subadmins.destroy', $u) }}"
                              onsubmit="return confirm('Remove this subadmin?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline text-sm">Remove</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif

        <p class="mt-8 text-sm">
            <a href="{{ route('flashcards.staff.dashboard') }}" class="text-indigo-600 hover:underline">← Staff home</a>
        </p>
    </div>
</div>
@endsection
