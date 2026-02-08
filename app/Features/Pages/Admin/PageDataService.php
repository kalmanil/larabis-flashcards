<?php

namespace App\Features\Pages\Tenants\flashcards\Admin;

use App\Features\Pages\Base\Admin\PageDataService as BaseAdminPageDataService;
use App\Features\Pages\Tenants\flashcards\Default\PageDataService as FlashcardsDefaultPageDataService;
use App\Tenancy\TenantContext;

/**
 * Flashcards admin view page data.
 */
class PageDataService extends BaseAdminPageDataService
{
    protected FlashcardsDefaultPageDataService $defaultService;

    public function __construct(TenantContext $tenantContext, FlashcardsDefaultPageDataService $defaultService)
    {
        parent::__construct($tenantContext);
        $this->defaultService = $defaultService;
    }

    public function getPageData(): array
    {
        $defaultData = $this->defaultService->getPageData();
        $adminData = parent::getPageData();
        $data = array_merge($defaultData, $adminData);
        $data['adminConfig'] = array_merge($data['adminConfig'] ?? [], [
            'view_type' => 'admin',
            'requires_auth' => true,
        ]);
        if (isset($data['dbConnection'])) {
            $data['dbConnection']['view'] = 'admin';
            $data['dbConnection']['log_to_console'] = true;
        }
        return $data;
    }

    public function getAdminDashboardData(): array
    {
        $connectionData = $this->defaultService->getPageData()['dbConnection'] ?? ['status' => 'unknown'];
        return [
            'db_status' => $connectionData,
            'admin_panel' => true,
            'stats' => [],
            'recent_activity' => [],
            'notifications' => [],
        ];
    }
}
