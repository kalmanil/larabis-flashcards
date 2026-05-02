<?php

namespace App\Features\Flashcards\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportExtraSenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'form_text' => ['required', 'string', 'max:255'],
            'source' => ['required', 'in:db,gemini,openai'],
            'existing_translations' => ['nullable', 'array'],
            'existing_translations.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
