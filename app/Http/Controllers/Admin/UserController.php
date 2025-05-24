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
        // Validar datos de entrada con formato de RUT y fuerza de contraseña
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'rut' => [
                'required',
                'string',
                'max:20',
                'unique:users,rut',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^\d{7,8}-[\dkK]$/', $value) || !$this->validateChileanRut($value)) {
                        $fail('El RUT no es válido. Debe tener formato 12345678-9.');
                    }
                }
            ],
            'role' => 'required|in:admin,guardia,visitante',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/', // al menos una mayúscula
                'regex:/[a-z]/', // al menos una minúscula
                'regex:/[0-9]/', // al menos un número
                // al menos un símbolo, incluyendo _
                "regex:/[ @$!%*#?&_\-.,;:!\"'\\/\[\]{}()=+<>|~`^%$#]/",
            ],
        ], [
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y símbolo.',
            'rut.unique' => 'El RUT ya está registrado.',
            'rut.required' => 'El RUT es obligatorio.',
            'rut.max' => 'El RUT no puede tener más de 20 caracteres.',
        ]);

        $data['password'] = bcrypt($data['password']);
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
            'rut' => [
                'required',
                'string',
                'max:20',
                'unique:users,rut,' . $user->id,
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^\d{7,8}-[\dkK]$/', $value) || !$this->validateChileanRut($value)) {
                        $fail('El RUT no es válido. Debe tener formato 12345678-9.');
                    }
                }
            ],
            'role' => 'required|in:admin,guardia,visitante',
        ], [
            'rut.unique' => 'El RUT ya está registrado.',
            'rut.required' => 'El RUT es obligatorio.',
            'rut.max' => 'El RUT no puede tener más de 20 caracteres.',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'rut' => $request->rut,
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

    /**
     * Valida el formato y dígito verificador de un RUT chileno.
     */
    private function validateChileanRut($rut)
    {
        $rut = preg_replace('/[^\dkK]/', '', str_replace('-', '', $rut));
        $body = substr($rut, 0, -1);
        $dv = strtoupper(substr($rut, -1));
        $suma = 0;
        $multiplo = 2;
        for ($i = strlen($body) - 1; $i >= 0; $i--) {
            $suma += $body[$i] * $multiplo;
            $multiplo = $multiplo < 7 ? $multiplo + 1 : 2;
        }
        $resto = $suma % 11;
        $dvEsperado = 11 - $resto;
        if ($dvEsperado == 11) $dvEsperado = '0';
        elseif ($dvEsperado == 10) $dvEsperado = 'K';
        else $dvEsperado = (string)$dvEsperado;
        return $dv == $dvEsperado;
    }
}
