<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>BikeControl - @yield('title')</title>

    <!-- Fuentes y CSS -->
    @include('partials.head')

    @include('partials.sweetalert2')
    <!-- Estilos adicionales -->
    <style>
        .sidebar-dark .nav-item.active .nav-link {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        #wrapper #content-wrapper {
            background-color: #f8f9fc;
        }
        .topbar {
            height: 4.375rem;
        }
        .sidebar-dark .sidebar-brand {
            height: 4.375rem;
        }
        .admin-heading-custom {
            font-size: 0.80rem;
            white-space: normal;
            overflow: visible;
            text-overflow: unset;
            max-width: 100%;
            display: block;
            word-break: break-word;
            line-height: 1.2;
            padding-right: 4px;
            padding-left: 4px;
        }
        .sidebar.toggled .admin-heading-custom {
            font-size: 0.65rem;
            letter-spacing: 0.5px;
            text-align: center;
            padding: 0 2px;
            white-space: normal;
            overflow: visible;
            text-overflow: unset;
        }
    </style>

    @stack('styles')

    <!-- Importación local del script html5-qrcode -->
    <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
</head>
<body id="page-top" class="sidebar-toggled">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        @include('partials.sidebar')

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                @include('partials.navbar')

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    @yield('content')
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            @include('partials.footer')
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    @include('partials.logout-modal')

    <!-- Scripts -->
    @include('partials.scripts')

    <!-- Scripts personalizados por página -->
    @yield('custom-scripts')

    <!-- SB Admin 2 Sidebar Script -->
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

    <!-- Inicialización de plugins -->
    <script>
        // Toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.body.classList.toggle('sidebar-toggled');
            document.querySelector('.sidebar').classList.toggle('toggled');

            if (document.querySelector('.sidebar').classList.contains('toggled')) {
                document.querySelector('.sidebar .collapse').classList.remove('show');
            }
        });

        // Cerrar el sidebar cuando se hace clic en un ítem del menú en móviles
        document.querySelectorAll('.sidebar .nav-item').forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    document.body.classList.add('sidebar-toggled');
                    document.querySelector('.sidebar').classList.add('toggled');
                }
            });
        });

        // Activar tooltips (Bootstrap 5)
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
            new bootstrap.Tooltip(el);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
