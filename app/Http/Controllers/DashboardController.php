<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\BuildRoleDashboardData;
use App\Http\Requests\DashboardHistoryRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardHistoryRequest $request, BuildRoleDashboardData $buildRoleDashboardData): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        return view('dashboard', [
            'user' => $user,
            ...$buildRoleDashboardData($user, $request->validated()),
        ]);
    }
}
