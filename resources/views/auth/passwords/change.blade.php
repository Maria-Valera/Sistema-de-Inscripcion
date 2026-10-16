<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambiar Contraseña - Sistema de Inscripción</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">  
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    
    <style>
        .login-page {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), 
                        url('{{ asset("HomePage/comunidad/login.jpeg") }}') no-repeat center center fixed;
            background-size: cover;
        }
        
        .login-box {
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
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
        
        <!-- Card de cambio de contraseña -->
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Por seguridad, debes cambiar tu contraseña antes de continuar.</p>

                <!-- Mensaje de error general -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" style="font-size: 0.875rem; padding: 0.75rem 1rem;">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="font-size: 1rem;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle mr-2" style="font-size: 1rem;"></i>
                            <div>
                                @foreach ($errors->all() as $error)
                                    <span class="mb-0">{{ $error }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Mensaje de éxito -->
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" style="font-size: 0.875rem; padding: 0.75rem 1rem;">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="font-size: 1rem;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle mr-2" style="font-size: 1rem;"></i>
                            <span class="mb-0">{{ session('status') }}</span>
                        </div>
                    </div>
                @endif

                <form action="{{ route('password.change.post') }}" method="post">
                    @csrf
                    
                    <!-- Contraseña actual -->
                    <div class="input-group mb-3">
                        <input type="password" name="current_password" class="form-control" 
                               placeholder="Contraseña actual" required autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Nueva contraseña -->
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control" 
                               placeholder="Nueva contraseña (mínimo 8 caracteres, con letras y números)" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Confirmar nueva contraseña -->
                    <div class="input-group mb-3">
                        <input type="password" name="password_confirmation" class="form-control" 
                               placeholder="Confirmar nueva contraseña" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botón de cambio -->
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Cambiar Contraseña</button>
                        </div>
                    </div>
                </form>

                <!-- Volver al inicio -->
                <p class="mt-3 mb-0 text-center">
                    <a href="{{ route('home') }}" class="text-center">Volver al inicio</a>
                </p>
                
                <!-- Copyright -->
                <p class="mb-0 text-center text-muted">
                    <small>&copy; {{ date('Y') }} Sistema de Inscripción. Todos los derechos reservados.</small>
                </p>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
</body>
</html>
