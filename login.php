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
$errors = []; // Array de errores para mostrar individualmente

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = validateLoginForm($email, $password);

    if (!$errors) {
        require_once __DIR__ . '/includes/auth.php';
        $result = login($email, $password);

        if ($result['success']) {
            log_auth('LOGIN', $email, true);

            // ✅ Guardar mensaje flash de éxito
            $_SESSION['flash_message'] = [
                'tipo' => 'exito',
                'titulo' => 'Benvingut!',
                'descripcion' => 'Has iniciat sessió correctament'
            ];

            header('Location: discover.php');
            exit;
        } else {
            log_auth('LOGIN', $email, false, $result['error']);
            
            // Error de autenticación (credenciales incorrectas)
            if (is_array($result['errors'])) {
                $errors = $result['errors'];
            } else {
                $errors['auth'] = $result['error'];
            }
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
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body class="login-page">
    <!-- Contenedor para los toasts -->
    <div id="contenedor-toast" class="contenedor-toast"></div>
    
    <main>
        <h1>Iniciar sessió</h1>

        <!-- ⭐ Mostrar cada error como una notificación separada -->
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $field => $error_message): ?>
                <div class="notification error">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- ⭐ IMPORTANTE: novalidate desactiva la validación HTML5 -->
        <form method="post" novalidate>
            <label>
                Correu electrònic
                <input
                    type="email" 
                    name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    placeholder="exemple@empresa.cat" 
                    maxlength="128"
                    autocomplete="email">
            </label>

            <label>
                Contrasenya
                <input
                    type="password" 
                    name="password"
                    placeholder="********" 
                    maxlength="128"
                    autocomplete="current-password">
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
    
    <script src="js/utils.js?v=<?php echo time(); ?>"></script>
</body>
</html>