@extends('adminlte::page')

@section('title','Zonas')

@section('plugins.Datatables', true)

@section('content_header')
  <h1>Gestión de Zonas</h1>
@stop

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <button id="btn-new-zone" class="btn btn-success">
      <i class="fas fa-plus"></i> Nueva Zona
    </button>
    <a href="{{ route('zones.map') }}" class="btn btn-info">
      <i class="fas fa-map"></i> Ver Mapa de Zonas
    </a>
  </div>

  <table id="tbl-zones" class="table table-striped table-hover w-100">
    <thead class="table-light">
      <tr>
        <th>Nombre</th>
        <th>Ubigeo</th>
        <th>Área (km²)</th>
        <th>Residuos (Tb)</th>
        <th>Estado</th>
        <th style="width:120px">Acciones</th>
      </tr>
    </thead>
  </table>
</div>

{{-- Modal Nueva Zona --}}
<div class="modal fade" id="modalZone" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modal-title-zone">Nueva Zona</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="frm-zone">
          @csrf
          <input type="hidden" name="department_id" id="department_id" value="{{ $lambayeque_id ?? config('app.default_department_id', 1) }}">
          <input type="hidden" name="polygon" id="polygon">
          <input type="hidden" name="area_km2" id="area_km2">
          
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre *</label>
              <input class="form-control" name="name" id="name" required>
            </div>
            <div class="col-md-3">
              <label class="form-label d-block">Departamento</label>
              <div class="form-control-plaintext fw-semibold">
                {{ config('app.default_department_name','Lambayeque') }}
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label">Residuos promedio (Tb)</label>
              <input class="form-control" name="avg_waste_tb" id="avg_waste_tb" type="number" step="0.01" min="0" placeholder="Opcional">
            </div>

            <div class="col-12">
              <label class="form-label d-block">Dibuje la zona en el mapa:</label>
              <div id="map-zone" style="height:360px;border-radius:12px;"></div>
              <div class="mt-2 d-flex gap-2">
                <button type="button" id="btn-clear" class="btn btn-outline-secondary btn-sm">
                  <i class="fas fa-eraser"></i> Limpiar
                </button>
                <button type="button" id="btn-undo" class="btn btn-outline-warning btn-sm">
                  <i class="fas fa-undo"></i> Eliminar último punto
                </button>
                <div class="ms-auto form-check">
                  <input class="form-check-input" type="checkbox" id="active" name="active" value="1" checked>
                  <label class="form-check-label" for="active">Activo</label>
                </div>
              </div>
              <small class="text-muted">El área se calculará automáticamente (km²) al cerrar el polígono.</small>
            </div>

            <div class="col-12">
              <label class="form-label">Descripción</label>
              <textarea class="form-control" name="description" id="description" rows="3"></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" id="btn-save-zone" class="btn btn-success">Guardar</button>
      </div>
    </div>
  </div>
</div>
@stop

