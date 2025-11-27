@extends('adminlte::page')

@section('title', 'Gestión de Modelos')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Gestión de Modelos</h1>
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
                            <i class="fas fa-plus"></i> Nuevo Modelo
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Marca</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Vehículos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($brandmodels as $model)
                                    <tr>
                                        <td>{{ $model->id }}</td>
                                        <td>{{ $model->brand->name }}</td>
                                        <td>{{ $model->name }}</td>
                                        <td>{{ Str::limit($model->description, 50) }}</td>
                                        <td><span class="badge badge-primary">{{ $model->vehicles_count }}</span></td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal{{ $model->id }}">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="confirmDelete({{ $model->id }}, '{{ $model->name }}')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No hay modelos registrados</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $brandmodels->links() }}
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
                <h5 class="modal-title">Nuevo Modelo</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('brandmodels.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="brand_id">Marca <span class="text-danger">*</span></label>
                        <select class="form-control @error('brand_id') is-invalid @enderror" 
                                id="brand_id" name="brand_id" required>
                            <option value="">Seleccionar marca</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('brand_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
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
@foreach($brandmodels as $model)
<div class="modal fade" id="editModal{{ $model->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Editar Modelo #{{ $model->id }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('brandmodels.update', $model) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="brand_id{{ $model->id }}">Marca <span class="text-danger">*</span></label>
                        <select class="form-control @error('brand_id') is-invalid @enderror" 
                                id="brand_id{{ $model->id }}" name="brand_id" required>
                            <option value="">Seleccionar marca</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" 
                                        {{ old('brand_id', $model->brand_id) == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('brand_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="name{{ $model->id }}">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name{{ $model->id }}" name="name" value="{{ old('name', $model->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="description{{ $model->id }}">Descripción</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description{{ $model->id }}" name="description" rows="3">{{ old('description', $model->description) }}</textarea>
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
    if (confirm(`¿Estás seguro de eliminar el modelo "${name}"?`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/brandmodels/${id}`;
        form.submit();
    }
}
</script>
@endpush