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
    
    <div class="alert alert-info">Recuerda revisar los ingresos y gestionar los usuarios del sistema periódicamente.</div>
    <!-- Tabs para agrupar los gráficos -->
    <ul class="nav nav-tabs mb-4" id="statsTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="accesos-tab" data-bs-toggle="tab" data-bs-target="#accesos" type="button" role="tab" aria-controls="accesos" aria-selected="true">Accesos</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="bicicletas-tab" data-bs-toggle="tab" data-bs-target="#bicicletas" type="button" role="tab" aria-controls="bicicletas" aria-selected="false">Bicicletas</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="usuarios-tab" data-bs-toggle="tab" data-bs-target="#usuarios" type="button" role="tab" aria-controls="usuarios" aria-selected="false">Usuarios</button>
      </li>
    </ul>
    <div class="tab-content" id="statsTabsContent">
      
      <!-- Tab Accesos -->
      <div class="tab-pane fade show active" id="accesos" role="tabpanel" aria-labelledby="accesos-tab">
        <div class="row">
          <div class="col-xl-6 mb-4">
            <div class="card shadow">
              <div class="card-header">Distribución de Tipos de Acceso (Mes Actual)</div>
              <div class="card-body">
                <div id="pieChartStatic" style="height: 350px;"></div>
              </div>
            </div>
          </div>
          <div class="col-xl-6 mb-4">
            <div class="card shadow">
              <div class="card-header">Accesos por Día (Últimos 7 días)</div>
              <div class="card-body">
                <div id="barChartAccessesByDay" style="height: 350px;"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xl-6 mb-4">
            <div class="card shadow">
              <div class="card-header">Estado de Accesos (Activos vs Finalizados)</div>
              <div class="card-body">
                <div id="pieChartEstadoAcceso" style="height: 350px;"></div>
              </div>
            </div>
          </div>
          <div class="col-xl-6 mb-4">
            <div class="card shadow">
              <div class="card-header">Duración Promedio de Accesos por Día (minutos, últimos 7 días)</div>
              <div class="card-body">
                <div id="chartAvgDurationByDay" style="height: 350px;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Tab Bicicletas -->
      <div class="tab-pane fade" id="bicicletas" role="tabpanel" aria-labelledby="bicicletas-tab">
        <div class="row">
          <div class="col-xl-6 mb-4">
            <div class="card shadow">
              <div class="card-header">Bicicletas por Marca</div>
              <div class="card-body">
                <div id="chart-bikes-by-brand" style="height: 350px;"></div>
              </div>
            </div>
          </div>
          <div class="col-xl-6 mb-4">
            <div class="card shadow">
              <div class="card-header">Bicicletas por Color</div>
              <div class="card-body">
                <div id="chart-bikes-by-color" style="height: 350px;"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xl-6 mb-4">
            <div class="card shadow">
              <div class="card-header">Bicicletas por Estado</div>
              <div class="card-body">
                <div id="chart-bikes-by-status" style="height: 350px;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Tab Usuarios -->
      <div class="tab-pane fade" id="usuarios" role="tabpanel" aria-labelledby="usuarios-tab">
        <div class="row">
          <div class="col-xl-6 mb-4">
            <div class="card shadow">
              <div class="card-header">Top 5 Usuarios con Más Ingresos (Mes Actual)</div>
              <div class="card-body">
                <div id="barChartTopUsers" style="height: 350px;"></div>
              </div>
            </div>
          </div>
          <div class="col-xl-6 mb-4">
            <div class="card shadow">
              <div class="card-header">Nuevos Usuarios por Mes (Últimos 6 meses)</div>
              <div class="card-body">
                <div id="barChartNewUsersByMonth" style="height: 350px;"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-xl-6 mb-4">
            <div class="card shadow">
              <div class="card-header">Usuarios Activos vs Inactivos (sin accesos en 30 días)</div>
              <div class="card-body">
                <div id="pieChartActiveInactiveUsers" style="height: 350px;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
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

