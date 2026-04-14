<?php

namespace Tests\Unit\Auth\Models;

use App\Features\Auth\Models\User;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    public function test_default_user_is_not_staff(): void
    {
        $user = User::create([
            'name' => 'Learner',
            'email' => 'learner@example.com',
            'password' => bcrypt('secret'),
            'role' => User::ROLE_USER,
        ]);

        $this->assertFalse($user->isStaff());
        $this->assertFalse($user->isSubadmin());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_subadmin_is_staff_but_not_superadmin(): void
    {
        $user = User::create([
            'name' => 'Sub',
            'email' => 'sub@example.com',
            'password' => bcrypt('secret'),
            'role' => User::ROLE_SUBADMIN,
        ]);

        $this->assertTrue($user->isStaff());
        $this->assertTrue($user->isSubadmin());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_superadmin_is_staff(): void
    {
        $user = User::create([
            'name' => 'Super',
            'email' => 'super@example.com',
            'password' => bcrypt('secret'),
            'role' => User::ROLE_SUPERADMIN,
        ]);

        $this->assertTrue($user->isStaff());
        $this->assertFalse($user->isSubadmin());
        $this->assertTrue($user->isSuperAdmin());
    }
}
