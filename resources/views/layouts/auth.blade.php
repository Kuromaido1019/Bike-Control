<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>BikeControl - @yield('title')</title>

    <!-- Fuentes -->
    <script src="https://kit.fontawesome.com/770f2e87c7.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- CSS -->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
</head>

<body class="bg-gradient-primary">
    @yield('content')

    <!-- Loader para login -->
    <div id="login-loader-bg">
        <div class="login-loader-content login-loader-flexcard">
            <div class="login-loader-logo-col">
                <img src="{{ asset('img/bike_logo.svg') }}" alt="BikeControl Logo" class="login-loader-logo">
            </div>
            <div class="login-loader-svg-col">
                <svg class="bike" viewBox="0 0 48 30" width="96px" height="60px">
                    <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1">
                        <g transform="translate(9.5,19)">
                            <circle class="bike__tire" r="9" stroke-dasharray="56.549 56.549" />
                            <g class="bike__spokes-spin" stroke-dasharray="31.416 31.416" stroke-dashoffset="-23.562">
                                <circle class="bike__spokes" r="5" />
                                <circle class="bike__spokes" r="5" transform="rotate(180,0,0)" />
                            </g>
                        </g>
                        <g transform="translate(24,19)">
                            <g class="bike__pedals-spin" stroke-dasharray="25.133 25.133" stroke-dashoffset="-21.991" transform="rotate(67.5,0,0)">
                                <circle class="bike__pedals" r="4" />
                                <circle class="bike__pedals" r="4" transform="rotate(180,0,0)" />
                            </g>
                        </g>
                        <g transform="translate(38.5,19)">
                            <circle class="bike__tire" r="9" stroke-dasharray="56.549 56.549" />
                            <g class="bike__spokes-spin" stroke-dasharray="31.416 31.416" stroke-dashoffset="-23.562">
                                <circle class="bike__spokes" r="5" />
                                <circle class="bike__spokes" r="5" transform="rotate(180,0,0)" />
                            </g>
                        </g>
                        <polyline class="bike__seat" points="14 3,18 3" stroke-dasharray="5 5" />
                        <polyline class="bike__body" points="16 3,24 19,9.5 19,18 8,34 7,24 19" stroke-dasharray="79 79" />
                        <path class="bike__handlebars" d="m30,2h6s1,0,1,1-1,1-1,1" stroke-dasharray="10 10" />
                        <polyline class="bike__front" points="32.5 2,38.5 19" stroke-dasharray="19 19" />
                    </g>
                </svg>
                <!-- Puedes agregar un mensaje aquí si lo deseas -->
            </div>
        </div>
    </div>
    <link rel="stylesheet" href="{{ asset('css/login-loader-bike.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login-loader.css') }}">
    <script>
        // Oculta el loader cuando la página termina de cargar (mínimo 6 segundos visible)
        window.addEventListener('DOMContentLoaded', function() {
            var loader = document.getElementById('login-loader-bg');
            var minTime = 500; // 2 segundos
            var start = Date.now();
            function hideLoader() {
                var elapsed = Date.now() - start;
                if (elapsed < minTime) {
                    setTimeout(hideLoader, minTime - elapsed);
                } else {
                    loader.classList.add('hide');
                }
            }
            window.addEventListener('load', hideLoader);
        });
    </script>

    <!-- Scripts principales -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
</body>

</html>
