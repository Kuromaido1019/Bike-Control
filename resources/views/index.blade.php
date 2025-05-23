@extends('layouts.app')

@section('title', 'Panel de Estadísticas')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Panel de Estadísticas</h1>
    @if(Auth::user()->role === 'admin')
    <form class="form-inline" method="GET" action="{{ route('admin.report') }}">
        <div class="input-group mr-2">
            <select class="form-control" name="type" id="reportType" required>
                <option value="" disabled selected>Selecciona tipo de reporte</option>
                <option value="general">Reporte General</option>
                <option value="accesos">Reporte de Accesos</option>
                <option value="usuarios">Reporte de Usuarios Activos</option>
                <option value="bicicletas">Reporte de Bicicletas</option>
                <option value="incidentes">Reporte de Incidentes/Observaciones</option>
            </select>
        </div>
        <button type="submit" class="btn btn-sm btn-primary shadow-sm" id="btnReport" disabled>
            <i class="fas fa-download fa-sm text-white-50"></i> Generar Reporte
        </button>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('reportType');
            const btn = document.getElementById('btnReport');
            select.addEventListener('change', function() {
                btn.disabled = !select.value;
            });
        });
    </script>
    @endif
</div>

@if(Auth::user()->role === 'admin')
    <!-- Estadísticas y tips para admin -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Usuarios Registrados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers ?? '---' }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Bicicletas Registradas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalBikes ?? '---' }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bicycle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Ingresos (Mensual)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $monthlyAccesses ?? '---' }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-days fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Usuario más activos (semana)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if(isset($topUsersWeek) && count($topUsersWeek))
                                    {{ $topUsersWeek[0]->user ? $topUsersWeek[0]->user->name : '---' }} ({{ $topUsersWeek[0]->total }})
                                @else
                                    ---
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Ranking de usuarios más activos -->
    <div class="card mb-4">
        <div class="card-header">Top 5 usuarios con más ingresos este mes</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Ingresos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topUsers as $userStat)
                            <tr>
                                <td>{{ $userStat->user ? $userStat->user->name : '---' }}</td>
                                <td>{{ $userStat->total }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2">Sin datos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="alert alert-info">Recuerda revisar los ingresos y gestionar los usuarios del sistema periódicamente.</div>
@endif

@if(Auth::user()->role === 'guardia')
    <!-- Estadísticas y tips para guardia -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Ingresos Hoy</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayAccesses ?? '---' }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-door-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Bicicletas en Bicicletero</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $bikesInParking ?? '---' }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bicycle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Ingresos Pendientes de Salida</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingExits ?? '---' }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Ingresos con Observaciones Hoy</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayObservations ?? '---' }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Tabla de últimos ingresos -->
    <div class="card mb-4">
        <div class="card-header">Últimos 5 ingresos</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentGuardAccesses as $access)
                            <tr>
                                <td>{{ $access->user ? $access->user->name : '---' }}</td>
                                <td>{{ $access->entrance_time ? \Carbon\Carbon::parse($access->entrance_time)->format('d/m/Y H:i') : '---' }}</td>
                                <td>{{ $access->exit_time ? \Carbon\Carbon::parse($access->exit_time)->format('d/m/Y H:i') : '---' }}</td>
                                <td>{{ $access->observation ?? '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">Sin ingresos recientes</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="alert alert-warning">Recuerda verificar la identidad de los usuarios y registrar correctamente los accesos.</div>
@endif

@if(Auth::user()->role === 'visitante')
    <!-- Estadísticas y tips para visitante -->
    <div class="row">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Mis Ingresos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $myAccesses ?? '---' }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-door-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Mi Bicicleta</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $myBikeStatus ?? '---' }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bicycle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Estadísticas adicionales -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Estado Bicicleta</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $bikeInParking ? 'En bicicletero' : 'Fuera' }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-warehouse fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Último Ingreso</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if($lastAccess && $lastAccess->entrance_time)
                                    {{ \Carbon\Carbon::parse($lastAccess->entrance_time)->format('d/m/Y H:i') }}
                                @else
                                    ---
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Días de uso este mes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $daysUsedThisMonth }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Tabla de últimos ingresos -->
    <div class="card mb-4">
        <div class="card-header">Últimos 5 ingresos</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAccesses as $access)
                            <tr>
                                <td>{{ $access->entrance_time ? \Carbon\Carbon::parse($access->entrance_time)->format('d/m/Y H:i') : '---' }}</td>
                                <td>{{ $access->exit_time ? \Carbon\Carbon::parse($access->exit_time)->format('d/m/Y H:i') : '---' }}</td>
                                <td>{{ $access->observation ?? '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">Sin ingresos recientes</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="alert alert-success">Recuerda asegurar tu bicicleta y registrar tus accesos correctamente.</div>
@endif
@endsection

@section('custom-scripts')
<!-- Page level plugins -->
<script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>
<script>
@if(Auth::user()->role === 'visitante' && isset($chartData))
    const ctx = document.getElementById('accessChart').getContext('2d');
    const chartData = {
        labels: {!! json_encode(array_column($chartData, 'date')) !!},
        datasets: [{
            label: 'Ingresos',
            data: {!! json_encode(array_column($chartData, 'count')) !!},
            backgroundColor: '#92AFD7',
            borderColor: '#395B50',
            borderWidth: 2,
            fill: true,
            tension: 0.3
        }]
    };
    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: { beginAtZero: true, precision: 0 }
            }
        }
    });
@endif
</script>
<!-- Page level custom scripts -->
<script src="{{ asset('js/demo/chart-area-demo.js') }}"></script>
<script src="{{ asset('js/demo/chart-pie-demo.js') }}"></script>
@endsection
