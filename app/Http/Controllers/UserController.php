<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRolesRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function editRoles(User $user): View
    {
        $this->authorize('manageRoles', $user);

        $roles = Role::query()->orderBy('name')->get();

        return view('users.roles', compact('user', 'roles'));
    }

    public function updateRoles(UpdateUserRolesRequest $request, User $user): RedirectResponse
    {
        $user->syncRoles($request->validated('roles') ?? []);

        return redirect()->back()->with('success', 'Roles actualizados correctamente.');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles')->latest()->paginate(10);

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
