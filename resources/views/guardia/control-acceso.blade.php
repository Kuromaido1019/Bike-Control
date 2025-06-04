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
                <button class="btn btn-secondary" id="btnActivarCamara" type="button" data-bs-toggle="modal" data-bs-target="#qrModal">
                    <i class="fas fa-camera"></i> Escanear Credencial
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
                            <th>Editar</th>
                            @if(str_contains(request()->path(), 'admin'))
                            <th>Eliminar</th>
                            @endif
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
                                    {{ \Carbon\Carbon::parse($access->entrance_time)->format('H:i:s') }}
                                @endif
                            </td>
                            <td>
                                @if($access->exit_time)
                                    {{ \Carbon\Carbon::parse($access->exit_time)->format('H:i:s') }}
                                @else
                                    <form method="POST" action="{{ route('guard.control-acceso.mark-exit', $access->id) }}" style="display:inline">
                                        @csrf
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
                                    data-bs-toggle="modal" data-bs-target="#editAccessModal"
                                    @if($access->exit_time) disabled @endif
                                >
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                            </td>
                            @if(str_contains(request()->path(), 'admin'))
                            <td>
                                <form method="POST" action="{{ route('admin.control-acceso.destroy', $access->id) }}" style="display:inline" onsubmit="return confirm('¿Está seguro de eliminar este acceso?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Eliminar</button>
                                </form>
                            </td>
                            @endif
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
                    <!-- Solo permitir editar la observación -->
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

<!-- Modal para escaneo QR -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrModalLabel">Escanear Código QR</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qrStepEscaneo">
                    <div class="mb-2" id="qrCameraLoading">
                        <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                        <span class="ms-2">Buscando cámaras disponibles...</span>
                    </div>
                    <div class="mb-2" id="qrCameraSelectGroup" style="display:none;">
                        <label for="qrCameraSelect" class="form-label">Seleccionar cámara:</label>
                        <select id="qrCameraSelect" class="form-select" style="max-width: 320px; margin:auto;"></select>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="btnRecargarCamaras">Recargar cámaras</button>
                    </div>
                    <div id="reader" style="width:320px; height:240px; margin:auto;"></div>
                    <div id="qrResult" class="mt-3"></div>
                    <button type="button" class="btn btn-warning mt-2" id="btnDetenerEscaneo">Detener escaneo</button>
                    <div id="qrPermisoError" class="text-danger mt-2"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custom-scripts')
