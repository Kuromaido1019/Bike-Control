<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('home') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-person-biking"></i>
        </div>
        <div class="sidebar-brand-text mx-3">BikeControl</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('home') }}">
            <i class="fa-solid fa-magnifying-glass-chart"></i>
            <span>Panel de Control</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    @auth
        @if(Auth::user()->role === 'admin')
        <!-- Opciones solo para admin -->
        <div class="sidebar-heading text-white admin-heading-custom">Gestión</div>
        <li class="nav-item {{ request()->is('admin/users*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.users.index') }}">
                <i class="fas fa-users-cog"></i>
                <span>Gestión de Usuarios</span>
            </a>
        </li>
        <li class="nav-item {{ request()->is('admin/control-acceso*') ? 'active' : '' }}">
            <a class="nav-link text-start" href="{{ route('admin.control-acceso') }}">
                <i class="fas fa-key"></i>
                <span>Gestión de Accesos</span>
            </a>
        </li>
        <li class="nav-item {{ request()->is('admin/bikes*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.bikes.index') }}">
                <i class="fas fa-bicycle"></i>
                <span>Gestión de Bicicletas</span>
            </a>
        </li>
        <li class="nav-item {{ request()->routeIs('guard.incidents.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('guard.incidents.index') }}">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Incidentes / Reclamos</span>
            </a>
        </li>
        <li class="nav-item {{ request()->is('admin/reportes*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.reports.index') }}">
                <i class="fas fa-chart-bar"></i>
                <span>Reportes</span>
            </a>
        </li>
        <hr class="sidebar-divider">
        @endif

        @if(Auth::user()->role === 'guardia')
        <!-- Opciones solo para guardia -->
        <div class="sidebar-heading">Guardia</div>
        <li class="nav-item {{ request()->routeIs('guard.control-acceso') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('guard.control-acceso') }}">
                <i class="fas fa-fw fa-shield-dog"></i>
                <span>Control Acceso</span>
            </a>
        </li>
        <li class="nav-item {{ request()->routeIs('guard.incidents.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('guard.incidents.index') }}">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Incidentes / Reclamos</span>
            </a>
        </li>
        <hr class="sidebar-divider">
        @endif

        @if(Auth::user()->role === 'visitante')
        <!-- Opciones solo para visitante -->
        <div class="sidebar-heading">Usuario</div>
        <li class="nav-item {{ request()->routeIs('visitante.mis-datos') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('visitante.mis-datos') }}">
                <i class="fas fa-fw fa-user-tie"></i>
                <span>Mi Usuario</span>
            </a>
        </li>
        <hr class="sidebar-divider">
        @endif
    @endauth

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
