<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     *
     * Centraliza la baja en UserPolicy::delete (CU27): impide eliminación física y
     * verifica procesos activos, igual que el CRUD administrativo.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Verificación de negocio centralizada: igual que UserPolicy::delete pero sin exigir permiso eliminar_usuarios
        // (el perfil propio puede borrarse si no tiene procesos activos, aunque el rol no tenga ese permiso)
        $hasActiveProcess = $user->procesosComoProfesional()
            ->whereNotIn('estado', ['finalizado', 'rechazado'])
            ->exists()
            || $user->procesosComoCoordinador()
                ->whereNotIn('estado', ['finalizado', 'rechazado'])
                ->exists();

        abort_if($hasActiveProcess, 403, 'No se puede eliminar el perfil con procesos activos.');

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
