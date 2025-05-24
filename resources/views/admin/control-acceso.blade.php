@extends('layouts.app')

@section('title', 'Control de Acceso')

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

@push('custom-scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css"/>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    var accessTable = $('#accessTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
        },
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100]
    });

    // Delegación para abrir el modal y cargar datos de edición
    $(document).on('click', '.btn-edit-access', function() {
        var btn = $(this);
        $('#editAccessForm').attr('action', '/admin/control-acceso/' + btn.data('id'));
        $('#edit_entrance_time').val(btn.data('entrance_time'));
        $('#edit_exit_time').val(btn.data('exit_time'));
        $('#edit_observation').val(btn.data('observation'));
    });

    // Confirmación y feedback para editar
    $('#editAccessForm').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: '¿Guardar cambios?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: form.action,
                    method: 'POST',
                    data: $(form).serialize(),
                    success: function(response) {
                        var modalEl = document.getElementById('editAccessModal');
                        var myModal = bootstrap.Modal.getInstance(modalEl);
                        if (!myModal) {
                            myModal = new bootstrap.Modal(modalEl);
                        }
                        myModal.hide();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Editado!',
                            text: 'El acceso fue actualizado.'
                        }).then(() => location.reload());
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo editar el acceso.', 'error');
                    }
                });
            }
        });
    });

    // Confirmación y feedback para eliminar
    $(document).on('submit', 'form[action*="control-acceso"]', function(e) {
        if (!$(this).hasClass('form-delete-access')) return;
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: '¿Deseas eliminar este acceso?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: form.action,
                    method: 'POST',
                    data: $(form).serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: 'El acceso fue removido.'
                        }).then(() => location.reload());
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo eliminar el acceso.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
