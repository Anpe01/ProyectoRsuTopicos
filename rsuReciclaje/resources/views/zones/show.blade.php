@extends('layouts.app')

@section('title', 'Detalle de la Zona')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Detalle de la Zona</h1>
            </div>
            <div class="col-sm-6">
                <a href="{{ route('zones.index') }}" class="btn btn-secondary float-right">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Información de la Zona -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Información General</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">ID:</th>
                                <td>{{ $zone->id }}</td>
                            </tr>
                            <tr>
                                <th>Nombre:</th>
                                <td>{{ $zone->name }}</td>
                            </tr>
                            <tr>
                                <th>Área:</th>
                                <td>{{ $zone->area ? number_format($zone->area, 2) . ' m²' : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Departamento:</th>
                                <td>{{ $zone->district->province->department->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Provincia:</th>
                                <td>{{ $zone->district->province->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Distrito:</th>
                                <td>{{ $zone->district->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Coordenadas:</th>
                                <td>
                                    <span class="badge badge-info">{{ $zone->coords->count() }} puntos</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Descripción:</th>
                                <td>{{ $zone->description ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Mapa de la Zona -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ubicación en el Mapa</h3>
                    </div>
                    <div class="card-body">
                        <div id="map" style="height: 400px; width: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coordenadas Detalladas -->
        @if($zone->coords->count() > 0)
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Coordenadas del Perímetro</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Secuencia</th>
                                        <th>Latitud</th>
                                        <th>Longitud</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($zone->coords->sortBy('sequence') as $coord)
                                    <tr>
                                        <td>{{ $coord->sequence }}</td>
                                        <td>{{ number_format($coord->latitude, 7) }}</td>
                                        <td>{{ number_format($coord->longitude, 7) }}</td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editCoordModal{{ $coord->id }}">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <form method="POST" action="{{ route('zonecoords.destroy', $coord) }}" 
                                                  style="display: inline;" onsubmit="return confirm('¿Eliminar esta coordenada?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Eliminar
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
            </div>
        </div>
        @endif

        <!-- Programas Asociados -->
        @if($zone->programs->count() > 0)
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Programas Asociados</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Vehículo</th>
                                        <th>Turno</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Fin</th>
                                        <th>Días de Semana</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($zone->programs as $program)
                                    <tr>
                                        <td>{{ $program->id }}</td>
                                        <td>{{ $program->vehicle->name ?? 'N/A' }}</td>
                                        <td>{{ $program->shift->name ?? 'N/A' }}</td>
                                        <td>{{ $program->start_date->format('d/m/Y') }}</td>
                                        <td>{{ $program->end_date ? $program->end_date->format('d/m/Y') : 'Indefinido' }}</td>
                                        <td>
                                            @if($program->days_of_week)
                                                @php
                                                    $days = json_decode($program->days_of_week, true);
                                                    $dayNames = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
                                                @endphp
                                                @foreach($days as $day)
                                                    <span class="badge badge-secondary">{{ $dayNames[$day - 1] ?? $day }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($program->end_date && $program->end_date->isPast())
                                                <span class="badge badge-danger">Finalizado</span>
                                            @elseif($program->start_date->isFuture())
                                                <span class="badge badge-warning">Pendiente</span>
                                            @else
                                                <span class="badge badge-success">Activo</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>

<!-- Modales de Editar Coordenadas -->
@foreach($zone->coords as $coord)
<div class="modal fade" id="editCoordModal{{ $coord->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Editar Coordenada #{{ $coord->id }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('zonecoords.update', $coord) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="latitude{{ $coord->id }}">Latitud <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('latitude') is-invalid @enderror" 
                               id="latitude{{ $coord->id }}" name="latitude" 
                               value="{{ old('latitude', $coord->latitude) }}" 
                               step="0.0000001" required>
                        @error('latitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="longitude{{ $coord->id }}">Longitud <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('longitude') is-invalid @enderror" 
                               id="longitude{{ $coord->id }}" name="longitude" 
                               value="{{ old('longitude', $coord->longitude) }}" 
                               step="0.0000001" required>
                        @error('longitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="sequence{{ $coord->id }}">Secuencia <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('sequence') is-invalid @enderror" 
                               id="sequence{{ $coord->id }}" name="sequence" 
                               value="{{ old('sequence', $coord->sequence) }}" 
                               min="1" required>
                        @error('sequence')
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

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar mapa
    const map = L.map('map').setView([-12.0464, -77.0428], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Coordenadas de la zona
    const coordinates = @json($zone->coords->sortBy('sequence')->map(function($coord) { 
        return [$coord->latitude, $coord->longitude]; 
    }));
    
    if (coordinates.length > 0) {
        // Dibujar polígono
        const polygon = L.polygon(coordinates, { color: 'blue', fillOpacity: 0.3 }).addTo(map);
        
        // Centrar mapa en el polígono
        map.fitBounds(polygon.getBounds());
        
        // Agregar marcadores en cada vértice
        coordinates.forEach((coord, index) => {
            L.marker(coord).addTo(map)
                .bindPopup(`Punto ${index + 1}<br>Lat: ${coord[0].toFixed(7)}<br>Lng: ${coord[1].toFixed(7)}`);
        });
    }
});
</script>
@endpush











