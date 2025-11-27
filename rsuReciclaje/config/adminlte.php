<?php

return [
    'title' => 'RSU Reciclaje',
    'layout_topnav' => false,
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'dashboard_url' => 'dashboard',
    'use_route_url' => true,

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                ['type' => 'js',  'asset' => false, 'location' => 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js'],
                ['type' => 'css', 'asset' => false, 'location' => 'https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css'],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                ['type' => 'js',  'asset' => false, 'location' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js'],
                ['type' => 'css', 'asset' => false, 'location' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                ['type' => 'js',  'asset' => false, 'location' => 'https://cdn.jsdelivr.net/npm/sweetalert2@11'],
            ],
        ],
    ],

    // Menú lateral
    'menu' => [
        ['header' => 'NAVEGACIÓN'],

        // 1) GESTIÓN DE VEHÍCULOS
        [
            'text' => 'GESTIÓN DE VEHÍCULOS',
            'icon' => 'fas fa-car-side',
            'submenu' => [
                ['text' => 'Marcas',    'icon' => 'fas fa-registered',  'route' => 'brands.index'],
                ['text' => 'Modelos',   'icon' => 'fas fa-cubes',       'route' => 'brandmodels.index'],
                ['text' => 'Tipos',     'icon' => 'fas fa-tags',        'route' => 'vehicletypes.index'],
                ['text' => 'Colores',   'icon' => 'fas fa-palette',     'route' => 'colors.index'],
                ['text' => 'Vehículos', 'icon' => 'fas fa-truck',       'route' => 'vehicles.index'],
                ['text' => 'Mantenimiento', 'icon' => 'fas fa-wrench',  'route' => 'maintenances.index'],
            ],
        ],

        // 2) GESTIÓN DE EMPLEADOS
        [
            'text' => 'GESTIÓN DE EMPLEADOS',
            'icon' => 'fas fa-users',
            'submenu' => [
                ['text' => 'Empleados',   'icon' => 'fas fa-user-friends', 'route' => 'employees.index'],
                ['text' => 'Funciones',   'icon' => 'fas fa-id-badge',     'route' => 'functions.index'],
                ['text' => 'Contratos',   'icon' => 'fas fa-file-signature','route' => 'contracts.index'],
                ['text' => 'Asistencias', 'icon' => 'fas fa-user-check',   'route' => 'attendances.index'],
                ['text' => 'Vacaciones',  'icon' => 'far fa-calendar-alt', 'route' => 'vacations.index'],
            ],
        ],

        // 3) PROGRAMACIÓN
        [
            'text' => 'PROGRAMACIÓN',
            'icon' => 'fas fa-calendar-alt',
            'submenu' => [
                ['text' => 'Turnos',             'icon' => 'far fa-clock',        'route' => 'shifts.index'],
                ['text' => 'Zonas',              'icon' => 'fas fa-map-marker-alt','route' => 'zones.index'],
                ['text' => 'Grupo de Personal',  'icon' => 'fas fa-user-cog',      'route' => 'workgroups.index'],
                ['text' => 'Programación',        'icon' => 'fas fa-business-time', 'route' => 'admin.schedulings.index'],
            ],
        ],

        // 4) GESTIÓN DE CAMBIOS
        [
            'text' => 'GESTIÓN DE CAMBIOS',
            'icon' => 'fas fa-exchange-alt',
            'submenu' => [
                ['text' => 'Cambios',           'icon' => 'fas fa-random',       'route' => 'programaciones.bulk-update'],
                ['text' => 'Motivos de cambio', 'icon' => 'fas fa-list-alt',     'route' => 'change-reasons.index'],
            ],
        ],
    ],
];





