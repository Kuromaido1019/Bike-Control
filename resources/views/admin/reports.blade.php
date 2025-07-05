@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h4 class="mb-4 text-primary">Reportes</h4>
            <div class="alert alert-info">Aquí podrás generar y visualizar reportes del sistema.</div>
            <!-- Formulario de filtro por fecha y rango de horas -->
            <form class="row g-3 align-items-end mb-4" method="GET" action="{{ route('admin.reports.index') }}">
                <div class="col-md-4">
                    <label for="date" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="date" name="date" value="{{ request('date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-2">
                    <label for="start_time" class="form-label">Hora inicio</label>
                    <input type="time" class="form-control" id="start_time" name="start_time" value="{{ request('start_time', '00:00') }}" required>
                </div>
                <div class="col-md-2">
                    <label for="end_time" class="form-label">Hora fin</label>
                    <input type="time" class="form-control" id="end_time" name="end_time" value="{{ request('end_time', '23:59') }}" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary ms-2">Limpiar</a>
                    <a href="{{ route('admin.reports.pdf', array_merge(request()->all(), ['download' => 1])) }}" class="btn btn-success ms-2" target="_blank">
                        <i class="fas fa-file-pdf"></i> Generar PDF
                    </a>
                </div>
            </form>
            <!-- Aquí puedes mostrar una tabla previa de resultados si lo deseas -->
            @if(isset($accesses))
                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Usuario</th>
                                <th>Guardia</th>
                                <th>Bicicleta</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($accesses as $access)
                            <tr>
                                <td>{{ $access->user ? $access->user->name : '---' }}</td>
                                <td>{{ $access->guardUser ? $access->guardUser->name : '---' }}</td>
                                <td>{{ $access->bike ? $access->bike->brand . ' ' . $access->bike->model : '---' }}</td>
                                <td>{{ $access->entrance_time ? \Carbon\Carbon::parse($access->entrance_time)->format('d/m/Y H:i') : '---' }}</td>
                                <td>{{ $access->exit_time ? \Carbon\Carbon::parse($access->exit_time)->format('d/m/Y H:i') : '---' }}</td>
                                <td>{{ $access->observation ?? '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">Sin accesos registrados en este rango.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