<!-- Espera activa para html5-qrcode antes de inicializar el escaneo -->
<script>
function waitForHtml5Qrcode(callback, maxWaitMs = 3000) {
    const start = Date.now();
    (function check() {
        if (window.Html5Qrcode) return callback();
        if (Date.now() - start > maxWaitMs) return callback('timeout');
        setTimeout(check, 50);
    })();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('#accessTable').DataTable({
        language: {
            url: '/js/Spanish.json'
        }
    });

    // Rellenar modal de edición con los datos del registro
    $('.btn-edit-access').on('click', function() {
        const btn = $(this);
        const id = btn.data('id');
        $('#editAccessForm').attr('action', `/guard/control-acceso/${id}`);
        $('#edit_observation').val(btn.data('observation'));
        // Formatear hora de entrada a HH:mm
        let entrada = btn.data('entrance_time');
        let horaEntrada = '';
        if (entrada && entrada.length >= 16 && entrada.includes(':')) {
            horaEntrada = entrada.substring(11,16);
        } else if (entrada && entrada.length >= 5) {
            horaEntrada = entrada.substring(0,5);
        }
        $('#edit_entrance_time').val(horaEntrada);
        // Formatear hora de salida a HH:mm
        let salida = btn.data('exit_time');
        let horaSalida = '';
        if (salida && salida.length >= 16 && salida.includes(':')) {
            horaSalida = salida.substring(11,16);
        } else if (salida && salida.length >= 5) {
            horaSalida = salida.substring(0,5);
        }
        $('#edit_exit_time').val(horaSalida);
    });

    // Enviar edición de observación solo si exit_time es null
    $('#editAccessForm').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        // Verificar si el botón de editar estaba habilitado (exit_time == null)
        const exitTime = $('#edit_exit_time').val();
        if (exitTime) {
            Swal.fire({
                icon: 'error',
                title: 'No permitido',
                text: 'No se puede editar la observación después de marcar la salida.',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }
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
                            text: 'La observación fue actualizada.'
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'No se pudo editar la observación.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    });

    // Lógica para cargar bicicletas según RUT
    $('#visitor_rut').on('change', function(e) {
        e.preventDefault(); // Evitar el comportamiento predeterminado
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
                    $('#bike_id').removeClass('is-invalid');
                    $('#bike_id').next('.invalid-feedback').remove();
                } else {
                    $('#bike_id').html('<option value="">Sin bicicletas registradas</option>').prop('disabled', true);
                    if ($('#bike_id').next('.invalid-feedback').length === 0) {
                        $('#bike_id').addClass('is-invalid').after('<div class="invalid-feedback">Este usuario no tiene bicicletas asociadas. No se puede registrar el acceso.</div>');
                    }
                    if ($('#addBikeBtn').length === 0) {
                        $('#bike_id').parent().append('<button type="button" class="btn btn-warning mt-2" id="addBikeBtn">Asociar Bicicleta</button>');
                    }
                }

                // Asegurarse de que el modal permanezca abierto
                var accessModal = new bootstrap.Modal(document.getElementById('addAccessModal'));
                accessModal.show();
            },
            error: function() {
                $('#bike_id').html('<option value="">Error al buscar</option>').prop('disabled', true);
                if ($('#bike_id').next('.invalid-feedback').length === 0) {
                    $('#bike_id').addClass('is-invalid').after('<div class="invalid-feedback">Error al buscar bicicletas.</div>');
                }
                if ($('#addBikeBtn').length === 0) {
                    $('#bike_id').parent().append('<button type="button" class="btn btn-warning mt-2" id="addBikeBtn">Asociar Bicicleta</button>');
                }

                // Asegurarse de que el modal permanezca abierto incluso en caso de error
                var accessModal = new bootstrap.Modal(document.getElementById('addAccessModal'));
                accessModal.show();
            }
        });
    });

    // Evento para mostrar modal de asociar bicicleta
    $(document).on('click', '#addBikeBtn', function() {
        // Cerrar el modal de "Nuevo Acceso"
        $('#addAccessModal').modal('hide');

        // Eliminar cualquier modal existente para evitar conflictos
        $('#modalAsociarBicicleta').remove();

        // Agregar el modal dinámicamente
        $('body').append(`
        <div class="modal fade" id="modalAsociarBicicleta" tabindex="-1" aria-labelledby="modalAsociarBicicletaLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="formAsociarBicicleta">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalAsociarBicicletaLabel">Asociar Bicicleta</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nueva_marca" class="form-label">Marca</label>
                                <input type="text" class="form-control" id="nueva_marca" name="brand" required>
                            </div>
                            <div class="mb-3">
                                <label for="nueva_modelo" class="form-label">Modelo</label>
                                <input type="text" class="form-control" id="nueva_modelo" name="model" required>
                            </div>
                            <div class="mb-3">
                                <label for="nueva_color" class="form-label">Color</label>
                                <input type="text" class="form-control" id="nueva_color" name="color" required>
                            </div>
                            <input type="hidden" id="rut_asociar" name="rut">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Guardar Bicicleta</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`);

        // Establecer el RUT en el campo oculto
        $('#rut_asociar').val($('#visitor_rut').val());

        // Inicializar el modal con Bootstrap
        var modal = new bootstrap.Modal(document.getElementById('modalAsociarBicicleta'));
        modal.show();
    });

    // Evento submit para asociar bicicleta
    $(document).on('submit', '#formAsociarBicicleta', function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            url: '/api/asociar-bicicleta',
            method: 'POST',
            data: form.serialize() + '&_token={{ csrf_token() }}',
            success: function(resp) {
                if (resp.success) {
                    $('#modalAsociarBicicleta').modal('hide'); // Cerrar modal de asociar bicicleta

                    // Reabrir el modal de "Nuevo Acceso"
                    var accessModal = new bootstrap.Modal(document.getElementById('addAccessModal'));
                    accessModal.show();

                    // Recargar bicicletas asociadas al usuario
                    $('#visitor_rut').trigger('blur');
                } else {
                    alert(resp.message || 'No se pudo asociar la bicicleta');
                }
            },
            error: function() {
                alert('Error al asociar bicicleta');
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

// --- En el flujo de escaneo QR, tras decidir el tipo de ingreso ---
let nextModalToOpen = null; // { modalId: string, runValue: string }

// --- Script de escaneo QR con html5-qrcode ---
let html5QrCodeScanner = null;
let qrCameras = [];
let currentCameraId = null;
const cameraModal = document.getElementById('qrModal');
const qrResult = document.getElementById('qrResult');
const qrStepEscaneo = document.getElementById('qrStepEscaneo');
const qrCameraSelect = document.getElementById('qrCameraSelect');
const qrPermisoError = document.getElementById('qrPermisoError');

function resetQrModal() {
    if (html5QrCodeScanner) {
        html5QrCodeScanner.stop().then(() => html5QrCodeScanner.clear());
        html5QrCodeScanner = null;
    }
    if (qrResult) qrResult.innerHTML = '';
    const readerDiv = document.getElementById('reader');
    if (readerDiv) readerDiv.innerHTML = '';
    if (qrPermisoError) qrPermisoError.innerHTML = '';
}

if (cameraModal) {
    cameraModal.addEventListener('shown.bs.modal', () => {
        resetQrModal();
        $('#qrCameraLoading').show();
        $('#qrCameraSelectGroup').hide();
        waitForHtml5Qrcode(function(timeout) {
            if (timeout) {
                if (qrPermisoError) qrPermisoError.innerHTML = 'No se encontró la librería de escaneo QR.';
                $('#qrCameraLoading').hide();
                return;
            }
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (qrPermisoError) qrPermisoError.innerHTML = 'Tu navegador no soporta acceso a la cámara (getUserMedia).';
                $('#qrCameraLoading').hide();
                return;
            }
            Html5Qrcode.getCameras().then(devices => {
                $('#qrCameraLoading').hide();
                if (!devices || devices.length === 0) {
                    if (qrPermisoError) qrPermisoError.innerHTML = 'No se detectaron cámaras disponibles.\n\nAsegúrate de que tu navegador tenga permisos y que la cámara esté conectada.';
                    return;
                }
                qrCameras = devices;
                qrCameraSelect.innerHTML = '';
                devices.forEach((cam, idx) => {
                    const opt = document.createElement('option');
                    opt.value = cam.id;
                    opt.text = cam.label || `Cámara ${idx+1}`;
                    qrCameraSelect.appendChild(opt);
                });
                if (devices.length === 1) {
                    $('#qrCameraSelectGroup').hide();
                } else {
                    $('#qrCameraSelectGroup').show();
                }
                currentCameraId = devices[0].id;
                startQrScan(currentCameraId);
            }).catch(err => {
                $('#qrCameraLoading').hide();
                if (qrPermisoError) qrPermisoError.innerHTML = 'Error al obtener cámaras: ' + (err && err.message ? err.message : err);
                console.error('Error al obtener cámaras:', err);
            });
        });
    });
    cameraModal.addEventListener('hidden.bs.modal', () => {
        resetQrModal();
        // --- Encadenar apertura de modal tras cierre completo del QR ---
        if (nextModalToOpen) {
            setTimeout(function() { // Espera mínima para asegurar backdrop limpio
                let modalInstance = null;
                if (nextModalToOpen.modalId === 'addAccessModal') {
                    $('#visitor_rut').val(nextModalToOpen.runValue).trigger('change');
                    modalInstance = new bootstrap.Modal(document.getElementById('addAccessModal'));
                    modalInstance.show();
                } else if (nextModalToOpen.modalId === 'quickAccessModal') {
                    $('#quick_rut').val(nextModalToOpen.runValue);
                    modalInstance = new bootstrap.Modal(document.getElementById('quickAccessModal'));
                    modalInstance.show();
                }
                // Solo limpiar backdrop si no hay ningún modal visible tras abrir el nuevo
                setTimeout(function() {
                    if ($('.modal.show').length === 0) {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                    }
                }, 300);
                nextModalToOpen = null;
            }, 50);
        }
    });
}

// Limpieza global de backdrop al cerrar cualquier modal de acceso
['addAccessModal', 'quickAccessModal'].forEach(function(modalId) {
    const modalEl = document.getElementById(modalId);
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function() {
            setTimeout(function() {
                if ($('.modal.show').length === 0) {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                }
            }, 100);
        });
    }
});

