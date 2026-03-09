<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} - Granja Porcina</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Iconos Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        /**
         * SISTEMA DE DISEÑO (CSS VARIABLES)
         * Centralizamos colores y medidas para facilitar cambios globales.
         */
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 80px;
            --topbar-height: 70px;
            --primary-bg: #f4f6f9;
            --sidebar-bg: #1e2227;
            --sidebar-hover: #2b3038;
        }
        
        body { background-color: var(--primary-bg); font-family: 'Inter', system-ui, sans-serif; overflow-x: hidden; font-size: 0.875rem; }
        
        /* Contenedor principal Flexible */
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        
        /* SIDEBAR: Manejo de transiciones y estados (colapsado/móvil) */
        .sidebar { position: sticky; top: 0; height: 100vh; overflow-y: auto; background-color: var(--sidebar-bg); color: #fff; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); width: var(--sidebar-width); z-index: 1040; display: flex; flex-direction: column; }
        .sidebar.collapsed { width: var(--sidebar-collapsed-width); }
        
        /* Estilos de navegación interna */
        .sidebar-brand { display: flex; align-items: center; justify-content: flex-start; padding: 1.5rem; height: var(--topbar-height); text-decoration: none; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-brand .brand-icon { font-size: 2rem; color: #f8b4b4; transition: all 0.3s; }
        .sidebar.collapsed .sidebar-brand { justify-content: center; padding: 1.5rem 0; }
        .sidebar.collapsed .brand-text { display: none; }
        
        .sidebar-nav { padding: 0.75rem 0.4rem; flex: 1; }
        .nav-item { margin-bottom: 0.2rem; }
        .nav-link { color: #a4b2c2; padding: 0.75rem 1rem; border-radius: 0.5rem; display: flex; align-items: center; transition: all 0.2s; white-space: nowrap; overflow: hidden; }
        .nav-link i { font-size: 1.25rem; min-width: 1.5rem; margin-right: 1rem; transition: all 0.2s; }
        .nav-link:hover, .nav-link.active { color: #fff; background-color: var(--sidebar-hover); }
        
        /* SIDEBAR COLAPSADO: centrado de iconos */
        .sidebar.collapsed .nav-link { justify-content: center; padding: 0.75rem 0; width: 100%; }
        .sidebar.collapsed .nav-link i { margin-right: 0 !important; font-size: 1.35rem; min-width: unset; }
        .sidebar.collapsed .nav-link > div { display: flex; justify-content: center; width: 100%; }
        .sidebar.collapsed .nav-link > div i { margin-right: 0 !important; }
        .sidebar.collapsed .nav-text { display: none; }
        
        /* MAIN CONTENT: Se expande/contrae según el sidebar */
        .main-content { width: calc(100% - var(--sidebar-width)); transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); min-height: 100vh; display: flex; flex-direction: column; }
        .sidebar.collapsed ~ .main-content { width: calc(100% - var(--sidebar-collapsed-width)); }
        
        /* Topbar / Header Superior */
        .topbar { position: sticky; top: 0; height: var(--topbar-height); background-color: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.02); z-index: 1030; }
        .toggle-btn { background: transparent; border: none; color: #4b5563; font-size: 1.5rem; cursor: pointer; padding: 0.5rem; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; transition: background 0.2s; }
        .toggle-btn:hover { background: #f3f4f6; }
        
        /* Content Panel: Área de renderizado del contenido */
        .content-panel { padding: 1.5rem; flex: 1; }
        
        /* Estilos globales para Cards y Tablas (Base del sistema) */
        .card { border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border-radius: 0.75rem; }
        .table-responsive { border-radius: 0.75rem; overflow: hidden; }
        .table > :not(caption) > * > * { padding: 0.55rem 0.85rem; }
        .table thead th { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.03em; }
        h2 { font-size: 1.25rem !important; }
        h3 { font-size: 1.1rem !important; }
        h5 { font-size: 0.95rem !important; }

        /* Responsividad: Ocultado de sidebar en pantallas pequeñas */
        /* Submenus y flechas (Custom implementation) */
        .submenu { display: none; }
        .submenu.show { display: block; }
        
        .nav-link[aria-expanded="true"] .menu-arrow { transform: rotate(180deg); }
        .menu-arrow { transition: transform 0.2s; font-size: 0.8rem !important; margin-right: 0 !important; }
        
        .sidebar.collapsed .menu-arrow { display: none; }
        .sidebar.collapsed .submenu.show { display: none !important; }
        
        .submenu .nav-link { padding-top: 0.5rem; padding-bottom: 0.5rem; font-size: 0.9rem; }
        .submenu .nav-link i { font-size: 1.1rem !important; margin-right: 0.75rem; }
        
        .nav-separator { padding: 1.5rem 1rem 0.5rem; }
        .nav-separator-text { color: #5b6e82; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; }
        .nav-separator-line { height: 1px; background: rgba(255,255,255,0.05); margin-top: 0.5rem; }
        .sidebar.collapsed .nav-separator { display: none; }

        /* Centrado global de modales relativo al contenido (compensando el sidebar de 260px) */
        @media (min-width: 992px) {
            .modal-dialog-centered {
                transform: translateX(130px) !important; /* Mueve el modal 130px (mitad del sidebar) a la derecha */
                transition: transform 0.3s ease-out;
            }
            /* Si el sidebar está colapsado (80px), ajustamos el centrado (40px) */
            .sidebar.collapsed ~ .main-content .modal-dialog-centered {
                transform: translateX(40px) !important;
            }
        }

        /* Efecto de desenfoque premium para el fondo de todos los modales */
        .modal-backdrop.show {
            backdrop-filter: blur(4px);
            background-color: rgba(30, 34, 39, 0.4);
        }
    </style>
    @livewireStyles
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar: Navegación Lateral -->
        <nav id="sidebar" class="sidebar">
            <a href="/" class="sidebar-brand text-decoration-none">
                <i class="ph-fill ph-piggy-bank brand-icon me-0 me-md-2"></i>
                <span class="fs-4 fw-bold mb-0 brand-text">Granja Pro</span>
            </a>
            
            <div class="sidebar-nav">
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }} text-decoration-none" wire:navigate>
                            <i class="ph ph-squares-four"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('users*', 'roles*', 'permissions*') ? 'active' : '' }} text-decoration-none d-flex justify-content-between align-items-center" 
                           href="#" data-submenu-toggle="usersSubmenu" aria-expanded="{{ request()->is('users*', 'roles*', 'permissions*') ? 'true' : 'false' }}">
                            <div class="d-flex align-items-center">
                                <i class="ph ph-users-three"></i>
                                <span class="nav-text">Gestión de Usuarios</span>
                            </div>
                            <i class="ph ph-caret-down menu-arrow nav-text"></i>
                        </a>
                        <div class="submenu {{ request()->is('users*', 'roles*', 'permissions*') ? 'show' : '' }}" id="usersSubmenu">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item">
                                    <a href="/users" class="nav-link {{ request()->is('users') && !request()->has('create') ? 'active' : '' }} text-decoration-none" wire:navigate>
                                        <i class="ph ph-list-bullets"></i>
                                        <span class="nav-text">Listado de Usuarios</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/users?create=1" class="nav-link {{ request()->has('create') ? 'active' : '' }} text-decoration-none" wire:navigate>
                                        <i class="ph ph-user-plus"></i>
                                        <span class="nav-text">Crear Usuario</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/roles" class="nav-link {{ request()->is('roles') ? 'active' : '' }} text-decoration-none" wire:navigate>
                                        <i class="ph ph-shield-check"></i>
                                        <span class="nav-text">Roles</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/permissions" class="nav-link {{ request()->is('permissions') ? 'active' : '' }} text-decoration-none" wire:navigate>
                                        <i class="ph ph-key"></i>
                                        <span class="nav-text">Permisos</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('employees*', 'attendance*', 'incidents*') ? 'active' : '' }} text-decoration-none d-flex justify-content-between align-items-center" 
                           href="#" data-submenu-toggle="personalSubmenu" aria-expanded="{{ request()->is('employees*', 'attendance*', 'incidents*') ? 'true' : 'false' }}">
                            <div class="d-flex align-items-center">
                                <i class="ph ph-briefcase"></i>
                                <span class="nav-text">Gestión de Personal</span>
                            </div>
                            <i class="ph ph-caret-down menu-arrow nav-text"></i>
                        </a>
                        <div class="submenu {{ request()->is('employees*', 'attendance*', 'incidents*') ? 'show' : '' }}" id="personalSubmenu">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item">
                                    <a href="/employees" class="nav-link {{ request()->is('employees') ? 'active' : '' }} text-decoration-none" wire:navigate>
                                        <i class="ph ph-identification-card"></i>
                                        <span class="nav-text">Empleados</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/attendance" class="nav-link {{ request()->is('attendance') ? 'active' : '' }} text-decoration-none" wire:navigate>
                                        <i class="ph ph-calendar-check"></i>
                                        <span class="nav-text">Asistencias</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/incidents" class="nav-link {{ request()->is('incidents') ? 'active' : '' }} text-decoration-none" wire:navigate>
                                        <i class="ph ph-file-text"></i>
                                        <span class="nav-text">Incidencias</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('warehouse*') ? 'active' : '' }} text-decoration-none d-flex justify-content-between align-items-center" 
                           href="#" data-submenu-toggle="inventorySubmenu" aria-expanded="{{ request()->is('warehouse*') ? 'true' : 'false' }}">
                            <div class="d-flex align-items-center">
                                <i class="ph ph-package"></i>
                                <span class="nav-text">Gestión de Almacén</span>
                            </div>
                            <i class="ph ph-caret-down menu-arrow nav-text"></i>
                        </a>
                        <div class="submenu {{ request()->is('warehouse*') ? 'show' : '' }}" id="inventorySubmenu">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item">
                                    <a href="/warehouse/a002" class="nav-link {{ request()->is('warehouse/a002') ? 'active' : '' }} text-decoration-none" wire:navigate>
                                        <i class="ph ph-hard-drive"></i>
                                        <span class="nav-text">A002</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/warehouse/a006" class="nav-link {{ request()->is('warehouse/a006') ? 'active' : '' }} text-decoration-none" wire:navigate>
                                        <i class="ph ph-stack"></i>
                                        <span class="nav-text">A006</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/warehouse/requests" class="nav-link {{ request()->is('warehouse/requests') ? 'active' : '' }} text-decoration-none" wire:navigate>
                                        <i class="ph ph-file-arrow-up"></i>
                                        <span class="nav-text">Solicitudes</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link text-decoration-none">
                                        <i class="ph ph-chart-line-down"></i>
                                        <span class="nav-text">Consumos</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('certificates*') ? 'active' : '' }} text-decoration-none d-flex justify-content-between align-items-center" 
                           href="#" data-submenu-toggle="certificatesSubmenu" aria-expanded="{{ request()->is('certificates*') ? 'true' : 'false' }}">
                            <div class="d-flex align-items-center">
                                <i class="ph ph-certificate"></i>
                                <span class="nav-text">Gestión de Certificados</span>
                            </div>
                            <i class="ph ph-caret-down menu-arrow nav-text"></i>
                        </a>
                        <div class="submenu {{ request()->is('certificates*') ? 'show' : '' }}" id="certificatesSubmenu">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item">
                                    <a href="/certificates" class="nav-link {{ request()->is('certificates') ? 'active' : '' }} text-decoration-none" wire:navigate>
                                        <i class="ph ph-list-bullets"></i>
                                        <span class="nav-text">Listado de certificados</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/certificates/create" class="nav-link {{ request()->is('certificates/create') ? 'active' : '' }} text-decoration-none" wire:navigate>
                                        <i class="ph ph-plus-circle"></i>
                                        <span class="nav-text">Crear certificado</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/certificates/causes" class="nav-link {{ request()->is('certificates/causes*') ? 'active' : '' }} text-decoration-none" wire:navigate>
                                        <i class="ph ph-stethoscope"></i>
                                        <span class="nav-text">Causas de muerte</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    
                    <!-- Separador REEMPLAZO -->
                    <li class="nav-separator">
                        <div class="nav-separator-text">Reemplazo</div>
                        <div class="nav-separator-line"></div>
                    </li>

                    {{-- Selección Genética --}}
                    <li class="nav-item">
                        <a class="nav-link text-decoration-none d-flex justify-content-between align-items-center" 
                           href="#" data-submenu-toggle="geneticSubmenu">
                            <div class="d-flex align-items-center">
                                <i class="ph ph-dna"></i>
                                <span class="nav-text">Selección Genética</span>
                            </div>
                            <i class="ph ph-caret-down menu-arrow nav-text"></i>
                        </a>
                        <div class="submenu" id="geneticSubmenu">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item">
                                    <a href="#" class="nav-link text-decoration-none">
                                        <i class="ph ph-baby"></i>
                                        <span class="nav-text">Partos</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link text-decoration-none">
                                        <i class="ph ph-tree-structure"></i>
                                        <span class="nav-text">Pedigree</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- Recria --}}
                    <li class="nav-item">
                        <a class="nav-link text-decoration-none d-flex justify-content-between align-items-center" 
                           href="#" data-submenu-toggle="recriaSubmenu">
                            <div class="d-flex align-items-center">
                                <i class="ph ph-chart-line-up"></i>
                                <span class="nav-text">Recria</span>
                            </div>
                            <i class="ph ph-caret-down menu-arrow nav-text"></i>
                        </a>
                        <div class="submenu" id="recriaSubmenu">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item">
                                    <a href="#" class="nav-link text-decoration-none">
                                        <i class="ph ph-sign-in"></i>
                                        <span class="nav-text">Ingresos</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link text-decoration-none">
                                        <i class="ph ph-arrows-left-right"></i>
                                        <span class="nav-text">Movimientos</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- Levante --}}
                    <li class="nav-item">
                        <a class="nav-link text-decoration-none d-flex justify-content-between align-items-center" 
                           href="#" data-submenu-toggle="levanteSubmenu">
                            <div class="d-flex align-items-center">
                                <i class="ph ph-trend-up"></i>
                                <span class="nav-text">Levante</span>
                            </div>
                            <i class="ph ph-caret-down menu-arrow nav-text"></i>
                        </a>
                        <div class="submenu" id="levanteSubmenu">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item">
                                    <a href="#" class="nav-link text-decoration-none">
                                        <i class="ph ph-sign-in"></i>
                                        <span class="nav-text">Ingresos</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link text-decoration-none">
                                        <i class="ph ph-arrows-left-right"></i>
                                        <span class="nav-text">Movimientos</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- Pubertad --}}
                    <li class="nav-item">
                        <a class="nav-link text-decoration-none d-flex justify-content-between align-items-center" 
                           href="#" data-submenu-toggle="pubertadSubmenu">
                            <div class="d-flex align-items-center">
                                <i class="ph ph-heart"></i>
                                <span class="nav-text">Pubertad</span>
                            </div>
                            <i class="ph ph-caret-down menu-arrow nav-text"></i>
                        </a>
                        <div class="submenu" id="pubertadSubmenu">
                            <ul class="nav flex-column ps-3">
                                <li class="nav-item">
                                    <a href="#" class="nav-link text-decoration-none">
                                        <i class="ph ph-sign-in"></i>
                                        <span class="nav-text">Ingresos</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link text-decoration-none">
                                        <i class="ph ph-arrows-left-right"></i>
                                        <span class="nav-text">Movimientos</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link text-decoration-none">
                                        <i class="ph ph-flame"></i>
                                        <span class="nav-text">Celos</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link text-decoration-none">
                                        <i class="ph ph-lightning"></i>
                                        <span class="nav-text">Activaciones</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- Ventas --}}
                    <li class="nav-item">
                        <a href="#" class="nav-link text-decoration-none">
                            <i class="ph ph-shopping-cart"></i>
                            <span class="nav-text">Ventas</span>
                        </a>
                    </li>

                    {{-- Mortalidad --}}
                    <li class="nav-item">
                        <a href="#" class="nav-link text-decoration-none">
                            <i class="ph ph-skull"></i>
                            <span class="nav-text">Mortalidad</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Topbar: Cabecera superior -->
            <header class="topbar">
                <div class="d-flex align-items-center">
                    <button class="toggle-btn me-3" id="sidebarToggle" title="Alternar Menú">
                        <i class="ph ph-list"></i>
                    </button>
                    <h4 class="mb-0 fw-bold text-dark d-none d-sm-block">{{ $title ?? 'Plataforma' }}</h4>
                </div>

                <!-- Perfil de Usuario y Logout -->
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="dropdownUser" aria-expanded="false">
                        @php
                            $displayName = Auth::user()->short_name;
                            if (!$displayName) {
                                $parts = explode(' ', trim(Auth::user()->name));
                                // Si tiene 4 nombres (Nombre1 Nombre2 Apellido1 Apellido2), tomar 1 y 3
                                if (count($parts) >= 4) {
                                    $displayName = $parts[0] . ' ' . $parts[2];
                                } elseif (count($parts) >= 2) {
                                    $displayName = $parts[0] . ' ' . $parts[1];
                                } else {
                                    $displayName = Auth::user()->name;
                                }
                            }
                        @endphp
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($displayName) }}&background=f8b4b4&color=1e2227" alt="Avatar" width="38" height="38" class="rounded-circle me-2 border">
                        <span class="d-none d-sm-inline text-uppercase"><strong>{{ $displayName }}</strong></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="dropdownUser">
                        <li><a class="dropdown-item" href="#"><i class="ph ph-user me-2"></i> Mi Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="ph ph-sign-out me-2"></i> Cerrar Sesión</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </header>

            <!-- Renderizado Dinámico de Vistas -->
            <div class="content-panel">
                {{ $slot }}
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @livewireScripts

    <script>
        /**
         * FUNCIÓN DE INICIALIZACIÓN (SPA COMPATIBLE)
         */
        /**
         * FUNCIÓN DE INICIALIZACIÓN (SPA COMPATIBLE)
         */
        function initLayout() {
            // 1. GESTIÓN DEL SIDEBAR TOGGLE (Botón Hamburguesa)
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            
            if (toggleBtn && sidebar) {
                // Removemos listeners previos para evitar duplicados al navegar
                const newToggleBtn = toggleBtn.cloneNode(true);
                toggleBtn.parentNode.replaceChild(newToggleBtn, toggleBtn);

                newToggleBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (window.innerWidth > 768) {
                        sidebar.classList.toggle('collapsed');
                        localStorage.setItem('sidebarState', sidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded');
                    } else {
                        sidebar.classList.toggle('mobile-show');
                    }
                });
                
                // Aplicar estado guardado (Solo en pantallas grandes)
                if (window.innerWidth > 768 && localStorage.getItem('sidebarState') === 'collapsed') {
                    sidebar.classList.add('collapsed');
                }
            }
        }

        // Delegación de eventos GLOBAL - Se añade una sola vez al cargar la app
        // No depende de Livewire:navigated porque se asigna al document
        if (!window.sidebarInitialized) {
            document.addEventListener('click', function(e) {
                // 1. Manejo de Dropdowns (Perfil)
                const ddToggle = e.target.closest('.dropdown-toggle');
                if (ddToggle) {
                    e.preventDefault(); e.stopPropagation();
                    bootstrap.Dropdown.getOrCreateInstance(ddToggle).toggle();
                }

                // 2. Manejo de Submenús (Custom)
                const subToggle = e.target.closest('[data-submenu-toggle]');
                if (subToggle) {
                    e.preventDefault(); e.stopPropagation();
                    const targetId = subToggle.getAttribute('data-submenu-toggle');
                    const targetEl = document.getElementById(targetId);
                    if (targetEl) {
                        const isShowing = targetEl.classList.toggle('show');
                        subToggle.setAttribute('aria-expanded', isShowing);
                    }
                }
            });
            window.sidebarInitialized = true;
        }

        // Suscripción a eventos de ciclo de vida
        document.addEventListener('livewire:navigated', initLayout);
        document.addEventListener('DOMContentLoaded', initLayout);

        /**
         * NOTIFICACIONES GLOBALES (Livewire -> SweetAlert2)
         */
        document.addEventListener('livewire:init', () => {
            Livewire.on('notify', (event) => {
                const data = event[0];
                Swal.fire({
                    icon: data.icon ?? 'success',
                    title: data.title ?? 'Operación Exitosa',
                    text: data.text ?? '',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                });
            });

            /**
             * MANEJO GLOBAL DE MODAL (Bootstrap v5 + Livewire)
             */
            Livewire.on('open-modal', (event) => {
                const data = event[0] || event;
                const modalEl = document.getElementById(data.id);
                if (modalEl) {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                }
            });

            Livewire.on('close-modal', (event) => {
                const data = event[0] || event;
                const modalEl = document.getElementById(data.id);
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        modal.hide();
                    }
                }
            });

            // Confirmación de Borrado de Registros
            Livewire.on('confirm-delete', (event) => {
                const data = event[0] || event;
                Swal.fire({
                    title: data.title ?? '¿Confirmar Acción?',
                    text: data.text ?? "Esta acción removerá el registro permanentemente.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Sí, Eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch(data.target || data.method, { id: data.id });
                    }
                })
            });
        });
    </script>
</body>
</html>
