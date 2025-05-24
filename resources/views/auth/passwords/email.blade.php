@extends('layouts.auth')

@section('title', 'Reiniciar Contraseña')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="row g-0">
                    <!-- Image Section -->
                    <div class="col-lg-6 d-flex align-items-center justify-content-center bg-light">
                        <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 25rem;"
                            src="{{ asset('img/forgot-password.svg') }}" alt="Imagen de seguridad">
                    </div>
                    <!-- Form Section -->
                    <div class="col-lg-6">
                        <div class="card-body p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Reiniciar Contraseña</h1>
                                <p class="mb-4">Ingresa tu correo electrónico para recibir un enlace de reinicio de contraseña.</p>
                                @if (session('status'))
                                    <div class="alert alert-success" role="alert">
                                        {{ __(session('status')) }}
                                    </div>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('password.email') }}" id="resetPasswordForm">
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

                                <!-- Botón de submit -->
                                <button type="submit" class="btn btn-primary btn-user btn-block" id="resetBtn">
                                    <span id="resetBtnText">Reiniciar Contraseña</span>
                                    <span id="resetSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                            </form>
                            <div class="text-center mt-4">
                                <a href="{{ route('login') }}" class="btn btn-secondary btn-user btn-block">Volver al Login</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