// Cambiar cámara
$(document).on('change', '#qrCameraSelect', function() {
    const newCameraId = this.value;
    if (newCameraId && newCameraId !== currentCameraId) {
        if (html5QrCodeScanner) {
            html5QrCodeScanner.stop().then(() => {
                html5QrCodeScanner.clear();
                startQrScan(newCameraId);
            });
        } else {
            startQrScan(newCameraId);
        }
    }
});

// Detener escaneo
$(document).on('click', '#btnDetenerEscaneo', function() {
    resetQrModal();
});

// Recargar cámaras
$(document).on('click', '#btnRecargarCamaras', function() {
    $('#qrCameraLoading').show();
    $('#qrCameraSelectGroup').hide();
    $('#qrPermisoError').html('');
    Html5Qrcode.getCameras().then(devices => {
        $('#qrCameraLoading').hide();
        if (!devices || devices.length === 0) {
            $('#qrPermisoError').html('No se detectaron cámaras disponibles.');
            return;
        }
        qrCameras = devices;
        qrCameraSelect.innerHTML = '';
        devices.forEach((cam, idx) => {
            const opt = document.createElement('option');
            opt.value = cam.id;
            opt.text = cam.label || `Cámara ${idx+1}`;
            qrCameraSelect.appendChild(opt);
        });
        if (devices.length === 1) {
            $('#qrCameraSelectGroup').hide();
        } else {
            $('#qrCameraSelectGroup').show();
        }
        currentCameraId = devices[0].id;
        startQrScan(currentCameraId);
    }).catch(err => {
        $('#qrCameraLoading').hide();
        $('#qrPermisoError').html('Error al obtener cámaras: ' + (err && err.message ? err.message : err));
        console.error('Error al obtener cámaras:', err);
    });
});

