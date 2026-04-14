@extends('layouts.app')

@section('title', 'All decks — Staff')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-5xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">All users’ decks</h1>

        <div class="overflow-x-auto border border-gray-200 rounded-lg bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="px-4 py-2 font-semibold">User</th>
                        <th class="px-4 py-2 font-semibold">Deck</th>
                        <th class="px-4 py-2 font-semibold">Cards</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($decks as $deck)
                        <tr class="border-t border-gray-100">
                            <td class="px-4 py-2">
                                <span class="font-medium">{{ $deck->user->name ?? '—' }}</span>
                                <span class="text-gray-500 block text-xs">{{ $deck->user->email ?? '' }}</span>
                            </td>
                            <td class="px-4 py-2">{{ $deck->name }} @if($deck->is_default)<span class="text-xs text-gray-500">(default)</span>@endif</td>
                            <td class="px-4 py-2">{{ $deck->deck_cards_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-gray-500">No decks.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $decks->links() }}
        </div>

        <p class="mt-8 text-sm">
            <a href="{{ route('flashcards.staff.dashboard') }}" class="text-indigo-600 hover:underline">← Staff home</a>
        </p>
    </div>
</div>
@endsection
