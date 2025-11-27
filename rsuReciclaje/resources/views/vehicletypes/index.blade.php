@extends('adminlte::page')

@section('title', 'Gestión de Tipos de Vehículos')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Gestión de Tipos de Vehículos</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <button class="btn btn-success mb-3" data-toggle="modal" data-target="#createModal">
                            <i class="fas fa-plus"></i> Nuevo Tipo
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vehicletypes as $type)
                                    <tr>
                                        <td>{{ $type->id }}</td>
                                        <td>{{ $type->name }}</td>
                                        <td>{{ Str::limit($type->description, 50) }}</td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal{{ $type->id }}">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="confirmDelete({{ $type->id }}, '{{ $type->name }}')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No hay tipos de vehículos registrados</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $vehicletypes->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Crear -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title">Nuevo Tipo de Vehículo</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('vehicletypes.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modales de Editar -->
@foreach($vehicletypes as $type)
<div class="modal fade" id="editModal{{ $type->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Editar Tipo #{{ $type->id }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('vehicletypes.update', $type) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name{{ $type->id }}">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name{{ $type->id }}" name="name" value="{{ old('name', $type->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="description{{ $type->id }}">Descripción</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description{{ $type->id }}" name="description" rows="3">{{ old('description', $type->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Formulario oculto para eliminar -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
function confirmDelete(id, name) {
    if (confirm(`¿Estás seguro de eliminar el tipo "${name}"?`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/vehicletypes/${id}`;
        form.submit();
    }
}
</script>
@endpush