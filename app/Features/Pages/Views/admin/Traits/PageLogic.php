<?php

namespace App\Features\Pages\Tenants\flashcards\Views\admin\Traits;

use App\Features\Pages\Tenants\flashcards\Traits\PageLogic as FlashcardsPageLogic;
use App\Helpers\TenancyHelper;

/**
 * Flashcards Admin View-Specific Page Logic
 * 
 * Provides admin view-specific data and logic for the flashcards tenant.
 * This trait extends the tenant-specific trait and adds admin-specific functionality.
 * 
 * This trait is dynamically discovered by TenantTraitRegistry with priority over
 * the tenant-specific trait when in admin view.
 */
trait PageLogic
{
    use FlashcardsPageLogic;
    
    /**
     * Get page data for flashcards admin view
     * This method is called dynamically by TenantTraitRegistry
     * 
     * @param object $caller The controller instance
     * @return array Page data including MySQL connection status for admin
     */
    public static function getPageData(object $caller): array
    {
        // Get base tenant data from tenant-specific trait
        $data = FlashcardsPageLogic::getPageData($caller);
        
        // Add admin-specific data
        $data['adminConfig'] = [
            'view_type' => 'admin',
            'requires_auth' => true,
        ];
        
        // Log connection status to console (for admin view)
        // The connection check is already in FlashcardsPageLogic::getPageData()
        // We'll add a flag to indicate this is for admin view
        if (isset($data['dbConnection'])) {
            $data['dbConnection']['view'] = 'admin';
            $data['dbConnection']['log_to_console'] = true;
        }
        
        return $data;
    }
    
    /**
     * Get admin dashboard data
     * 
     * @param object $caller The controller instance
     * @return array Admin dashboard data
     */
    public static function getAdminDashboardData(object $caller): array
    {
        $connectionData = FlashcardsPageLogic::checkConnection($caller);
        
        return [
            'db_status' => $connectionData,
            'admin_panel' => true,
        ];
    }
}
