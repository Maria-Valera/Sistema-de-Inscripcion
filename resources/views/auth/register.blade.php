<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro - Sistema de Inscripción</title>
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    
    <style>
        /* Fondo con imagen */
        .login-page {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), 
                        url('{{ asset("HomePage/vidaEstudiantil/Bievenida_img.jpg") }}') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            height: auto !important;
            overflow-y: auto;
            padding: 20px 0;
        }
        
        /* Ajustes adicionales */
        .login-box {
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Estilos para validación en tiempo real */
        .is-valid {
            border-color: #28a745 !important;
        }
        
        .is-invalid {
            border-color: #dc3545 !important;
        }
        
        .valid-feedback {
            color: #28a745;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .validation-message {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <!-- Logo -->
        <div class="login-logo">
            <a href="{{ url('/') }}" class="d-flex flex-column align-items-center">
                <div class="logo-container">
                    <img src="{{ asset('HomePage/comunidad/liceoLogo.png') }}" alt="Logo" class="login-logo-img">
                </div>
                <div class="mt-3 text-center">
                    <h4 class="mb-0">Sistema de Inscripción</h4>
                    <small class="text-white-50">Liceo "Gral" Juan Guillermo Iribarren</small>
                </div>
            </a>
        </div>
        
        <!-- Card de registro -->
        <div class="card">
            <div class="card-body register-card-body">
                <p class="login-box-msg">Registrar una nueva cuenta</p>

                <div class="alert alert-info py-2 px-3 mb-3 text-sm" role="alert">
                    <i class="fas fa-info-circle mr-1"></i>
                    Debes tener tu cédula registrada previamente en el sistema para crear una cuenta. Si no puedes registrarte, contacta a la institución.
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger py-2 px-3 mb-3 text-sm">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register') }}" method="post">
                    @csrf

                    <div class="input-group mb-3">
                        <select name="tipo_documento_id" class="form-control @error('tipo_documento_id') is-invalid @enderror" required autofocus>
                            <option value="">Tipo de documento</option>
                            @php
                                $tipos = isset($tipoDocumentos) ? $tipoDocumentos : \App\Models\TipoDocumento::where('status', 1)->get();
                            @endphp
                            @foreach ($tipos as $tipo)
                                <option value="{{ $tipo->id }}" {{ old('tipo_documento_id') == $tipo->id ? 'selected' : '' }}>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-id-card"></span>
                            </div>
                        </div>
                        @error('tipo_documento_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="input-group mb-3">
                        <input type="text" name="numero_documento" class="form-control @error('numero_documento') is-invalid @enderror" 
                               placeholder="Número de documento" value="{{ old('numero_documento') }}" maxlength="20" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-hashtag"></span>
                            </div>
                        </div>
                        @error('numero_documento')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               placeholder="Correo electrónico" value="{{ old('email') }}" maxlength="255" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                               placeholder="Contraseña (mínimo 8 caracteres, letras y números)" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="input-group mb-3">
                        <input type="password" name="password_confirmation" class="form-control" 
                               placeholder="Confirmar contraseña" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Registrarse</button>
                        </div>
                    </div>
                </form>

                <p class="mt-3 mb-0 text-center">
                    <a href="{{ route('login') }}" class="text-center">¿Ya tengo una cuenta? Iniciar sesión</a>
                </p>
                
                <!-- Espacio adicional -->
                <div class="mt-4 mb-3"></div>
                
                <!-- Copyright -->
                <p class="mb-0 text-center text-muted">
                    <small>&copy; {{ date('Y') }} Sistema de Inscripción. Todos los derechos reservados.</small>
                </p>
            </div>
            <!-- /.form-box -->
        </div><!-- /.card -->
    </div>
    <!-- /.register-box -->

    <!-- jQuery -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    
    <!-- Validación en tiempo real -->
    <script>
    $(document).ready(function() {
        let emailTimeout;
        let validationResults = {
            tipo_documento_id: false,
            numero_documento: false,
            email: false,
            password: false,
            password_confirmation: false
        };
        
        function scrollToBottomIfNeeded() {
            const body = document.body;
            const html = document.documentElement;
            const height = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);
            const scrollPosition = window.scrollY + window.innerHeight;
            if (scrollPosition >= height) {
                window.scrollTo(0, height);
            }
        }
        
        // Validación del tipo de documento
        $('select[name="tipo_documento_id"]').on('change', function() {
            const val = $(this).val();
            const $inputGroup = $(this).closest('.input-group');
            $inputGroup.find('.validation-message').remove();
            $(this).removeClass('is-valid is-invalid');

            if (!val) {
                $(this).addClass('is-invalid');
                $inputGroup.append('<div class="validation-message invalid-feedback">Seleccione un tipo de documento.</div>');
                validationResults.tipo_documento_id = false;
            } else {
                $(this).addClass('is-valid');
                validationResults.tipo_documento_id = true;
            }
        });

        // Validación del número de documento
        $('input[name="numero_documento"]').on('input', function() {
            const val = $(this).val().trim();
            const $inputGroup = $(this).closest('.input-group');
            $inputGroup.find('.validation-message').remove();
            $(this).removeClass('is-valid is-invalid');

            if (val.length === 0) {
                validationResults.numero_documento = false;
                return;
            }

            if (val.length > 20) {
                $(this).addClass('is-invalid');
                $inputGroup.append('<div class="validation-message invalid-feedback">El número de documento no puede superar 20 caracteres.</div>');
                validationResults.numero_documento = false;
            } else {
                $(this).addClass('is-valid');
                validationResults.numero_documento = true;
            }
        });

        // Validación del email
        $('input[name="email"]').on('input', function() {
            const email = $(this).val();
            const $inputGroup = $(this).closest('.input-group');
            
            // Eliminar mensajes anteriores
            $inputGroup.find('.validation-message').remove();
            $(this).removeClass('is-valid is-invalid');
            
            // Limpiar timeout anterior
            clearTimeout(emailTimeout);
            
            if (email.length === 0) {
                validationResults.email = false;
                return;
            }
            
            // Validación básica de formato
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                $(this).addClass('is-invalid');
                $inputGroup.append('<div class="validation-message invalid-feedback">El formato del correo electrónico no es válido.</div>');
                validationResults.email = false;
                return;
            }
            
            if (email.length > 255) {
                $(this).addClass('is-invalid');
                $inputGroup.append('<div class="validation-message invalid-feedback">El correo electrónico no puede tener más de 255 caracteres.</div>');
                validationResults.email = false;
                return;
            }
            
            // Correo con formato válido
            $('input[name="email"]').removeClass('is-invalid').addClass('is-valid');
            validationResults.email = true;
            scrollToBottomIfNeeded();
        });

        
        // Validación de la contraseña (mínimo 8 caracteres, con letras y números)
        $('input[name="password"]').on('input', function() {
            const password = $(this).val();
            const $inputGroup = $(this).closest('.input-group');
            
            // Eliminar mensajes anteriores
            $inputGroup.find('.validation-message').remove();
            $(this).removeClass('is-valid is-invalid');
            
            if (password.length === 0) {
                validationResults.password = false;
                return;
            }
            
            const hasLetter = /[a-zA-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);

            if (password.length < 8) {
                $(this).addClass('is-invalid');
                $inputGroup.append('<div class="validation-message invalid-feedback">La contraseña debe tener al menos 8 caracteres.</div>');
                validationResults.password = false;
                scrollToBottomIfNeeded();
            } else if (!hasLetter || !hasNumber) {
                $(this).addClass('is-invalid');
                $inputGroup.append('<div class="validation-message invalid-feedback">La contraseña debe contener al menos una letra y un número.</div>');
                validationResults.password = false;
                scrollToBottomIfNeeded();
            } else {
                $(this).addClass('is-valid');
                validationResults.password = true;
            }
            
            // Revalidar confirmación de contraseña si ya tiene valor
            const confirmValue = $('input[name="password_confirmation"]').val();
            if (confirmValue.length > 0) {
                $('input[name="password_confirmation"]').trigger('input');
            }
        });
        
        // Validación de confirmación de contraseña
        $('input[name="password_confirmation"]').on('input', function() {
            const password = $('input[name="password"]').val();
            const confirmation = $(this).val();
            const $inputGroup = $(this).closest('.input-group');
            
            // Eliminar mensajes anteriores
            $inputGroup.find('.validation-message').remove();
            $(this).removeClass('is-valid is-invalid');
            
            if (confirmation.length === 0) {
                validationResults.password_confirmation = false;
                return;
            }
            
            if (password !== confirmation) {
                $(this).addClass('is-invalid');
                $inputGroup.append('<div class="validation-message invalid-feedback">La confirmación de contraseña no coincide.</div>');
                validationResults.password_confirmation = false;
                scrollToBottomIfNeeded();
            } else {
                $(this).addClass('is-valid');
                validationResults.password_confirmation = true;
            }
        });
        
        // Comprobar estado inicial en selects con old()
        if ($('select[name="tipo_documento_id"]').val()) {
            validationResults.tipo_documento_id = true;
        }
        if ($('input[name="numero_documento"]').val()) {
            validationResults.numero_documento = true;
        }

        // Permitir envío nativo normal
        $('form').on('submit', function(e) {
            if (!$('select[name="tipo_documento_id"]').val()) {
                e.preventDefault();
                $('select[name="tipo_documento_id"]').addClass('is-invalid');
                return false;
            }
        });
    });
    </script>
</body>
</html>