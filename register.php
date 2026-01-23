<?php
require_once 'includes/mail.php';
require_once 'includes/db.php'; // Incluye tu conexión a la base de datos aquí
require_once 'includes/bd_profile.php';
function assignTagsToUserByEmail(string $email, array $tags): bool
{
    global $conn;

    if (empty($tags)) {
        return true;
    }

    try {
        // Obtener user_id
        $stmtUser = $conn->prepare("SELECT user_id FROM user WHERE email = ? LIMIT 1");
        $stmtUser->execute([$email]);
        $user_id = $stmtUser->fetchColumn();

        if (!$user_id) {
            throw new Exception("Usuario no encontrado para el email: $email");
        }

        // Preparar consultas
        $stmtTag = $conn->prepare("SELECT tag_id FROM tag WHERE name = ?");
        $stmtInsert = $conn->prepare(
            "INSERT INTO user_tags (user_id, tag_id)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE tag_id = tag_id"
        );

        foreach ($tags as $tag_name) {
            $stmtTag->execute([$tag_name]);
            $tag_id = $stmtTag->fetchColumn();

            if ($tag_id) {
                $stmtInsert->execute([$user_id, $tag_id]);
            }
        }

        return true;

    } catch (Throwable $e) {
        error_log('assignTagsToUserByEmail ERROR: ' . $e->getMessage());
        return false;
    }
}

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
            $expires = date('Y-m-d H:i:s', time() + 48 * 60 * 60); // 48 horas
            $stmt = $conn->prepare("INSERT INTO user (email, password_hash, name, surnames, city, phone_number, entity, type, image_path, is_active, validation_token, validation_expires) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, 0, ?, ?)");
            $stmt->execute([$email, $password_hash, $nombre, $apellidos, $ciudad, $telefono, $entidad, $tipo, $token, $expires]);
            // Enviar email de validación
            enviarCorreoValidacion($email, $token, $nombre);

            // Asignar etiquetas al usuario usando su email
            $tags = $_POST['tags'] ?? [];
            assignTagsToUserByEmail($email, $tags);

            
            $mensaje = '<div class="success">Registro exitoso. Revisa tu correo para validar la cuenta.</div>';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registre d'usuari</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body class="register-page">
    <div class="register-shell">
        <form class="register-form" method="POST" autocomplete="off">
            <h1>Registre d'usuari</h1>
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
                    <label for="nombre">Nom</label>
                    <input type="text" name="nombre" id="nombre" required>
                </div>
                <div class="form-group">
                    <label for="apellidos">Cognoms</label>
                    <input type="text" name="apellidos" id="apellidos" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Contrasenya</label>
                    <input type="password" name="password" id="password" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="ciudad">Ciutat</label>
                    <input type="text" name="ciudad" id="ciudad" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Telèfon</label>
                    <input type="text" name="telefono" id="telefono" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="entidad">Entitat</label>
                    <input type="text" name="entidad" id="entidad" required>
                </div>
                <div class="form-group">
                    <label for="tipo">Tipus</label>
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
            <h3>Etiquetes</h3>
            <div class="user-tags-section">
                <div class="tags-list" id="tags-list">
                    <?php foreach ($tags as $tag): ?>
                        <div class="tag-item">
                            <span><?php echo htmlspecialchars($tag); ?></span>
                            <button type="button" class="remove-tag-btn" data-tag="<?php echo htmlspecialchars($tag); ?>">×</button>
                            <input type="hidden" name="tags[]" value="<?php echo htmlspecialchars($tag); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <br>
                <div class="add-tag-container">
                    <div class="tag-search-wrapper">
                        <input type="text" id="tag-search" class="tag-search-input" placeholder="Escriu una etiqueta...">
                        <div class="tag-suggestions" id="tag-suggestions"></div>
                    </div>
                    <button type="button" id="add-tag-btn" class="btn btn-secondary">+ Afegir</button>
                </div>
            </div>
            <button type="submit">Registrarse</button>
        </form>
    </div>
    <script>
        const allAvailableTags = <?php echo json_encode(getAllAvailableTags()); ?>;
    </script>
    <script src="js/etiquetas.js?v=<?php echo time(); ?>"></script>
</body>
</html>
