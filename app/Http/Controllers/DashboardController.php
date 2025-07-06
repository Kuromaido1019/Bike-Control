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
            // 13. Accesos por día de la semana (histórico)
            $weekdaysLabels = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
            $accessesByWeekdayData = [0,0,0,0,0,0,0];
            $exitsByWeekdayData = [0,0,0,0,0,0,0];
            $allAccesses = Access::all();
            foreach ($allAccesses as $access) {
                $entrance = $access->entrance_time ? (\Carbon\Carbon::parse($access->entrance_time)) : null;
                $exit = $access->exit_time ? (\Carbon\Carbon::parse($access->exit_time)) : null;
                if ($entrance) {
                    $day = (int)$entrance->format('N') - 1; // 0=Lunes
                    $accessesByWeekdayData[$day]++;
                }
                if ($exit) {
                    $day = (int)$exit->format('N') - 1;
                    $exitsByWeekdayData[$day]++;
                }
            }

            // Duración promedio de accesos por día (últimos 7 días)
            $avgDurationLabels = [];
            $avgDurationData = [];
            $start = now()->subDays(6)->startOfDay();
            for ($i = 0; $i < 7; $i++) {
                $date = $start->copy()->addDays($i);
                $accesses = Access::whereDate('entrance_time', $date)
                    ->whereNotNull('exit_time')
                    ->get();
                $totalDuration = 0;
                $count = 0;
                foreach ($accesses as $a) {
                    $entrance = \Carbon\Carbon::parse($a->entrance_time);
                    $exit = \Carbon\Carbon::parse($a->exit_time);
                    $duration = $exit->diffInMinutes($entrance);
                    $totalDuration += $duration;
                    $count++;
                }
                $avg = $count > 0 ? round($totalDuration / $count, 1) : 0;
                $avgDurationLabels[] = $date->format('d/m');
                $avgDurationData[] = $avg;
            }
            // 11. Nuevos usuarios por mes (últimos 6 meses)
            $newUsersByMonthLabels = [];
            $newUsersByMonthData = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->copy()->subMonths($i);
                $label = $date->format('m/Y');
                $count = \App\Models\User::where('role', 'visitante')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
                $newUsersByMonthLabels[] = $label;
                $newUsersByMonthData[] = $count;
            }

            // 12. Usuarios activos vs inactivos (sin accesos en 30 días)
            $users = \App\Models\User::where('role', 'visitante')->get();
            $activos = 0;
            $inactivos = 0;
            foreach ($users as $u) {
                $lastAccess = $u->accessesAsVisitor()->orderByDesc('entrance_time')->first();
                if ($lastAccess && \Carbon\Carbon::parse($lastAccess->entrance_time)->gt(now()->subDays(30))) {
                    $activos++;
                } else {
                    $inactivos++;
                }
            }
            $activeInactiveUsersLabels = ['Activos', 'Inactivos'];
            $activeInactiveUsersData = [$activos, $inactivos];
            // 1. Usuarios registrados
            $totalUsers = \App\Models\User::where('role', 'visitante')->count();
            // 2. Bicicletas registradas
            $totalBikes = Bike::count();
            // 3. Ingresos mensual
            $monthlyAccesses = Access::whereMonth('entrance_time', now()->month)
                ->whereYear('entrance_time', now()->year)
                ->count();
            // 4. Accesos por día (últimos 7 días)
            $accessesByDayLabels = [];
            $accessesByDayData = [];
            $start = now()->subDays(6)->startOfDay();
            for ($i = 0; $i < 7; $i++) {
                $date = $start->copy()->addDays($i);
                $accessesByDayLabels[] = $date->format('d/m');
                $accessesByDayData[] = Access::whereDate('entrance_time', $date)->count();
            }
            // 5. Tipos de acceso (entrada/salida)
            $tipoAccesoLabels = ['Entrada', 'Salida'];
            $tipoAccesoData = [
                Access::whereNotNull('entrance_time')->whereNull('exit_time')->count(),
                Access::whereNotNull('exit_time')->count(),
            ];
            // 6. Top 5 usuarios con más accesos (para gráfico)
            $topUsersChart = Access::whereMonth('entrance_time', now()->month)
                ->whereYear('entrance_time', now()->year)
                ->selectRaw('user_id, count(*) as total')
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->orderByDesc('total')
                ->with('user')
                ->limit(5)
                ->get();
            $topUsersLabels = $topUsersChart->map(fn($u) => $u->user ? $u->user->name : '---')->toArray();
            $topUsersData = $topUsersChart->map(fn($u) => $u->total)->toArray();
            // 7. Estado de accesos (activos/finalizados)
            $estadoAccesoLabels = ['Activos', 'Finalizados'];
            $estadoAccesoData = [
                Access::whereNull('exit_time')->count(),
                Access::whereNotNull('exit_time')->count(),
            ];

            // 8. Bicicletas por Marca
            $bikesByBrand = Bike::selectRaw('brand, COUNT(*) as total')->groupBy('brand')->orderByDesc('total')->get();
            $bikesByBrandLabels = $bikesByBrand->pluck('brand')->toArray();
            $bikesByBrandData = $bikesByBrand->pluck('total')->toArray();

            // 9. Bicicletas por Color
            $bikesByColor = Bike::selectRaw('color, COUNT(*) as total')->groupBy('color')->orderByDesc('total')->get();
            $bikesByColorLabels = $bikesByColor->pluck('color')->toArray();
            $bikesByColorData = $bikesByColor->pluck('total')->toArray();

            // 10. Bicicletas por Estado (En bicicletero / Fuera)
            // En bicicletero: tiene un access sin exit_time
            // Fuera: no tiene access sin exit_time
            $bikes = Bike::with(['accesses' => function($q) {
                $q->whereNull('exit_time');
            }])->get();
            $enBicicletero = 0;
            $fuera = 0;
            foreach ($bikes as $bike) {
                if ($bike->accesses->count() > 0) {
                    $enBicicletero++;
                } else {
                    $fuera++;
                }
            }
            $bikesByStatusLabels = ['En bicicletero', 'Fuera'];
            $bikesByStatusData = [$enBicicletero, $fuera];
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
            'accessesByDayLabels' => $accessesByDayLabels ?? [],
            'accessesByDayData' => $accessesByDayData ?? [],
            'tipoAccesoLabels' => $tipoAccesoLabels ?? [],
            'tipoAccesoData' => $tipoAccesoData ?? [],
            'topUsersLabels' => $topUsersLabels ?? [],
            'topUsersData' => $topUsersData ?? [],
            'estadoAccesoLabels' => $estadoAccesoLabels ?? [],
            'estadoAccesoData' => $estadoAccesoData ?? [],
            'bikesByBrandLabels' => $bikesByBrandLabels ?? [],
            'bikesByBrandData' => $bikesByBrandData ?? [],
            'bikesByColorLabels' => $bikesByColorLabels ?? [],
            'bikesByColorData' => $bikesByColorData ?? [],
            'bikesByStatusLabels' => $bikesByStatusLabels ?? [],
            'bikesByStatusData' => $bikesByStatusData ?? [],
            'newUsersByMonthLabels' => $newUsersByMonthLabels ?? [],
            'newUsersByMonthData' => $newUsersByMonthData ?? [],
            'activeInactiveUsersLabels' => $activeInactiveUsersLabels ?? [],
            'activeInactiveUsersData' => $activeInactiveUsersData ?? [],
            'weekdaysLabels' => $weekdaysLabels ?? [],
            'accessesByWeekdayData' => $accessesByWeekdayData ?? [],
            'exitsByWeekdayData' => $exitsByWeekdayData ?? [],
            'avgDurationLabels' => $avgDurationLabels ?? [],
            'avgDurationData' => $avgDurationData ?? [],
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
