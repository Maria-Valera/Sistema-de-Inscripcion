<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Acceso Denegado</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --danger: #ef4444;
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
        .error-card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 520px;
            width: 100%;
            padding: 2.5rem;
            text-align: center;
            border-top: 6px solid var(--danger);
        }
        .icon-box {
            width: 80px;
            height: 80px;
            background: #fee2e2;
            color: var(--danger);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }
        h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #111827;
        }
        p {
            font-size: 1rem;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: #ffffff;
            background-color: var(--primary);
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-box">
            <i class="fas fa-shield-alt"></i>
        </div>
        <h1>Acceso Denegado</h1>
        <p>{{ $exception->getMessage() ?: 'No tienes los permisos requeridos para acceder a este módulo o recurso del sistema.' }}</p>
        <a href="{{ auth()->check() ? auth()->user()->rutaDashboard() : url('/') }}" class="btn">
            <i class="fas fa-arrow-left"></i> Volver a mi inicio
        </a>
    </div>
</body>
</html>
