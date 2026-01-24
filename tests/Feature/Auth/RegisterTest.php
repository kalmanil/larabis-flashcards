<?php

namespace Tests\Feature\Auth;

use App\Features\Auth\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Registration Feature Tests
 * 
 * SKIPPED: These tests require auth routes to be registered (Phase 5).
 * Unskip these tests after routes are registered in Larabis.
 */
class RegisterTest extends TestCase
{

    /**
     * Skip message for all tests
     */
    protected function skipUntilRoutesRegistered(): void
    {
        $this->markTestSkipped('Auth routes not registered yet. Complete Phase 5 to enable these tests.');
    }

    /**
     * Test registration form can be displayed
     */
    public function test_registration_form_can_be_displayed()
    {
        $this->skipUntilRoutesRegistered();

        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /**
     * Test user can register with valid data
     */
    public function test_user_can_register_with_valid_data()
    {
        $this->skipUntilRoutesRegistered();

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);

        $this->assertAuthenticated();
    }

    /**
     * Test user password is hashed during registration
     */
    public function test_user_password_is_hashed_during_registration()
    {
        $this->skipUntilRoutesRegistered();

        $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'john@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /**
     * Test user is logged in after registration
     */
    public function test_user_is_logged_in_after_registration()
    {
        $this->skipUntilRoutesRegistered();

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticated();

        $user = User::where('email', 'john@example.com')->first();
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test registration requires name field
     */
    public function test_registration_requires_name()
    {
        $this->skipUntilRoutesRegistered();

        $response = $this->post('/register', [
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertGuest();
    }

    /**
     * Test registration requires email field
     */
    public function test_registration_requires_email()
    {
        $this->skipUntilRoutesRegistered();

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Test registration requires valid email format
     */
    public function test_registration_requires_valid_email_format()
    {
        $this->skipUntilRoutesRegistered();

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Test registration requires unique email
     */
    public function test_registration_requires_unique_email()
    {
        $this->skipUntilRoutesRegistered();

        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Test registration requires password confirmation
     */
    public function test_registration_requires_password_confirmation()
    {
        $this->skipUntilRoutesRegistered();

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    /**
     * Test registration requires minimum password length
     */
    public function test_registration_requires_minimum_password_length()
    {
        $this->skipUntilRoutesRegistered();

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }
}
