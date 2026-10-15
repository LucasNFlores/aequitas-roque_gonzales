<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\BuildRoleDashboardData;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, BuildRoleDashboardData $buildRoleDashboardData): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        return view('dashboard', [
            'user' => $user,
            ...$buildRoleDashboardData($user),
        ]);
    }
}
