<!-- Quiero hacer que esta pagina sea accesible cuando el usuario hace clic en el enlace de confirmación en su correo electrónico. -->
<!-- Lo que debe llevar esta pagina es un cuadrado en el medio de la pagina que diga "Tu correo ha sido confirmado exitosamente. Ya puedes iniciar sesión." junto a un botón que lleve al inicio de sesión -->
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmació de Correu Electrònic</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Josefin Sans', sans-serif;
            background-color: linear-gradient(135deg, #A3D2CA 0%, #B5EAEA 100%);
        }
        .confirmation-box {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .confirmation-box h1 {
            margin-bottom: 20px;
            color: #333;
        }
        .confirmation-box p {
            margin-bottom: 30px;
            color: #666;
        }
        .confirmation-box button {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #b7a7ff, #baa7ff);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(205, 167, 255, 0.4);
        }
        .confirmation-box button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(205, 167, 255, 0.6);
        }
    </style>
</head>
<body>
    <div class="confirmation-box">
        <h1>Correu Confirmat Exitosament</h1>
        <p>El teu correu ha estat confirmat exitosament. Ja pots iniciar sessió.</p>
        <button><a href="login.php">Iniciar Sessió</a></button>
    </div>
</body>
</html>