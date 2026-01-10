<?php

namespace App\Features\Pages\Tenants\flashcards\Traits;

use App\Shared\Traits\Base\PageLogic as BasePageLogic;
use App\Helpers\TenancyHelper;

/**
 * Flashcards Tenant-Specific Page Logic
 * 
 * Provides tenant-specific data and logic for the flashcards tenant.
 * This trait is dynamically discovered by TenantTraitRegistry.
 */
trait PageLogic
{
    use BasePageLogic;
    
    protected $flashcardsConfig;
    
    /**
     * Get page data for flashcards tenant
     * This method is called dynamically by TenantTraitRegistry
     * 
     * @param object $caller The controller instance
     * @return array Page data including MySQL connection status
     */
    public static function getPageData(object $caller): array
    {
        return [
            'flashcardsConfig' => self::getFlashcardsConfig(),
            'dbConnection' => self::checkConnection($caller),
        ];
    }
    
    /**
     * Get flashcards tenant configuration
     * 
     * @return array Configuration array
     */
    protected static function getFlashcardsConfig(): array
    {
        return [
            'name' => 'Flashcards',
            'version' => '1.0',
            'description' => 'Flashcard learning application',
        ];
    }
    
    /**
     * Check MySQL database connection for flashcards tenant
     * 
     * @param object $caller The controller instance
     * @return array Connection status information
     */
    public static function checkConnection(object $caller): array
    {
        try {
            // Check if we're in tenant context
            if (!TenancyHelper::isTenantContext()) {
                return [
                    'status' => 'not_initialized',
                    'message' => 'Tenancy not initialized',
                ];
            }
            
            // Try to query the tenant database
            // The connection name 'mysql' is configured in config/database.php
            // and is automatically set to tenant_{tenant_id} by stancl/tenancy
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
}
