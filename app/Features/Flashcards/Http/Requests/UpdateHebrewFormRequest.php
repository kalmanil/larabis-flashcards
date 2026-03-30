<?php

namespace App\Features\Flashcards\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHebrewFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'form_text' => 'required|string|max:255',
            'shoresh_root' => 'nullable|string|max:100',
            'transcription_ru' => 'nullable|string|max:255',
            'frequency_rank' => 'nullable|integer|min:1',
            'frequency_per_million' => 'nullable|numeric|min:0',
            'new_entries' => 'nullable|array',
            'new_entries.*.translation_ru' => 'nullable|string|max:255',
            'new_entries.*.form_type' => 'nullable|string|max:100',
            'new_entries.*.transcription_ru' => 'nullable|string|max:255',
            'enrichment_flow' => 'nullable|boolean',
        ];
    }
}
