<?php
require_once 'includes/mail.php';
require_once 'includes/bd_register.php';
require_once 'includes/bd_profile.php';

$mensaje = "";

/* =========================
   VALIDACIÓN POR TOKEN
========================= */
if (isset($_GET['validate'])) {
    $token = $_GET['validate'];

    $usuario = getUserByValidationToken($token);

    if (
        $usuario &&
        !$usuario['is_active'] &&
        $usuario['validation_expires'] > date('Y-m-d H:i:s')
    ) {
        activateUser((int)$usuario['user_id']);
        $mensaje = '¡Cuenta verificada correctamente! Ya puedes iniciar sesión.';
    } else {
        http_response_code(403);
        exit;
    }
}

/* =========================
   REGISTRO DE USUARIO
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Datos del formulario
    $nombre    = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $ciudad    = trim($_POST['ciudad'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $entidad   = trim($_POST['entidad'] ?? '');
    $tipo      = $_POST['tipo'] ?? '';
    $tags     = $_POST['tags'] ?? [];

    // Validaciones
    $errores = [];

    if (!$nombre)    $errores[] = "El nombre es obligatorio.";
    if (!$apellidos) $errores[] = "Los apellidos son obligatorios.";
    if (!$email)     $errores[] = "El email es obligatorio.";
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errores[] = "El email no es válido.";
    if (!$password)  $errores[] = "La contraseña es obligatoria.";
    if (strlen($password) < 8)
        $errores[] = "La contraseña debe tener al menos 8 caracteres.";
    if (!$ciudad)    $errores[] = "La ciudad es obligatoria.";
    if (!$telefono)  $errores[] = "El teléfono es obligatorio.";
    if (!$entidad)   $errores[] = "La entidad es obligatoria.";
    if (!$tipo)      $errores[] = "El tipo es obligatorio.";

    if ($errores) {
        $mensaje = implode('<br>', $errores);
    } else {

        // Comprobar email
        if (emailExists($email)) {
            $mensaje = "El email ya está registrado.";
        } else {

            // Crear usuario
            $passwordHash = hash('sha256', $password);
            $token   = hash('sha256', $email . 'simbio1');
            $expires = date('Y-m-d H:i:s', time() + 48 * 60 * 60);

            createInactiveUser(
                $email,
                $passwordHash,
                $nombre,
                $apellidos,
                $ciudad,
                $telefono,
                $entidad,
                $tipo,
                $token,
                $expires
            );

            // Asignar etiquetas
            if (!empty($tags)) {
                assignTagsToUser($email, $tags);
            }

            enviarCorreoValidacion($email, $token, $nombre);

            $mensaje = "Registro exitoso. Revisa tu correo para validar la cuenta.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Registre D'Usuari</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body class="register-page">
    <div class="register-shell">
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
