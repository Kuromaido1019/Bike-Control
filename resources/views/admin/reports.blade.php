@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h4 class="mb-4 text-primary">Reportes</h4>
            <div class="alert alert-info">Aquí podrás generar y visualizar reportes del sistema.</div>
            <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.reports.index') }}">
                <div class="accordion mb-4" id="filtrosAccordion">
                    <!-- Fechas y horas -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFechaHora">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFechaHora" aria-expanded="true" aria-controls="collapseFechaHora">
                                1. Fechas y Horas
                            </button>
                        </h2>
                        <div id="collapseFechaHora" class="accordion-collapse collapse show" aria-labelledby="headingFechaHora" data-bs-parent="#filtrosAccordion">
                            <div class="accordion-body row g-3">
                                <div class="col-md-3">
                                    <label for="date_start" class="form-label">Fecha inicio</label>
                                    <input type="date" class="form-control" id="date_start" name="date_start" value="{{ request('date_start', date('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="date_end" class="form-label">Fecha fin</label>
                                    <input type="date" class="form-control" id="date_end" name="date_end" value="{{ request('date_end', date('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label for="start_time" class="form-label">Hora inicio</label>
                                    <input type="time" class="form-control" id="start_time" name="start_time" value="{{ request('start_time', '00:00') }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label for="end_time" class="form-label">Hora fin</label>
                                    <input type="time" class="form-control" id="end_time" name="end_time" value="{{ request('end_time', '23:59') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Datos de la bicicleta -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingBicicleta">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBicicleta" aria-expanded="false" aria-controls="collapseBicicleta" id="btnBicicleta">
                                2. Datos de la Bicicleta
                            </button>
                        </h2>
                        <div id="collapseBicicleta" class="accordion-collapse collapse" aria-labelledby="headingBicicleta" data-bs-parent="#filtrosAccordion">
                            <div class="accordion-body row g-3">
                                <div class="col-md-4">
                                    <label for="bike_model" class="form-label">Modelo Bicicleta</label>
                                    <select class="form-select" id="bike_model" name="bike_model">
                                        <option value="">Todos</option>
                                        @foreach($modelos as $modelo)
                                            <option value="{{ $modelo }}" {{ request('bike_model') == $modelo ? 'selected' : '' }}>{{ $modelo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="bike_color" class="form-label">Color Bicicleta</label>
                                    <select class="form-select" id="bike_color" name="bike_color">
                                        <option value="">Todos</option>
                                        @foreach($colores as $color)
                                            <option value="{{ $color }}" {{ request('bike_color') == $color ? 'selected' : '' }}>{{ $color }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="bike_brand" class="form-label">Marca Bicicleta</label>
                                    <select class="form-select" id="bike_brand" name="bike_brand">
                                        <option value="">Todos</option>
                                        @foreach($marcas as $marca)
                                            <option value="{{ $marca }}" {{ request('bike_brand') == $marca ? 'selected' : '' }}>{{ $marca }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Cuarto colapsable: búsqueda por nombre o RUT -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingUsuarioRut">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUsuarioRut" aria-expanded="false" aria-controls="collapseUsuarioRut" id="btnUsuarioRut">
                                4. Buscar por Usuario o RUT
                            </button>
                        </h2>
                        <div id="collapseUsuarioRut" class="accordion-collapse collapse" aria-labelledby="headingUsuarioRut" data-bs-parent="#filtrosAccordion">
                            <div class="accordion-body row g-3">
                                <div class="col-md-6">
                                    <label for="search_name" class="form-label">Nombre Usuario</label>
                                    <input type="text" class="form-control" id="search_name" name="search_name" value="{{ request('search_name') }}" placeholder="Buscar por nombre">
                                </div>
                                <div class="col-md-6">
                                    <label for="search_rut" class="form-label">RUT</label>
                                    <input type="text" class="form-control" id="search_rut" name="search_rut" value="{{ request('search_rut') }}" placeholder="Buscar por RUT">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Detalles del acceso -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingDetalles">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDetalles" aria-expanded="false" aria-controls="collapseDetalles" id="btnDetalles">
                                3. Detalles del Acceso
                            </button>
                        </h2>
                        <div id="collapseDetalles" class="accordion-collapse collapse" aria-labelledby="headingDetalles" data-bs-parent="#filtrosAccordion">
                            <div class="accordion-body row g-3">
                                <div class="col-md-4">
                                    <label for="status" class="form-label">Estado Acceso</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">Todos</option>
                                        <option value="activo" {{ request('status') == 'activo' ? 'selected' : '' }}>Activo (sin salida)</option>
                                        <option value="finalizado" {{ request('status') == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="tipo_acceso" class="form-label">Tipo de Acceso</label>
                                    <select class="form-select" id="tipo_acceso" name="tipo_acceso">
                                        <option value="">Todos</option>
                                        <option value="entrada" {{ request('tipo_acceso') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                                        <option value="salida" {{ request('tipo_acceso') == 'salida' ? 'selected' : '' }}>Salida</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="observation" class="form-label">Observación contiene</label>
                                    <input type="text" class="form-control" id="observation" name="observation" value="{{ request('observation') }}" placeholder="Palabra clave">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">Limpiar</a>
                    <a href="{{ route('admin.reports.pdf', array_merge(request()->all(), ['download' => 1])) }}" class="btn btn-success ms-auto{{ (isset($accesses) && count($accesses) > 0) ? '' : ' disabled' }}" target="_blank" id="btnPdf">
                        <i class="fas fa-file-pdf"></i> Generar PDF
                    </a>
                </div>
            </form>
            @if(isset($accesses))
                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-hover" id="accessTable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>RUT</th>
                                <th>Guardia</th>
                                <th>Bicicleta</th>
                                <th>Tipo de Acceso</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($accesses as $access)
                            <tr>
                                <td>{{ $access->id }}</td>
                                <td>{{ $access->user ? $access->user->name : '---' }}</td>
                                <td>{{ $access->user ? $access->user->rut : '---' }}</td>
                                <td>{{ $access->guardUser ? $access->guardUser->name : '---' }}</td>
                                <td>{{ $access->bike ? $access->bike->brand . ' ' . $access->bike->model : '---' }}</td>
                                <td>
                                    @if($access->entrance_time && !$access->exit_time)
                                        Entrada
                                    @elseif($access->exit_time)
                                        Salida
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $access->entrance_time ? \Carbon\Carbon::parse($access->entrance_time)->format('d/m/Y H:i') : '---' }}</td>
                                <td>{{ $access->exit_time ? \Carbon\Carbon::parse($access->exit_time)->format('d/m/Y H:i') : '---' }}</td>
                                <td>{{ $access->observation ?? '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9">Sin accesos registrados en este rango.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('custom-scripts')
<script>
// Deshabilitar los colapsables de bicicleta y detalles si no hay fecha y hora completas
function toggleCollapsibles() {
    const date = document.getElementById('date').value;
    const start = document.getElementById('start_time').value;
    const end = document.getElementById('end_time').value;
    const disabled = !(date && start && end);
    document.getElementById('btnBicicleta').disabled = disabled;
    document.getElementById('btnDetalles').disabled = disabled;
}
// Deshabilitar el botón PDF si no hay registros
function togglePdfButton() {
    var btnPdf = document.getElementById('btnPdf');
    var table = document.querySelector('.table-responsive tbody');
    if (!btnPdf || !table) return;
    var rows = table.querySelectorAll('tr');
    var hasData = false;
    rows.forEach(function(row) {
        if (row.children.length > 1) {
            hasData = true;
        }
    });
    btnPdf.classList.toggle('disabled', !hasData);
    btnPdf.setAttribute('aria-disabled', !hasData);
    btnPdf.tabIndex = hasData ? 0 : -1;
}
document.addEventListener('DOMContentLoaded', function() {
    toggleCollapsibles();
    document.getElementById('date').addEventListener('change', toggleCollapsibles);
    document.getElementById('start_time').addEventListener('change', toggleCollapsibles);
    document.getElementById('end_time').addEventListener('change', toggleCollapsibles);
    togglePdfButton();
});
</script>
@endpush
