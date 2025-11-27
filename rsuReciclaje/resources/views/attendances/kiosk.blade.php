<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Kiosco de Asistencias</title>
  <link href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
  <style>
    body { 
      min-height: 100vh; 
      display: flex; 
      align-items: center; 
      justify-content: center;
      background: linear-gradient(135deg,#a0d8ef,#c4e8d5);
    }
    .card { width: 380px; }
  </style>
</head>
<body>
  <div class="card shadow-lg">
    <div class="card-body">
      <h3 class="text-center mb-3">Bienvenido</h3>
      <h6 class="text-muted text-center">Registro de Asistencia</h6>
      <form id="frm-kiosk" class="mt-3">
        @csrf
        <div class="mb-3">
          <input type="text" name="dni" minlength="8" maxlength="8" class="form-control" placeholder="Documento de identidad" required autofocus>
        </div>
        <div class="mb-3">
          <input type="password" name="secret" class="form-control" placeholder="Contraseña o PIN" required>
        </div>
        <input type="hidden" name="period" value="1">
        <button class="btn btn-primary w-100" type="submit">
          <i class="fas fa-check"></i> Registrar Asistencia
        </button>
      </form>
    </div>
  </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(function(){
  $('#frm-kiosk').on('submit', function(e){
    e.preventDefault();
    const $form = $(this);
    const $btn = $form.find('button[type="submit"]');
    const originalText = $btn.html();
    
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registrando...');
    
    $.ajax({
      url: '{{ route('attendances.kiosk.store') }}',
      method: 'POST',
      data: $form.serialize(),
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(res){
        if (res && res.ok) {
          toastr.success(res.msg || 'Registrado correctamente');
          $form[0].reset();
          setTimeout(() => {
            $form.find('input[name="dni"]').focus();
          }, 100);
        } else {
          toastr.error(res.msg || 'No se pudo registrar');
        }
      },
      error: function(xhr){
        const msg = xhr.responseJSON?.msg || xhr.responseJSON?.message || 'No se pudo registrar';
        toastr.error(msg);
      },
      complete: function(){
        $btn.prop('disabled', false).html(originalText);
      }
    });
  });
  
  // Enter en password envía el formulario
  $('input[name="secret"]').on('keypress', function(e){
    if (e.which === 13) {
      $('#frm-kiosk').submit();
    }
  });
});
</script>
</body>
</html>