@section('css')
{{-- CDN Leaflet --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
@stop

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
<script>
let DT;
const ZONE = { mode: 'create', id: null };

// DataTable
$(document).ready(function() {
  DT = $('#tbl-zones').DataTable({
    ajax: { 
      url: "{{ route('zones.index') }}", 
      dataSrc: 'data'
    },
    serverSide: false,
    processing: true,
    columns: [
      { data: 'name' },
      { data: 'ubigeo' },
      { data: 'area_km2' },
      { data: 'avg_waste_tb', defaultContent: '' },
      { data: 'active_badge' },
      { data: 'actions', orderable:false, searchable:false }
    ],
    language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
    error: function(xhr, error, thrown) {
      console.error('Error DataTables:', error, thrown);
      console.error('Response:', xhr.responseText);
    }
  });

  // Abrir modal CREAR
  document.getElementById('btn-new-zone').addEventListener('click', () => {
    ZONE.mode = 'create';
    ZONE.id = null;
    document.getElementById('frm-zone').reset();
    document.getElementById('polygon').value = '';
    document.getElementById('area_km2').value = '';
    document.getElementById('department_id').value = {{ $lambayeque_id ?? config('app.default_department_id', 1) }};
    document.getElementById('active').checked = true;
    document.getElementById('modal-title-zone').textContent = 'Nueva Zona';
    openZoneModal(null);
  });

  // Delegación de eventos para editar/eliminar
  $('#tbl-zones tbody').on('click','.btn-edit', function(){
    const id = $(this).data('id');
    editZone(id);
  });
  
  $('#tbl-zones tbody').on('click','.btn-del', function(){
    const id = $(this).data('id');
    if(!confirm('¿Eliminar zona?')) return;
    fetch(`/zones/${id}`, {
      method:'DELETE',
      headers:{
        'X-CSRF-TOKEN':'{{ csrf_token() }}',
        'Content-Type':'application/json'
      }
    })
    .then(r=>r.json())
    .then(()=> DT.ajax.reload(null,false))
    .catch(err=>{
      console.error('Error:', err);
      alert('Error al eliminar');
    });
  });
});

// Función para editar zona
async function editZone(id){
  try {
    const res = await fetch(`{{ url('zones') }}/${id}`);
    if (!res.ok) throw new Error('Error al cargar zona');
    const z = await res.json();
    
    ZONE.mode = 'edit';
    ZONE.id = id;

    // Campos simples
    document.getElementById('name').value = z.name ?? '';
    document.getElementById('avg_waste_tb').value = z.avg_waste_tb ?? '';
    document.getElementById('description').value = z.description ?? '';
    document.getElementById('department_id').value = z.department_id ?? {{ $lambayeque_id ?? config('app.default_department_id', 1) }};
    document.getElementById('active').checked = !!z.active;
    document.getElementById('modal-title-zone').textContent = 'Editar Zona';

    // Polígono y área
    if (z.polygon) {
      document.getElementById('polygon').value = JSON.stringify(z.polygon);
      const areaInput = document.getElementById('area_km2');
      areaInput.value = z.area_km2 ? Number(z.area_km2).toFixed(3) : '';
    } else {
      document.getElementById('polygon').value = '';
      document.getElementById('area_km2').value = '';
    }

    openZoneModal(z);
  } catch (error) {
    console.error('Error al cargar zona:', error);
    alert('Error al cargar los datos de la zona');
  }
}

// Abrir modal y inicializar mapa
function openZoneModal(zData){
  $('#modalZone').modal('show');
  // Esperar a que el modal esté completamente visible antes de inicializar el mapa
  $('#modalZone').on('shown.bs.modal', function() {
    initMapWithData(zData);
    // Remover el listener para evitar que se ejecute múltiples veces
    $(this).off('shown.bs.modal');
  });
}

// Inicializar mapa con datos
function initMapWithData(zData){
  // Limpiar mapa anterior si existe
  if (window.map) { 
    // Remover todos los event listeners
    window.map.off();
    window.map.remove(); 
    window.map = null; 
    window.layerGroup = null;
    window.lastLayer = null;
    // Limpiar el contenedor
    const mapContainer = document.getElementById('map-zone');
    if (mapContainer) {
      mapContainer.innerHTML = '';
    }
  }
  
  // Pequeño delay para asegurar que el contenedor tiene tamaño
  setTimeout(() => {
    window.map = L.map('map-zone').setView([-6.7714, -79.8409], 14);
    
    // Invalidar tamaño después de crear el mapa para que se renderice correctamente
    setTimeout(() => {
      if (window.map) {
        window.map.invalidateSize();
      }
    }, 100);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution:'© OpenStreetMap'
    }).addTo(window.map);

    window.layerGroup = new L.FeatureGroup(); 
    window.map.addLayer(window.layerGroup);
    
    const drawControl = new L.Control.Draw({
      draw: { 
        polygon:{allowIntersection:false, showArea:true}, 
        polyline:false, rectangle:false, circle:false, marker:false, circlemarker:false 
      },
      edit: { featureGroup: window.layerGroup, edit:true, remove:true }
    });
    window.map.addControl(drawControl);

    // Si hay polígono existente, dibujarlo y ajustar bounds
    if (zData && zData.polygon) {
      try {
        const gj = { type: 'Feature', geometry: zData.polygon, properties:{} };
        const geo = L.geoJSON(gj);
        geo.eachLayer(l => {
          window.layerGroup.addLayer(l);
          window.lastLayer = l;
          setTimeout(() => {
            const bounds = l.getBounds ? l.getBounds() : window.layerGroup.getBounds();
            if (bounds && bounds.isValid()) {
              window.map.fitBounds(bounds.pad(0.2));
              window.map.invalidateSize();
            }
          }, 150);
        });
        
        // Si no hay area_km2 precargada, calcúlala desde el layer
        if (!document.getElementById('area_km2').value && window.lastLayer) {
          updateFields(window.lastLayer);
        }
      } catch (e) {
        console.error('Error al dibujar polígono:', e);
      }
    }

    // Event listeners del mapa
    window.map.on(L.Draw.Event.CREATED, e => {
      window.layerGroup.clearLayers(); 
      window.lastLayer = e.layer;
      window.layerGroup.addLayer(window.lastLayer);
      updateFields(window.lastLayer);
    });
    
    window.map.on('draw:edited', e => {
      const first = Object.values(e.layers._layers)[0];
      if(first){ 
        window.lastLayer = first; 
        updateFields(window.lastLayer); 
      }
    });
    
    window.map.on('draw:deleted', () => {
      window.lastLayer = null;
      document.getElementById('polygon').value = '';
      document.getElementById('area_km2').value = '';
    });

    // Botones de control
    const btnClear = document.getElementById('btn-clear');
    const btnUndo = document.getElementById('btn-undo');
    
    // Remover listeners anteriores si existen
    const newBtnClear = btnClear.cloneNode(true);
    btnClear.parentNode.replaceChild(newBtnClear, btnClear);
    newBtnClear.onclick = () => {
      window.layerGroup.clearLayers(); 
      window.lastLayer = null;
      document.getElementById('polygon').value = ''; 
      document.getElementById('area_km2').value = '';
    };
    
    const newBtnUndo = btnUndo.cloneNode(true);
    btnUndo.parentNode.replaceChild(newBtnUndo, btnUndo);
    newBtnUndo.onclick = () => {
      if(!window.lastLayer) return;
      const latlngs = window.lastLayer.getLatLngs()[0];
      if(latlngs.length <= 3) return;
      latlngs.pop(); 
      window.lastLayer.setLatLngs([latlngs]); 
      window.lastLayer.redraw();
      updateFields(window.lastLayer);
    };
  }, 200); // Delay para asegurar que el contenedor está listo
}

