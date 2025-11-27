@extends('adminlte::page')

@section('title','Vacaciones')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
  <h1>Gestión de Vacaciones</h1>
@stop

@section('content')
  <button type="button"
          class="btn btn-success mb-3"
          id="btn-new-vac">
    <i class="fas fa-plus"></i> Nueva vacación
  </button>

  <table id="tbl-vacations" class="table table-striped table-bordered" style="width:100%">
    <thead>
      <tr>
        <th>Empleado</th>
        <th>Inicio</th>
        <th>Fin</th>
        <th>Días</th>
        <th>Notas</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
    </tbody>
  </table>

  {{-- Modal --}}
  <div class="modal fade" id="modalVacation" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Nueva vacación</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <form id="frmVacation" action="{{ route('vacations.store') }}" method="POST">
          @csrf
          <input type="hidden" name="_method" id="vac_method" value="POST">
          <input type="hidden" name="vacation_id" id="vacation_id">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-4">
                <label class="form-label">Empleado *</label>
                <select class="form-control" name="employee_id" id="vac_employee_id" required>
                  <option value="">Seleccione…</option>
                  @foreach($employees as $e)
                    <option value="{{ $e->id }}">{{ $e->last_name }}, {{ $e->first_name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Inicio *</label>
                <input type="date" class="form-control" name="start_date" id="vac_start" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Fin *</label>
                <input type="date" class="form-control" name="end_date" id="vac_end" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Días</label>
                <input type="number" class="form-control" name="days" id="vac_days" min="1" placeholder="(auto)" readonly>
              </div>
              <div class="col-md-9">
                <label class="form-label">Notas</label>
                <input type="text" class="form-control" name="notes" maxlength="500" placeholder="Observaciones">
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success" id="btn-save-vac">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    console.log('Script de vacaciones cargado');
    console.log('Botón encontrado:', $('#btn-new-vac').length);
    console.log('Modal encontrado:', $('#modalVacation').length);
    const tbl = $('#tbl-vacations').DataTable({
        ajax: '{{ route('vacations.datatable') }}',
        serverSide: false,
        processing: true,
        pageLength: 10,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
            emptyTable: 'Sin registros'
        },
        columns: [
            { data: 'employee' },
            { data: 'start_date' },
            { data: 'end_date' },
            { data: 'days' },
            { data: 'notes' },
            { data: 'actions', orderable:false, searchable:false },
        ]
    });

    // Calcular días automáticamente
    $('#vac_start, #vac_end').on('change', function() {
        const start = $('#vac_start').val();
        const end = $('#vac_end').val();
        if (start && end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            if (endDate >= startDate) {
                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                $('#vac_days').val(diffDays);
            }
        }
    });

    // Abrir modal con jQuery directamente (Bootstrap 4 compatible)
    $(document).on('click', '#btn-new-vac', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Click en botón Nueva vacación');
        
        // Verificar que el modal existe
        const $modal = $('#modalVacation');
        if ($modal.length === 0) {
            console.error('Modal no encontrado!');
            alert('Error: No se encontró el modal. Recarga la página.');
            return;
        }
        
        // Limpiar formulario antes de abrir
        const $form = $('#frmVacation');
        if ($form.length > 0) {
            $form[0].reset();
            $('#vac_method').val('POST');
            $form.attr('action', '{{ route('vacations.store') }}');
            $('.modal-title').text('Nueva vacación');
            $('#vacation_id').val('');
        }
        
        // Abrir modal directamente con Bootstrap 4 jQuery
        console.log('Abriendo modal...');
        $modal.modal('show');
        
        // Verificar después de un momento
        setTimeout(function() {
            if ($modal.hasClass('show')) {
                console.log('Modal abierto correctamente');
            } else {
                console.error('El modal no se abrió. Verifica que Bootstrap esté cargado.');
            }
        }, 500);
    });

    // Envío AJAX real del form
    $('#frmVacation').on('submit', function(e){
        e.preventDefault();
        const $form = $(this);
        const $btn = $('#btn-save-vac').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        const originalBtnText = $btn.html();
        const formData = $form.serialize();
        const method = $('#vac_method').val();
        const url = method === 'PUT' 
            ? '{{ route('vacations.update', ':id') }}'.replace(':id', $('#vacation_id').val())
            : $form.attr('action');

        $.ajax({
            url: url,
            method: method === 'PUT' ? 'POST' : 'POST',
            data: formData + (method === 'PUT' ? '&_method=PUT' : ''),
            headers: { 
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
            },
            dataType: 'json'
        })
        .done(function(resp){
            if (resp && resp.ok) {
                // Cerrar modal
                $('#modalVacation').modal('hide');
                tbl.ajax.reload(null, false);
                if (window.Swal) {
                    Swal.fire({icon: 'success', title: 'Éxito', text: 'Vacación registrada correctamente.', timer: 2000, showConfirmButton: false});
                } else {
                    alert('Vacación registrada correctamente.');
                }
            } else {
                let msg = resp.message || 'No se pudo registrar.';
                if (resp.errors) {
                    msg = Object.values(resp.errors).flat().join('\n');
                }
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning', 
                        title: 'Atención', 
                        html: msg.replace(/\n/g, '<br>'),
                        width: '600px'
                    });
                } else {
                    alert(msg);
                }
            }
        })
        .fail(function(xhr){
            let msg = 'Error al registrar.';
            if (xhr.responseJSON) {
                if (xhr.responseJSON.errors) {
                    // Mostrar todos los errores de validación
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    msg = errors.join('\n');
                } else if (xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
            }
            if (window.Swal) {
                Swal.fire({
                    icon: 'error', 
                    title: 'Error de validación', 
                    html: msg.replace(/\n/g, '<br>'),
                    width: '600px'
                });
            } else {
                alert(msg);
            }
        })
        .always(function(){
            $btn.prop('disabled', false).html('Guardar');
        });
    });

    // Funciones globales para editar/eliminar
    window.EditVacation = function(id) {
        $.get('{{ route('vacations.index') }}/' + id + '/edit')
            .done(function(data) {
                if (data.id) {
                    $('#vac_method').val('PUT');
                    $('#vacation_id').val(data.id);
                    $('#vac_employee_id').val(data.employee_id).trigger('change');
                    $('#vac_start').val(data.start_date);
                    $('#vac_end').val(data.end_date);
                    $('#vac_days').val(data.days);
                    $('input[name="notes"]').val(data.notes || '');
                    $('#frmVacation').attr('action', '{{ route('vacations.update', ':id') }}'.replace(':id', id));
                    $('.modal-title').text('Editar vacación');
                    // Abrir modal
                    $('#modalVacation').modal('show');
                }
            })
            .fail(function() {
                if (window.Swal) {
                    Swal.fire({icon: 'error', title: 'Error', text: 'No se pudieron cargar los datos.'});
                } else {
                    alert('No se pudieron cargar los datos.');
                }
            });
    };

    window.DeleteVacation = function(id) {
        if (window.Swal) {
            Swal.fire({
                title: '¿Eliminar vacación?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('vacations.index') }}/' + id,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                        }
                    })
                    .done(function(resp) {
                        if (resp && resp.ok) {
                            tbl.ajax.reload(null, false);
                            Swal.fire('Eliminado', 'Vacación eliminada.', 'success');
                        }
                    })
                    .fail(function() {
                        Swal.fire('Error', 'No se pudo eliminar.', 'error');
                    });
                }
            });
        } else {
            if (confirm('¿Eliminar vacación?')) {
                $.ajax({
                    url: '{{ route('vacations.index') }}/' + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                    }
                })
                .done(function(resp) {
                    if (resp && resp.ok) {
                        tbl.ajax.reload(null, false);
                        alert('Vacación eliminada.');
                    }
                });
            }
        }
    };

    // Inicializar Select2 cuando el modal se muestre (Bootstrap 4)
    if ($.fn.select2) {
        $('#modalVacation').on('shown.bs.modal', function() {
            if (!$('#vac_employee_id').hasClass('select2-hidden-accessible')) {
                $('#vac_employee_id').select2({
                    width: '100%',
                    dropdownParent: $('#modalVacation')
                });
            }
        });
    }

    // Debug: verificar que todo esté listo
    console.log('Configuración de vacaciones completada');
});
</script>
@stop
