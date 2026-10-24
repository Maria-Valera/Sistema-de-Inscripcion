<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificar Correo - Sistema de Inscripción</title>
    
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
        .login-page {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), 
                        url('{{ asset("HomePage/vidaEstudiantil/Bievenida_img.jpg") }}') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            height: auto !important;
            overflow-y: auto;
            padding: 20px 0;
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
        
        <!-- Card de verificación -->
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg font-weight-bold mb-3">Verifica tu correo electrónico</p>

                @if (session('resent'))
                    <div class="alert alert-success py-2 px-3 mb-3 text-sm" role="alert">
                        <i class="fas fa-check-circle mr-1"></i>
                        Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
                    </div>
                @endif

                <p class="text-muted text-center mb-4">
                    Te enviamos un correo a <strong>{{ auth()->user()->email }}</strong>. Revisa tu bandeja y haz click en el enlace para activar tu cuenta.
                </p>

                <p class="text-muted text-center text-sm mb-4">
                    Si no recibiste el correo, puedes solicitar otro a continuación:
                </p>

                <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-block mb-3">
                        <i class="fas fa-paper-plane mr-2"></i> Reenviar correo de verificación
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar sesión
                    </button>
                </form>

                <!-- Espacio adicional -->
                <div class="mt-4 mb-2"></div>
                
                <!-- Copyright -->
                <p class="mb-0 text-center text-muted">
                    <small>&copy; {{ date('Y') }} Sistema de Inscripción. Todos los derechos reservados.</small>
                </p>
            </div>
            <!-- /.login-card-body -->
        </div><!-- /.card -->
    </div>
    <!-- /.login-box -->

    <!-- jQuery -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
</body>
</html>