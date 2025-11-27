@extends('adminlte::page')

@section('title', 'Gestión de Marcas')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Gestión de Marcas</h1>
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
                            <i class="fas fa-plus"></i> Nueva Marca
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Logo</th>
                                        <th>Descripción</th>
                                        <th>Modelos</th>
                                        <th>Vehículos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($brands as $brand)
                                    <tr>
                                        <td>{{ $brand->id }}</td>
                                        <td>{{ $brand->name }}</td>
                                        <td>
                                            @if($brand->logo)
                                                @php
                                                    $filename = basename($brand->logo);
                                                @endphp
                                                <img src="{{ route('brands.logos.serve', ['filename' => $filename]) }}" alt="{{ $brand->name }}" 
                                                     class="img-thumbnail" 
                                                     style="max-width: 80px; max-height: 80px; cursor: pointer;"
                                                     data-toggle="modal" 
                                                     data-target="#logoPreviewModal{{ $brand->id }}"
                                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'80\' height=\'80\'%3E%3Crect fill=\'%23ddd\' width=\'80\' height=\'80\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'12\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3EImagen no disponible%3C/text%3E%3C/svg%3E';">
                                            @else
                                                <span class="text-muted">Sin logo</span>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($brand->description, 50) }}</td>
                                        <td><span class="badge badge-info">{{ $brand->models_count }}</span></td>
                                        <td><span class="badge badge-primary">{{ $brand->vehicles_count }}</span></td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal{{ $brand->id }}">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="confirmDelete({{ $brand->id }}, '{{ $brand->name }}')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No hay marcas registradas</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $brands->links() }}
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
                <h5 class="modal-title">Nueva Marca</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('brands.store') }}" method="POST" enctype="multipart/form-data">
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
                        <label for="logo">Logo (Archivo)</label>
                        <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                               id="logo" name="logo" accept="image/*"
                               onchange="previewLogo(this, 'previewCreate')">
                        <div id="previewCreate" class="mt-2" style="display: none;">
                            <img id="preview-imgCreate" src="" alt="Vista previa" 
                                 class="img-thumbnail" 
                                 style="max-width: 150px; max-height: 150px;">
                            <br><small class="text-muted">Vista previa del logo</small>
                        </div>
                        <small class="form-text text-muted">Formatos permitidos: JPEG, PNG, JPG, GIF, SVG, WEBP (máx. 2MB)</small>
                        @error('logo')
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
@foreach($brands as $brand)
<!-- Modal de Vista Previa de Logo -->
@if($brand->logo)
<div class="modal fade" id="logoPreviewModal{{ $brand->id }}" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h5 class="modal-title">Logo - {{ $brand->name }}</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        @php
            $filename = basename($brand->logo);
        @endphp
        <img src="{{ route('brands.logos.serve', ['filename' => $filename]) }}" alt="{{ $brand->name }}" 
             class="img-fluid" style="max-height: 500px;"
             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'500\' height=\'500\'%3E%3Crect fill=\'%23ddd\' width=\'500\' height=\'500\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'24\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3EImagen no disponible%3C/text%3E%3C/svg%3E';">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
@endif

<div class="modal fade" id="editModal{{ $brand->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Editar Marca #{{ $brand->id }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('brands.update', $brand) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name{{ $brand->id }}">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name{{ $brand->id }}" name="name" value="{{ old('name', $brand->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="logo{{ $brand->id }}">Logo (Archivo)</label>
                        @if($brand->logo)
                            @php
                                $filename = basename($brand->logo);
                            @endphp
                            <div class="mb-2">
                                <img src="{{ route('brands.logos.serve', ['filename' => $filename]) }}" alt="{{ $brand->name }}" 
                                     class="img-thumbnail" 
                                     style="max-width: 150px; max-height: 150px; cursor: pointer;"
                                     data-toggle="modal" 
                                     data-target="#logoPreviewModal{{ $brand->id }}"
                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'150\' height=\'150\'%3E%3Crect fill=\'%23ddd\' width=\'150\' height=\'150\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3EImagen no disponible%3C/text%3E%3C/svg%3E';">
                                <br><small class="text-muted">Logo actual (click para ampliar)</small>
                            </div>
                        @endif
                        <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                               id="logo{{ $brand->id }}" name="logo" accept="image/*"
                               onchange="previewLogo(this, 'preview{{ $brand->id }}')">
                        <div id="preview{{ $brand->id }}" class="mt-2" style="display: none;">
                            <img id="preview-img{{ $brand->id }}" src="" alt="Vista previa" 
                                 class="img-thumbnail" 
                                 style="max-width: 150px; max-height: 150px;">
                            <br><small class="text-muted">Vista previa del nuevo logo</small>
                        </div>
                        <small class="form-text text-muted">Dejar vacío para mantener el logo actual. Formatos: JPEG, PNG, JPG, GIF, SVG, WEBP (máx. 2MB)</small>
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="description{{ $brand->id }}">Descripción</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description{{ $brand->id }}" name="description" rows="3">{{ old('description', $brand->description) }}</textarea>
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
    if (confirm(`¿Estás seguro de eliminar la marca "${name}"?`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/brands/${id}`;
        form.submit();
    }
}

// Función para previsualizar logo antes de subir
function previewLogo(input, previewId) {
    const preview = document.getElementById(previewId);
    const previewImg = document.getElementById('preview-img' + previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}
</script>
@endpush