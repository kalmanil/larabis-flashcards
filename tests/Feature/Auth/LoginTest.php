<?php

namespace Tests\Feature\Auth;

use App\Features\Auth\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Login Feature Tests
 * 
 * SKIPPED: These tests require auth routes to be registered (Phase 5).
 * Unskip these tests after routes are registered in Larabis.
 */
class LoginTest extends TestCase
{

    /**
     * Skip message for all tests
     */
    protected function skipUntilRoutesRegistered(): void
    {
        $this->markTestSkipped('Auth routes not registered yet. Complete Phase 5 to enable these tests.');
    }

    /**
     * Test login form can be displayed
     */
    public function test_login_form_can_be_displayed()
    {
        $this->skipUntilRoutesRegistered();

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Test user can login with valid credentials
     */
    public function test_user_can_login_with_valid_credentials()
    {
        $this->skipUntilRoutesRegistered();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test user cannot login with invalid credentials
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $this->skipUntilRoutesRegistered();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Test user cannot login with non-existent email
     */
    public function test_user_cannot_login_with_nonexistent_email()
    {
        $this->skipUntilRoutesRegistered();

        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Test login requires email field
     */
    public function test_login_requires_email()
    {
        $this->skipUntilRoutesRegistered();

        $response = $this->post('/login', [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test login requires password field
     */
    public function test_login_requires_password()
    {
        $this->skipUntilRoutesRegistered();

        $response = $this->post('/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * Test remember me functionality
     */
    public function test_user_can_login_with_remember_me()
    {
        $this->skipUntilRoutesRegistered();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);

        // Check that remember token cookie is set
        $this->assertNotNull($user->fresh()->remember_token);
    }

    /**
     * Test authenticated user cannot access login page
     */
    public function test_authenticated_user_cannot_access_login_page()
    {
        $this->skipUntilRoutesRegistered();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->get('/login');

        // Should redirect authenticated users (if middleware is set up)
        $response->assertRedirect();
    }
}
