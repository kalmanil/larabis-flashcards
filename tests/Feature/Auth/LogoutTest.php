<?php

namespace Tests\Feature\Auth;

use App\Features\Auth\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Logout Feature Tests
 * 
 * SKIPPED: These tests require auth routes to be registered (Phase 5).
 * Unskip these tests after routes are registered in Larabis.
 */
class LogoutTest extends TestCase
{

    /**
     * Skip message for all tests
     */
    protected function skipUntilRoutesRegistered(): void
    {
        $this->markTestSkipped('Auth routes not registered yet. Complete Phase 5 to enable these tests.');
    }

    /**
     * Test authenticated user can logout
     */
    public function test_authenticated_user_can_logout()
    {
        $this->skipUntilRoutesRegistered();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Test logout invalidates session
     */
    public function test_logout_invalidates_session()
    {
        $this->skipUntilRoutesRegistered();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertSessionMissing(Auth::guard()->getName());
    }

    /**
     * Test guest cannot access logout
     */
    public function test_guest_cannot_access_logout()
    {
        $this->skipUntilRoutesRegistered();

        $response = $this->post('/logout');

        // Should redirect or show error
        // This depends on your middleware setup
        $response->assertRedirect();
    }
}
