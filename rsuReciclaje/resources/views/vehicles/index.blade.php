@extends('adminlte::page')

@section('title', 'Gestión de Vehículos')
@section('content_header')
  <h1>Gestión de Vehículos</h1>
@stop

@section('content')
  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

  <button class="btn btn-success mb-2" data-toggle="modal" data-target="#modalCreate">+ Nuevo Vehículo</button>

  <table class="table table-bordered table-striped" id="tbl-vehicles">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Placa</th>
                                        <th>Año</th>
                                        <th>Marca</th>
                                        <th>Modelo</th>
                                        <th>Logo</th>
                                        <th>Tipo</th>
                                        <th>Color</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
    <tbody>
      @foreach($vehicles as $vehicle)
        <tr>
          <td>{{ $vehicle->code }}</td>
          <td>{{ $vehicle->name }}</td>
          <td>{{ $vehicle->plate }}</td>
          <td>{{ $vehicle->year }}</td>
          <td>{{ $vehicle->brand->name }}</td>
          <td>{{ $vehicle->model->name ?? $vehicle->brandmodel->name ?? 'N/A' }}</td>
          <td>
            @if($vehicle->logoBrand && $vehicle->logoBrand->logo)
              @php
                  $filename = basename($vehicle->logoBrand->logo);
              @endphp
              <img src="{{ route('brands.logos.serve', ['filename' => $filename]) }}" alt="{{ $vehicle->logoBrand->name }}" 
                   style="max-width: 40px; max-height: 40px; border: 1px solid #ddd; padding: 2px;"
                   onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\'%3E%3Crect fill=\'%23ddd\' width=\'40\' height=\'40\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'10\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3EImagen no disponible%3C/text%3E%3C/svg%3E';">
            @else
              <span class="text-muted">Sin logo</span>
            @endif
          </td>
          <td>{{ $vehicle->type->name }}</td>
          <td>
            <span style="display: inline-block; width: 15px; height: 15px; background-color: {{ $vehicle->color->code ?? '#ccc' }}; border: 1px solid #ccc; border-radius: 2px; margin-right: 5px;"></span>
            {{ $vehicle->color->name }}
          </td>
          <td>
            @if($vehicle->status)
              <span class="badge badge-success">Activo</span>
            @else
              <span class="badge badge-danger">Inactivo</span>
            @endif
          </td>
          <td class="text-nowrap">
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalEdit{{ $vehicle->id }}">Editar</button>
            <form action="{{ route('vehicles.destroy',$vehicle) }}" method="POST" class="d-inline frm-delete">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm" data-delete>Eliminar</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{ $vehicles->links() }}

  {{-- Modal Crear --}}
  <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-height: 90vh; margin: 1.75rem auto;">
      <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header bg-success" style="flex-shrink: 0; border-bottom: 1px solid #dee2e6;">
          <h5 class="modal-title">Nuevo Vehículo</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form method="POST" action="{{ route('vehicles.store') }}">
          @csrf
          <input type="hidden" name="current_modal" value="modalCreate">
          <div class="modal-body" style="overflow-y: scroll; overflow-x: hidden; flex: 1; max-height: calc(90vh - 120px);">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code">Código <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" value="{{ old('code') }}" 
                                       placeholder="VEH-XXXX" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type_id">Tipo <span class="text-danger">*</span></label>
                                <select class="form-control @error('type_id') is-invalid @enderror" 
                                        id="type_id" name="type_id" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" {{ old('type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="plate">Placa <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('plate') is-invalid @enderror" 
                                       id="plate" name="plate" value="{{ old('plate') }}" 
                                       placeholder="ABC-123" required>
                                @error('plate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="year">Año <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('year') is-invalid @enderror" 
                                       id="year" name="year" value="{{ old('year') }}" 
                                       min="1950" max="{{ date('Y') }}" required>
                                @error('year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="color_id">Color <span class="text-danger">*</span></label>
                                <select class="form-control @error('color_id') is-invalid @enderror" 
                                        id="color_id" name="color_id" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($colors as $color)
                                        <option value="{{ $color->id }}" {{ old('color_id') == $color->id ? 'selected' : '' }}>
                                            {{ $color->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('color_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="brand_id">Marca <span class="text-danger">*</span></label>
                                <select class="form-control @error('brand_id') is-invalid @enderror" 
                                        id="brand_id" name="brand_id" required>
                                    <option value="">Seleccione...</option>
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
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="brandmodel_id_create">Modelo <span class="text-danger">*</span></label>
                                <select class="form-control @error('brandmodel_id') is-invalid @enderror" 
                                        id="brandmodel_id_create" name="brandmodel_id" required disabled>
                                    <option value="">Seleccione una marca primero</option>
                                </select>
                                @error('brandmodel_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="load_capacity">Capacidad de Carga (kg)</label>
                                <input type="number" class="form-control @error('load_capacity') is-invalid @enderror" 
                                       id="load_capacity" name="load_capacity" value="{{ old('load_capacity') }}" 
                                       step="0.01" min="0">
                                @error('load_capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fuel_capacity_l">Capacidad de Combustible (L)</label>
                                <input type="number" class="form-control @error('fuel_capacity_l') is-invalid @enderror" 
                                       id="fuel_capacity_l" name="fuel_capacity_l" value="{{ old('fuel_capacity_l') }}" 
                                       step="0.01" min="0">
                                @error('fuel_capacity_l')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="compaction_capacity_kg">Capacidad de Compactación (kg)</label>
                                <input type="number" class="form-control @error('compaction_capacity_kg') is-invalid @enderror" 
                                       id="compaction_capacity_kg" name="compaction_capacity_kg" value="{{ old('compaction_capacity_kg') }}" 
                                       step="0.01" min="0">
                                @error('compaction_capacity_kg')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="people_capacity">Capacidad de Personas</label>
                                <input type="number" class="form-control @error('people_capacity') is-invalid @enderror" 
                                       id="people_capacity" name="people_capacity" value="{{ old('people_capacity') }}" 
                                       min="1" max="50">
                                @error('people_capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Logo del Vehículo</label>
                        <div class="mb-2">
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#logoModal">
                                <i class="fas fa-images"></i> Seleccionar Logo
                            </button>
                            <input type="hidden" id="logo_id" name="logo_id" value="{{ old('logo_id') }}">
                            <div id="selected-logo-preview" class="mt-2" style="display: none;">
                                <img id="selected-logo-img" src="" alt="Logo seleccionado" 
                                     style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 5px;">
                                <br><small class="text-muted" id="selected-logo-name"></small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {{ old('status') ? 'checked' : '' }}>
                            <label class="form-check-label" for="status">
                                Vehículo activo
                            </label>
                        </div>
                    </div>
                </div>
          <div class="modal-footer" style="flex-shrink: 0;">
            <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button class="btn btn-success">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Modales de Editar --}}
  @foreach($vehicles as $vehicle)
  <div class="modal fade" id="modalEdit{{ $vehicle->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-height: 90vh; margin: 1.75rem auto;">
      <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header bg-primary" style="flex-shrink: 0; border-bottom: 1px solid #dee2e6;">
          <h5 class="modal-title">Editar Vehículo</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form method="POST" action="{{ route('vehicles.update',$vehicle) }}">
          @csrf @method('PUT')
          <input type="hidden" name="current_modal" value="modalEdit{{ $vehicle->id }}">
          <div class="modal-body" style="overflow-y: scroll; overflow-x: hidden; flex: 1; max-height: calc(90vh - 120px);">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code{{ $vehicle->id }}">Código <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code{{ $vehicle->id }}" name="code" value="{{ old('code', $vehicle->code) }}" 
                                       placeholder="VEH-XXXX" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type_id{{ $vehicle->id }}">Tipo <span class="text-danger">*</span></label>
                                <select class="form-control @error('type_id') is-invalid @enderror" 
                                        id="type_id{{ $vehicle->id }}" name="type_id" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" 
                                                {{ old('type_id', $vehicle->type_id) == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name{{ $vehicle->id }}">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name{{ $vehicle->id }}" name="name" value="{{ old('name', $vehicle->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="plate{{ $vehicle->id }}">Placa <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('plate') is-invalid @enderror" 
                                       id="plate{{ $vehicle->id }}" name="plate" value="{{ old('plate', $vehicle->plate) }}" 
                                       placeholder="ABC-123" required>
                                @error('plate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="year{{ $vehicle->id }}">Año <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('year') is-invalid @enderror" 
                                       id="year{{ $vehicle->id }}" name="year" value="{{ old('year', $vehicle->year) }}" 
                                       min="1950" max="{{ date('Y') }}" required>
                                @error('year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="color_id{{ $vehicle->id }}">Color <span class="text-danger">*</span></label>
                                <select class="form-control @error('color_id') is-invalid @enderror" 
                                        id="color_id{{ $vehicle->id }}" name="color_id" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($colors as $color)
                                        <option value="{{ $color->id }}" 
                                                {{ old('color_id', $vehicle->color_id) == $color->id ? 'selected' : '' }}>
                                            {{ $color->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('color_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="brand_id{{ $vehicle->id }}">Marca <span class="text-danger">*</span></label>
                                <select class="form-control @error('brand_id') is-invalid @enderror" 
                                        id="brand_id{{ $vehicle->id }}" name="brand_id" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" 
                                                {{ old('brand_id', $vehicle->brand_id) == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('brand_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="brandmodel_id_edit_{{ $vehicle->id }}">Modelo <span class="text-danger">*</span></label>
                                <select class="form-control @error('brandmodel_id') is-invalid @enderror" 
                                        id="brandmodel_id_edit_{{ $vehicle->id }}" name="brandmodel_id" required>
                                    <option value="">Seleccione...</option>
                                </select>
                                @error('brandmodel_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="load_capacity{{ $vehicle->id }}">Capacidad de Carga (kg)</label>
                                <input type="number" class="form-control @error('load_capacity') is-invalid @enderror" 
                                       id="load_capacity{{ $vehicle->id }}" name="load_capacity" 
                                       value="{{ old('load_capacity', $vehicle->load_capacity) }}" 
                                       step="0.01" min="0">
                                @error('load_capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fuel_capacity_l{{ $vehicle->id }}">Capacidad de Combustible (L)</label>
                                <input type="number" class="form-control @error('fuel_capacity_l') is-invalid @enderror" 
                                       id="fuel_capacity_l{{ $vehicle->id }}" name="fuel_capacity_l" 
                                       value="{{ old('fuel_capacity_l', $vehicle->fuel_capacity_l) }}" 
                                       step="0.01" min="0">
                                @error('fuel_capacity_l')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="compaction_capacity_kg{{ $vehicle->id }}">Capacidad de Compactación (kg)</label>
                                <input type="number" class="form-control @error('compaction_capacity_kg') is-invalid @enderror" 
                                       id="compaction_capacity_kg{{ $vehicle->id }}" name="compaction_capacity_kg" 
                                       value="{{ old('compaction_capacity_kg', $vehicle->compaction_capacity_kg) }}" 
                                       step="0.01" min="0">
                                @error('compaction_capacity_kg')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="people_capacity{{ $vehicle->id }}">Capacidad de Personas</label>
                                <input type="number" class="form-control @error('people_capacity') is-invalid @enderror" 
                                       id="people_capacity{{ $vehicle->id }}" name="people_capacity" 
                                       value="{{ old('people_capacity', $vehicle->people_capacity) }}" 
                                       min="1" max="50">
                                @error('people_capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description{{ $vehicle->id }}">Descripción</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description{{ $vehicle->id }}" name="description" rows="3">{{ old('description', $vehicle->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Logo del Vehículo</label>
                        <div class="mb-2">
                            <button type="button" class="btn btn-info btn-sm btn-select-logo" 
                                    data-vehicle-id="{{ $vehicle->id }}" 
                                    data-current-logo-id="{{ $vehicle->logo_id }}">
                                <i class="fas fa-images"></i> Seleccionar Logo
                            </button>
                            <input type="hidden" id="logo_id{{ $vehicle->id }}" name="logo_id" value="{{ old('logo_id', $vehicle->logo_id) }}">
                            <div id="selected-logo-preview{{ $vehicle->id }}" class="mt-2" style="{{ $vehicle->logoBrand && $vehicle->logoBrand->logo ? '' : 'display: none;' }}">
                                @if($vehicle->logoBrand && $vehicle->logoBrand->logo)
                                    @php
                                        $filename = basename($vehicle->logoBrand->logo);
                                    @endphp
                                    <img src="{{ route('brands.logos.serve', ['filename' => $filename]) }}" alt="{{ $vehicle->logoBrand->name }}" 
                                         style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 5px;"
                                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\'%3E%3Crect fill=\'%23ddd\' width=\'100\' height=\'100\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'12\' dy=\'10.5\' font-weight=\'bold\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3EImagen no disponible%3C/text%3E%3C/svg%3E';">
                                    <br><small class="text-muted">{{ $vehicle->logoBrand->name }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="status{{ $vehicle->id }}" name="status" value="1" {{ old('status', $vehicle->status) ? 'checked' : '' }}>
                            <label class="form-check-label" for="status{{ $vehicle->id }}">
                                Vehículo activo
                            </label>
                        </div>
                    </div>
                </div>
          <div class="modal-footer" style="flex-shrink: 0;">
            <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endforeach

  {{-- Modal de Selección de Logos --}}
  <div class="modal fade" id="logoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h5 class="modal-title">
            <i class="fas fa-images"></i> Seleccionar Logo
          </h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="logos-container" class="row">
            <div class="col-12 text-center">
              <i class="fas fa-spinner fa-spin"></i> Cargando logos...
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="btn-confirm-logo" disabled>Confirmar Selección</button>
        </div>
      </div>
    </div>
  </div>

@stop

@section('js')
<script>
// Helper: carga modelos para una marca en un <select> dado
function loadModels(brandId, $select, selectedId = null) {
    $select.prop('disabled', true).empty().append('<option value="">Cargando…</option>');
    if (!brandId) {
        $select.prop('disabled', true).empty().append('<option value="">Seleccione una marca primero</option>');
        return;
    }
    var url = "{{ route('brandmodels.byBrand', ':id') }}".replace(':id', brandId);
    $.get(url, function(items){
        $select.empty().append('<option value="">Seleccione…</option>');
        if (items && items.length) {
            items.forEach(function(m){
                var opt = $('<option>').val(m.id).text(m.name);
                if (selectedId && Number(selectedId) === Number(m.id)) opt.attr('selected', 'selected');
                $select.append(opt);
            });
            $select.prop('disabled', false);
        } else {
            $select.append('<option value="">No hay modelos para esta marca</option>');
            $select.prop('disabled', true);
        }
    }).fail(function(){
        $select.empty().append('<option value="">Error cargando modelos</option>').prop('disabled', true);
    });
}

// Crear
$('#brand_id').on('change', function(){
    loadModels($(this).val(), $('#brandmodel_id_create'));
});

// Editar: al abrir cada modal, cargar según la marca seleccionada
@foreach($vehicles as $v)
$('#modalEdit{{ $v->id }}').on('shown.bs.modal', function(){
    var $brand  = $('#brand_id{{ $v->id }}');
    var $model  = $('#brandmodel_id_edit_{{ $v->id }}');
    var brandId = $brand.val();
    var selectedModelId = "{{ $v->brandmodel_id ?? '' }}";
    loadModels(brandId, $model, selectedModelId);
});

// Si cambian la marca dentro del modal de edición, recargar modelos
$('#brand_id{{ $v->id }}').on('change', function(){
    loadModels($(this).val(), $('#brandmodel_id_edit_{{ $v->id }}'));
});
@endforeach

// Reabrir modal tras validación fallida
@if ($errors->any() && old('current_modal'))
    $(function(){ $('#{{ old('current_modal') }}').modal('show'); });
@endif

// Confirmación elegante de borrado (SweetAlert2)
$(document).on('click','[data-delete]', function(e){
    e.preventDefault();
    const form = $(this).closest('form')[0];
    Swal.fire({title:'¿Eliminar vehículo?', icon:'warning', showCancelButton:true,
               confirmButtonText:'Sí, eliminar', cancelButtonText:'Cancelar'})
        .then(res => { if(res.isConfirmed) form.submit(); });
});

// DataTable opcional
$('#tbl-vehicles').DataTable({ pageLength: 10, ordering: true, responsive: true });

// Variables para el modal de logos
let currentLogoModalContext = null; // 'create' o vehicleId para editar
let selectedLogoId = null;
let selectedLogoUrl = null;
let selectedLogoName = null;

// Cargar logos cuando se abre el modal
$('#logoModal').on('show.bs.modal', function(e) {
  const button = $(e.relatedTarget);
  const vehicleId = button.data('vehicle-id');
  
  // Determinar contexto: crear o editar
  if (vehicleId) {
    currentLogoModalContext = vehicleId;
    selectedLogoId = button.data('current-logo-id') || null;
  } else {
    currentLogoModalContext = 'create';
    selectedLogoId = null;
  }
  
  loadLogos();
});

// Cargar logos disponibles
function loadLogos() {
  const container = $('#logos-container');
  container.html('<div class="col-12 text-center"><i class="fas fa-spinner fa-spin"></i> Cargando logos...</div>');
  
  $.get('{{ route("vehicles.logos") }}', function(response) {
    if (response.ok && response.logos && response.logos.length > 0) {
      container.empty();
      
      response.logos.forEach(function(logo) {
        const isSelected = selectedLogoId && Number(logo.id) === Number(selectedLogoId);
        const cardClass = isSelected ? 'border-primary border-3' : 'border-secondary';
        
        container.append(`
          <div class="col-md-3 col-sm-4 col-6 mb-3">
            <div class="card logo-card ${cardClass}" 
                 data-logo-id="${logo.id}" 
                 data-logo-url="${logo.logo_url}" 
                 data-logo-name="${logo.name}"
                 style="cursor: pointer; transition: all 0.3s; min-height: 180px;">
              <div class="card-body text-center p-2 d-flex flex-column justify-content-between" style="min-height: 180px;">
                <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                  <img src="${logo.logo_url}" alt="${logo.name}" 
                       class="img-fluid"
                       style="max-width: 100%; max-height: 120px; object-fit: contain; border: 1px solid #ddd; padding: 5px; background: white;"
                       onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'100\\' height=\\'100\\'%3E%3Crect fill=\\'%23ddd\\' width=\\'100\\' height=\\'100\\'/%3E%3Ctext fill=\\'%23999\\' font-family=\\'sans-serif\\' font-size=\\'14\\' dy=\\'10.5\\' font-weight=\\'bold\\' x=\\'50%25\\' y=\\'50%25\\' text-anchor=\\'middle\\'%3EImagen no disponible%3C/text%3E%3C/svg%3E';">
                </div>
                <p class="mb-0 mt-2" style="font-size: 0.85rem; font-weight: 500;">${logo.name}</p>
              </div>
            </div>
          </div>
        `);
      });
      
      // Si hay un logo preseleccionado, activarlo
      if (selectedLogoId) {
        $(`.logo-card[data-logo-id="${selectedLogoId}"]`).addClass('border-primary border-3');
      }
    } else {
      container.html('<div class="col-12 text-center text-muted"><p>No hay logos disponibles. Primero debe subir logos en la sección de Marcas.</p></div>');
    }
  }).fail(function() {
    container.html('<div class="col-12 text-center text-danger"><p>Error al cargar los logos</p></div>');
  });
}

// Seleccionar logo al hacer click
$(document).on('click', '.logo-card', function() {
  // Remover selección anterior
  $('.logo-card').removeClass('border-primary border-3').addClass('border-secondary');
  
  // Marcar como seleccionado
  $(this).removeClass('border-secondary').addClass('border-primary border-3');
  
  // Guardar datos del logo seleccionado
  selectedLogoId = $(this).data('logo-id');
  selectedLogoUrl = $(this).data('logo-url');
  selectedLogoName = $(this).data('logo-name');
  
  // Habilitar botón de confirmar
  $('#btn-confirm-logo').prop('disabled', false);
});

// Confirmar selección de logo
$('#btn-confirm-logo').on('click', function() {
  if (!selectedLogoId) {
    return;
  }
  
  if (currentLogoModalContext === 'create') {
    // Para crear
    $('#logo_id').val(selectedLogoId);
    $('#selected-logo-img').attr('src', selectedLogoUrl);
    $('#selected-logo-name').text(selectedLogoName);
    $('#selected-logo-preview').show();
  } else {
    // Para editar
    const vehicleId = currentLogoModalContext;
    $(`#logo_id${vehicleId}`).val(selectedLogoId);
    $(`#selected-logo-preview${vehicleId}`).html(`
      <img src="${selectedLogoUrl}" alt="${selectedLogoName}" 
           style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 5px;">
      <br><small class="text-muted">${selectedLogoName}</small>
    `).show();
  }
  
  // Cerrar modal
  $('#logoModal').modal('hide');
  
  // Resetear selección
  selectedLogoId = null;
  selectedLogoUrl = null;
  selectedLogoName = null;
  $('#btn-confirm-logo').prop('disabled', true);
});

// Manejar botones de seleccionar logo en modales de edición
$(document).on('click', '.btn-select-logo', function(e) {
  e.preventDefault();
  const vehicleId = $(this).data('vehicle-id');
  const currentLogoId = $(this).data('current-logo-id');
  
  // Establecer contexto y logo preseleccionado
  currentLogoModalContext = vehicleId;
  selectedLogoId = currentLogoId || null;
  
  // Abrir modal
  $('#logoModal').modal('show');
});
</script>
@stop