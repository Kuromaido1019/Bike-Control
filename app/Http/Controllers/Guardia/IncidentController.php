<?php

namespace App\Http\Controllers\Guardia;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidentController extends Controller
{
    public function index()
    {
        // Si es admin, mostrar todos los incidentes
        if (Auth::user()->role === 'admin') {
            $incidents = Incident::orderByDesc('created_at')->get();
            return view('guardia.incidents', compact('incidents'));
        }
        // Si es guardia, mostrar solo los suyos
        if (Auth::user()->role === 'guardia') {
            $incidents = Incident::where('guard_id', Auth::id())->orderByDesc('created_at')->get();
            return view('guardia.incidents', compact('incidents'));
        }
        abort(403, 'No autorizado');
    }

    public function create()
    {
        if (Auth::user()->role !== 'guardia') {
            abort(403, 'No autorizado');
        }
        // Mostrar formulario para crear incidente
        return view('guardia.incidents.create');
    }

    public function store(Request $request)
    {
        // Permitir que admin y guardia registren incidentes
        if (!in_array(Auth::user()->role, ['guardia', 'admin'])) {
            abort(403, 'No autorizado');
        }
        $data = $request->validate([
            'rut' => 'required|string|max:20',
            'categoria' => 'required|string|max:50',
            'detalle' => 'required|string',
        ]);
        $data['guard_id'] = Auth::id();
        Incident::create($data);
        return redirect()->route('guard.incidents.index')->with('success', 'Incidente registrado correctamente.');
    }
}