// Actualizar campos hidden con polígono y área
function updateFields(layer){
  const gj = layer.toGeoJSON();
  const latlngs = layer.getLatLngs()[0];
  
  if (latlngs.length < 3) {
    document.getElementById('area_km2').value = '';
    return;
  }
  
  // Calcular área usando fórmula de Shoelace con corrección geodésica
  let area = 0;
  for (let i = 0; i < latlngs.length; i++) {
    const j = (i + 1) % latlngs.length;
    area += latlngs[i].lng * latlngs[j].lat;
    area -= latlngs[j].lng * latlngs[i].lat;
  }
  area = Math.abs(area) / 2;
  
  // Corrección geodésica
  const avgLat = latlngs.reduce((sum, ll) => sum + ll.lat, 0) / latlngs.length;
  const latRad = avgLat * Math.PI / 180;
  const metersPerDegLat = 111132.92 - 559.82 * Math.cos(2 * latRad) + 1.175 * Math.cos(4 * latRad);
  const metersPerDegLng = 111412.84 * Math.cos(latRad) - 93.5 * Math.cos(3 * latRad);
  const areaM2 = area * metersPerDegLat * metersPerDegLng;
  const km2 = parseFloat((areaM2 / 1e6).toFixed(3));
  
  document.getElementById('area_km2').value = km2;
  document.getElementById('polygon').value = JSON.stringify(gj.geometry);
}

// Guardar (create / edit)
document.getElementById('btn-save-zone').addEventListener('click', async function(){
  const form = document.getElementById('frm-zone');
  const fd = new FormData(form);
  const $btn = $('#btn-save-zone').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

  // Toggle activo
  if (!fd.has('active')) fd.append('active','0');

  let url = "{{ route('zones.store') }}";
  let opts = { 
    method:'POST', 
    headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}, 
    body: fd 
  };

  if (ZONE.mode === 'edit' && ZONE.id) {
    url = `{{ url('zones') }}/${ZONE.id}`;
    fd.append('_method','PUT');
  }
  
  try {
    const rsp = await fetch(url, opts);
    const data = await rsp.json();
    
    if (!rsp.ok) {
      const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Error de validación');
      if (window.Swal) {
        Swal.fire({icon: 'error', title: 'Error', text: msg});
      } else {
        alert(msg);
      }
      $btn.prop('disabled', false).html('Guardar');
      return;
    }
    
    // Resetear botón PRIMERO
    $btn.prop('disabled', false).html('Guardar');
    
    // Cerrar modal y refrescar tabla
    $('#modalZone').modal('hide');
    DT.ajax.reload(null,false);
    
    if (window.Swal) {
      Swal.fire({icon: 'success', title: 'Éxito', text: data.msg || (ZONE.mode === 'edit' ? 'Zona actualizada' : 'Zona creada'), timer: 2000, showConfirmButton: false});
    } else if (window.toastr) {
      toastr.success(data.msg || (ZONE.mode === 'edit' ? 'Zona actualizada' : 'Zona creada'));
    }
    
    // Resetear estado
    ZONE.mode = 'create';
    ZONE.id = null;
  } catch (error) {
    console.error('Error:', error);
    const msg = error.message || 'Error al guardar la zona';
    if (window.Swal) {
      Swal.fire({icon: 'error', title: 'Error', text: msg});
    } else {
      alert(msg);
    }
    $btn.prop('disabled', false).html('Guardar');
  }
});

// Resetear modal al cerrar
$('#modalZone').on('hidden.bs.modal', function() {
  // Asegurar que el botón esté reseteado
  $('#btn-save-zone').prop('disabled', false).html('Guardar');
  
  // Limpiar mapa
  if (window.map) {
    window.map.off(); // Remover todos los event listeners
    window.map.remove();
    window.map = null;
    window.layerGroup = null;
    window.lastLayer = null;
  }
  
  // Limpiar contenedor del mapa
  const mapContainer = document.getElementById('map-zone');
  if (mapContainer) {
    mapContainer.innerHTML = '';
  }
  
  // Resetear formulario
  document.getElementById('frm-zone').reset();
  document.getElementById('polygon').value = '';
  document.getElementById('area_km2').value = '';
  document.getElementById('department_id').value = {{ $lambayeque_id ?? config('app.default_department_id', 1) }};
  document.getElementById('active').checked = true;
  
  // Resetear estado
  ZONE.mode = 'create';
  ZONE.id = null;
});
</script>
@stop
