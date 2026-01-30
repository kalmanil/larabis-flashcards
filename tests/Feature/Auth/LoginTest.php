<?php

namespace Tests\Feature\Auth;

use App\Features\Auth\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Login Feature Tests
 * 
 * Tests for email/password authentication flow.
 */
class LoginTest extends TestCase
{
    /**
     * Test login form can be displayed
     */
    public function test_login_form_can_be_displayed()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Test user can login with valid credentials
     */
    public function test_user_can_login_with_valid_credentials()
    {
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
     * Test authenticated user can still access login page
     * (No redirect middleware configured for this route)
     */
    public function test_authenticated_user_can_access_login_page()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->get('/login');

        // Without guest middleware, authenticated users can still access login page
        $response->assertStatus(200);
    }
}
