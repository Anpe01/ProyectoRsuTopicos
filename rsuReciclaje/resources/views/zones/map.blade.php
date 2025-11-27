@extends('adminlte::page')

@section('title','Mapa de Zonas')

@section('plugins.Datatables', true)

@section('content_header')
  <h1>Mapa de Zonas Activas</h1>
@stop

@section('content')
<div class="container-fluid position-relative">
  <!-- Botón Volver al Listado - posición absoluta arriba a la derecha -->
  <a href="{{ route('zones.index') }}" class="btn btn-primary position-absolute" style="top: 10px; right: 10px; z-index: 1000;">
    <i class="fas fa-arrow-left"></i> Volver al Listado
  </a>
  
  <div class="card">
    <div class="card-body p-0">
      <div id="map-all-zones" style="height: calc(100vh - 250px); min-height: 600px; width: 100%;"></div>
    </div>
  </div>
</div>

{{-- Modal Detalles de Zona --}}
<div class="modal fade" id="modalZoneDetail" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #17a2b8; color: white;">
        <h5 class="modal-title">
          <i class="fas fa-info-circle"></i> Detalles de la Zona
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: 1;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="zone-detail-content">
          {{-- Contenido se carga dinámicamente --}}
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
@stop

@section('css')
{{-- CDN Leaflet --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
  .leaflet-popup-content {
    min-width: 200px;
  }
  .zone-popup-title {
    font-weight: bold;
    font-size: 1.1em;
    margin-bottom: 8px;
    color: #28a745;
  }
  .zone-popup-info {
    margin: 4px 0;
    font-size: 0.9em;
  }
  .info-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    transition: transform 0.2s;
  }
  .info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }
  .info-card-icon {
    font-size: 2em;
    margin-bottom: 10px;
  }
  .info-card-value {
    font-size: 1.2em;
    font-weight: bold;
    color: #333;
  }
  .info-card-label {
    font-size: 0.9em;
    color: #6c757d;
    margin-top: 5px;
  }
  .coordinates-table {
    font-size: 0.9em;
  }
  .coordinates-table th {
    background-color: #f8f9fa;
    font-weight: 600;
  }
