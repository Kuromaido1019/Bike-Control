<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Access;
use App\Models\Bike;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $myAccesses = null;
        $myBikeStatus = null;
        $lastAccess = null;
        $bikeInParking = null;
        $recentAccesses = collect();
        $daysUsedThisMonth = 0;
        $chartData = [];
        $todayAccesses = null;
        $bikesInParking = null;
        $pendingExits = null;
        $recentGuardAccesses = collect();
        $todayObservations = null;
        $totalUsers = null;
        $totalBikes = null;
        $monthlyAccesses = null;
        $topUsers = collect();
        $topUsersWeek = collect();

        if ($user->role === 'visitante') {
            $myAccesses = $user->accessesAsVisitor()->count();
            $myBike = $user->bikes()->first();
            $myBikeStatus = $myBike ? ($myBike->brand . ' - ' . $myBike->model . ' (' . $myBike->color . ')') : 'Sin bicicleta registrada';

            // 1. Último ingreso
            $lastAccess = $user->accessesAsVisitor()->orderByDesc('entrance_time')->first();

            // 2. Estado de la bicicleta (en bicicletero si tiene un access sin exit_time)
            $bikeInParking = $user->accessesAsVisitor()->whereNull('exit_time')->exists();

            // 3. Últimos 5 ingresos
            $recentAccesses = $user->accessesAsVisitor()->orderByDesc('entrance_time')->limit(5)->get();

            // 5. Días de uso en el mes actual
            $daysUsedThisMonth = $user->accessesAsVisitor()
                ->whereMonth('entrance_time', now()->month)
                ->whereYear('entrance_time', now()->year)
                ->get()
                ->groupBy(function($item) {
                    // Asegurarse de que entrance_time es instancia de Carbon
                    $date = $item->entrance_time instanceof \Carbon\Carbon ? $item->entrance_time : \Carbon\Carbon::parse($item->entrance_time);
                    return $date->format('Y-m-d');
                })
                ->count();

            // 6. Chart: ingresos por día últimos 7 días
            $chartData = [];
            $start = now()->subDays(6)->startOfDay();
            for ($i = 0; $i < 7; $i++) {
                $date = $start->copy()->addDays($i);
                $count = $user->accessesAsVisitor()->whereDate('entrance_time', $date)->count();
                $chartData[] = [
                    'date' => $date->format('d/m'),
                    'count' => $count
                ];
            }
        }

        if ($user->role === 'guardia') {
            // 1. Ingresos hoy
            $todayAccesses = Access::whereDate('entrance_time', now()->toDateString())->count();
            // 2. Bicicletas en bicicletero (accesos sin exit_time)
            $bikesInParking = Access::whereNull('exit_time')->count();
            // 3. Ingresos pendientes de salida
            $pendingExits = Access::whereNull('exit_time')->count();
            // 4. Últimos 5 ingresos
            $recentGuardAccesses = Access::orderByDesc('entrance_time')->limit(5)->get();
            // 5. Ingresos o salidas con observaciones hoy
            $todayObservations = Access::whereDate('entrance_time', now()->toDateString())
                ->whereNotNull('observation')
                ->where('observation', '!=', '')
                ->count();
            $todayObservations += Access::whereDate('exit_time', now()->toDateString())
                ->whereNotNull('observation')
                ->where('observation', '!=', '')
                ->count();
        }

        if ($user->role === 'admin') {
            // 1. Usuarios registrados
            $totalUsers = \App\Models\User::where('role', 'visitante')->count();
            // 2. Bicicletas registradas
            $totalBikes = Bike::count();
            // 3. Ingresos mensual
            $monthlyAccesses = Access::whereMonth('entrance_time', now()->month)
                ->whereYear('entrance_time', now()->year)
                ->count();
            // 6. Top 5 usuarios con más ingresos esta semana
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();
            $topUsersWeek = Access::whereBetween('entrance_time', [$startOfWeek, $endOfWeek])
                ->selectRaw('user_id, count(*) as total')
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->orderByDesc('total')
                ->with('user')
                ->limit(5)
                ->get();
            // Top 5 usuarios con más ingresos este mes (para la tabla)
            $topUsers = Access::whereMonth('entrance_time', now()->month)
                ->whereYear('entrance_time', now()->year)
                ->selectRaw('user_id, count(*) as total')
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->orderByDesc('total')
                ->with('user')
                ->limit(5)
                ->get();
        }

        return view('index', [
            'myAccesses' => $myAccesses,
            'myBikeStatus' => $myBikeStatus,
            'lastAccess' => $lastAccess,
            'bikeInParking' => $bikeInParking,
            'recentAccesses' => $recentAccesses,
            'daysUsedThisMonth' => $daysUsedThisMonth,
            'chartData' => $chartData,
            'todayAccesses' => $todayAccesses,
            'bikesInParking' => $bikesInParking,
            'pendingExits' => $pendingExits,
            'recentGuardAccesses' => $recentGuardAccesses,
            'todayObservations' => $todayObservations,
            'totalUsers' => $totalUsers,
            'totalBikes' => $totalBikes,
            'monthlyAccesses' => $monthlyAccesses,
            'topUsers' => $topUsers,
            'topUsersWeek' => $topUsersWeek,
        ]);
    }

    public function report(Request $request)
    {
        $type = $request->input('type');
        $data = [];
        $title = '';
        $today = now()->format('d-m-Y_H-i');

        switch ($type) {
            case 'general':
                $data['totalUsers'] = \App\Models\User::where('role', 'visitante')->count();
                $data['totalBikes'] = Bike::count();
                $data['monthlyAccesses'] = Access::whereMonth('entrance_time', now()->month)
                    ->whereYear('entrance_time', now()->year)
                    ->count();
                $data['topUsers'] = Access::whereMonth('entrance_time', now()->month)
                    ->whereYear('entrance_time', now()->year)
                    ->selectRaw('user_id, count(*) as total')
                    ->whereNotNull('user_id')
                    ->groupBy('user_id')
                    ->orderByDesc('total')
                    ->with('user')
                    ->limit(5)
                    ->get();
                $title = 'Reporte General';
                break;
            case 'accesos':
                $data['accesses'] = Access::orderByDesc('entrance_time')->limit(100)->with('user')->get();
                $title = 'Reporte de Accesos (últimos 100)';
                break;
            case 'usuarios':
                $data['topUsers'] = Access::whereMonth('entrance_time', now()->month)
                    ->whereYear('entrance_time', now()->year)
                    ->selectRaw('user_id, count(*) as total')
                    ->whereNotNull('user_id')
                    ->groupBy('user_id')
                    ->orderByDesc('total')
                    ->with('user')
                    ->get();
                $title = 'Reporte de Usuarios Activos (mes actual)';
                break;
            case 'bicicletas':
                $data['bikes'] = Bike::with('user')->get();
                $title = 'Reporte de Bicicletas Registradas';
                break;
            case 'incidentes':
                $data['incidents'] = Access::where(function($q) {
                        $q->whereNotNull('observation')->where('observation', '!=', '');
                    })
                    ->orderByDesc('entrance_time')
                    ->limit(100)
                    ->with('user')
                    ->get();
                $title = 'Reporte de Incidentes/Observaciones';
                break;
            default:
                abort(404);
        }
        $data['title'] = $title;
        $data['generated'] = $today;
        $data['type'] = $type;
        $pdf = Pdf::loadView('admin.report-pdf', $data);

        // Limpiar el título para el nombre del archivo (sin caracteres no permitidos)
        $filename = preg_replace('#[\\/:*?"<>|]#', '', $title . ' - ' . $today) . '.pdf';
        return $pdf->download($filename);
    }
}
