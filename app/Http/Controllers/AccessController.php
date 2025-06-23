<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Access;
use App\Models\Bike;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Models\AccessModification;

class AccessController extends Controller
{
    public function index()
    {
        // Filtrar accesos solo por la fecha de hoy usando SQL puro (CURDATE())
        $accesses = Access::with(['user', 'guardUser', 'bike'])
            ->whereRaw('DATE(entrance_time) = CURDATE()')
            ->orderByDesc('entrance_time')
            ->get();
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
        Access::create($data);
        return redirect()->route('guard.control-acceso')->with('success', 'Acceso registrado correctamente.');
    }

    public function update(Request $request, Access $access)
    {
        \Log::info('Datos recibidos en AccessController@update', [
            'request' => $request->all(),
            'access_id' => $access->id,
        ]);
        try {
            // Permitir solo editar observación si exit_time es null
            if ($access->exit_time !== null) {
                $msg = 'No se puede editar la observación después de marcar la salida.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 403);
                }
                return back()->with('error', $msg);
            }
            $data = $request->validate([
                'observation' => 'nullable|string|max:255',
            ], [
                'observation.max' => 'La observación no puede superar los 255 caracteres.',
            ]);
            $access->update($data);
            \Log::info('Observación actualizada correctamente en AccessController@update', ['access_id' => $access->id]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true]);
            }
            return redirect()->route('guard.control-acceso')->with('success', 'Observación actualizada correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error en AccessController@update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Ocurrió un error interno al actualizar la observación.'], 500);
            }
            return back()->with('error', 'Ocurrió un error interno al actualizar la observación.');
        }
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
        // Guardar datos anteriores para auditoría antes de eliminar
        $datos_anteriores = $access->toArray();
        $accessId = $access->id;
        $editadoPor = auth()->id() ?? 0;
        $fechaEdicion = now();
        // Insertar en access_modifications ANTES del delete
        $insert = \DB::table('access_modifications')->insert([
            'access_id' => $accessId,
            'accion' => 'eliminado',
            'datos_anteriores' => json_encode($datos_anteriores),
            'datos_nuevos' => null,
            'editado_por' => $editadoPor,
            'fecha_edicion' => $fechaEdicion,
            'created_at' => $fechaEdicion,
            'updated_at' => $fechaEdicion,
        ]);
        // Solo si el insert fue exitoso, eliminar el acceso
        if ($insert) {
            $deleted = $access->delete();
            \Log::info('Auditoría insertada y acceso eliminado', ['access_id' => $accessId, 'deleted' => $deleted]);
        } else {
            \Log::error('No se pudo insertar auditoría, acceso NO eliminado', ['access_id' => $accessId]);
            return back()->with('error', 'No se pudo registrar la auditoría. El acceso no fue eliminado.');
        }
        return redirect()->route('admin.control-acceso')->with('success', 'Acceso eliminado correctamente.');
    }

    public function getUserByRut($rut)
    {
        try {
            // Normalizar RUT: quitar espacios y pasar a mayúsculas
            $rut = strtoupper(trim($rut));
            $user = User::where('rut', $rut)->first();
            if (!$user) return response()->json(['message' => 'Usuario no encontrado'], 404);
            // Proteger la relación bikes ante posibles errores
            try {
                $bike = $user->bikes()->first();
            } catch (\Exception $e) {
                \Log::error('Error al obtener bicicleta para usuario', ['rut' => $rut, 'error' => $e->getMessage()]);
                $bike = null;
            }
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'rut' => $user->rut,
                'bike' => $bike ? [
                    'id' => $bike->id,
                    'brand' => $bike->brand,
                    'model' => $bike->model
                ] : null
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en getUserByRut', ['rut' => $rut, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Error interno al buscar usuario'], 500);
        }
    }

    public function markExit($id)
    {
        $access = Access::findOrFail($id);
        if (is_null($access->exit_time)) {
            $exitTime = \Carbon\Carbon::now('America/Santiago');
            $access->exit_time = $exitTime;
            $access->save();
            
            \Log::info('Salida marcada', [
                'access_id' => $access->id,
                'exit_time' => $exitTime->toDateTimeString(),
                'exit_time_iso' => $exitTime->toIso8601String(),
                'db_exit_time' => $access->exit_time,
            ]);
            return redirect()->route('guard.control-acceso')->with('success', 'Salida marcada correctamente.');
        } else {
            return redirect()->route('guard.control-acceso')->with('error', 'La salida ya fue marcada.');
        }
    }
}
