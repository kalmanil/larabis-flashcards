{{-- Hidden ensures add_to_deck is always posted (0 when unchecked). Default on for first visit. --}}
<div>
    <input type="hidden" name="add_to_deck" value="0">
    <label class="inline-flex items-center">
        <input type="checkbox" name="add_to_deck" value="1"
               {{ old('add_to_deck', '1') == '1' ? 'checked' : '' }}
               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
        <span class="ml-2 text-gray-700">Add to my deck</span>
    </label>
</div>
