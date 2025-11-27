@extends('adminlte::page')

@section('title', 'Gestión de Colores')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Gestión de Colores</h1>
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
                            <i class="fas fa-plus"></i> Nuevo Color
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Código</th>
                                        <th>Muestra</th>
                                        <th>Descripción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($colors as $color)
                                    <tr>
                                        <td>{{ $color->id }}</td>
                                        <td>{{ $color->name }}</td>
                                        <td>{{ $color->code ?? 'N/A' }}</td>
                                        <td>
                                            @if($color->code)
                                                <div style="width: 30px; height: 30px; background-color: {{ $color->code }}; border: 1px solid #ccc; border-radius: 3px;"></div>
                                            @else
                                                <span class="text-muted">Sin código</span>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($color->description, 50) }}</td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal{{ $color->id }}">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="confirmDelete({{ $color->id }}, '{{ $color->name }}')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No hay colores registrados</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $colors->links() }}
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
                <h5 class="modal-title">Nuevo Color</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('colors.store') }}" method="POST">
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
                        <label for="code">Código de Color</label>
                        <div class="d-flex align-items-center" data-color-pair>
                            <input type="color" class="mr-2" data-role="code-picker" value="{{ Str::startsWith(old('code'), '#') ? old('code') : '#000000' }}">
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                   id="code" name="code" value="{{ old('code') }}" 
                                   placeholder="#FF0000">
                        </div>
                        <small class="form-text text-muted">Puedes elegir en la paleta o pegar un HEX (#FF0000)</small>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="description">Descripción <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
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
@foreach($colors as $color)
<div class="modal fade" id="editModal{{ $color->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Editar Color #{{ $color->id }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('colors.update', $color) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name{{ $color->id }}">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name{{ $color->id }}" name="name" value="{{ old('name', $color->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="code{{ $color->id }}">Código de Color</label>
                        <div class="d-flex align-items-center" data-color-pair>
                            <input type="color" class="mr-2" data-role="code-picker" value="{{ Str::startsWith(old('code', $color->code), '#') ? old('code', $color->code) : '#000000' }}">
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                   id="code{{ $color->id }}" name="code" value="{{ old('code', $color->code) }}" 
                                   placeholder="#FF0000">
                        </div>
                        <small class="form-text text-muted">Puedes elegir en la paleta o pegar un HEX (#FF0000)</small>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="description{{ $color->id }}">Descripción <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description{{ $color->id }}" name="description" rows="3" required>{{ old('description', $color->description) }}</textarea>
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
    if (confirm(`¿Estás seguro de eliminar el color "${name}"?`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/colors/${id}`;
        form.submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    function isValidHex(value) {
        return /^#([0-9A-Fa-f]{6}|[0-9A-Fa-f]{3})$/.test(value);
    }

    document.querySelectorAll('[data-color-pair]').forEach(function(pair) {
        const picker = pair.querySelector('[data-role="code-picker"]');
        const input = pair.querySelector('input[name="code"]');
        if (!picker || !input) return;

        // Inicial: si el texto tiene HEX válido, sincroniza al picker
        if (input.value && isValidHex(input.value)) {
            picker.value = input.value;
        }

        // Cambiar desde la paleta actualiza el texto
        picker.addEventListener('input', function() {
            input.value = picker.value;
        });

        // Cambiar texto (si HEX válido) actualiza la paleta
        input.addEventListener('input', function() {
            if (isValidHex(input.value)) {
                picker.value = input.value;
            }
        });
    });
});
</script>
@endpush