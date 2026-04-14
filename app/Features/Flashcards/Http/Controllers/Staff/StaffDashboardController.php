<?php

namespace App\Features\Flashcards\Http\Controllers\Staff;

use App\Features\Auth\Models\User;
use App\Helpers\TenancyHelper;
use Illuminate\Support\Facades\Auth;

class StaffDashboardController
{
    public function index()
    {
        $user = Auth::user();

        return TenancyHelper::view('flashcards.staff.dashboard', [
            'isSuperAdmin' => $user->isSuperAdmin(),
            'subadminCount' => User::where('role', User::ROLE_SUBADMIN)->count(),
        ]);
    }
}
