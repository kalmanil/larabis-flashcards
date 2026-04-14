<?php

namespace App\Features\Flashcards\Http\Controllers\Staff;

use App\Features\Flashcards\Models\HebrewForm;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Tenant-wide word pool export (JSON). Subadmin subset includes export; superadmin same.
 */
class StaffWordExportController
{
    public function json(): StreamedResponse
    {
        $filename = 'hebrew-forms-'.now()->format('Y-m-d-His').'.ndjson';

        return response()->streamDownload(function () {
            HebrewForm::query()
                ->with(['shoresh', 'translations.language'])
                ->orderBy('id')
                ->chunk(200, function ($chunk) {
                    foreach ($chunk as $form) {
                        echo json_encode($form->toArray(), JSON_UNESCAPED_UNICODE)."\n";
                    }
                });
        }, $filename, [
            'Content-Type' => 'application/x-ndjson',
        ]);
    }
}
