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
            'shoresh_id' => 'nullable|exists:shoresh,id',
            'new_shoresh' => 'nullable|string|max:100',
            'form_type' => 'nullable|string|max:100',
            'transcription_ru' => 'nullable|string|max:255',
            'transcription_en' => 'nullable|string|max:255',
            'frequency_rank' => 'nullable|integer|min:1',
            'frequency_per_million' => 'nullable|numeric|min:0',
            'translation_ids' => 'nullable|array',
            'translation_ids.*' => 'exists:translations,id',
            'new_translations_ru' => 'nullable',
            'new_translations_en' => 'nullable',
        ];
    }
}
