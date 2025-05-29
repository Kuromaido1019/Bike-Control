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
        // Log de los datos recibidos para depuración
        \Log::info('Datos recibidos en AccessController@update', [
            'request' => $request->all(),
            'access_id' => $access->id,
        ]);
        try {
            $data = $request->validate([
                'user_id' => 'required|exists:users,id',
                'guard_id' => 'required|exists:users,id',
                'bike_id' => 'nullable|exists:bikes,id',
                'entrance_time' => [
                    'required',
                    'regex:/^([01]\d|2[0-3]):([0-5]\d)$/',
                ],
                'exit_time' => [
                    'nullable',
                    'regex:/^([01]\d|2[0-3]):([0-5]\d)$/',
                ],
                'observation' => 'nullable|string|max:255',
            ], [
                'entrance_time.required' => 'La hora de entrada es obligatoria.',
                'entrance_time.regex' => 'La hora de entrada debe tener formato HH:mm.',
                'exit_time.regex' => 'La hora de salida debe tener formato HH:mm.',
                'observation.max' => 'La observación no puede superar los 255 caracteres.',
                'user_id.required' => 'El visitante es obligatorio.',
                'user_id.exists' => 'El visitante seleccionado no existe.',
                'guard_id.required' => 'El guardia es obligatorio.',
                'guard_id.exists' => 'El guardia seleccionado no existe.',
                'bike_id.exists' => 'La bicicleta seleccionada no existe.',
            ]);

            // Log de los datos validados
            \Log::info('Datos validados en AccessController@update', $data);

            // Guardar datos anteriores para auditoría
            $datos_anteriores = $access->toArray();
            $accessId = $access->id;
            $editadoPor = auth()->id() ?? 0;
            $fechaEdicion = now();
            // Actualizar los campos de hora en formato DATETIME conservando la fecha original
            $originalEntrance = $access->entrance_time;
            $originalExit = $access->exit_time;
            if ($originalEntrance) {
                $date = date('Y-m-d', strtotime($originalEntrance));
                $data['entrance_time'] = $date . ' ' . $data['entrance_time'] . ':00';
            }
            if ($data['exit_time'] !== null && $originalExit) {
                $date = date('Y-m-d', strtotime($originalExit));
                $data['exit_time'] = $date . ' ' . $data['exit_time'] . ':00';
            } elseif ($data['exit_time'] !== null && $originalEntrance) {
                // Si no hay exit_time previo, usar la fecha de entrada
                $date = date('Y-m-d', strtotime($originalEntrance));
                $data['exit_time'] = $date . ' ' . $data['exit_time'] . ':00';
            }

            // Insertar en access_modifications ANTES del update
            $datos_nuevos_temp = array_merge($datos_anteriores, $data); // Previsualización de nuevos datos
            $insert = \DB::table('access_modifications')->insert([
                'access_id' => $accessId,
                'accion' => 'editado',
                'datos_anteriores' => json_encode($datos_anteriores),
                'datos_nuevos' => json_encode($datos_nuevos_temp),
                'editado_por' => $editadoPor,
                'fecha_edicion' => $fechaEdicion,
                'created_at' => $fechaEdicion,
                'updated_at' => $fechaEdicion,
            ]);
            if ($insert) {
                $access->update($data);
                
                \Log::info('Auditoría de edición insertada y acceso actualizado', ['access_id' => $accessId]);
            } else {
                \Log::error('No se pudo insertar auditoría de edición, acceso NO actualizado', ['access_id' => $accessId]);
                return back()->with('error', 'No se pudo registrar la auditoría. El acceso no fue actualizado.');
            }

            \Log::info('Registro actualizado correctamente en AccessController@update', ['access_id' => $access->id]);
            return redirect()->route('admin.control-acceso')->with('success', 'Registro actualizado correctamente.');
        } catch (\Exception $e) {
            \Log::error('Error en AccessController@update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Ocurrió un error interno al actualizar el acceso.');
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
}
