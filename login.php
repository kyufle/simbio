<?php
require_once 'includes/auth.php';


// Redirecciona si ya está logueado
if (isLogged()) {
    header('Location: discover.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email === '' && $password === '') {
        $error = 'Debes rellenar el email y la contraseña';
    } elseif ($email === '') {
        $error = 'El email es obligatorio';
    } elseif ($password === '') {
        $error = 'La contraseña es obligatoria';
    } elseif (!strpos($email, '@')) {
        $error = 'El email no es válido';
    } elseif (strpos($email, ' ') !== false) {
        $error = 'El email no puede contener espacios';
    } elseif (strlen($password) < 3) {
        $error = 'La contraseña es demasiado corta';
    } else {
        $result = login($email, $password);
        if ($result && !empty($result['success'])) {
            header('Location: discover.php');
            exit;
        } else {
            $error = !empty($result['error']) ? $result['error'] : 'Credenciales incorrectas';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="login-page">

    <main>
        <h1>Iniciar Sesión</h1>

        <?php if ($error): ?>
            <div class="notification error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <label>
                Email
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="ejemplo@empresa.com" maxlength="128">
            </label>

            <label>
                Contraseña
                <input type="password" name="password" placeholder="********" maxlength="128">
            </label>

            <button type="submit">Entrar</button>
        </form>
        <div class="button-group">
            <button class="registre-btn" onclick="window.location.href='register.php'">Registrarse</button>
            <button class="back-btn" onclick="window.location.href='index.php'">Ir al inicio</button>
        </div>
    </main>
</body>

</html>