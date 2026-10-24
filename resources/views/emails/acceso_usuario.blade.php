<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credenciales de Acceso</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e1e4e8;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .header {
            background-color: #007bff;
            color: #ffffff;
            padding: 20px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
        }
        .content {
            padding: 30px;
            line-height: 1.6;
        }
        .cred-box {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .cred-box p {
            margin: 8px 0;
            font-size: 15px;
        }
        .cred-box strong {
            color: #495057;
        }
        .password {
            font-family: monospace;
            background: #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 16px;
            color: #d63384;
            font-weight: bold;
        }
        .alert {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            padding: 12px 16px;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 14px;
        }
        .footer {
            background-color: #f8f9fa;
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #e1e4e8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sistema de Inscripción Escolar</h1>
        </div>
        <div class="content">
            <p>Hola, <strong>{{ $user->name }}</strong>:</p>
            <p>Se han generado tus credenciales de acceso al sistema escolar. A continuación encontrarás los datos para ingresar:</p>
            
            <div class="cred-box">
                <p><strong>Correo electrónico:</strong> {{ $user->email }}</p>
                <p><strong>Contraseña temporal:</strong> <span class="password">{{ $passwordTemporal }}</span></p>
            </div>

            <div class="alert">
                <strong>Importante:</strong> Al ingresar al sistema por primera vez se te solicitará cambiar esta contraseña temporal por una personal y segura.
            </div>

            <p>Si no solicitaste este acceso o tienes alguna duda, por favor comunícate con la dirección de la institución educativa.</p>
        </div>
        <div class="footer">
            <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
        </div>
    </div>
</body>
</html>
