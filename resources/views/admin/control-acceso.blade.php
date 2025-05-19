@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h4 class="mb-4 text-primary">Control Acceso (Admin)</h4>

            <!-- Tabla de accesos -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="accessTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Visitante</th>
                            <th>RUT</th>
                            <th>Bicicleta</th>
                            <th>Guardia</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Observación</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accesses as $access)
                        <tr>
                            <td>{{ $access->id }}</td>
                            <td>{{ $access->user->name ?? '-' }}</td>
                            <td>{{ $access->user->rut ?? '-' }}</td>
                            <td>{{ $access->bike ? $access->bike->brand . ' ' . $access->bike->model : '-' }}</td>
                            <td>{{ $access->guardUser->name ?? '-' }}</td>
                            <td>{{ $access->entrance_time ? \Carbon\Carbon::parse($access->entrance_time)->format('H:i') : '-' }}</td>
                            <td>{{ $access->exit_time ? \Carbon\Carbon::parse($access->exit_time)->format('H:i') : '-' }}</td>
                            <td>{{ $access->observation ?? '-' }}</td>
                            <td>
                                <button class="btn btn-info btn-sm btn-edit-access"
                                    data-id="{{ $access->id }}"
                                    data-user_id="{{ $access->user_id }}"
                                    data-guard_id="{{ $access->guard_id }}"
                                    data-bike_id="{{ $access->bike_id }}"
                                    data-entrance_time="{{ $access->entrance_time }}"
                                    data-exit_time="{{ $access->exit_time }}"
                                    data-observation="{{ $access->observation }}"
                                    data-bs-toggle="modal" data-bs-target="#editAccessModal">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.control-acceso.destroy', $access->id) }}" style="display:inline" onsubmit="return confirm('¿Está seguro de eliminar este acceso?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar acceso -->
<div class="modal fade" id="editAccessModal" tabindex="-1" aria-labelledby="editAccessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editAccessForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editAccessModalLabel">Editar Acceso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_entrance_time" class="form-label">Hora de entrada</label>
                        <input type="time" class="form-control" name="entrance_time" id="edit_entrance_time">
                    </div>
                    <div class="mb-3">
                        <label for="edit_exit_time" class="form-label">Hora de salida</label>
                        <input type="time" class="form-control" name="exit_time" id="edit_exit_time">
                    </div>
                    <div class="mb-3">
                        <label for="edit_observation" class="form-label">Observación</label>
                        <textarea class="form-control" name="observation" id="edit_observation"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
