<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Access;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $date_start = $request->input('date_start', date('Y-m-d'));
        $date_end = $request->input('date_end', date('Y-m-d'));
        $start_time = $request->input('start_time', '00:00');
        $end_time = $request->input('end_time', '23:59');

        // Construir los rangos de fecha y hora
        $startDateTime = Carbon::parse($date_start . ' ' . $start_time);
        $endDateTime = Carbon::parse($date_end . ' ' . $end_time);

        // Filtrar por rango de fecha y hora
        $baseQuery = Access::with(['user', 'guardUser', 'bike'])
            ->whereBetween('entrance_time', [$startDateTime, $endDateTime]);

        // 2. Obtener valores únicos para selects contextuales
        $baseResults = $baseQuery->get();
        $guardias = $baseResults->pluck('guardUser')->filter()->unique('id')->sortBy('name');
        $usuarios = $baseResults->pluck('user')->filter()->unique('id')->sortBy('name');
        $modelos = $baseResults->pluck('bike')->filter()->unique('model')->pluck('model')->sort();
        $colores = $baseResults->pluck('bike')->filter()->unique('color')->pluck('color')->sort();
        $marcas = $baseResults->pluck('bike')->filter()->unique('brand')->pluck('brand')->sort();

        // 3. Aplicar los demás filtros sobre la base filtrada
        $accesses = $baseResults;
        if ($request->filled('guard_id')) {
            $accesses = $accesses->where('guard_id', $request->input('guard_id'));
        }
        if ($request->filled('user_id')) {
            $accesses = $accesses->where('user_id', $request->input('user_id'));
        }
        if ($request->filled('bike_model')) {
            $accesses = $accesses->filter(function($a) use ($request) {
                return $a->bike && $a->bike->model == $request->input('bike_model');
            });
        }
        if ($request->filled('bike_color')) {
            $accesses = $accesses->filter(function($a) use ($request) {
                return $a->bike && $a->bike->color == $request->input('bike_color');
            });
        }
        if ($request->filled('bike_brand')) {
            $accesses = $accesses->filter(function($a) use ($request) {
                return $a->bike && $a->bike->brand == $request->input('bike_brand');
            });
        }
        if ($request->filled('status')) {
            if ($request->input('status') === 'activo') {
                $accesses = $accesses->whereNull('exit_time');
            } elseif ($request->input('status') === 'finalizado') {
                $accesses = $accesses->whereNotNull('exit_time');
            }
        }
        if ($request->filled('observation')) {
            $accesses = $accesses->filter(function($a) use ($request) {
                return str_contains(strtolower($a->observation ?? ''), strtolower($request->input('observation')));
            });
        }
        // Filtro por nombre de usuario
        if ($request->filled('search_name')) {
            $accesses = $accesses->filter(function($a) use ($request) {
                return $a->user && stripos($a->user->name, $request->input('search_name')) !== false;
            });
        }
        // Filtro por RUT de usuario
        if ($request->filled('search_rut')) {
            $accesses = $accesses->filter(function($a) use ($request) {
                return $a->user && stripos($a->user->rut, $request->input('search_rut')) !== false;
            });
        }

        return view('admin.reports', [
            'accesses' => $accesses,
            'date_start' => $date_start,
            'date_end' => $date_end,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'guardias' => $guardias,
            'usuarios' => $usuarios,
            'modelos' => $modelos,
            'colores' => $colores,
            'marcas' => $marcas,
        ]);
    }

    public function pdf(Request $request)
    {
        $date_start = $request->input('date_start', date('Y-m-d'));
        $date_end = $request->input('date_end', date('Y-m-d'));
        $start_time = $request->input('start_time', '00:00');
        $end_time = $request->input('end_time', '23:59');
        $startDateTime = Carbon::parse($date_start . ' ' . $start_time);
        $endDateTime = Carbon::parse($date_end . ' ' . $end_time);
        $baseQuery = Access::with(['user', 'guardUser', 'bike'])
            ->whereBetween('entrance_time', [$startDateTime, $endDateTime]);
        $baseResults = $baseQuery->get();
        $accesses = $baseResults;
        if ($request->filled('guard_id')) {
            $accesses = $accesses->where('guard_id', $request->input('guard_id'));
        }
        if ($request->filled('user_id')) {
            $accesses = $accesses->where('user_id', $request->input('user_id'));
        }
        if ($request->filled('bike_model')) {
            $accesses = $accesses->filter(function($a) use ($request) {
                return $a->bike && $a->bike->model == $request->input('bike_model');
            });
        }
        if ($request->filled('bike_color')) {
            $accesses = $accesses->filter(function($a) use ($request) {
                return $a->bike && $a->bike->color == $request->input('bike_color');
            });
        }
        if ($request->filled('bike_brand')) {
            $accesses = $accesses->filter(function($a) use ($request) {
                return $a->bike && $a->bike->brand == $request->input('bike_brand');
            });
        }
        if ($request->filled('status')) {
            if ($request->input('status') === 'activo') {
                $accesses = $accesses->whereNull('exit_time');
            } elseif ($request->input('status') === 'finalizado') {
                $accesses = $accesses->whereNotNull('exit_time');
            }
        }
        if ($request->filled('observation')) {
            $accesses = $accesses->filter(function($a) use ($request) {
                return str_contains(strtolower($a->observation ?? ''), strtolower($request->input('observation')));
            });
        }
        // Filtro por nombre de usuario
        if ($request->filled('search_name')) {
            $accesses = $accesses->filter(function($a) use ($request) {
                return $a->user && stripos($a->user->name, $request->input('search_name')) !== false;
            });
        }
        // Filtro por RUT de usuario
        if ($request->filled('search_rut')) {
            $accesses = $accesses->filter(function($a) use ($request) {
                return $a->user && stripos($a->user->rut, $request->input('search_rut')) !== false;
            });
        }
        $title = "Reporte de Accesos del $date_start al $date_end ($start_time a $end_time)";
        $generated = Carbon::now()->format('d/m/Y H:i');
        $pdf = Pdf::loadView('admin.report-pdf', compact('accesses', 'title', 'generated', 'date_start', 'date_end', 'start_time', 'end_time'));
        $filename = "Reporte_Accesos_{$date_start}_{$date_end}_{$start_time}_{$end_time}.pdf";
        return $pdf->download($filename);
    }
}
