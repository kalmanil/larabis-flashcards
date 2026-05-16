<?php

namespace App\Features\Flashcards\Http\Requests;

use App\Features\Flashcards\Support\FormTypeCatalog;
use Illuminate\Foundation\Http\FormRequest;

class StoreHebrewFormsFromRussianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'russian_gloss' => 'required|string|max:255',
            'hebrew_forms' => 'required|array|min:1',
            'hebrew_forms.*.form_text' => 'nullable|string|max:255',
            'hebrew_forms.*.shoresh_root' => 'nullable|string|max:100',
            'hebrew_forms.*.transcription_ru' => 'nullable|string|max:255',
            'hebrew_forms.*.form_type' => [
                'nullable',
                'string',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    if (! is_string($value)) {
                        return;
                    }
                    if (FormTypeCatalog::canonical($value) === null) {
                        $fail(__('validation.in', ['attribute' => $attribute]));
                    }
                },
            ],
            'hebrew_forms.*.frequency_rank' => 'nullable|integer|min:1',
            'hebrew_forms.*.frequency_per_million' => 'nullable|numeric|min:0',
            'hebrew_forms.*.add_to_deck' => 'nullable|in:0,1',
            'save_continue' => 'nullable|in:0,1',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function nonEmptyHebrewRows(): array
    {
        $rows = [];
        foreach ($this->input('hebrew_forms', []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            if (trim((string) ($row['form_text'] ?? '')) === '') {
                continue;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->nonEmptyHebrewRows() === []) {
                $validator->errors()->add('hebrew_forms', 'Add at least one Hebrew form.');
            }
        });
    }
}
