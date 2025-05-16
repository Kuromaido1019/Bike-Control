<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use App\Models\Profile;

class ProfileController extends Controller
{
    /**
     * Muestra el perfil del usuario
     */
    public function show()
    {
        return view('profile.show', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Actualiza el perfil del usuario
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        // Actualizar datos básicos del usuario
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->save();

        // Actualizar datos del perfil
        $profile = $user->profile;
        if ($profile) {
            $profile->phone = $request->input('phone');
            $profile->alt_phone = $request->input('alt_phone');
            $profile->career = $request->input('career');
            $profile->save();
        }

        return redirect()->back()->with('success', 'Datos actualizados correctamente.');
    }
}
