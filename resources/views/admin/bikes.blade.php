@extends('layouts.app')

@section('title', 'Gestión de Bicicletas')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Gestión de Bicicletas</h1>
    <a href="{{ request()->query('all') ? route('admin.bikes.index') : route('admin.bikes.index', ['all' => 1]) }}" class="btn btn-secondary btn-sm">
        {{ request()->query('all') ? 'Ver solo activas' : 'Ver todas' }}
    </a>
</div>

<!-- Modal Editar Bicicleta -->
<div class="modal fade" id="editBikeModal" tabindex="-1" aria-labelledby="editBikeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editBikeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editBikeModalLabel">Editar Bicicleta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editBikeId" name="bike_id">
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
<!-- Fin Modal Editar Bicicleta -->

<!-- Tabla de bicicletas -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Bicicletas Registradas</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="bikesTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Color</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bikes as $bike)
                        <tr>
                            <td>{{ $bike->id }}</td>
                            <td>{{ $bike->brand }}</td>
                            <td>{{ $bike->model }}</td>
                            <td>{{ $bike->color }}</td>
                            <td>{{ $bike->user ? $bike->user->name : '-' }}</td>
                            <td>
                                <span class="badge {{ $bike->estado == 'activo' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($bike->estado) }}
                                </span>
                            </td>
                            <td>
                                @if($bike->estado === 'activo')
                                    <button class="btn btn-sm btn-primary btn-edit-bike"
                                        data-id="{{ $bike->id }}"
                                        data-brand="{{ $bike->brand }}"
                                        data-model="{{ $bike->model }}"
                                        data-color="{{ $bike->color }}"
                                        data-bs-toggle="modal" data-bs-target="#editBikeModal">
                                        Editar
                                    </button>
                                    <form action="{{ route('admin.bikes.inactivate', $bike->id) }}" method="POST" style="display:inline-block;" class="form-inactivate-bike">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">Inactivar</button>
                                    </form>
                                    <form action="{{ route('admin.bikes.destroy', $bike->id) }}" method="POST" style="display:inline-block;" class="form-delete-bike">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.bikes.activate', $bike->id) }}" method="POST" style="display:inline-block;" class="form-activate-bike">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Activar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No hay bicicletas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
    var bikesTable = $('#bikesTable').DataTable({
        language: {
            url: '/js/Spanish.json'
        },
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100]
    });

    // Delegación para abrir el modal y cargar datos
    $(document).on('click', '.btn-edit-bike', function() {
        var btn = $(this);
        var id = btn.data('id');
        var brand = btn.data('brand');
        var model = btn.data('model');
        var color = btn.data('color');
        $('#editBikeId').val(id);
        $('#editBikeBrand').val(brand);
        $('#editBikeModel').val(model);
        $('#editBikeColor').val(color);
        $('#editBikeForm').attr('action', '/admin/bikes/' + id);
    });

    // Confirmación y feedback para editar
    $('#editBikeForm').on('submit', function(e) {
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
                        var modalEl = document.getElementById('editBikeModal');
                        var myModal = bootstrap.Modal.getInstance(modalEl);
                        if (!myModal) {
                            myModal = new bootstrap.Modal(modalEl);
                        }
                        myModal.hide();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Editado!',
                            text: 'Los datos de la bicicleta fueron actualizados.'
                        }).then(() => location.reload());
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo editar la bicicleta.', 'error');
                    }
                });
            }
        });
    });

    // Confirmación y feedback para eliminar
    $(document).on('submit', '.form-delete-bike', function(e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: '¿Deseas eliminar esta bicicleta?',
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
                            title: '¡Eliminada!',
                            text: 'La bicicleta fue removida.'
                        }).then(() => location.reload());
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo eliminar la bicicleta.', 'error');
                    }
                });
            }
        });
    });

    // Confirmación y feedback para activar
    $(document).on('submit', '.form-activate-bike', function(e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: '¿Deseas activar esta bicicleta?',
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
                            title: '¡Activada!',
                            text: 'La bicicleta fue activada.'
                        }).then(() => location.reload());
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo activar la bicicleta.', 'error');
                    }
                });
            }
        });
    });
    // Confirmación y feedback para inactivar
    $(document).on('submit', '.form-inactivate-bike', function(e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: '¿Deseas inactivar esta bicicleta?',
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
                            title: '¡Inactivada!',
                            text: 'La bicicleta fue inactivada.'
                        }).then(() => location.reload());
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo inactivar la bicicleta.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