@push('custom-scripts')
<!-- ApexCharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pie chart dinámico con datos de la BD
    var pieDiv = document.querySelector("#pieChartStatic");
    if (pieDiv) {
        var optionsPie = {
            chart: { type: 'pie', height: 350 },
            series: @json($tipoAccesoData),
            labels: @json($tipoAccesoLabels),
            colors: ['#1cc88a', '#e74a3b', '#36b9cc']
        };
        var chartPie = new ApexCharts(pieDiv, optionsPie);
        chartPie.render();
    }
    // Gráfico de barras: Accesos por día
    var barDiv = document.querySelector("#barChartAccessesByDay");
    if (barDiv) {
        var optionsBar = {
            chart: { type: 'bar', height: 350 },
            series: [{
                name: 'Accesos',
                data: @json($accessesByDayData)
            }],
            xaxis: {
                categories: @json($accessesByDayLabels)
            },
            colors: ['#4e73df']
        };
        var chartBar = new ApexCharts(barDiv, optionsBar);
        chartBar.render();
    }
    // Gráfico de pastel: Estado de accesos
    var estadoDiv = document.querySelector("#pieChartEstadoAcceso");
    if (estadoDiv) {
        var optionsEstado = {
            chart: { type: 'pie', height: 350 },
            series: @json($estadoAccesoData),
            labels: @json($estadoAccesoLabels),
            colors: ['#f6c23e', '#858796']
        };
        var chartEstado = new ApexCharts(estadoDiv, optionsEstado);
        chartEstado.render();
    }
    // Gráfico de línea: Duración promedio de accesos por día
    var avgDurationDiv = document.querySelector("#chartAvgDurationByDay");
    if (avgDurationDiv) {
        var optionsAvgDuration = {
            chart: { type: 'line', height: 350 },
            series: [{
                name: 'Duración promedio (min)',
                data: @json($avgDurationData)
            }],
            xaxis: {
                categories: @json($avgDurationLabels)
            },
            colors: ['#e67e22']
        };
        var chartAvgDuration = new ApexCharts(avgDurationDiv, optionsAvgDuration);
        chartAvgDuration.render();
    }

    // Gráfico de barras: Top 5 usuarios
    var topUsersDiv = document.querySelector("#barChartTopUsers");
    if (topUsersDiv) {
        var optionsTopUsers = {
            chart: { type: 'bar', height: 350 },
            series: [{
                name: 'Ingresos',
                data: @json($topUsersData)
            }],
            xaxis: {
                categories: @json($topUsersLabels)
            },
            colors: ['#36b9cc']
        };
        var chartTopUsers = new ApexCharts(topUsersDiv, optionsTopUsers);
        chartTopUsers.render();
    }

    // Gráfico de barras: Nuevos usuarios por mes
    var newUsersDiv = document.querySelector("#barChartNewUsersByMonth");
    if (newUsersDiv) {
        var optionsNewUsers = {
            chart: { type: 'bar', height: 350 },
            series: [{
                name: 'Nuevos usuarios',
                data: @json($newUsersByMonthData)
            }],
            xaxis: {
                categories: @json($newUsersByMonthLabels)
            },
            colors: ['#1cc88a']
        };
        var chartNewUsers = new ApexCharts(newUsersDiv, optionsNewUsers);
        chartNewUsers.render();
    }

    // Gráfico de pastel: Usuarios activos vs inactivos
    var activeInactiveDiv = document.querySelector("#pieChartActiveInactiveUsers");
    if (activeInactiveDiv) {
        var optionsActiveInactive = {
            chart: { type: 'pie', height: 350 },
            series: @json($activeInactiveUsersData),
            labels: @json($activeInactiveUsersLabels),
            colors: ['#36b9cc', '#e74a3b']
        };
        var chartActiveInactive = new ApexCharts(activeInactiveDiv, optionsActiveInactive);
        chartActiveInactive.render();
    }

    // Bicicletas por Marca
    var bikesByBrandDiv = document.querySelector("#chart-bikes-by-brand");
    if (bikesByBrandDiv) {
        var optionsBikesByBrand = {
            chart: { type: 'bar', height: 350 },
            series: [{
                name: 'Cantidad',
                data: @json($bikesByBrandData)
            }],
            xaxis: {
                categories: @json($bikesByBrandLabels)
            },
            colors: ['#f6c23e']
        };
        var chartBikesByBrand = new ApexCharts(bikesByBrandDiv, optionsBikesByBrand);
        chartBikesByBrand.render();
    }

    // Bicicletas por Color
    var bikesByColorDiv = document.querySelector("#chart-bikes-by-color");
    if (bikesByColorDiv) {
        var optionsBikesByColor = {
            chart: { type: 'pie', height: 350 },
            series: @json($bikesByColorData),
            labels: @json($bikesByColorLabels),
            colors: ['#1cc88a', '#e74a3b', '#36b9cc', '#4e73df', '#f6c23e', '#858796']
        };
        var chartBikesByColor = new ApexCharts(bikesByColorDiv, optionsBikesByColor);
        chartBikesByColor.render();
    }

    // Bicicletas por Estado
    var bikesByStatusDiv = document.querySelector("#chart-bikes-by-status");
    if (bikesByStatusDiv) {
        var optionsBikesByStatus = {
            chart: { type: 'pie', height: 350 },
            series: @json($bikesByStatusData),
            labels: @json($bikesByStatusLabels),
            colors: ['#36b9cc', '#e74a3b', '#1cc88a']
        };
        var chartBikesByStatus = new ApexCharts(bikesByStatusDiv, optionsBikesByStatus);
        chartBikesByStatus.render();
    }
});
</script>
@endpush
