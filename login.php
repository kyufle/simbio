<?php
session_start();
require_once __DIR__ . '/includes/db.php';

function isLoggedIn()
{
    return !empty($_SESSION['user']);
}

function validateLoginForm($email, $password)
{
    if ($email === '' && $password === '') {
        return 'Debes rellenar el email y la contraseña';
    }

    if ($email === '') {
        return 'El email es obligatorio';
    }

    if ($password === '') {
        return 'La contraseña es obligatoria';
    }

    if (strpos($email, '@') === false) {
        return 'El email no es válido';
    }

    if (strpos($email, ' ') !== false) {
        return 'El email no puede contener espacios';
    }

    if (strlen($password) < 3) {
        return 'La contraseña es demasiado corta';
    }

    return null;
}

// Redirigir si ya está logueado
if (isLoggedIn()) {
    header('Location: discover.php');
    exit;
}

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $error = validateLoginForm($email, $password);

    if (!$error) {
        require_once __DIR__ . '/includes/auth.php';
        $result = login($email, $password);

        if ($result['success']) {

            // ✅ Guardar mensaje flash

            $_SESSION['flash_message'] = [
                'tipo' => 'exito',
                'titulo' => '¡Bienvenido!',
                'descripcion' => 'Has iniciado sesión correctamente'
            ];

            header('Location: discover.php');
            exit;
        } else {
            $error = $result['error'];
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
    <!-- Contenedor para los toasts -->
    <div id="contenedor-toast" class="contenedor-toast"></div>
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
                <input
                    type="email" name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    placeholder="ejemplo@empresa.com" maxlength="128">
            </label>

            <label>
                Contraseña
                <input
                    type="password" name="password"
                    placeholder="********" maxlength="128">
            </label>

            <button type="submit">Entrar</button>
        </form>
    </main>
    <script src="utils.js"></script>
</body>
</html>