<?php

namespace Tests\Unit\Auth\Models;

use App\Features\Auth\Models\User;
use App\Features\Auth\Models\SocialAccount;
use Tests\TestCase;

class SocialAccountTest extends TestCase
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
     * Test social account can be created
     */
    public function test_social_account_can_be_created()
    {
        $user = $this->createTestUser();

        $socialAccount = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_id' => '123456789',
            'access_token' => 'test-token',
        ]);

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_id' => '123456789',
        ]);

        $this->assertInstanceOf(SocialAccount::class, $socialAccount);
    }

    /**
     * Test social account belongs to user
     */
    public function test_social_account_belongs_to_user()
    {
        $user = $this->createTestUser();
        $socialAccount = $this->createSocialAccount($user);

        $this->assertInstanceOf(User::class, $socialAccount->user);
        $this->assertEquals($user->id, $socialAccount->user->id);
    }

    /**
     * Test social account token expiration check
     */
    public function test_social_account_token_expiration_check()
    {
        $user = $this->createTestUser();

        // Account without expiration (never expires)
        $accountWithoutExpiry = $this->createSocialAccount($user, [
            'provider_id' => 'no_expiry_123',
            'expires_at' => null,
        ]);

        $this->assertFalse($accountWithoutExpiry->isTokenExpired());

        // Account with future expiration
        $accountFutureExpiry = $this->createSocialAccount($user, [
            'provider_id' => 'future_expiry_123',
            'expires_at' => now()->addDays(1),
        ]);

        $this->assertFalse($accountFutureExpiry->isTokenExpired());

        // Account with past expiration
        $accountPastExpiry = $this->createSocialAccount($user, [
            'provider_id' => 'past_expiry_123',
            'expires_at' => now()->subDay(1),
        ]);

        $this->assertTrue($accountPastExpiry->isTokenExpired());
    }

    /**
     * Test social account has unique provider and provider_id combination
     */
    public function test_social_account_unique_provider_constraint()
    {
        $user = $this->createTestUser();

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_id' => '123456',
        ]);

        // Should be able to create with different provider
        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '123456',
        ]);

        $this->assertDatabaseCount('social_accounts', 2);

        // Should not be able to create duplicate provider/provider_id
        $this->expectException(\Illuminate\Database\QueryException::class);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_id' => '123456',
        ]);
    }

    /**
     * Test social account is deleted when user is deleted
     */
    public function test_social_account_cascade_delete_on_user_deletion()
    {
        $user = $this->createTestUser();
        $socialAccount = $this->createSocialAccount($user);

        $accountId = $socialAccount->id;

        // Delete user
        $user->delete();

        // Social account should also be deleted
        $this->assertDatabaseMissing('social_accounts', [
            'id' => $accountId,
        ]);
    }
}
