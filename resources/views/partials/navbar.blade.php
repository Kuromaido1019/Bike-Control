<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <!-- Sidebar Toggle -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">
        <div class="topbar-divider d-none d-sm-block"></div>
        <!-- Informacion y Funciones -->
        <li class="nav-item d-flex align-items-center">
            @if(Auth::check())
                <img class="img-profile rounded-circle me-2" src="{{ asset('img/profile-m.svg') }}" style="width:32px; height:32px;">
                <span class="d-none d-lg-inline text-gray-600 small me-3">{{ Auth::user()->name }}</span>
                <div class="vr mx-2 d-none d-lg-block" style="height:32px;"></div>
            @endif
            <a class="nav-link text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="fas fa-sign-out-alt fa-sm fa-fw me-1"></i>
                Cerrar Sesión
            </a>
        </li>
        <!--/ Informacion y Funciones -->
    </ul>
</nav>
