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
        
        body { background-color: var(--primary-bg); font-family: 'Inter', system-ui, sans-serif; overflow-x: hidden; }
        
        /* Contenedor principal Flexible */
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        
        /* SIDEBAR: Manejo de transiciones y estados (colapsado/móvil) */
        .sidebar { min-height: 100vh; background-color: var(--sidebar-bg); color: #fff; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); width: var(--sidebar-width); z-index: 1040; display: flex; flex-direction: column; }
        .sidebar.collapsed { width: var(--sidebar-collapsed-width); }
        
        /* Estilos de navegación interna */
        .sidebar-brand { display: flex; align-items: center; justify-content: flex-start; padding: 1.5rem; height: var(--topbar-height); text-decoration: none; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-brand .brand-icon { font-size: 2rem; color: #f8b4b4; transition: all 0.3s; }
        .sidebar.collapsed .sidebar-brand { justify-content: center; padding: 1.5rem 0; }
        .sidebar.collapsed .brand-text { display: none; }
        
        .sidebar-nav { padding: 1rem 0.5rem; flex: 1; }
        .nav-item { margin-bottom: 0.2rem; }
        .nav-link { color: #a4b2c2; padding: 0.8rem 1rem; border-radius: 0.5rem; display: flex; align-items: center; transition: all 0.2s; white-space: nowrap; }
        .nav-link i { font-size: 1.25rem; min-width: 1.5rem; margin-right: 1rem; transition: all 0.2s; }
        .nav-link:hover, .nav-link.active { color: #fff; background-color: var(--sidebar-hover); }
        
        .sidebar.collapsed .nav-link { justify-content: center; padding: 0.8rem 0; }
        .sidebar.collapsed .nav-link i { margin-right: 0; font-size: 1.5rem; }
        .sidebar.collapsed .nav-text { display: none; }
        
        /* MAIN CONTENT: Se expande/contrae según el sidebar */
        .main-content { width: calc(100% - var(--sidebar-width)); transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); min-height: 100vh; display: flex; flex-direction: column; }
        .sidebar.collapsed ~ .main-content { width: calc(100% - var(--sidebar-collapsed-width)); }
        
        /* Topbar / Header Superior */
        .topbar { height: var(--topbar-height); background-color: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.02); z-index: 1030; }
        .toggle-btn { background: transparent; border: none; color: #4b5563; font-size: 1.5rem; cursor: pointer; padding: 0.5rem; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; transition: background 0.2s; }
        .toggle-btn:hover { background: #f3f4f6; }
        
        /* Content Panel: Área de renderizado del contenido */
        .content-panel { padding: 1.5rem; flex: 1; }
        
        /* Estilos globales para Cards y Tablas (Base del sistema) */
        .card { border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border-radius: 0.75rem; }
        .table-responsive { border-radius: 0.75rem; overflow: hidden; }
        .table > :not(caption) > * > * { padding: 1rem 1.5rem; }

        /* Responsividad: Ocultado de sidebar en pantallas pequeñas */
        @media (max-width: 768px) {
            .sidebar { margin-left: calc(var(--sidebar-width) * -1); position: fixed; }
            .sidebar.mobile-show { margin-left: 0; box-shadow: 4px 0 10px rgba(0,0,0,0.1); }
            .main-content { width: 100%; margin-left: 0; }
            .sidebar.collapsed ~ .main-content { width: 100%; }
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
                        <a href="/users" class="nav-link {{ request()->is('users') ? 'active' : '' }} text-decoration-none" wire:navigate>
                            <i class="ph ph-users-three"></i>
                            <span class="nav-text">Gestión de Usuarios</span>
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
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=f8b4b4&color=1e2227" alt="Avatar" width="38" height="38" class="rounded-circle me-2 border">
                        <span class="d-none d-sm-inline"><strong>{{ Auth::user()->name }}</strong></span>
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
         * Se ejecuta en carga inicial y en cada navegación de Livewire.
         */
        function initLayout() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            
            // 1. GESTIÓN DEL SIDEBAR
            if (toggleBtn && sidebar) {
                // Recuperar estado previo del sidebar
                if (localStorage.getItem('sidebarState') === 'collapsed' && window.innerWidth > 768) {
                    sidebar.classList.add('collapsed');
                }

                // Clonar botón para limpiar listeners de la navegación anterior (Evita duplicados)
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
            }

            // 2. GESTIÓN MANUAL DE DROPDOWNS (Solución definitiva SPA)
            // Bootstrap automático suele fallar al intercambiar el DOM con Livewire.
            document.querySelectorAll('.dropdown-toggle').forEach(el => {
                const newEl = el.cloneNode(true);
                el.parentNode.replaceChild(newEl, el);
                newEl.removeAttribute('data-bs-toggle'); // Deshabilitamos Auto-Bootstrap
                
                const dropdownInstance = new bootstrap.Dropdown(newEl);
                newEl.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropdownInstance.toggle();
                });
            });
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

            // Confirmación de Borrado de Registros
            Livewire.on('confirm-delete', (event) => {
                const data = event[0];
                Swal.fire({
                    title: '¿Confirmar Acción?',
                    text: data.text ?? "Esta acción removerá el registro permanentemente.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Sí, Eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch(data.method, { id: data.id });
                    }
                })
            });
        });
    </script>
</body>
</html>