</style>
@stop

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Inicializar mapa
  const map = L.map('map-all-zones').setView([-6.7714, -79.8409], 13); // Centro Chiclayo
  
  // Capa de tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  // Datos de las zonas desde PHP
  const zones = @json($zones);
  
  // Almacenar datos completos de zonas para el modal
  window.zonesData = {};
  zones.forEach(z => {
    window.zonesData[z.id] = z;
  });
  
  // Grupo de capas para todas las zonas
  const zonesGroup = L.layerGroup().addTo(map);
  
  // Colores aleatorios pero consistentes para cada zona
  function getColorForZone(id) {
    const colors = [
      '#28a745', '#007bff', '#ffc107', '#dc3545', '#17a2b8',
      '#6f42c1', '#e83e8c', '#fd7e14', '#20c997', '#6610f2'
    ];
    return colors[id % colors.length];
  }

  // Array para almacenar los bounds de todas las zonas
  const allBounds = [];

  // Función para contar puntos en el polígono
  function countPolygonPoints(polygon) {
    if (!polygon || !polygon.coordinates) return 0;
    // GeoJSON Polygon tiene estructura: coordinates[0] es el anillo exterior
    if (polygon.type === 'Polygon' && polygon.coordinates[0]) {
      return polygon.coordinates[0].length;
    }
    return 0;
  }

  // Función para extraer coordenadas del polígono
  function extractCoordinates(polygon) {
    if (!polygon || !polygon.coordinates) return [];
    if (polygon.type === 'Polygon' && polygon.coordinates[0]) {
      // coordinates[0] es el anillo exterior (el último punto es igual al primero)
      return polygon.coordinates[0].slice(0, -1).map((coord, index) => ({
        num: index + 1,
        lat: coord[1], // GeoJSON usa [lng, lat]
        lng: coord[0]
      }));
    }
    return [];
  }

  // Función para mostrar detalles de zona
  function showZoneDetail(zoneId) {
    const zone = window.zonesData[zoneId];
    if (!zone) {
      alert('Zona no encontrada');
      return;
    }

    const points = countPolygonPoints(zone.polygon);
    const coordinates = extractCoordinates(zone.polygon);
    const department = zone.department ? zone.department.name : 'No especificado';
    const avgWaste = zone.avg_waste_tb ? parseFloat(zone.avg_waste_tb).toFixed(2) + ' Tb' : 'No especificado';

    // Construir HTML del contenido
    let html = `
      <div class="row mb-4">
        <div class="col-md-6 mb-3">
          <div class="info-card">
            <div class="info-card-icon" style="color: #28a745;">
              <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="info-card-value">${zone.name || 'Sin nombre'}</div>
            <div class="info-card-label">Nombre</div>
          </div>
        </div>
        <div class="col-md-6 mb-3">
          <div class="info-card">
            <div class="info-card-icon" style="color: #6f42c1;">
              <i class="fas fa-shapes"></i>
            </div>
            <div class="info-card-value">${points}</div>
            <div class="info-card-label">Puntos</div>
          </div>
        </div>
        <div class="col-md-6 mb-3">
          <div class="info-card">
            <div class="info-card-icon" style="color: #007bff;">
              <i class="fas fa-building"></i>
            </div>
            <div class="info-card-value">${department}</div>
            <div class="info-card-label">Departamento</div>
          </div>
        </div>
        <div class="col-md-6 mb-3">
          <div class="info-card">
            <div class="info-card-icon" style="color: #ffc107;">
              <i class="fas fa-trash-alt"></i>
            </div>
            <div class="info-card-value">${avgWaste}</div>
            <div class="info-card-label">Residuos promedio</div>
          </div>
        </div>
      </div>
    `;

    // Descripción
    html += `
      <div class="mb-4">
        <h6 class="font-weight-bold mb-2">Descripción</h6>
        <div class="card" style="background-color: #ffffff; border: 1px solid #dee2e6;">
          <div class="card-body">
            <p class="mb-0">${zone.description || 'No hay descripción disponible.'}</p>
          </div>
        </div>
      </div>
    `;

    // Coordenadas
    html += `
      <div>
        <h6 class="font-weight-bold mb-2">Coordenadas</h6>
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-hover coordinates-table">
            <thead>
              <tr>
                <th style="width: 80px;">#</th>
                <th>Latitud</th>
                <th>Longitud</th>
              </tr>
            </thead>
            <tbody>
    `;

    if (coordinates.length > 0) {
      coordinates.forEach(coord => {
        html += `
          <tr>
            <td>${coord.num}</td>
            <td>${coord.lat.toFixed(6)}</td>
            <td>${coord.lng.toFixed(6)}</td>
          </tr>
        `;
      });
    } else {
      html += `
        <tr>
          <td colspan="3" class="text-center text-muted">No hay coordenadas disponibles</td>
        </tr>
      `;
    }

    html += `
            </tbody>
          </table>
        </div>
      </div>
    `;

    // Mostrar contenido en el modal
    document.getElementById('zone-detail-content').innerHTML = html;
    $('#modalZoneDetail').modal('show');
  }

  // Dibujar cada zona en el mapa
  zones.forEach(function(zone) {
    if (!zone.polygon) return;
    
    try {
      const color = getColorForZone(zone.id);
      
      // Crear GeoJSON feature
      const geoJson = {
        type: 'Feature',
        geometry: zone.polygon,
        properties: {
          id: zone.id,
          name: zone.name,
          area: zone.area_km2,
          description: zone.description || '',
          department: zone.department ? zone.department.name : 'No especificado',
          avg_waste_tb: zone.avg_waste_tb
        }
      };

      // Dibujar polígono
      const polygon = L.geoJSON(geoJson, {
        style: {
          fillColor: color,
          fillOpacity: 0.4,
          color: color,
          weight: 3,
          opacity: 0.8
        },
        onEachFeature: function(feature, layer) {
          // Popup al hacer clic
          const popupContent = `
            <div class="zone-popup-title">${feature.properties.name}</div>
            <div class="zone-popup-info"><strong>Departamento:</strong> ${feature.properties.department}</div>
            <div class="zone-popup-info"><strong>Área:</strong> ${parseFloat(feature.properties.area || 0).toFixed(3)} km²</div>
            <div class="mt-2">
              <button class="btn btn-primary btn-sm btn-view-detail" data-zone-id="${feature.properties.id}" style="width: 100%;">
                <i class="fas fa-eye"></i> Ver Detalle
              </button>
            </div>
          `;
          layer.bindPopup(popupContent);
          
          // Tooltip al pasar el mouse
          layer.bindTooltip(feature.properties.name, {
            permanent: false,
            direction: 'center'
          });
        }
      });

      zonesGroup.addLayer(polygon);
      
      // Guardar bounds para ajustar el mapa después
      if (polygon.getBounds && polygon.getBounds().isValid()) {
        allBounds.push(polygon.getBounds());
      }
    } catch (e) {
      console.error('Error al dibujar zona ' + zone.id + ':', e);
    }
  });

  // Delegación de eventos para botones "Ver Detalle" en popups
  map.on('popupopen', function(e) {
    const popup = e.popup;
    const content = popup.getContent();
    if (content && content.includes('btn-view-detail')) {
      // Esperar a que el popup se renderice completamente
      setTimeout(() => {
        const btn = popup._container.querySelector('.btn-view-detail');
        if (btn) {
          btn.addEventListener('click', function() {
            const zoneId = parseInt(this.getAttribute('data-zone-id'));
            map.closePopup();
            showZoneDetail(zoneId);
          });
        }
      }, 100);
    }
  });

  // Ajustar el mapa para mostrar todas las zonas
  if (allBounds.length > 0) {
    const group = new L.featureGroup(zonesGroup.getLayers());
    const bounds = group.getBounds();
    if (bounds.isValid()) {
      map.fitBounds(bounds.pad(0.1)); // 10% de padding
    }
  }

  // Leyenda (opcional)
  const legend = L.control({position: 'bottomright'});
  legend.onAdd = function() {
    const div = L.DomUtil.create('div', 'info legend');
    div.innerHTML = `
      <div style="background: white; padding: 10px; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
        <strong>Zonas Activas: ${zones.length}</strong>
      </div>
    `;
    return div;
  };
  legend.addTo(map);
});
</script>
@stop
