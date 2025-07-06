@extends('layouts.app')

@section('title', 'Incidentes / Reclamos')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header bg-white border-bottom">
                    <h4 class="mb-0 text-primary">Incidentes / Reclamos Registrados</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#incidentModal">
                        <i class="fas fa-plus"></i> Nuevo Incidente
                    </button>
                    <!-- Modal para crear incidente -->
                    <div class="modal fade" id="incidentModal" tabindex="-1" aria-labelledby="incidentModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="incidentModalLabel">Registrar Incidente / Reclamo</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <form method="POST" action="{{ route('guard.incidents.store') }}">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="rut" class="form-label">RUT de la persona</label>
                                            <input type="text" class="form-control" id="rut" name="rut" maxlength="20" required value="{{ old('rut') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="categoria" class="form-label">Categoría</label>
                                            <select class="form-select" id="categoria" name="categoria" required>
                                                <option value="">Seleccione una categoría</option>
                                                <option value="Reclamo" @if(old('categoria')=='Reclamo') selected @endif>Reclamo</option>
                                                <option value="Incidente" @if(old('categoria')=='Incidente') selected @endif>Incidente</option>
                                                <option value="Sugerencia" @if(old('categoria')=='Sugerencia') selected @endif>Sugerencia</option>
                                                <option value="Otro" @if(old('categoria')=='Otro') selected @endif>Otro</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="detalle" class="form-label">Detalle</label>
                                            <textarea class="form-control" id="detalle" name="detalle" rows="4" required>{{ old('detalle') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Registrar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>RUT</th>
                                    <th>Categoría</th>
                                    <th>Detalle</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incidents as $incident)
                                <tr>
                                    <td>{{ $incident->id }}</td>
                                    <td>{{ $incident->created_at->format('d-m-Y H:i') }}</td>
                                    <td>{{ $incident->rut }}</td>
                                    <td>{{ $incident->categoria }}</td>
                                    <td>{{ $incident->detalle }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay incidentes registrados.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
