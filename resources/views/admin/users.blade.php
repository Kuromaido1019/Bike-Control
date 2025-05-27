@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: "{{ addslashes(session('success')) }}",
                confirmButtonText: 'Aceptar',
                timer: 2500,
                timerProgressBar: true
            });
        });
    </script>
@endif

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Gestión de Usuarios</h1>
    <button
        class="btn btn-primary btn-sm"
        id="btnAddUser"
        data-bs-toggle="modal"
        data-bs-target="#userModal">
        <i class="fas fa-user-plus fa-sm"></i> Nuevo Usuario
    </button>
</div>

<!-- Tabla de usuarios -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Usuarios Registrados</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="usersTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>RUT</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->rut }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ ucfirst($user->role) }}</td>
                            <td>
                                <span class="badge {{ $user->estado == 'activo' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($user->estado) }}
                                </span>
                            </td>
                            <td>
                                @if($user->estado === 'activo')
                                    <button
                                        class="btn btn-sm btn-primary btn-edit"
                                        data-id="{{ $user->id }}"
                                        data-rut="{{ $user->rut }}"
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->email }}"
                                        data-role="{{ $user->role }}">
                                        Editar
                                    </button>
                                    <form action="{{ route('admin.users.inactivate', $user->id) }}" method="POST" style="display:inline-block;" class="form-inactivate-user">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">Inactivar</button>
                                    </form>
                                    <form
                                        action="{{ route('admin.users.destroy', $user->id) }}"
                                        method="POST"
                                        style="display:inline-block;" class="form-delete-user">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-danger">
                                            Eliminar
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.users.activate', $user->id) }}" method="POST" style="display:inline-block;" class="form-activate-user">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Activar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para agregar o editar usuario -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="userForm" method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <input type="hidden" id="user_id" name="user_id" value="">
                <input type="hidden" id="_method" name="_method" value="POST">

                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Agregar Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">

                    <!-- Mostrar errores aquí -->
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="rut" class="form-label">RUT</label>
                        <input type="text" class="form-control @error('rut') is-invalid @enderror" id="rut" name="rut" value="{{ old('rut', isset($user) ? $user->rut : '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Rol</label>
                        <select class="form-control @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="guardia" {{ old('role') == 'guardia' ? 'selected' : '' }}>Guardia</option>
                            <option value="visitante" {{ old('role') == 'visitante' ? 'selected' : '' }}>Visitante</option>
                        </select>
                    </div>

                    <div class="mb-3" id="passwordField">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="submitButton">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('custom-scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css"/>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    var usersTable = $('#usersTable').DataTable({
        language: {
            url: '/js/Spanish.json'
        },
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100]
    });

    // Botón editar usuario: abrir modal y cargar datos
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        var rut = $(this).data('rut');
        var name = $(this).data('name');
        var email = $(this).data('email');
        var role = $(this).data('role');
        $('#userModalLabel').text('Editar Usuario');
        $('#submitButton').text('Actualizar');
        $('#userForm').attr('action', '/admin/users/' + id);
        $('#_method').val('PUT');
        $('#user_id').val(id);
        $('#name').val(name);
        $('#email').val(email);
        $('#rut').val(rut);
        $('#role').val(role);
        $('#passwordField').hide();
        $('#password').prop('required', false).val('');
        var modalEl = document.getElementById('userModal');
        var myModal = bootstrap.Modal.getInstance(modalEl);
        if (!myModal) {
            myModal = new bootstrap.Modal(modalEl);
        }
        myModal.show();
    });

    // Al cerrar el modal, restaurar formulario a modo creación
    $('#userModal').on('hidden.bs.modal', function() {
        $('#userForm').trigger('reset');
        $('#_method').val('POST');
        $('#userForm').attr('action', "{{ route('admin.users.store') }}");
        $('#userModalLabel').text('Agregar Nuevo Usuario');
        $('#submitButton').text('Guardar');
        $('#passwordField').show();
        $('#password').prop('required', true).val('');
    });

    // Confirmación y feedback para activar usuario
    $(document).on('submit', '.form-activate-user', function(e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: '¿Deseas activar este usuario?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, activar',
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
                            title: '¡Activado!',
                            text: 'El usuario fue activado.'
                        }).then(() => location.reload());
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo activar el usuario.', 'error');
                    }
                });
            }
        });
    });
    // Confirmación y feedback para inactivar usuario
    $(document).on('submit', '.form-inactivate-user', function(e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: '¿Deseas inactivar este usuario?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, inactivar',
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
                            title: '¡Inactivado!',
                            text: 'El usuario fue inactivado.'
                        }).then(() => location.reload());
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo inactivar el usuario.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
