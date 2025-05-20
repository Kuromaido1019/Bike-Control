@extends('layouts.auth')
@section('title', 'Registro de visitante')
@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-6 d-flex align-items-center justify-content-center">
                            <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 25rem;" src="{{ asset('img/undraw_ride.svg') }}" alt="Imagen de registro">
                        </div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Registro de visitante</h1>
                                </div>
                                <form method="POST" action="{{ route('register') }}" id="registerForm">
                                    @csrf
                                    <!-- Paso 1: Datos de usuario -->
                                    <div id="step-1" class="step">
                                        <h6 class="text-primary">1. Datos de usuario</h6>
                                        <div class="form-group mb-2">
                                            <input type="text" class="form-control form-control-user" name="name" value="{{ old('name') }}" required placeholder="Nombre completo">
                                            @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="form-group mb-2">
                                            <input type="text" class="form-control form-control-user" name="rut" value="{{ old('rut') }}" required placeholder="RUT">
                                            @error('rut')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="form-group mb-2">
                                            <input type="email" class="form-control form-control-user" name="email" value="{{ old('email') }}" required placeholder="Correo electrónico">
                                            @error('email')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="form-group mb-2">
                                            <input type="password" class="form-control form-control-user" name="password" required placeholder="Contraseña">
                                            @error('password')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="form-group mb-2">
                                            <input type="password" class="form-control form-control-user" name="password_confirmation" required placeholder="Confirmar contraseña">
                                        </div>
                                        <button type="button" class="btn btn-primary btn-user btn-block mt-3" onclick="nextStep(1)">Siguiente</button>
                                    </div>
                                    <!-- Paso 2: Perfil -->
                                    <div id="step-2" class="step d-none">
                                        <h6 class="text-primary">2. Perfil</h6>
                                        <div class="form-group mb-2">
                                            <input type="text" class="form-control form-control-user" name="phone" value="{{ old('phone') }}" required placeholder="Teléfono">
                                            @error('phone')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="form-group mb-2">
                                            <input type="date" class="form-control form-control-user" name="birthdate" value="{{ old('birthdate') }}" required placeholder="Fecha de nacimiento">
                                            @error('birthdate')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="form-group mb-2">
                                            <input type="text" class="form-control form-control-user" name="career" value="{{ old('career') }}" required placeholder="Carrera">
                                            @error('career')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="d-flex justify-content-between mt-3">
                                            <button type="button" class="btn btn-secondary" onclick="prevStep(2)">Atrás</button>
                                            <button type="button" class="btn btn-primary" onclick="nextStep(2)">Siguiente</button>
                                        </div>
                                    </div>
                                    <!-- Paso 3: Bicicleta -->
                                    <div id="step-3" class="step d-none">
                                        <h6 class="text-primary">3. Bicicleta</h6>
                                        <div class="form-group mb-2">
                                            <input type="text" class="form-control form-control-user" name="brand" value="{{ old('brand') }}" required placeholder="Marca">
                                            @error('brand')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="form-group mb-2">
                                            <input type="text" class="form-control form-control-user" name="model" value="{{ old('model') }}" required placeholder="Modelo">
                                            @error('model')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="form-group mb-4">
                                            <input type="text" class="form-control form-control-user" name="color" value="{{ old('color') }}" required placeholder="Color">
                                            @error('color')<span class="text-danger">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <button type="button" class="btn btn-secondary" onclick="prevStep(3)">Atrás</button>
                                            <button type="submit" class="btn btn-primary btn-user">Registrarse</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="text-center mt-4">
                                    <a class="small" href="{{ route('login') }}">¿Ya tienes cuenta? Inicia sesión</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    // Validación y máscara para RUT chileno
    $(function() {
        // Máscara para RUT: 12.345.678-9 o 12.345.678-K
        $("input[name='rut']").mask('00.000.000-A', {
            translation: {
                'A': { pattern: /[0-9kK]/, optional: true }
            },
            onKeyPress: function(rut, e, field, options) {
                // Uppercase K
                field.val(rut.replace('k', 'K'));
            }
        });

        // Máscara para RUT chileno con formato automático: 12.345.678-9 o 8.861.155-K
        $("input[name='rut']").on('input', function() {
            let value = $(this).val().replace(/[^0-9kK]/g, '').toUpperCase();
            let formatted = '';
            if (value.length > 1) {
                let cuerpo = value.slice(0, -1);
                let dv = value.slice(-1);
                // Formatear cuerpo con puntos
                cuerpo = cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                formatted = cuerpo + '-' + dv;
            } else {
                formatted = value;
            }
            $(this).val(formatted);
        });

        // Máscara para teléfono chileno (8 dígitos)
        $("input[name='phone']").mask('00000000');
    });

    function validarRut(rut) {
        rut = rut.replace(/\./g, '').replace(/\-/g, '').toUpperCase();
        if (!rut.match(/^\d{7,8}[0-9K]$/)) return false;
        var cuerpo = rut.slice(0, -1);
        var dv = rut.slice(-1);
        var suma = 0, multiplo = 2;
        for (var i = cuerpo.length - 1; i >= 0; i--) {
            suma += parseInt(cuerpo.charAt(i)) * multiplo;
            multiplo = multiplo < 7 ? multiplo + 1 : 2;
        }
        var dvEsperado = 11 - (suma % 11);
        dvEsperado = dvEsperado === 11 ? '0' : dvEsperado === 10 ? 'K' : dvEsperado.toString();
        return dv === dvEsperado;
    }

    function validarEmail(email) {
        return /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email);
    }

    function validarTelefono(phone) {
        return /^\d{8}$/.test(phone);
    }

    function showError(input, message) {
        $(input).addClass('is-invalid');
        if (!$(input).next('.text-danger').length) {
            $(input).after('<span class="text-danger">' + message + '</span>');
        }
    }
    function clearErrors(step) {
        $('#step-' + step + ' .is-invalid').removeClass('is-invalid');
        $('#step-' + step + ' .text-danger').remove();
    }

    function nextStep(current) {
        clearErrors(current);
        var valid = true;
        if (current === 1) {
            var name = $("input[name='name']");
            var rut = $("input[name='rut']");
            var email = $("input[name='email']");
            var password = $("input[name='password']");
            var password_confirmation = $("input[name='password_confirmation']");
            if (!name.val()) {
                showError(name, 'El nombre es obligatorio');
                valid = false;
            }
            if (!rut.val()) {
                showError(rut, 'El RUT es obligatorio');
                valid = false;
            } else if (!validarRut(rut.val())) {
                showError(rut, 'RUT chileno inválido');
                valid = false;
            }
            if (!email.val()) {
                showError(email, 'El correo es obligatorio');
                valid = false;
            } else if (!validarEmail(email.val())) {
                showError(email, 'Correo electrónico inválido');
                valid = false;
            }
            if (!password.val()) {
                showError(password, 'La contraseña es obligatoria');
                valid = false;
            }
            if (!password_confirmation.val()) {
                showError(password_confirmation, 'Debes confirmar la contraseña');
                valid = false;
            } else if (password.val() !== password_confirmation.val()) {
                showError(password_confirmation, 'Las contraseñas no coinciden');
                valid = false;
            }
        }
        if (current === 2) {
            var phone = $("input[name='phone']");
            var birthdate = $("input[name='birthdate']");
            var career = $("input[name='career']");
            if (!phone.val()) {
                showError(phone, 'El teléfono es obligatorio');
                valid = false;
            } else if (!validarTelefono(phone.val())) {
                showError(phone, 'El teléfono debe tener 8 dígitos');
                valid = false;
            }
            if (!birthdate.val()) {
                showError(birthdate, 'La fecha de nacimiento es obligatoria');
                valid = false;
            }
            if (!career.val()) {
                showError(career, 'La carrera es obligatoria');
                valid = false;
            }
        }
        if (current === 3) {
            var brand = $("input[name='brand']");
            var model = $("input[name='model']");
            var color = $("input[name='color']");
            if (!brand.val()) {
                showError(brand, 'La marca es obligatoria');
                valid = false;
            }
            if (!model.val()) {
                showError(model, 'El modelo es obligatorio');
                valid = false;
            }
            if (!color.val()) {
                showError(color, 'El color es obligatorio');
                valid = false;
            }
        }
        if (!valid) return;
        document.getElementById('step-' + current).classList.add('d-none');
        document.getElementById('step-' + (current + 1)).classList.remove('d-none');
    }

    function prevStep(current) {
        clearErrors(current);
        document.getElementById('step-' + current).classList.add('d-none');
        document.getElementById('step-' + (current - 1)).classList.remove('d-none');
    }
    // Validación final al enviar
    $("#registerForm").on('submit', function(e) {
        clearErrors(3);
        var rut = $("input[name='rut']");
        var email = $("input[name='email']");
        var phone = $("input[name='phone']");
        var valid = true;
        if (!validarRut(rut.val())) {
            showError(rut, 'RUT chileno inválido');
            valid = false;
        }
        if (!validarEmail(email.val())) {
            showError(email, 'Correo electrónico inválido');
            valid = false;
        }
        if (!validarTelefono(phone.val())) {
            showError(phone, 'El teléfono debe tener 8 dígitos');
            valid = false;
        }
        if (!valid) e.preventDefault();
    });
</script>
@endsection
