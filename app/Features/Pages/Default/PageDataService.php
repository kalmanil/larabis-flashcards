<?php

namespace App\Features\Pages\Tenants\flashcards\Default;

use App\Contracts\GeoCountryResolver;
use App\Features\Pages\Base\Default\PageDataService as BasePageDataService;
use App\Helpers\TenancyHelper;
use App\Tenancy\TenantContext;
use Illuminate\Http\Request;

/**
 * Flashcards default (landing) view page data.
 */
class PageDataService extends BasePageDataService
{
    public function __construct(
        TenantContext $tenantContext,
        protected Request $request,
        protected GeoCountryResolver $geoCountryResolver
    )
    {
        parent::__construct($tenantContext);
    }

    public function getPageData(): array
    {
        $base = parent::getPageData();
        return array_merge($base, [
            'flashcardsConfig' => $this->getFlashcardsConfig(),
            'dbConnection' => $this->checkConnection(),
            'visitorGeo' => $this->getVisitorGeo(),
        ]);
    }

    protected function getFlashcardsConfig(): array
    {
        return [
            'name' => 'Flashcards',
            'version' => '1.0',
            'description' => 'Flashcard learning application',
        ];
    }

    protected function checkConnection(): array
    {
        try {
            if (!TenancyHelper::isTenantContext()) {
                return [
                    'status' => 'not_initialized',
                    'message' => 'Tenancy not initialized',
                ];
            }
            \DB::connection('mysql')->select('SELECT 1 as test');
            $databaseName = \DB::connection('mysql')->getDatabaseName();
            return [
                'status' => 'connected',
                'database' => $databaseName,
                'message' => 'MySQL connection successful',
                'timestamp' => now()->toDateTimeString(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'error_type' => get_class($e),
                'timestamp' => now()->toDateTimeString(),
            ];
        }
    }

    protected function getVisitorGeo(): array
    {
        return [
            'ip' => $this->request->ip(),
            'country' => $this->geoCountryResolver->resolveCountryCode($this->request),
        ];
    }
}
