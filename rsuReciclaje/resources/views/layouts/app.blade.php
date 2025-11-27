<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RSU Reciclaje')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>
        </nav>

        <!-- Sidebar -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="{{ route('dashboard') }}" class="brand-link">
                <span class="brand-text font-weight-light">RSU Reciclaje</span>
            </a>

            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <!-- Gestión de Vehículos -->
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-truck"></i>
                                <p>Gestión de Vehículos <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('brands.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Marcas</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('brandmodels.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Modelos</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('vehicletypes.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Tipos</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('colors.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Colores</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('vehicles.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Vehículos</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Gestión de Personal -->
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Gestión de Personal <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('functions.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Tipos de empleados</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('employees.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Empleados</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('contracts.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Contratos</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('attendances.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Asistencias</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('vacations.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Vacaciones</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Gestión de Zonas -->
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-map"></i>
                                <p>Gestión de Zonas <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('zones.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Zonas</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Programación -->
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>Programación <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('shifts.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Turnos</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('programs.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Programación</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('runs.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Ejecuciones diarias</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <strong>Copyright &copy; 2025 RSU Reciclaje.</strong>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    
    {{-- Bootstrap 5 bundle (incluye Popper) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    
    {{-- MUY IMPORTANTE: habilitar el stack para inyectar JS por vista --}}
    @stack('scripts')
</body>
</html>


