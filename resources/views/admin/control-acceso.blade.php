@extends('layouts.app')

@section('title', 'Control de Acceso')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <h4 class="mb-4 text-primary">Gestión de Accesos</h4>
            <div class="card shadow mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 text-primary">Accesos Registrados</h5>
                </div>
                <div class="card-body">
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
                                        <button class="btn btn-primary btn-sm btn-edit-access"
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
            url: '/js/Spanish.json'
        },
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100]
    });

    // Delegación para abrir el modal y cargar datos de edición
    $(document).on('click', '.btn-edit-access', function() {
        var btn = $(this);
        $('#editAccessForm').attr('action', '/admin/control-acceso/' + btn.data('id'));
        // DEBUG: Mostrar los valores en consola
        console.log('entrance_time:', btn.data('entrance_time'));
        console.log('exit_time:', btn.data('exit_time'));
        console.log('observation:', btn.data('observation'));
        // Si la hora viene como fecha completa, extraer HH:mm
        let entrada = btn.data('entrance_time');
        let horaEntrada = '';
        if (entrada && entrada !== 'null' && entrada !== 'undefined' && entrada !== '-') {
            if (entrada.length >= 16 && entrada.includes(':')) {
                // Formato tipo '2025-05-20 18:48:24'
                horaEntrada = entrada.substring(11,16);
            } else if (entrada.length >= 5) {
                horaEntrada = entrada.substring(0,5);
            }
        }
        $('#edit_entrance_time').val(horaEntrada);
        let salida = btn.data('exit_time');
        let horaSalida = '';
        if (salida && salida !== 'null' && salida !== 'undefined' && salida !== '-') {
            if (salida.length >= 16 && salida.includes(':')) {
                horaSalida = salida.substring(11,16);
            } else if (salida.length >= 5) {
                horaSalida = salida.substring(0,5);
            }
        }
        $('#edit_exit_time').val(horaSalida);
        let obs = btn.data('observation');
        $('#edit_observation').val((obs && obs !== 'null' && obs !== 'undefined' && obs !== '-') ? obs : '');
        // Asegura que los campos ocultos requeridos por el backend estén presentes
        if ($('#editAccessForm input[name="user_id"]').length === 0) {
            $('#editAccessForm').append('<input type="hidden" name="user_id" value="'+btn.data('user_id')+'">');
        } else {
            $('#editAccessForm input[name="user_id"]').val(btn.data('user_id'));
        }
        if ($('#editAccessForm input[name="guard_id"]').length === 0) {
            $('#editAccessForm').append('<input type="hidden" name="guard_id" value="'+btn.data('guard_id')+'">');
        } else {
            $('#editAccessForm input[name="guard_id"]').val(btn.data('guard_id'));
        }
        if ($('#editAccessForm input[name="bike_id"]').length === 0) {
            $('#editAccessForm').append('<input type="hidden" name="bike_id" value="'+btn.data('bike_id')+'">');
        } else {
            $('#editAccessForm input[name="bike_id"]').val(btn.data('bike_id'));
        }
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

    // Validación antes de enviar el formulario de edición de acceso
    $('#editAccessForm').on('submit', function(e) {
        // Validar hora de entrada (requerida y formato HH:mm)
        let entrada = $('#edit_entrance_time').val();
        let horaRegex = /^([01]\d|2[0-3]):([0-5]\d)$/;
        if (!entrada || !horaRegex.test(entrada)) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La hora de entrada es obligatoria y debe tener formato HH:mm.',
                confirmButtonText: 'Aceptar'
            });
            $('#edit_entrance_time').focus();
            return false;
        }
        // Validar hora de salida (si existe, debe tener formato HH:mm)
        let salida = $('#edit_exit_time').val();
        if (salida && !horaRegex.test(salida)) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La hora de salida debe tener formato HH:mm.',
                confirmButtonText: 'Aceptar'
            });
            $('#edit_exit_time').focus();
            return false;
        }
        // Validar observación (máximo 255 caracteres)
        let obs = $('#edit_observation').val();
        if (obs && obs.length > 255) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La observación no puede superar los 255 caracteres.',
                confirmButtonText: 'Aceptar'
            });
            $('#edit_observation').focus();
            return false;
        }
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
