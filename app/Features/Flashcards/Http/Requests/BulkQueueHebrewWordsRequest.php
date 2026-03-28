<?php

namespace App\Features\Flashcards\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkQueueHebrewWordsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lines' => 'required|string|max:500000',
        ];
    }
}
