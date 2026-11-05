<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sin Módulos Asignados</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --warning: #f59e0b;
            --dark: #1f2937;
            --light: #f3f4f6;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: var(--dark);
        }
        .card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 520px;
            width: 100%;
            padding: 2.5rem;
            text-align: center;
            border-top: 6px solid var(--warning);
        }
        .icon-box {
            width: 80px;
            height: 80px;
            background: #fef3c7;
            color: var(--warning);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }
        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #111827;
        }
        p {
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .user-info {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            color: #374151;
        }
        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #ef4444;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 0.95rem;
        }
        .btn-logout:hover {
            background: #dc2626;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-box">
            <i class="fas fa-user-lock"></i>
        </div>
        <h1>Sin Módulos Asignados</h1>
        <p>
            Su cuenta se encuentra registrada y verificada en el sistema, pero su rol actual no tiene módulos ni paneles de trabajo configurados. Comuníquese con la dirección del plantel para que le asignen los permisos correspondientes.
        </p>

        @auth
        <div class="user-info">
            <strong>Usuario:</strong> {{ auth()->user()->email }} <br>
            <strong>Rol actual:</strong> {{ auth()->user()->rolPrincipalEfectivo() ?? 'Ninguno' }}
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </button>
        </form>
        @endauth
    </div>
</body>
</html>
