<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.users', compact('users'));
    }

    public function store(Request $request)
    {
        // Validar datos de entrada
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,guardia,visitante',
            'password' => 'required|string|min:6',
        ]);

        // Encriptar la contraseña
        $data['password'] = bcrypt($data['password']);

        // Crear usuario
        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        // Aquí podrías retornar una vista de edición si lo deseas
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,guardia,visitante',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
            return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado correctamente.');
        } catch (QueryException $e) {
            // Código SQL para violación de restricción de clave foránea en MySQL es 1451
            if ($e->getCode() == '23000' || strpos($e->getMessage(), '1451') !== false) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'No se puede eliminar el usuario porque tiene registros asociados en el sistema.');
            }
            // Para otros errores, lanza la excepción para no ocultar errores inesperados
            throw $e;
        }
    }
}
