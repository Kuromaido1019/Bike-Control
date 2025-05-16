@extends('layouts.app')

@section('title', 'Mi Usuario')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <!-- Tarjeta de usuario y perfil -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Ficha de Usuario</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <img src="{{ asset('img/undraw_profile.svg') }}" class="rounded-circle me-4" style="width: 90px; height: 90px; object-fit: cover;">
                        <div>
                            <h5 class="mb-1">{{ Auth::user()->name }}</h5>
                            <span class="badge bg-secondary">{{ ucfirst(Auth::user()->role) }}</span>
                        </div>
                    </div>
                    <hr>
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Nombre</dt>
                        <dd class="col-sm-7">{{ Auth::user()->name }}</dd>
                        <dt class="col-sm-5">Email</dt>
                        <dd class="col-sm-7">{{ Auth::user()->email }}</dd>
                        <dt class="col-sm-5">Rol</dt>
                        <dd class="col-sm-7">{{ ucfirst(Auth::user()->role) }}</dd>
                        @if(Auth::user()->profile)
                            <dt class="col-sm-5">RUT</dt>
                            <dd class="col-sm-7">{{ Auth::user()->profile->rut ?? '-' }}</dd>
                            <dt class="col-sm-5">Fecha de nacimiento</dt>
                            <dd class="col-sm-7">{{ Auth::user()->profile->birth_date ?? '-' }}</dd>
                            <dt class="col-sm-5">Teléfono</dt>
                            <dd class="col-sm-7">{{ Auth::user()->profile->phone ?? '-' }}</dd>
                            <dt class="col-sm-5">Teléfono alternativo</dt>
                            <dd class="col-sm-7">{{ Auth::user()->profile->alt_phone ?? '-' }}</dd>
                            <dt class="col-sm-5">Carrera</dt>
                            <dd class="col-sm-7">{{ Auth::user()->profile->career ?? '-' }}</dd>
                        @endif
                    </dl>
                </div>
                <div class="card-footer text-end bg-white border-0">
                    <button class="btn btn-outline-primary btn-sm" id="btnEditProfile">
                        <i class="fas fa-edit"></i> Editar mis datos
                    </button>
                </div>
            </div>
        </div>
        <!-- Modal para editar datos de usuario -->
        <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="editProfileForm" method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" id="editProfileModalLabel">Editar mis datos</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="editName" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="editName" name="name" value="{{ Auth::user()->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="editEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="editEmail" name="email" value="{{ Auth::user()->email }}" required>
                            </div>
                            @if(Auth::user()->profile)
                                <div class="mb-3">
                                    <label for="editPhone" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" id="editPhone" name="phone" value="{{ Auth::user()->profile->phone }}">
                                </div>
                                <div class="mb-3">
                                    <label for="editAltPhone" class="form-label">Teléfono alternativo</label>
                                    <input type="text" class="form-control" id="editAltPhone" name="alt_phone" value="{{ Auth::user()->profile->alt_phone }}">
                                </div>
                                <div class="mb-3">
                                    <label for="editCareer" class="form-label">Carrera</label>
                                    <input type="text" class="form-control" id="editCareer" name="career" value="{{ Auth::user()->profile->career }}">
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Fin modal editar datos de usuario -->
        <!-- Tarjeta de bicicletas -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Mis Bicicletas</h4>
                </div>
                <div class="card-body">
                    @if(Auth::user()->bikes && Auth::user()->bikes->count())
                        @foreach(Auth::user()->bikes as $bike)
                            <div class="mb-3 p-2 border rounded d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Marca:</strong> {{ $bike->brand ?? '-' }}<br>
                                    <strong>Modelo:</strong> {{ $bike->model ?? '-' }}<br>
                                    <strong>Color:</strong> {{ $bike->color ?? '-' }}
                                </div>
                                <a href="#" class="btn btn-outline-primary btn-sm ms-3 btn-edit-bike"
                                   data-id="{{ $bike->id }}"
                                   data-brand="{{ $bike->brand }}"
                                   data-model="{{ $bike->model }}"
                                   data-color="{{ $bike->color }}">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            </div>
                        @endforeach
                    @else
                        <div class="text-muted">No tienes bicicletas registradas.</div>
                    @endif
                </div>
            </div>
        </div>
        <!-- Modal para editar bicicleta -->
        <div class="modal fade" id="editBikeModal" tabindex="-1" aria-labelledby="editBikeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="editBikeForm" method="POST" action="">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editBikeModalLabel">Editar Bicicleta</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="editBikeBrand" class="form-label">Marca</label>
                                <input type="text" class="form-control" id="editBikeBrand" name="brand" required>
                            </div>
                            <div class="mb-3">
                                <label for="editBikeModel" class="form-label">Modelo</label>
                                <input type="text" class="form-control" id="editBikeModel" name="model" required>
                            </div>
                            <div class="mb-3">
                                <label for="editBikeColor" class="form-label">Color</label>
                                <input type="text" class="form-control" id="editBikeColor" name="color" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Fin modal editar bicicleta -->
    </div>
</div>
@endsection

@push('custom-scripts')
<script>
$(document).ready(function() {
    // Abrir modal de edición de bicicleta y cargar datos
    $(document).on('click', '.btn-edit-bike', function(e) {
        e.preventDefault();
        var bikeId = $(this).data('id');
        var brand = $(this).data('brand');
        var model = $(this).data('model');
        var color = $(this).data('color');
        $('#editBikeBrand').val(brand);
        $('#editBikeModel').val(model);
        $('#editBikeColor').val(color);
        $('#editBikeForm').attr('action', '/bikes/' + bikeId);
        var modalEl = document.getElementById('editBikeModal');
        var myModal = bootstrap.Modal.getInstance(modalEl);
        if (!myModal) {
            myModal = new bootstrap.Modal(modalEl);
        }
        myModal.show();
    });

    // Modal editar bicicleta: log de envío y errores
    $('#editBikeForm').on('submit', function(e) {
        console.log('Enviando formulario de edición de bicicleta...');
        var action = $(this).attr('action');
        var data = $(this).serialize();
        console.log('Action:', action);
        console.log('Data:', data);
        // Permitir el submit normal para ver si hay errores en red
        // Si quieres AJAX, descomenta lo siguiente:
        /*
        e.preventDefault();
        $.ajax({
            url: action,
            method: 'POST',
            data: data,
            success: function(resp) {
                console.log('Respuesta:', resp);
                Swal.fire({ icon: 'success', title: '¡Éxito!', text: 'Bicicleta actualizada', timer: 1500 });
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar la bicicleta' });
            }
        });
        */
    });

    // Abrir modal de edición de perfil
    $('#btnEditProfile').click(function() {
        var modalEl = document.getElementById('editProfileModal');
        var myModal = bootstrap.Modal.getInstance(modalEl);
        if (!myModal) {
            myModal = new bootstrap.Modal(modalEl);
        }
        myModal.show();
    });

    // Feedback visual tras guardar cambios
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: "{{ session('success') }}",
            confirmButtonText: 'Aceptar',
            timer: 2000,
            timerProgressBar: true
        });
    @endif
});
</script>
@endpush
