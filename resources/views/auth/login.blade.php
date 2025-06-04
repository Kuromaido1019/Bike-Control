@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="container">
    <!-- Fila externa -->
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Fila anidada dentro del cuerpo de la tarjeta -->
                    <div class="row">
                        <div class="col-lg-6 d-flex align-items-center justify-content-center">
                            <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 25rem;"
                                src="{{ asset('img/login.svg') }}" alt="Imagen de seguridad">
                        </div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Bienvenido a Bike Control</h1>
                                </div>
                                <form method="POST" action="{{ route('login') }}" id="loginForm">
                                    @csrf

                                    <!-- Campo Email -->
                                    <div class="form-group">
                                        <input type="email" class="form-control form-control-user"
                                            name="email" id="email" value="{{ old('email') }}"
                                            required autocomplete="email" autofocus maxlength="100"
                                            placeholder="Ingresa tu correo electrónico...">
                                        @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Campo Contraseña -->
                                    <div class="form-group">
                                        <input type="password" class="form-control form-control-user"
                                            name="password" id="password" required maxlength="100"
                                            autocomplete="current-password"
                                            placeholder="Contraseña">
                                        @error('password')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Botón de submit -->
                                    <button type="submit" class="btn btn-primary btn-user btn-block" id="loginBtn">
                                        <span id="loginBtnText">Iniciar Sesión</span>
                                        <span id="loginSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    </button>

                                    <!-- Enlaces adicionales -->
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="{{ route('password.request') }}">
                                            ¿Olvidaste tu contraseña?
                                        </a>
                                    </div>
                                    <!-- Enlace de registro
                                    <div class="text-center mt-2">
                                        <a class="small" href="{{ route('register') }}">
                                            ¿No tienes cuenta? Regístrate
                                        </a>
                                    </div>
                                    -->
                                </form>
                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const loginForm = document.getElementById('loginForm');
                                    const loginBtn = document.getElementById('loginBtn');
                                    const loginBtnText = document.getElementById('loginBtnText');
                                    const loginSpinner = document.getElementById('loginSpinner');
                                    if (loginForm) {
                                        loginForm.addEventListener('submit', function() {
                                            loginBtn.disabled = true;
                                            loginBtnText.textContent = 'Validando...';
                                            loginSpinner.classList.remove('d-none');
                                        });
                                    }
                                });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
