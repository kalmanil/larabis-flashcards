<?php

namespace Tests\Unit\Auth\Models;

use App\Features\Auth\Models\User;
use App\Features\Auth\Models\SocialAccount;
use Tests\TestCase;

class UserTest extends TestCase
{

    /**
     * Helper to create a test user
     */
    protected function createTestUser(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'email' => 'test' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ], $attributes));
    }

    /**
     * Helper to create a social account
     */
    protected function createSocialAccount(User $user, array $attributes = []): SocialAccount
    {
        return SocialAccount::create(array_merge([
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_id' => uniqid(),
        ], $attributes));
    }

    /**
     * Test user can be created
     */
    public function test_user_can_be_created()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
    }

    /**
     * Test user has social accounts relationship
     */
    public function test_user_has_social_accounts_relationship()
    {
        $user = $this->createTestUser();
        $socialAccount = $this->createSocialAccount($user, [
            'provider' => 'facebook',
        ]);

        $this->assertTrue($user->socialAccounts->contains($socialAccount));
        $this->assertEquals(1, $user->socialAccounts->count());
    }

    /**
     * Test user can get social account by provider
     */
    public function test_user_can_get_social_account_by_provider()
    {
        $user = $this->createTestUser();
        $facebookAccount = $this->createSocialAccount($user, [
            'provider' => 'facebook',
            'provider_id' => '123456',
        ]);

        $found = $user->getSocialAccount('facebook');

        $this->assertNotNull($found);
        $this->assertEquals($facebookAccount->id, $found->id);
        $this->assertEquals('facebook', $found->provider);
    }

    /**
     * Test user can check if has social account
     */
    public function test_user_can_check_if_has_social_account()
    {
        $user = $this->createTestUser();

        // Initially no social accounts
        $this->assertFalse($user->hasSocialAccount('facebook'));

        // Create social account
        $this->createSocialAccount($user, [
            'provider' => 'facebook',
        ]);

        // Refresh the user to get fresh relationship data
        $user->refresh();

        // Now has social account
        $this->assertTrue($user->hasSocialAccount('facebook'));
        $this->assertFalse($user->hasSocialAccount('google'));
    }

    /**
     * Test user password is hashed
     */
    public function test_user_password_is_hashed()
    {
        $password = 'plaintext-password';
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => $password, // Will be auto-hashed due to cast
        ]);

        $this->assertNotEquals($password, $user->password);
        $this->assertTrue(\Hash::check($password, $user->password));
    }

    /**
     * Test user password is hidden from serialization
     */
    public function test_user_password_is_hidden_from_serialization()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    /**
     * Test user can have multiple social accounts
     */
    public function test_user_can_have_multiple_social_accounts()
    {
        $user = $this->createTestUser();

        $this->createSocialAccount($user, [
            'provider' => 'facebook',
            'provider_id' => 'fb123',
        ]);

        $this->createSocialAccount($user, [
            'provider' => 'google',
            'provider_id' => 'google123',
        ]);

        // Refresh to get fresh relationship data
        $user->refresh();

        $this->assertEquals(2, $user->socialAccounts->count());
        $this->assertTrue($user->hasSocialAccount('facebook'));
        $this->assertTrue($user->hasSocialAccount('google'));
    }
}
