@extends('layouts.app')

@section('title', 'Detalle del Vehículo')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Detalle del Vehículo</h1>
            </div>
            <div class="col-sm-6">
                <a href="{{ route('vehicles.index') }}" class="btn btn-secondary float-right">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Información del Vehículo -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Información General</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">ID:</th>
                                <td>{{ $vehicle->id }}</td>
                            </tr>
                            <tr>
                                <th>Código:</th>
                                <td>{{ $vehicle->code }}</td>
                            </tr>
                            <tr>
                                <th>Nombre:</th>
                                <td>{{ $vehicle->name }}</td>
                            </tr>
                            <tr>
                                <th>Placa:</th>
                                <td>{{ $vehicle->plate }}</td>
                            </tr>
                            <tr>
                                <th>Año:</th>
                                <td>{{ $vehicle->year }}</td>
                            </tr>
                            <tr>
                                <th>Marca:</th>
                                <td>{{ $vehicle->brand->name }}</td>
                            </tr>
                            <tr>
                                <th>Modelo:</th>
                                <td>{{ $vehicle->model->name }}</td>
                            </tr>
                            <tr>
                                <th>Tipo:</th>
                                <td>{{ $vehicle->type->name }}</td>
                            </tr>
                            <tr>
                                <th>Color:</th>
                                <td>
                                    <span style="display: inline-block; width: 20px; height: 20px; background-color: {{ $vehicle->color->code ?? '#ccc' }}; border: 1px solid #ccc; border-radius: 3px; margin-right: 8px;"></span>
                                    {{ $vehicle->color->name }}
                                </td>
                            </tr>
                            <tr>
                                <th>Capacidad de Carga:</th>
                                <td>{{ $vehicle->load_capacity ? $vehicle->load_capacity . ' kg' : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Estado:</th>
                                <td>
                                    @if($vehicle->status)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Descripción:</th>
                                <td>{{ $vehicle->description }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Galería de Imágenes -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Galería de Imágenes</h3>
                        <div class="card-tools">
                            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#uploadModal">
                                <i class="fas fa-plus"></i> Subir Imagen
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($vehicle->images->count() > 0)
                            <div class="row">
                                @foreach($vehicle->images as $image)
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <img src="{{ Storage::url($image->image) }}" 
                                             class="card-img-top" 
                                             style="height: 150px; object-fit: cover;"
                                             alt="Imagen del vehículo">
                                        <div class="card-body p-2">
                                            @if($image->profile)
                                                <span class="badge badge-success mb-2">Imagen de Perfil</span>
                                            @else
                                                <form method="POST" action="{{ route('vehicles.images.profile', [$vehicle, $image]) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm mb-2">
                                                        <i class="fas fa-star"></i> Marcar como Perfil
                                                    </button>
                                                </form>
                                            @endif
                                            <br>
                                            <form method="POST" action="{{ route('vehicles.images.destroy', [$vehicle, $image]) }}" 
                                                  style="display: inline;" onsubmit="return confirm('¿Eliminar esta imagen?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-image fa-3x mb-3"></i>
                                <p>No hay imágenes subidas</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Subir Imagen -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title">Subir Imagen</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('vehicles.images.store', $vehicle) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="image">Imagen <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" 
                               id="image" name="image" accept="image/jpeg,image/png,image/webp" required>
                        <small class="form-text text-muted">
                            Formatos permitidos: JPG, PNG, WEBP. Tamaño máximo: 4MB
                        </small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="profile" name="profile" value="1">
                            <label class="form-check-label" for="profile">
                                Marcar como imagen de perfil
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Subir</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection











