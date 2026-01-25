<!-- Quiero hacer que esta pagina sea accesible cuando el usuario hace clic en el enlace de confirmación en su correo electrónico. -->
<!-- Lo que debe llevar esta pagina es un cuadrado en el medio de la pagina que diga "Tu correo ha sido confirmado exitosamente. Ya puedes iniciar sesión." junto a un botón que lleve al inicio de sesión -->
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmació de Correu Electrònic</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body class="confirmation-page">
    <div class="confirmation-box">
        <h1>Correu Confirmat Exitosament</h1>
        <p>El teu correu ha estat confirmat exitosament. Ja pots iniciar sessió.</p>
        <button><a href="login.php">Iniciar Sessió</a></button>
    </div>
</body>
</html>