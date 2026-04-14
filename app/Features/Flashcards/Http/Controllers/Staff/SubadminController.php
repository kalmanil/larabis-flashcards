<?php

namespace App\Features\Flashcards\Http\Controllers\Staff;

use App\Features\Auth\Models\User;
use App\Features\Flashcards\Http\Requests\StoreSubadminRequest;
use App\Helpers\TenancyHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SubadminController
{
    public function index()
    {
        $subadmins = User::where('role', User::ROLE_SUBADMIN)
            ->orderBy('name')
            ->get();

        return TenancyHelper::view('flashcards.staff.subadmins', [
            'subadmins' => $subadmins,
        ]);
    }

    public function store(StoreSubadminRequest $request): RedirectResponse
    {
        $data = $request->validated();

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_SUBADMIN,
        ]);

        return redirect()
            ->route('flashcards.staff.subadmins.index')
            ->with('status', 'Subadmin created.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if (!$user->isSubadmin()) {
            abort(404);
        }

        $user->delete();

        return redirect()
            ->route('flashcards.staff.subadmins.index')
            ->with('status', 'Subadmin removed.');
    }
}
