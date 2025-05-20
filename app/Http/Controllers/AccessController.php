<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Access;
use App\Models\Bike;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;

class AccessController extends Controller
{
    public function index()
    {
        $accesses = Access::with(['user', 'guardUser', 'bike'])->orderByDesc('entrance_time')->get();
        $bikes = Bike::all();
        $visitantes = User::where('role', 'visitante')->get();
        return view(request()->is('admin/*') ? 'admin.control-acceso' : 'guardia.control-acceso', compact('accesses', 'bikes', 'visitantes'));
    }

    public function store(Request $request)
    {
        // Buscar usuario por RUT
        $rut = $request->input('visitor_rut');
        $user = User::where('rut', $rut)->first();
        if (!$user) {
            return back()->with('error', 'No se encontró un usuario registrado con ese RUT.');
        }
        $data = $request->validate([
            'bike_id' => 'nullable|exists:bikes,id',
            'observation' => 'nullable|string',
            'guard_id' => 'required|exists:users,id',
        ]);
        $data['user_id'] = $user->id;
        $data['entrance_time'] = now()->format('Y-m-d H:i:s'); // Guardar la fecha y hora actual en formato DATETIME
        $data['exit_time'] = null; // Dejar vacío el campo de salida
        Access::create($data);
        return redirect()->route('guard.control-acceso')->with('success', 'Acceso registrado correctamente.');
    }

    public function update(Request $request, Access $access)
    {
        if ($request->has('mark_exit')) {
            // Asegurarse de que solo se actualice el campo `exit_time`
            $access->update(['exit_time' => now()->format('Y-m-d H:i:s')]);
            return redirect()->route('guard.control-acceso')->with('success', 'Hora de salida registrada correctamente.');
        }

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'guard_id' => 'required|exists:users,id',
            'bike_id' => 'nullable|exists:bikes,id',
            'entrance_time' => 'required',
            'exit_time' => 'nullable',
            'observation' => 'nullable|string',
        ]);

        $access->update($data);

        return redirect()->route('guard.control-acceso')->with('success', 'Registro actualizado correctamente.');
    }

    public function getBikesByRut($rut)
    {
        $user = User::where('rut', $rut)->first();
        if (!$user) return response()->json([]);
        $bikes = $user->bikes()->get(['id', 'brand', 'model']);
        return response()->json($bikes);
    }

    public function quickAccess(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'rut' => 'required|string|max:20|unique:users,rut',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:30',
            'bike_brand' => 'required|string|max:50',
            'bike_color' => 'required|string|max:30',
            'observation' => 'nullable|string',
            'guard_id' => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            // Crear usuario visitante
            $user = \App\Models\User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt('visitante_' . $data['rut']),
                'role' => 'visitante',
                'rut' => $data['rut'],
            ]);

            // Crear perfil con teléfono
            $user->profile()->create([
                'phone' => $data['phone'],
            ]);

            // Enviar correo para que el usuario cree su contraseña (antes de crear bicicleta y acceso)
            $token = app('auth.password.broker')->createToken($user);
            \Log::info('Intentando enviar notificación de restablecimiento de contraseña', ['user_id' => $user->id, 'email' => $user->email]);
            try {
                $user->notify(new \App\Notifications\ResetPasswordNotification($token));
                \Log::info('Notificación de restablecimiento enviada correctamente', ['user_id' => $user->id, 'email' => $user->email]);
            } catch (\Exception $e) {
                \Log::error('Error al enviar notificación de restablecimiento', ['user_id' => $user->id, 'email' => $user->email, 'error' => $e->getMessage()]);
                DB::rollBack();
                return back()->withInput()->with('error', 'Error al enviar correo de restablecimiento: ' . $e->getMessage());
            }

            // Crear bicicleta
            $bike = $user->bikes()->create([
                'brand' => $data['bike_brand'],
                'color' => $data['bike_color'],
            ]);

            // Crear acceso
            \App\Models\Access::create([
                'user_id' => $user->id,
                'guard_id' => $data['guard_id'],
                'bike_id' => $bike->id,
                'entrance_time' => now()->format('Y-m-d H:i:s'), // Asignar fecha y hora actual como string
                'observation' => $data['observation'] ?? null,
            ]);

            DB::commit();
            return redirect()->route('guard.control-acceso')->with('success', 'Ingreso rápido registrado correctamente. Se ha enviado un correo al usuario para que cree su contraseña.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al registrar ingreso rápido: ' . $e->getMessage());
        }
    }

    // Paso 1: Crear usuario y perfil
    public function quickUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'rut' => 'required|string|max:20|unique:users,rut',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:30',
            'guard_id' => 'required|exists:users,id',
        ]);
        try {
            DB::beginTransaction();
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt('visitante_' . $data['rut']),
                'role' => 'visitante',
                'rut' => $data['rut'],
            ]);
            $user->profile()->create([
                'phone' => $data['phone'],
            ]);
            DB::commit();
            return response()->json(['success' => true, 'user_id' => $user->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Paso 2: Crear bicicleta y acceso
    public function quickBike(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'bike_brand' => 'required|string|max:50',
            'bike_color' => 'required|string|max:30',
            'observation' => 'nullable|string',
            'guard_id' => 'required|exists:users,id',
        ]);
        try {
            DB::beginTransaction();
            $user = User::findOrFail($data['user_id']);
            $bike = $user->bikes()->create([
                'brand' => $data['bike_brand'],
                'color' => $data['bike_color'],
            ]);
            Access::create([
                'user_id' => $user->id,
                'guard_id' => $data['guard_id'],
                'bike_id' => $bike->id,
                'entrance_time' => now()->format('Y-m-d H:i:s'), // Asignar fecha y hora actual como string
                'observation' => $data['observation'] ?? null,
            ]);
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Cancelar y eliminar usuario, perfil y bicicletas si se cancela el wizard
    public function quickCancel(User $user)
    {
        try {
            DB::beginTransaction();
            $user->bikes()->delete();
            $user->profile()->delete();
            $user->delete();
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Access $access)
    {
        $access->delete();
        return redirect()->route('admin.control-acceso')->with('success', 'Acceso eliminado correctamente.');
    }
}
