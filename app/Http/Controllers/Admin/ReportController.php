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
        $startDateTime = Carbon::parse($date_start . ' ' . $start_time);
        $endDateTime = Carbon::parse($date_end . ' ' . $end_time);

        $baseQuery = Access::with(['user', 'guardUser', 'bike'])
            ->whereBetween('entrance_time', [$startDateTime, $endDateTime]);
        $baseResults = $baseQuery->get();
        $guardias = $baseResults->pluck('guardUser')->filter()->unique('id')->sortBy('name');
        $usuarios = $baseResults->pluck('user')->filter()->unique('id')->sortBy('name');
        $modelos = $baseResults->pluck('bike')->filter()->unique('model')->pluck('model')->sort();
        $colores = $baseResults->pluck('bike')->filter()->unique('color')->pluck('color')->sort();
        $marcas = $baseResults->pluck('bike')->filter()->unique('brand')->pluck('brand')->sort();

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
        if ($request->filled('search_name')) {
            $accesses = $accesses->filter(function($a) use ($request) {
                return $a->user && stripos($a->user->name, $request->input('search_name')) !== false;
            });
        }
        if ($request->filled('search_rut')) {
            $accesses = $accesses->filter(function($a) use ($request) {
                return $a->user && stripos($a->user->rut, $request->input('search_rut')) !== false;
            });
        }

        // SweetAlert2: mensaje de búsqueda
        if ($request->hasAny(['date_start','date_end','start_time','end_time','guard_id','user_id','bike_model','bike_color','bike_brand','status','observation','search_name','search_rut'])) {
            $icon = count($accesses) > 0 ? 'success' : 'warning';
            $title = count($accesses) > 0 ? '¡Datos encontrados!' : 'Sin resultados';
            $text = count($accesses) > 0 ? 'Se han encontrado registros para los filtros aplicados.' : 'No se encontraron registros para los filtros seleccionados.';
            session(['search_result' => compact('icon','title','text')]);
        } else {
            session()->forget('search_result');
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
    public function csv(Request $request)
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
        if ($request->filled('search_name')) {
            $accesses = $accesses->filter(function($a) use ($request) {
                return $a->user && stripos($a->user->name, $request->input('search_name')) !== false;
            });
        }
        if ($request->filled('search_rut')) {
            $accesses = $accesses->filter(function($a) use ($request) {
                return $a->user && stripos($a->user->rut, $request->input('search_rut')) !== false;
            });
        }

        $filename = "Reporte_Accesos_{$date_start}_{$date_end}_{$start_time}_{$end_time}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        $columns = [
            'ID', 'Usuario', 'RUT', 'Guardia', 'Bicicleta', 'Tipo de Acceso', 'Entrada', 'Salida', 'Observación'
        ];
        $callback = function() use ($accesses, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($accesses as $access) {
                fputcsv($file, [
                    $access->id,
                    $access->user ? $access->user->name : '---',
                    $access->user ? $access->user->rut : '---',
                    $access->guardUser ? $access->guardUser->name : '---',
                    $access->bike ? $access->bike->brand . ' ' . $access->bike->model : '---',
                    ($access->entrance_time && !$access->exit_time) ? 'Entrada' : ($access->exit_time ? 'Salida' : '-'),
                    $access->entrance_time ? Carbon::parse($access->entrance_time)->format('d/m/Y H:i') : '---',
                    $access->exit_time ? Carbon::parse($access->exit_time)->format('d/m/Y H:i') : '---',
                    $access->observation ?? ''
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
}
