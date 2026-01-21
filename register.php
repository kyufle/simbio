
<?php
require_once 'includes/mail.php';
require_once 'includes/db.php'; // Incluye tu conexión a la base de datos aquí

$mensaje = "";

if (isset($_GET['validate'])) {
    $token = $_GET['validate'];
    $stmt = $conn->prepare("SELECT user_id, validation_expires, is_active FROM user WHERE validation_token = ? LIMIT 1");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch();
    if ($usuario && !$usuario['is_active'] && $usuario['validation_expires'] > date('Y-m-d H:i:s')) {
        // Activar usuario y eliminar token
        $stmt = $conn->prepare("UPDATE user SET is_active = 1, validation_token = NULL, validation_expires = NULL WHERE user_id = ?");
        $stmt->execute([$usuario['user_id']]);
        $mensaje = '<div class="success">¡Cuenta verificada correctamente! Ya puedes iniciar sesión.</div>';
    } else {
        // Provocar error 403 real para que Apache lo gestione
        http_response_code(403);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $ciudad = trim($_POST['ciudad'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $entidad = trim($_POST['entidad'] ?? '');
    $tipo = $_POST['tipo'] ?? '';
    // $imagen = $_FILES['imagen'] ?? null; // Si quieres añadir imagen

    $errores = array();
    if (!$nombre) $errores[] = "El nombre es obligatorio.";
    if (!$apellidos) $errores[] = "Los apellidos son obligatorios.";
    if (!$email) $errores[] = "El email es obligatorio.";
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = "El email no es válido.";
    if (!$password) $errores[] = "La contraseña es obligatoria.";
    if ($password && strlen($password) < 8) $errores[] = "La contraseña debe tener al menos 8 caracteres.";
    if (!$ciudad) $errores[] = "La ciudad es obligatoria.";
    if (!$telefono) $errores[] = "El teléfono es obligatorio.";
    if (!$entidad) $errores[] = "La entidad es obligatoria.";
    if (!$tipo) $errores[] = "El tipo es obligatorio.";

    if (count($errores) > 0) {
        $mensaje = '<div class="error"><ul><li>' . implode('</li><li>', $errores) . '</li></ul></div>';
    } else {
        // Comprobar si el email ya existe
        $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $mensaje = '<div class="error">El email ya está registrado.</div>';
        } else {
            // Crear usuario inactivo
            $password_hash = hash('sha256', $password);
            $token = hash('sha256', $email . 'simbio1');
            $expires = date('Y-m-d H:i:s', time() + 1800); // 30 minutos
            $stmt = $conn->prepare("INSERT INTO user (email, password_hash, name, surnames, city, phone_number, entity, type, image_path, is_active, validation_token, validation_expires) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, 0, ?, ?)");
            $stmt->execute([$email, $password_hash, $nombre, $apellidos, $ciudad, $telefono, $entidad, $tipo, $token, $expires]);
            // Enviar email de validación
            enviarCorreoValidacion($email, $token, $nombre);
            $mensaje = '<div class="success">Registro exitoso. Revisa tu correo para validar la cuenta.</div>';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de usuario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="register-page">
        <form class="register-form" method="POST" autocomplete="off">
            <h1>Registro de usuario</h1>
            <?php
            if ($mensaje) {
                if (strpos($mensaje, 'success') !== false) {
                    echo '<div class="success">' . strip_tags($mensaje) . '</div>';
                } elseif (strpos($mensaje, 'error') !== false) {
                    echo '<div class="error">' . strip_tags($mensaje) . '</div>';
                } else {
                    echo '<div class="info">' . strip_tags($mensaje) . '</div>';
                }
            }
            ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre" required>
                </div>
                <div class="form-group">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" name="password" id="password" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="ciudad">Ciudad</label>
                    <input type="text" name="ciudad" id="ciudad" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="entidad">Entidad</label>
                    <input type="text" name="entidad" id="entidad" required>
                </div>
                <div class="form-group">
                    <label for="tipo">Tipo</label>
                    <select name="tipo" id="tipo" required>
                        <option value="">Selecciona...</option>
                        <option value="Empresa">Empresa</option>
                        <option value="Centre">Centre</option>
                    </select>
                </div>
            </div>
            <!-- Si quieres añadir imagen, descomenta esto
            <div class="form-group">
                <label for="imagen">Imagen (opcional)</label>
                <input type="file" name="imagen" id="imagen" accept="image/*">
            </div>
            -->
            <button type="submit">Registrarse</button>
        </form>
    </div>
</body>
</html>
