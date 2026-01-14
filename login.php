<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/logger.php';

function isLoggedIn()
{
    return !empty($_SESSION['user']);
}

function validateLoginForm($email, $password)
{
    if ($email === '' && $password === '') {
        return 'Has d\'emplenar el correu electrònic i la contrasenya';
    }

    if ($email === '') {
        return 'El correu electrònic és obligatori';
    }

    if ($password === '') {
        return 'La contrasenya és obligatòria';
    }

    if (strpos($email, '@') === false) {
        return 'El correu electrònic no és vàlid';
    }

    if (strpos($email, ' ') !== false) {
        return 'El correu electrònic no pot contenir espais';
    }

    if (strlen($password) < 3) {
        return 'La contrasenya és massa curta';
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
            log_auth('LOGIN', $email, true);

            // ✅ Guardar mensaje flash
            $_SESSION['flash_message'] = [
                'tipo' => 'exito',
                'titulo' => 'Benvingut!',
                'descripcion' => 'Has iniciat sessió correctament'
            ];

            header('Location: discover.php');
            exit;
        } else {
            log_auth('LOGIN', $email, false, $result['error']);
            $error = $result['error'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inici de sessió</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login-page">
    <!-- Contenedor para los toasts -->
    <div id="contenedor-toast" class="contenedor-toast"></div>
    <main>
        <h1>Iniciar sessió</h1>

        <?php if ($error): ?>
            <div class="notification error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <label>
                Correu electrònic
                <input
                    type="email" name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    placeholder="exemple@empresa.cat" maxlength="128">
            </label>

            <label>
                Contrasenya
                <input
                    type="password" name="password"
                    placeholder="********" maxlength="128">
            </label>

            <button type="submit">Iniciar sessió</button>
        </form>
    </main>
    <script src="utils.js"></script>
</body>
</html>