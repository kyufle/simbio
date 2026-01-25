<!-- Quiero hacer que esta pagina sea accesible cuando el usuario hace clic en el enlace de confirmación en su correo electrónico. -->
<!-- Lo que debe llevar esta pagina es un cuadrado en el medio de la pagina que diga "Tu correo ha sido confirmado exitosamente. Ya puedes iniciar sesión." junto a un botón que lleve al inicio de sesión -->
<?php
    if (isset($_GET['validate'])) {
        $token = $_GET['validate'];
        $stmt = $conn->prepare("SELECT user_id, validation_expires, is_active FROM user WHERE validation_token = ? LIMIT 1");
        $stmt->execute([$token]);
        $usuario = $stmt->fetch();
        if ($usuario && !$usuario['is_active'] && $usuario['validation_expires'] > date('Y-m-d H:i:s')) {
            // Activar usuario y eliminar token
            $stmt = $conn->prepare("UPDATE user SET is_active = 1, validation_token = NULL, validation_expires = NULL WHERE user_id = ?");
            $stmt->execute([$usuario['user_id']]);
        } else {
            // Provocar error 403 real para que Apache lo gestione
            http_response_code(403);
            exit;
        }
    }
?>
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