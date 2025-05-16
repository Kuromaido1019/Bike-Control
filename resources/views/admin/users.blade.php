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
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ucfirst($user->role) }}</td>
                        <td>
                            <button
                                class="btn btn-sm btn-info btn-edit"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}"
                                data-role="{{ $user->role }}">
                                Editar
                            </button>

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
                        <label for="role" class="form-label">Rol</label>
                        <select class="form-control @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="guardia" {{ old('role') == 'guardia' ? 'selected' : '' }}>Guardia</option>
                            <option value="visitante" {{ old('role') == 'visitante' ? 'selected' : '' }}>Visitante</option>
                        </select>
                    </div>

                    <div class="mb-3" id="passwordField">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
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
<script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            }
        });

        // Al abrir el modal para agregar, limpiar el método PUT si existe
        $('#btnAddUser').click(function() {
            $('#userModalLabel').text('Agregar Nuevo Usuario');
            $('#submitButton').text('Guardar');
            $('#userForm').attr('action', "{{ route('admin.users.store') }}");
            $('#_method').val('POST');
            $('#passwordField').show();
            $('#password').prop('required', true).val('');
            $('#user_id').val('');
            $('#name').val('');
            $('#email').val('');
            $('#role').val('admin');
        });

        // Delegación para que funcione con DataTables
        $(document).on('click', '.btn-edit', function() {
            console.log('Botón editar presionado');
            var id = $(this).data('id');
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
            $('#role').val(role);
            $('#passwordField').hide();
            $('#password').prop('required', false).val('');

            // Solución Bootstrap 5: Si el modal ya está instanciado, reutilízalo
            var modalEl = document.getElementById('userModal');
            var myModal = bootstrap.Modal.getInstance(modalEl);
            if (!myModal) {
                myModal = new bootstrap.Modal(modalEl);
            }
            myModal.show();
        });

        // Al cerrar el modal, restablecer el formulario
        $('#userModal').on('hidden.bs.modal', function() {
            $('#userForm').trigger('reset');
            $('#_method').val('POST');
            $('#userForm').attr('action', "{{ route('admin.users.store') }}");
            $('#userModalLabel').text('Agregar Nuevo Usuario');
            $('#submitButton').text('Guardar');
            $('#passwordField').show();
            $('#password').prop('required', true).val('');
        });

        // Validar el formulario antes de enviarlo
        $('#userForm').submit(function(e) {
            // Solo validar password si el campo está visible (es creación)
            if ($('#passwordField').is(':visible')) {
                let password = $('#password').val();
                if (password.length < 6) {
                    e.preventDefault(); // Cancelar el envío
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'La contraseña debe tener al menos 6 caracteres.',
                            confirmButtonText: 'Aceptar'
                        });
                    } else {
                        alert('La contraseña debe tener al menos 6 caracteres.');
                    }
                    $('#password').focus();
                }
            }
        });

        // Confirmación con SweetAlert para eliminar usuario
        $(document).on('submit', '.form-delete-user', function(e) {
            e.preventDefault();
            var form = this;
            Swal.fire({
                title: '¿Deseas eliminar este usuario?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
<script>
$(document).ready(function() {
    @if ($errors->any())
        var myModal = new bootstrap.Modal(document.getElementById('userModal'));
        myModal.show();
    @endif
});
</script>

@endpush
