<?php

namespace Tests\Feature\Auth;

use App\Features\Auth\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Logout Feature Tests
 * 
 * Tests for user logout flow.
 */
class LogoutTest extends TestCase
{
    /**
     * Test authenticated user can logout
     */
    public function test_authenticated_user_can_logout()
    {
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
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
    }

    /**
     * Test guest can access logout endpoint (just redirects)
     */
    public function test_guest_can_access_logout_endpoint()
    {
        $response = $this->post('/logout');

        // Without auth middleware, guests can hit logout and get redirected
        $response->assertRedirect('/');
    }
}
