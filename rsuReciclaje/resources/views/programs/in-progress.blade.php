@extends('adminlte::page')

@section('title', 'En Progreso')

@section('content_header')
  <h1>En Progreso</h1>
@stop

@section('content')
<div class="container-fluid">
  <div class="card">
    <div class="card-body text-center" style="padding: 60px 20px;">
      <i class="fas fa-cog fa-spin fa-4x text-primary mb-4"></i>
      <h3 class="mb-3">Módulo en Desarrollo</h3>
      <p class="text-muted mb-4">Esta funcionalidad está actualmente en progreso.</p>
      <a href="{{ route('admin.schedulings.index') }}" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i> Volver a Programaciones
      </a>
    </div>
  </div>
</div>
@stop


