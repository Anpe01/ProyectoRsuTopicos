<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registro de Asistencia</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-default@5/default.min.css">
<style>
body{
  min-height:100vh; margin:0;
  background: linear-gradient(135deg,#a8e0ff,#b8b5ff,#dfffd6);
  display:flex; align-items:center; justify-content:center; font-family: system-ui,Segoe UI,Roboto,Helvetica,Arial;
}
.card{ background:#fff; width:360px; padding:28px; border-radius:16px; box-shadow:0 8px 28px rgba(0,0,0,.1); }
h2{ margin:0 0 12px; text-align:center; }
.input{ width:100%; padding:12px 14px; border:1px solid #d9d9d9; border-radius:10px; margin:10px 0; font-size:16px; box-sizing:border-box;}
.btn{ width:100%; padding:12px 14px; border:0; border-radius:10px; background:#2c74b3; color:#fff; font-weight:600; cursor:pointer; font-size:16px;}
.btn:disabled{ opacity:.6; cursor:not-allowed;}
.small{ color:#777; font-size:12px; margin-top:8px; text-align:center;}
</style>
</head>
<body>
  <div class="card">
    <h2>Bienvenido</h2>
    <div class="small">Registro de Asistencia</div>
    <input id="dni" class="input" type="text" placeholder="Documento de identidad" maxlength="12">
    <input id="password" class="input" type="password" placeholder="Contraseña">
    <button id="btn" class="btn">Registrar Asistencia</button>
  </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const btn = document.getElementById('btn');
btn.onclick = async ()=>{
  btn.disabled = true;
  try{
    const res = await fetch("{{ route('kiosk.store') }}", {
      method:'POST',
      headers:{
        'Content-Type':'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
      },
      body: JSON.stringify({
        dni: document.getElementById('dni').value.trim(),
        password: document.getElementById('password').value
      })
    });
    const data = await res.json();
    if(data.ok){
      Swal.fire('¡Registrado!', data.msg, 'success');
      document.getElementById('dni').value = '';
      document.getElementById('password').value = '';
    }else{
      Swal.fire({
        title: 'No registrado',
        text: data.msg || data.message || 'Verifique sus datos.',
        icon: 'error',
        confirmButtonText: 'Aceptar'
      });
    }
  }catch(e){
    Swal.fire({
      title: 'Error',
      text: 'No se pudo conectar con el servidor. Intente nuevamente.',
      icon: 'error',
      confirmButtonText: 'Aceptar'
    });
    console.error('Error:', e);
  }finally{
    btn.disabled = false;
  }
};

// Permitir Enter para enviar
document.getElementById('password').addEventListener('keypress', function(e) {
  if (e.key === 'Enter') {
    btn.click();
  }
});
</script>
</body>
</html>

