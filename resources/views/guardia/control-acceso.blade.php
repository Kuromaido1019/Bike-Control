@extends('layouts.app')

@section('title', 'Control Acceso')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h4 class="mb-4 text-primary">Control Acceso</h4>

            <!-- Botones para agregar nuevo acceso y para ingreso rápido -->
            <div class="mb-3 d-flex gap-2">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAccessModal">
                    <i class="fas fa-plus"></i> Nuevo Acceso (Usuario Registrado)
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quickAccessModal">
                    <i class="fas fa-user-plus"></i> Nuevo Ingreso Rápido
                </button>
            </div>

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
                            <th>Acciones</th>
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
                            <td>
                                @if($access->entrance_time)
                                    {{ \Carbon\Carbon::parse($access->entrance_time)->format('H:i') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($access->exit_time)
                                    {{ \Carbon\Carbon::parse($access->exit_time)->format('H:i') }}
                                @else
                                    <form method="POST" action="{{ route('guard.control-acceso.update', $access->id) }}" style="display:inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="exit_time" value="{{ now()->format('Y-m-d\TH:i') }}">
                                        <input type="hidden" name="user_id" value="{{ $access->user_id }}">
                                        <input type="hidden" name="guard_id" value="{{ $access->guard_id }}">
                                        <input type="hidden" name="bike_id" value="{{ $access->bike_id }}">
                                        <input type="hidden" name="entrance_time" value="{{ $access->entrance_time }}">
                                        <input type="hidden" name="observation" value="{{ $access->observation }}">
                                        <button type="submit" class="btn btn-success btn-sm">Marcar Salida</button>
                                    </form>
                                @endif
                            </td>
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
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar acceso -->
<div class="modal fade" id="addAccessModal" tabindex="-1" aria-labelledby="addAccessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('guard.control-acceso.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addAccessModalLabel">Nuevo Acceso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="visitor_rut" class="form-label">RUT del Visitante</label>
                        <input type="text" class="form-control" id="visitor_rut" name="visitor_rut" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="bike_id" class="form-label">Bicicleta</label>
                        <select class="form-control" name="bike_id" id="bike_id" disabled>
                            <option value="">Seleccione un visitante primero</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="entrance_time" class="form-label">Fecha y hora de entrada</label>
                        <input type="datetime-local" class="form-control" name="entrance_time" required>
                    </div>
                    <div class="mb-3">
                        <label for="observation" class="form-label">Observación</label>
                        <textarea class="form-control" name="observation"></textarea>
                    </div>
                    <input type="hidden" name="guard_id" value="{{ Auth::id() }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ingreso rápido (wizard 2 pasos) -->
<div class="modal fade" id="quickAccessModal" tabindex="-1" aria-labelledby="quickAccessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Paso 1: Usuario -->
            <form id="quickUserForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="quickAccessModalLabel">Ingreso Rápido de Visitante - Paso 1</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="quick_name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="quick_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="quick_rut" class="form-label">RUT</label>
                        <input type="text" class="form-control" id="quick_rut" name="rut" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="quick_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="quick_email" name="email" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="quick_phone" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="quick_phone" name="phone" required>
                    </div>
                    <input type="hidden" name="guard_id" value="{{ Auth::id() }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="quickUserNext">Siguiente</button>
                </div>
            </form>
            <!-- Paso 2: Bicicleta y confirmación -->
            <form id="quickBikeForm" style="display:none;">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Ingreso Rápido de Visitante - Paso 2</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="quick_bike_brand" class="form-label">Marca de Bicicleta</label>
                        <input type="text" class="form-control" id="quick_bike_brand" name="bike_brand" required>
                    </div>
                    <div class="mb-3">
                        <label for="quick_bike_color" class="form-label">Color de Bicicleta</label>
                        <input type="text" class="form-control" id="quick_bike_color" name="bike_color" required>
                    </div>
                    <div class="mb-3">
                        <label for="quick_observation" class="form-label">Observación</label>
                        <textarea class="form-control" name="observation" id="quick_observation"></textarea>
                    </div>
                    <input type="hidden" name="user_id" id="quick_user_id">
                    <input type="hidden" name="guard_id" value="{{ Auth::id() }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="quickBikeBack">Atrás</button>
                    <button type="button" class="btn btn-success" id="quickBikeSubmit">Registrar Ingreso</button>
                </div>
            </form>
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
                    <input type="hidden" name="guard_id" value="{{ Auth::id() }}">
                    <div class="mb-3">
                        <label for="edit_user_id" class="form-label">Visitante</label>
                        <select class="form-control" name="user_id" id="edit_user_id" required>
                            <option value="">Seleccione...</option>
                            @foreach($visitantes as $visitante)
                                <option value="{{ $visitante->id }}">{{ $visitante->name }} ({{ $visitante->rut }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_bike_id" class="form-label">Bicicleta</label>
                        <select class="form-control" name="bike_id" id="edit_bike_id">
                            <option value="">Sin bicicleta</option>
                            @foreach($bikes as $bike)
                                <option value="{{ $bike->id }}">{{ $bike->brand }} {{ $bike->model }} ({{ $bike->user->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_entrance_time" class="form-label">Fecha y hora de entrada</label>
                        <input type="datetime-local" class="form-control" name="entrance_time" id="edit_entrance_time" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_exit_time" class="form-label">Fecha y hora de salida</label>
                        <input type="datetime-local" class="form-control" name="exit_time" id="edit_exit_time">
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('#accessTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
        }
    });

    // Rellenar modal de edición con los datos del registro
    function formatForDatetimeLocal(datetime) {
        if (!datetime) return '';
        let [date, time] = datetime.split(' ');
        if (!date || !time) return '';
        let [hh, mm] = time.split(':');
        return `${date}T${hh}:${mm}`;
    }
    $('.btn-edit-access').on('click', function() {
        const btn = $(this);
        const id = btn.data('id');
        $('#editAccessForm').attr('action', `/guard/control-acceso/${id}`);
        $('#edit_user_id').val(btn.data('user_id'));
        $('#edit_bike_id').val(btn.data('bike_id'));
        $('#edit_entrance_time').val(formatForDatetimeLocal(btn.data('entrance_time')));
        $('#edit_exit_time').val(btn.data('exit_time') ? formatForDatetimeLocal(btn.data('exit_time')) : '');
        $('#edit_observation').val(btn.data('observation'));
    });

    // Lógica para cargar bicicletas según RUT
    $('#visitor_rut').on('blur', function() {
        var rut = $(this).val();
        if (!rut) return;
        $('#bike_id').prop('disabled', true).html('<option value="">Buscando...</option>');
        $.ajax({
            url: '/api/bicicletas-por-rut/' + rut,
            method: 'GET',
            success: function(data) {
                if (data.length > 0) {
                    var options = '<option value="">Sin bicicleta</option>';
                    data.forEach(function(bike) {
                        options += `<option value="${bike.id}">${bike.brand} ${bike.model}</option>`;
                    });
                    $('#bike_id').html(options).prop('disabled', false);
                } else {
                    $('#bike_id').html('<option value="">Sin bicicletas registradas</option>').prop('disabled', true);
                }
            },
            error: function() {
                $('#bike_id').html('<option value="">Error al buscar</option>').prop('disabled', true);
            }
        });
    });
});

$(function() {
    let createdUserId = null;
    // Paso 1: Crear usuario y perfil
    $('#quickUserNext').on('click', function(e) {
        e.preventDefault();
        let form = $('#quickUserForm');
        $.ajax({
            url: "{{ route('guard.control-acceso.quick.user') }}",
            method: 'POST',
            data: form.serialize(),
            success: function(resp) {
                if (resp.success) {
                    createdUserId = resp.user_id;
                    $('#quick_user_id').val(resp.user_id);
                    $('#quickUserForm').hide();
                    $('#quickBikeForm').show();
                } else {
                    Swal.fire('Error', resp.message || 'No se pudo crear el usuario', 'error');
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error al crear usuario';
                Swal.fire('Error', msg, 'error');
            }
        });
    });
    // Paso 2: Registrar bicicleta y acceso
    $('#quickBikeSubmit').on('click', function(e) {
        e.preventDefault();
        let form = $('#quickBikeForm');
        Swal.fire({
            title: '¿Confirmar ingreso?',
            text: '¿Deseas registrar el acceso de este visitante?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, registrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('guard.control-acceso.quick.bike') }}",
                    method: 'POST',
                    data: form.serialize(),
                    success: function(resp) {
                        if (resp.success) {
                            Swal.fire('¡Listo!', 'Acceso registrado correctamente.', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', resp.message || 'No se pudo registrar el acceso', 'error');
                        }
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error al registrar acceso';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            } else {
                // Si cancela, eliminar usuario creado
                if (createdUserId) {
                    $.ajax({
                        url: '/guard/control-acceso/quick/cancel/' + createdUserId,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                    });
                }
                $('#quickAccessModal').modal('hide');
            }
        });
    });
    // Botón atrás
    $('#quickBikeBack').on('click', function() {
        $('#quickBikeForm').hide();
        $('#quickUserForm').show();
    });
    // Reset wizard al cerrar modal
    $('#quickAccessModal').on('hidden.bs.modal', function() {
        $('#quickUserForm')[0].reset();
        $('#quickBikeForm')[0].reset();
        $('#quickUserForm').show();
        $('#quickBikeForm').hide();
        createdUserId = null;
    });
});
</script>
@endpush