function isValidRut(rut) {
    // Valida formato chileno: 12345678-9 o 12345678-K
    return /^[0-9]{7,8}-[0-9kK]$/.test(rut);
}

function buscarUsuarioPorRut(rut) {
    return $.ajax({
        url: '/api/usuario-por-rut/' + encodeURIComponent(rut),
        method: 'GET'
    });
}

// --- Mejorar manejo de errores en el flujo QR ---
// Reemplazar el uso de .fail en el flujo QR por manejo de error más detallado
function startQrScan(cameraId) {
    currentCameraId = cameraId;
    qrResult.innerHTML = '';
    if (qrPermisoError) qrPermisoError.innerHTML = '';
    const readerDiv = document.getElementById('reader');
    if (readerDiv) {
        // Eliminar cualquier canvas o video residual antes de crear la nueva instancia
        while (readerDiv.firstChild) {
            readerDiv.removeChild(readerDiv.firstChild);
        }
    }
    if (html5QrCodeScanner && html5QrCodeScanner._isScanning) {
        html5QrCodeScanner.stop().then(() => html5QrCodeScanner.clear());
        html5QrCodeScanner = null;
    } else if (html5QrCodeScanner) {
        html5QrCodeScanner.clear();
        html5QrCodeScanner = null;
    }
    html5QrCodeScanner = new Html5Qrcode('reader', { useBarCodeDetectorIfSupported: true });
    html5QrCodeScanner.start(
        cameraId,
        { fps: 10, qrbox: { width: 250, height: 180 } },
        qrText => {
            let runValue = '';
            try {
                // Si el QR es una URL con parámetros, extraer RUN
                const url = new URL(qrText);
                runValue = url.searchParams.get('RUN');
            } catch (e) {
                // No es una URL válida, intentar extraer con regex
                const match = qrText.match(/RUN=([0-9kK\-]+)/);
                if (match) runValue = match[1];
            }
            if (!runValue) runValue = qrText;
            qrResult.innerHTML = `<span class='text-success'>RUN detectado: <b>${runValue}</b></span>`;
            if (isValidRut(runValue)) {
                // --- NUEVO FLUJO: SweetAlert para decidir tipo de ingreso ---
                setTimeout(function() {
                    Swal.fire({
                        title: '¿El usuario está previamente registrado?',
                        text: 'RUN/RUT detectado: ' + runValue,
                        icon: 'question',
                        showCancelButton: true,
                        showDenyButton: true,
                        confirmButtonText: 'Sí',
                        denyButtonText: 'No',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Usuario registrado
                            const qrModal = bootstrap.Modal.getInstance(document.getElementById('qrModal'));
                            if (qrModal) qrModal.hide();
                            nextModalToOpen = { modalId: 'addAccessModal', runValue };
                        } else if (result.isDenied) {
                            // Ingreso rápido
                            const qrModal = bootstrap.Modal.getInstance(document.getElementById('qrModal'));
                            if (qrModal) qrModal.hide();
                            nextModalToOpen = { modalId: 'quickAccessModal', runValue };
                        } else {
                            // Cancelar: no hacer nada
                        }
                    });
                }, 400);
                return;
            } else {
                Swal.fire('RUN inválido', 'El RUN/RUT escaneado no tiene un formato válido.', 'warning');
            }
            if (qrPermisoError) qrPermisoError.innerHTML = '';
            html5QrCodeScanner.stop().then(() => html5QrCodeScanner.clear());
        },
        errorMsg => {
            // No mostrar errores de escaneo en UI
        }
    ).catch(err => {
        if (qrResult) qrResult.innerHTML = `<span class="text-danger">Error al iniciar la cámara: ${err && err.message ? err.message : err}</span>`;
    });
}

// Log en consola al enviar el formulario de nuevo acceso (modal usuario registrado)
$('#addAccessModal form').on('submit', function(e) {
    const now = new Date();
    console.log('[LOG] Registro desde modal usuario registrado:', now.toLocaleString('es-CL'), 'ISO:', now.toISOString());
});

// Log en consola al presionar "Marcar Entrada" o "Marcar Salida" (ambos)
$(document).on('submit', 'form[action*="control-acceso"]', function(e) {
    if ($(this).find('input[name="mark_entrance"]').length) {
        const now = new Date();
        console.log('[LOG] Botón Marcar Entrada presionado:', now.toLocaleString('es-CL'), 'ISO:', now.toISOString());
    }
    if ($(this).find('input[name="mark_exit"]').length) {
        const now = new Date();
        console.log('[LOG] Botón Marcar Salida presionado:', now.toLocaleString('es-CL'), 'ISO:', now.toISOString());
    }
});
$(document).on('submit', 'form[action*="control-acceso/"][action$="/salida"]', function(e) {
    const now = new Date();
    console.log('[LOG] Botón Marcar Salida (ruta específica) presionado:', now.toLocaleString('es-CL'), 'ISO:', now.toISOString());
});
</script>
@endpush
