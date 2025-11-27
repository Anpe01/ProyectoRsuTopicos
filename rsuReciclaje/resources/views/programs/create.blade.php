@extends('adminlte::page')

@section('title', 'Programación Masiva')

@section('content')
<div class="container-fluid">
  <!-- Encabezado -->
  <div class="card" style="border: 1px solid #dee2e6; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <div class="card-body" style="padding: 20px;">
      <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0" style="font-size: 1.5rem; font-weight: 600;">Programación Masiva</h1>
        <a href="{{ route('programaciones.index') }}" class="text-primary" style="text-decoration: none; font-weight: 500;">
          <i class="fas fa-arrow-left"></i> Volver
        </a>
      </div>
    </div>
  </div>

  <!-- Selector de Turno -->
  <div class="card" style="border: 1px solid #dee2e6; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <div class="card-body" style="padding: 20px;">
      <label class="mb-3 d-block" style="font-weight: 600; font-size: 1rem;">Seleccione Turno <span class="text-danger">*</span></label>
      <div id="shifts-container" class="d-flex flex-wrap justify-content-center align-items-center gap-3" style="min-height: 60px;">
        <div class="text-center w-100">
          <i class="fas fa-spinner fa-spin"></i> Cargando turnos...
        </div>
      </div>
      <input type="hidden" id="selected-shift-id" value="">
      <hr style="margin-top: 20px; margin-bottom: 0;">
    </div>
  </div>

  <!-- Periodo y Validación -->
  <div class="card" style="border: 1px solid #dee2e6; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <div class="card-body" style="padding: 20px;">
      <div class="row align-items-end">
        <div class="col-md-4">
          <div class="form-group mb-0">
            <label class="mb-1" style="font-weight: 500;">
              Fecha de inicio <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <input type="date" class="form-control" id="start-date" required 
                     style="border-radius: 4px 0 0 4px;">
              <div class="input-group-append">
                <span class="input-group-text" style="background-color: #f8f9fa; border-radius: 0 4px 4px 0;">
                  <i class="fas fa-calendar-alt"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group mb-0">
            <label class="mb-1" style="font-weight: 500;">Fecha de fin</label>
            <div class="input-group">
              <input type="date" class="form-control" id="end-date"
                     style="border-radius: 4px 0 0 4px;">
              <div class="input-group-append">
                <span class="input-group-text" style="background-color: #f8f9fa; border-radius: 0 4px 4px 0;">
                  <i class="fas fa-calendar-alt"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <button type="button" class="btn btn-outline-info btn-block" id="btn-validate-availability" 
                  style="border-color: #17a2b8; color: #17a2b8;">
            <i class="fas fa-calendar-check"></i> Validar Disponibilidad
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Cuadrícula de Grupos -->
  <div id="groups-container" class="row">
    <!-- Los grupos se cargarán dinámicamente aquí -->
  </div>

  <!-- Pie de página - Botón Registrar -->
  <div class="card" style="border: 1px solid #dee2e6; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 20px;">
    <div class="card-body" style="padding: 20px;">
      <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-success btn-lg" id="btn-register-schedule" 
                style="min-width: 200px; font-weight: 600;">
          <i class="fas fa-save"></i> Registrar Programación
        </button>
      </div>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
  let selectedShiftId = null;
  let selectedGroups = [];
  let allEmployees = [];
  let allVehicles = [];

  // Cargar turnos activos y crear botones
  function loadShifts() {
    $.get('/api/shifts/active')
      .done(function(shifts) {
        const $container = $('#shifts-container');
        $container.empty();
        
        if (shifts.length === 0) {
          $container.html('<div class="alert alert-warning w-100 text-center">No hay turnos activos registrados</div>');
          return;
        }
        
        // Crear botón para cada turno
        shifts.forEach(function(shift) {
          const $btn = $('<button>')
            .attr('type', 'button')
            .addClass('btn btn-outline-primary btn-lg shift-btn')
            .attr('data-shift-id', shift.id)
            .css({
              'min-width': '150px',
              'margin': '5px'
            })
            .text(shift.name);
          
          $container.append($btn);
        });
        
        // Agregar botón de restablecer
        const $resetBtn = $('<button>')
          .attr('type', 'button')
          .addClass('btn btn-secondary btn-lg')
          .attr('id', 'btn-reset-turno')
          .css({
            'min-width': '150px',
            'margin': '5px'
          })
          .html('<i class="fas fa-undo"></i> Restablecer');
        
        $container.append($resetBtn);
        
        // Evento para seleccionar turno
        $(document).on('click', '.shift-btn', function() {
          // Remover clase active de todos los botones
          $('.shift-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
          
          // Agregar clase active al botón seleccionado
          $(this).removeClass('btn-outline-primary').addClass('active btn-primary');
          
          selectedShiftId = $(this).data('shift-id');
          $('#selected-shift-id').val(selectedShiftId);
          
          // Cargar grupos cuando se selecciona un turno
          loadGroups();
        });
        
        // Evento para restablecer turno
        $(document).on('click', '#btn-reset-turno', function() {
          selectedShiftId = null;
          $('#selected-shift-id').val('');
          $('.shift-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
          selectedGroups = [];
          $('#groups-container').empty();
        });
      })
      .fail(function(xhr) {
        console.error('Error al cargar turnos:', xhr);
        $('#shifts-container').html('<div class="alert alert-danger w-100 text-center">Error al cargar los turnos</div>');
      });
  }
  
  // Cargar turnos al iniciar
  loadShifts();

  // Cargar grupos activos
  function loadGroups() {
    if (!selectedShiftId) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Advertencia', 'Debe seleccionar un turno primero', 'warning');
      }
      return;
    }

    $.ajax({
      url: '/api/groups/active',
      method: 'GET',
      data: { shift_id: selectedShiftId },
      success: function(groups) {
        selectedGroups = groups.map(g => ({
          ...g,
          selected_vehicle_id: g.vehicle_id,
          selected_conductor_id: g.driver_id,
          selected_ayudante1_id: g.helper1_id,
          selected_ayudante2_id: g.helper2_id
        }));
        renderGroups();
        loadEmployees();
        loadVehicles();
      },
      error: function() {
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error', 'No se pudieron cargar los grupos', 'error');
        }
      }
    });
  }

  // Cargar empleados
  function loadEmployees() {
    $.get('/api/employees/active').done(function(employees) {
      allEmployees = employees;
    }).fail(function() {
      // Fallback
      $.get('/employees').done(function(data) {
        // Parsear si es necesario
      });
    });
  }

  // Cargar vehículos
  function loadVehicles() {
    $.get('/api/vehicles/active').done(function(vehicles) {
      allVehicles = vehicles;
    }).fail(function() {
      // Fallback
    });
  }

  // Renderizar grupos en tarjetas
  function renderGroups() {
    $('#groups-container').empty();
    
    selectedGroups.forEach(function(group, index) {
      const cardHtml = `
        <div class="col-md-4 mb-4" data-group-id="${group.id}">
          <div class="card" style="border: 1px solid #dee2e6; box-shadow: 0 1px 3px rgba(0,0,0,0.1); height: 100%;">
            <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; position: relative;">
              <h5 class="mb-0" style="font-weight: 600; text-transform: uppercase; font-size: 0.95rem;">
                ${group.name}
              </h5>
              <button type="button" class="btn btn-sm btn-danger" style="position: absolute; top: 8px; right: 8px; padding: 4px 8px;" 
                      onclick="removeGroup(${group.id})" title="Eliminar grupo">
                <i class="fas fa-trash"></i>
              </button>
            </div>
            <div class="card-body" style="padding: 15px;">
              <!-- Datos del grupo -->
              <div class="mb-3">
                <div class="mb-2">
                  <strong>Zona:</strong> <span>${group.zone_name}</span>
                </div>
                <div class="mb-2">
                  <strong>Turno:</strong> <span>${group.shift_name}</span>
                </div>
                <div class="mb-2">
                  <strong>Días:</strong> <span>${group.days_display}</span>
                </div>
                <div class="mb-2 d-flex justify-content-between align-items-center">
                  <div class="flex-grow-1">
                    <strong>Vehículo:</strong> 
                    <span class="vehicle-display" data-group-id="${group.id}">${group.vehicle_code} (Capacidad: ${group.vehicle_capacity})</span>
                  </div>
                  <button type="button" class="btn btn-sm btn-warning ml-2" onclick="changeVehicle(${group.id})" 
                          style="padding: 2px 6px; min-width: 30px;" title="Cambiar vehículo">
                    <i class="fas fa-exchange-alt"></i>
                  </button>
                </div>
              </div>
              
              <hr style="margin: 15px 0;">
              
              <!-- Roles -->
              <div class="mb-2">
                <label class="mb-1" style="font-weight: 600; font-size: 0.9rem;">Conductor</label>
                <select class="form-control form-control-sm group-conductor" data-group-id="${group.id}" 
                        style="font-size: 0.85rem;">
                  <option value="">Seleccione...</option>
                </select>
              </div>
              ${group.vehicle_capacity >= 2 ? `
                <div class="mb-2">
                  <label class="mb-1" style="font-weight: 600; font-size: 0.9rem;">Ayudante 1</label>
                  <select class="form-control form-control-sm group-ayudante1" data-group-id="${group.id}" 
                          style="font-size: 0.85rem;">
                    <option value="">Seleccione...</option>
                  </select>
                </div>
              ` : ''}
              ${group.vehicle_capacity >= 3 ? `
                <div class="mb-2">
                  <label class="mb-1" style="font-weight: 600; font-size: 0.9rem;">Ayudante 2</label>
                  <select class="form-control form-control-sm group-ayudante2" data-group-id="${group.id}" 
                          style="font-size: 0.85rem;">
                    <option value="">Seleccione...</option>
                  </select>
                </div>
              ` : ''}
            </div>
          </div>
        </div>
      `;
      
      $('#groups-container').append(cardHtml);
      
      // Cargar empleados en los selectores
      loadGroupEmployees(group.id);
    });
  }

  // Cargar empleados para un grupo
  function loadGroupEmployees(groupId) {
    // Cargar empleados activos
    $.get('/api/employees/active').done(function(employees) {
      const group = selectedGroups.find(g => g.id === groupId);
      if (!group) return;

      // Conductor
      const conductorSelect = $(`.group-conductor[data-group-id="${groupId}"]`);
      conductorSelect.empty().append('<option value="">Seleccione...</option>');
      employees.forEach(function(emp) {
        const selected = emp.id == group.selected_conductor_id ? 'selected' : '';
        conductorSelect.append(`<option value="${emp.id}" ${selected}>${emp.first_name} ${emp.last_name}</option>`);
      });
      
      // Ayudante 1
      const ayudante1Select = $(`.group-ayudante1[data-group-id="${groupId}"]`);
      ayudante1Select.empty().append('<option value="">Seleccione...</option>');
      employees.forEach(function(emp) {
        const selected = emp.id == group.selected_ayudante1_id ? 'selected' : '';
        ayudante1Select.append(`<option value="${emp.id}" ${selected}>${emp.first_name} ${emp.last_name}</option>`);
      });
      
      // Ayudante 2
      const ayudante2Select = $(`.group-ayudante2[data-group-id="${groupId}"]`);
      ayudante2Select.empty().append('<option value="">Seleccione...</option>');
      employees.forEach(function(emp) {
        const selected = emp.id == group.selected_ayudante2_id ? 'selected' : '';
        ayudante2Select.append(`<option value="${emp.id}" ${selected}>${emp.first_name} ${emp.last_name}</option>`);
      });
    }).fail(function() {
      console.error('Error al cargar empleados');
    });
  }

  // Actualizar selección de personal
  $(document).on('change', '.group-conductor, .group-ayudante1, .group-ayudante2', function() {
    const groupId = parseInt($(this).data('group-id'));
    const group = selectedGroups.find(g => g.id === groupId);
    if (!group) return;

    const field = $(this).hasClass('group-conductor') ? 'selected_conductor_id' :
                  $(this).hasClass('group-ayudante1') ? 'selected_ayudante1_id' : 'selected_ayudante2_id';
    
    group[field] = $(this).val() || null;
  });

  // Cambiar vehículo
  window.changeVehicle = function(groupId) {
    const group = selectedGroups.find(g => g.id === groupId);
    if (!group) return;

    // Cargar vehículos disponibles
    $.get('/api/vehicles/active').done(function(vehicles) {
      const options = vehicles.map(v => {
        const code = v.code || v.name || 'N/A';
        const capacity = v.people_capacity || v.capacity || 0;
        const selected = v.id == group.selected_vehicle_id ? 'selected' : '';
        return `<option value="${v.id}" ${selected}>${code} (Capacidad: ${capacity})</option>`;
      }).join('');

      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: 'Cambiar Vehículo',
          html: `
            <div class="form-group text-left">
              <label>Seleccione un vehículo:</label>
              <select id="swal-vehicle-select" class="form-control">
                <option value="">Seleccione...</option>
                ${options}
              </select>
            </div>
          `,
          showCancelButton: true,
          confirmButtonText: 'Guardar',
          cancelButtonText: 'Cancelar',
          preConfirm: () => {
            const vehicleId = $('#swal-vehicle-select').val();
            if (!vehicleId) {
              Swal.showValidationMessage('Debe seleccionar un vehículo');
              return false;
            }
            return vehicleId;
          }
        }).then((result) => {
          if (result.isConfirmed && result.value) {
            group.selected_vehicle_id = result.value;
            const selectedVehicle = vehicles.find(v => v.id == result.value);
            if (selectedVehicle) {
              const code = selectedVehicle.code || selectedVehicle.name || 'N/A';
              const capacity = selectedVehicle.people_capacity || selectedVehicle.capacity || 0;
              // Actualizar el texto del vehículo en la tarjeta
              $(`.vehicle-display[data-group-id="${groupId}"]`).text(`${code} (Capacidad: ${capacity})`);
            }
          }
        });
      } else {
        const vehicleId = prompt('Seleccione un vehículo (ID):');
        if (vehicleId) {
          group.selected_vehicle_id = vehicleId;
        }
      }
    }).fail(function() {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Error', 'No se pudieron cargar los vehículos', 'error');
      }
    });
  };

  // Eliminar grupo
  window.removeGroup = function(groupId) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: '¿Eliminar grupo?',
        text: 'Este grupo se eliminará de la programación',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          selectedGroups = selectedGroups.filter(g => g.id !== groupId);
          $(`[data-group-id="${groupId}"]`).closest('.col-md-4').remove();
        }
      });
    } else {
      if (confirm('¿Eliminar grupo?')) {
        selectedGroups = selectedGroups.filter(g => g.id !== groupId);
        $(`[data-group-id="${groupId}"]`).closest('.col-md-4').remove();
      }
    }
  };

  // Validar disponibilidad
  $('#btn-validate-availability').on('click', function() {
    const startDate = $('#start-date').val();
    const endDate = $('#end-date').val() || startDate;

    if (!startDate) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Error', 'Debe seleccionar una fecha de inicio', 'error');
      }
      return;
    }

    if (selectedGroups.length === 0) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Advertencia', 'Debe seleccionar al menos un grupo', 'warning');
      }
      return;
    }

    const $btn = $(this);
    const originalHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Validando...');

    $.ajax({
      url: '/programaciones/validate-availability',
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      data: JSON.stringify({
        start_date: startDate,
        end_date: endDate,
        group_ids: selectedGroups.map(g => g.id)
      }),
      success: function(response) {
        // Actualizar estado de validación en cada grupo
        if (response.groups && response.groups.length > 0) {
          response.groups.forEach(function(groupValidation) {
            const group = selectedGroups.find(g => g.id === groupValidation.group_id);
            if (group) {
              group.validation = groupValidation;
            }
            
            // Actualizar visualmente la tarjeta del grupo
            updateGroupCardValidation(groupValidation);
          });
        }
        
        if (response.ok) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              title: 'Validación Exitosa',
              text: response.message,
              icon: 'success',
              confirmButtonText: 'Aceptar'
            });
          } else {
            alert(response.message);
          }
        } else {
          // Mostrar resumen de conflictos y vacaciones (mismo formato que el botón Registrar)
          const conflictGroups = response.groups.filter(g => g.has_conflicts);
          let conflictSummary = `<div class="alert alert-danger" style="border-left: 4px solid #dc3545;">
            <h5><i class="fas fa-exclamation-triangle"></i> INCONSISTENCIAS DETECTADAS</h5>
            <p>Se encontraron <strong>${conflictGroups.length} grupo(s)</strong> con inconsistencias:</p>`;
          
          conflictGroups.forEach(function(g) {
            conflictSummary += `<div class="mb-3" style="border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; background-color: #f8f9fa; margin-top: 10px;">
              <strong style="color: #dc3545;">${g.group_name}:</strong><br>`;
            
            // Mostrar conflictos de programación
            if (g.conflict_dates && g.conflict_dates.length > 0) {
              const datesList = g.conflict_dates.map(d => `${d.day_name} ${d.date_formatted}`).join(', ');
              conflictSummary += `<span class="badge badge-warning mr-2">Conflictos de programación</span> ${g.conflict_count} conflicto(s) en: ${datesList}<br>`;
            }
            
            // Mostrar problemas de vacaciones (mismo formato que Registrar Programación)
            if (g.vacation_issues && g.vacation_issues.length > 0) {
              conflictSummary += `<div class="mt-2" style="margin-top: 8px;">`;
              conflictSummary += `<strong style="color: #dc3545;"><i class="fas fa-calendar-times"></i> Personal con vacaciones:</strong><ul style="margin-top: 5px; margin-bottom: 0; padding-left: 20px;">`;
              g.vacation_issues.forEach(function(issue) {
                conflictSummary += `<li style="margin-bottom: 5px;">
                  <strong>${issue.employee_name}</strong> (${issue.role}) tiene vacaciones del 
                  <strong>${issue.vacation_start}</strong> al <strong>${issue.vacation_end}</strong> 
                  (${issue.vacation_days} días) - <span style="color: #dc3545;">No puede estar disponible</span>
                </li>`;
              });
              conflictSummary += `</ul></div>`;
            }
            
            // Mostrar problemas de disponibilidad del personal (contrato, duplicados, etc.)
            if (g.personnel_availability_issues && g.personnel_availability_issues.length > 0) {
              conflictSummary += `<div class="mt-2" style="margin-top: 8px;">`;
              conflictSummary += `<strong style="color: #dc3545;"><i class="fas fa-user-times"></i> Problemas de disponibilidad del personal:</strong><ul style="margin-top: 5px; margin-bottom: 0; padding-left: 20px;">`;
              g.personnel_availability_issues.forEach(function(issue) {
                const issuesList = issue.issues.map(i => `<span style="color: #dc3545;">${i}</span>`).join(', ');
                conflictSummary += `<li style="margin-bottom: 5px;">
                  <strong>${issue.employee_name}</strong> (${issue.role}) el día <strong>${issue.date}</strong>: ${issuesList}
                </li>`;
              });
              conflictSummary += `</ul></div>`;
            }
            
            conflictSummary += `</div>`;
          });
          
          conflictSummary += `
            <p class="mb-0 mt-3"><strong>Por favor, corrija las inconsistencias antes de registrar la programación.</strong></p>
          </div>`;
          
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              title: 'Inconsistencias Detectadas',
              html: conflictSummary,
              icon: 'error',
              confirmButtonText: 'Entendido',
              width: '700px',
              customClass: {
                popup: 'text-left'
              }
            });
          } else {
            alert(response.message);
          }
        }
      },
      error: function(xhr) {
        let errorMsg = 'Error al validar disponibilidad';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMsg = xhr.responseJSON.message;
        }
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error', errorMsg, 'error');
        } else {
          alert(errorMsg);
        }
      },
      complete: function() {
        $btn.prop('disabled', false).html(originalHtml);
      }
    });
  });

  // Actualizar visualmente la tarjeta del grupo con el estado de validación
  function updateGroupCardValidation(groupValidation) {
    const card = $(`[data-group-id="${groupValidation.group_id}"]`);
    if (!card.length) return;
    
    // Remover clases anteriores
    card.find('.card').removeClass('border-success border-warning border-danger');
    
    // Remover alertas anteriores
    card.find('.validation-alert').remove();
    
    if (groupValidation.has_conflicts) {
      // Agregar borde rojo y alerta
      card.find('.card').addClass('border-danger');
      
      let alertHtml = `<div class="validation-alert mb-2" style="margin-top: 10px;">`;
      
      // Mostrar conflictos de programación
      if (groupValidation.conflict_dates && groupValidation.conflict_dates.length > 0) {
        const datesList = groupValidation.conflict_dates.map(d => 
          `${d.day_name} ${d.date_formatted}`
        ).join('<br>');
        
        alertHtml += `
          <div class="alert alert-danger mb-2" style="padding: 10px; font-size: 0.85rem; border-left: 4px solid #dc3545;">
            <strong><i class="fas fa-exclamation-triangle"></i> Conflictos de programación (${groupValidation.conflict_count}):</strong><br>
            ${datesList}
          </div>
        `;
      }
      
      // Mostrar problemas de vacaciones
      if (groupValidation.vacation_issues && groupValidation.vacation_issues.length > 0) {
        alertHtml += `<div class="alert alert-warning mb-2" style="padding: 10px; font-size: 0.85rem; border-left: 4px solid #ffc107;">
          <strong><i class="fas fa-calendar-times"></i> Personal con vacaciones (${groupValidation.vacation_issues.length}):</strong><br>
          <ul style="margin-top: 5px; margin-bottom: 0; padding-left: 20px;">`;
        
        groupValidation.vacation_issues.forEach(function(issue) {
          alertHtml += `<li style="margin-bottom: 3px;">
            <strong>${issue.employee_name}</strong> (${issue.role}) - 
            Vacaciones: ${issue.vacation_start} al ${issue.vacation_end} (${issue.vacation_days} días)
          </li>`;
        });
        
        alertHtml += `</ul></div>`;
      }
      
      alertHtml += `</div>`;
      
      card.find('.card-body').append(alertHtml);
    } else {
      // Agregar borde verde si está válido
      card.find('.card').addClass('border-success');
      
      const alertHtml = `
        <div class="alert alert-success validation-alert mb-2" style="padding: 10px; font-size: 0.85rem; margin-top: 10px;">
          <div class="alert alert-success mb-0" style="padding: 10px; border-left: 4px solid #28a745;">
            <strong><i class="fas fa-check-circle"></i> Sin inconsistencias - Listo para programar</strong>
          </div>
        </div>
      `;
      
      card.find('.card-body').append(alertHtml);
    }
  }

  // Registrar programación
  $('#btn-register-schedule').on('click', function() {
    const startDate = $('#start-date').val();
    const endDate = $('#end-date').val() || startDate;

    if (!startDate) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Error', 'Debe seleccionar una fecha de inicio', 'error');
      }
      return;
    }

    if (!selectedShiftId) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Error', 'Debe seleccionar un turno', 'error');
      }
      return;
    }

    if (selectedGroups.length === 0) {
      if (typeof Swal !== 'undefined') {
        Swal.fire('Error', 'Debe seleccionar al menos un grupo', 'error');
      }
      return;
    }

    // Verificar si hay grupos con conflictos
    const groupsWithConflicts = selectedGroups.filter(g => 
      g.validation && g.validation.has_conflicts
    );
    
    if (groupsWithConflicts.length > 0) {
      let conflictMessage = `
        <div class="alert alert-danger" style="border-left: 4px solid #dc3545;">
          <h5><i class="fas fa-exclamation-triangle"></i> INCONSISTENCIAS DETECTADAS</h5>
          <p>Se encontraron <strong>${groupsWithConflicts.length} grupo(s)</strong> con inconsistencias:</p>`;
      
      groupsWithConflicts.forEach(function(g) {
        conflictMessage += `<div class="mb-3" style="border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; background-color: #f8f9fa; margin-top: 10px;">
          <strong style="color: #dc3545;">${g.validation.group_name}:</strong><br>`;
        
        // Mostrar conflictos de programación
        if (g.validation.conflict_dates && g.validation.conflict_dates.length > 0) {
          const datesList = g.validation.conflict_dates.map(d => `${d.day_name} ${d.date_formatted}`).join(', ');
          conflictMessage += `<span class="badge badge-warning mr-2">Conflictos de programación</span> ${g.validation.conflict_count} conflicto(s) en: ${datesList}<br>`;
        }
        
        // Mostrar problemas de vacaciones
        if (g.validation.vacation_issues && g.validation.vacation_issues.length > 0) {
          conflictMessage += `<div class="mt-2" style="margin-top: 8px;">`;
          conflictMessage += `<strong style="color: #dc3545;"><i class="fas fa-calendar-times"></i> Personal con vacaciones:</strong><ul style="margin-top: 5px; margin-bottom: 0; padding-left: 20px;">`;
          g.validation.vacation_issues.forEach(function(issue) {
            conflictMessage += `<li style="margin-bottom: 5px;">
              <strong>${issue.employee_name}</strong> (${issue.role}) tiene vacaciones del 
              <strong>${issue.vacation_start}</strong> al <strong>${issue.vacation_end}</strong> 
              (${issue.vacation_days} días) - <span style="color: #dc3545;">No puede estar disponible</span>
            </li>`;
          });
          conflictMessage += `</ul></div>`;
        }
        
        // Mostrar problemas de disponibilidad del personal (contrato, duplicados, etc.)
        if (g.validation.personnel_availability_issues && g.validation.personnel_availability_issues.length > 0) {
          conflictMessage += `<div class="mt-2" style="margin-top: 8px;">`;
          conflictMessage += `<strong style="color: #dc3545;"><i class="fas fa-user-times"></i> Problemas de disponibilidad del personal:</strong><ul style="margin-top: 5px; margin-bottom: 0; padding-left: 20px;">`;
          g.validation.personnel_availability_issues.forEach(function(issue) {
            const issuesList = issue.issues.map(i => `<span style="color: #dc3545;">${i}</span>`).join(', ');
            conflictMessage += `<li style="margin-bottom: 5px;">
              <strong>${issue.employee_name}</strong> (${issue.role}) el día <strong>${issue.date}</strong>: ${issuesList}
            </li>`;
          });
          conflictMessage += `</ul></div>`;
        }
        
        conflictMessage += `</div>`;
      });
      
      conflictMessage += `
          <p class="mb-0 mt-3"><strong>Por favor, corrija las inconsistencias antes de registrar la programación.</strong></p>
        </div>`;
      
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: 'No se puede registrar',
          html: conflictMessage,
          icon: 'error',
          confirmButtonText: 'Entendido',
          width: '600px'
        });
      } else {
        alert('Hay grupos con conflictos. Por favor, corrija los conflictos antes de registrar.');
      }
      return;
    }

    const $btn = $(this);
    const originalHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registrando...');

    const groupsData = selectedGroups.map(g => ({
      group_id: g.id,
      vehicle_id: g.selected_vehicle_id || g.vehicle_id,
      conductor_id: g.selected_conductor_id || null,
      ayudante1_id: g.selected_ayudante1_id || null,
      ayudante2_id: g.selected_ayudante2_id || null
    }));

    $.ajax({
      url: '/programaciones/bulk',
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      data: JSON.stringify({
        shift_id: selectedShiftId,
        start_date: startDate,
        end_date: endDate,
        groups: groupsData
      }),
      success: function(response) {
        if (response.ok) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              title: 'Éxito',
              text: response.msg,
              icon: 'success',
              confirmButtonText: 'Aceptar'
            }).then(() => {
              window.location.href = '{{ route("programaciones.index") }}';
            });
          } else {
            alert(response.msg);
            window.location.href = '{{ route("programaciones.index") }}';
          }
        } else {
          if (typeof Swal !== 'undefined') {
            Swal.fire('Error', response.msg || 'Error al registrar la programación', 'error');
          } else {
            alert(response.msg || 'Error al registrar la programación');
          }
        }
      },
      error: function(xhr) {
        let errorMsg = 'Error al registrar la programación';
        if (xhr.responseJSON) {
          if (xhr.responseJSON.errors) {
            const errors = Object.values(xhr.responseJSON.errors).flat();
            errorMsg = errors.join('<br>');
          } else if (xhr.responseJSON.message) {
            errorMsg = xhr.responseJSON.message;
          } else if (xhr.responseJSON.msg) {
            errorMsg = xhr.responseJSON.msg;
          }
        }
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error', errorMsg, 'error');
        } else {
          alert(errorMsg);
        }
      },
      complete: function() {
        $btn.prop('disabled', false).html(originalHtml);
      }
    });
  });

  // Establecer fechas por defecto
  const today = new Date();
  const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
  const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
  
  $('#start-date').val(formatDate(firstDay));
  $('#end-date').val(formatDate(lastDay));

  function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }
});
</script>
@stop

@section('css')
<style>
  .btn.active {
    opacity: 1;
    font-weight: 600;
  }
  .card {
    transition: box-shadow 0.2s;
  }
  .card:hover {
    box-shadow: 0 2px 6px rgba(0,0,0,0.15) !important;
  }
</style>
@stop

