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
    $errors = [];

    // Validar email
    if ($email === '') {
        $errors['email'] = 'El correu electrònic és obligatori';
    } else {
        if (strpos($email, '@') === false) {
            $errors['email'] = 'El correu electrònic no és vàlid (falta "@")';
        } elseif (strpos($email, ' ') !== false) {
            $errors['email'] = 'El correu electrònic no pot contenir espais';
        } elseif (strlen($email) < 5) {
            $errors['email'] = 'El correu electrònic és massa curt';
        }
    }

    // Validar contrasenya
    if ($password === '') {
        $errors['password'] = 'La contrasenya és obligatòria';
    } else {
        if (strlen($password) < 3) {
            $errors['password'] = 'La contrasenya és massa curta (mínim 3 caràcters)';
        } elseif (strlen($password) > 128) {
            $errors['password'] = 'La contrasenya és massa llarga';
        }
    }

    return empty($errors) ? null : $errors;
}

// Redirigir si ya está logueado
if (isLoggedIn()) {
    header('Location: discover.php');
    exit;
}

$email = '';
$errors = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = validateLoginForm($email, $password);

    if (!$errors) {
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
            
            // DEBUG
            error_log("Flash guardado en login.php: " . json_encode($_SESSION['flash_message']));
            error_log("Session ID: " . session_id());

            header('Location: discover.php');
            exit;
        } else {
            log_auth('LOGIN', $email, false, $result['error']);
            // Agregar errores de credenciales de forma estructurada
            $errors = $result['errors'] ?? ['general' => $result['error']];
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

        <?php if ($errors): ?>
            <div class="notification error-box">
                <div class="error-title">⚠️ Errors en l'inici de sessió:</div>
                <ul class="error-list">
                    <?php foreach ($errors as $field => $message): ?>
                        <li class="error-item<?= $field !== 'general' ? ' field-' . htmlspecialchars($field) : '' ?>">
                            <?= htmlspecialchars($message) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post">
            <label class="<?= isset($errors['email']) ? 'input-error' : '' ?>">
                Correu electrònic
                <?php if (isset($errors['email'])): ?>
                    <span class="field-error-indicator">❌</span>
                <?php endif; ?>
                <input
                    type="email" name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    placeholder="exemple@empresa.cat" maxlength="128"
                    aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>">
            </label>

            <label class="<?= isset($errors['password']) ? 'input-error' : '' ?>">
                Contrasenya
                <?php if (isset($errors['password'])): ?>
                    <span class="field-error-indicator">❌</span>
                <?php endif; ?>
                <input
                    type="password" name="password"
                    placeholder="********" maxlength="128"
                    aria-invalid="<?= isset($errors['password']) ? 'true' : 'false' ?>">
            </label>

            <button type="submit">Iniciar sessió</button>
        </form>
        <!--
        <div class="button-group">
            <button href="register.php" class="registre-btn">Registrar-se</button>
            <button href="index.php" class="back-btn">Anar al inici</button>
        </div>
            -->
    </main>
    <script src="js/utils.js"></script>
</body>
</html>