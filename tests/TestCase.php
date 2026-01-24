<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        // Path to Larabis root
        $larabisPath = dirname(__DIR__, 3);
        
        // Load Laravel application
        $app = require $larabisPath . '/bootstrap/app.php';

        // Bootstrap the application
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure tenant environment variables are set
        if (!isset($_ENV['DOMAIN_TENANT_ID'])) {
            $_ENV['DOMAIN_TENANT_ID'] = 'flashcards';
            $_ENV['DOMAIN_CODE'] = 'default';
            $_ENV['DOMAIN_VIEW_TYPE'] = 'default';
            
            putenv('DOMAIN_TENANT_ID=flashcards');
            putenv('DOMAIN_CODE=default');
            putenv('DOMAIN_VIEW_TYPE=default');
        }

        // Create tenant-specific tables after RefreshDatabase has run
        $this->createTenantTables();
    }

    /**
     * Create tenant-specific tables for testing.
     * 
     * These tables exist in MySQL (tenant_flashcards) in production,
     * but for fast unit testing we create them in SQLite in-memory.
     */
    protected function createTenantTables(): void
    {
        // Drop and recreate to ensure clean state
        Schema::dropIfExists('social_accounts');
        
        Schema::create('social_accounts', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider');
            $table->string('provider_id');
            $table->string('access_token')->nullable();
            $table->string('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_id']);
        });
    }
}
