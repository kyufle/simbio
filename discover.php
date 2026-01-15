<?php
require_once 'includes/auth.php';
require_once 'includes/logger.php';

// Si no està connectat, redirigeix a login.php
if (!isLogged()) {
    log_warning("Acceso denegado a discover.php - Usuario no autenticado");
    header('Location: login.php');
    exit;
}

log_info("Usuario accedió a discover.php");
$flash = $_SESSION['flash_message'] ?? null;

    
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Descobrir</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=1" />
</head>
<body class="discover-page">

<div id="contenedor-toast" class="contenedor-toast"></div>

<nav class="sidebar">
    <ul class="nav-links">
        <li><a href="discover.php">Descobrir</a></li>
        <li><a href="profile.php">Perfil</a></li>
        <li><a href="messages.php">Converses</a></li>
    </ul>
    <div class="session-info">
        <?php if (isLogged()): ?>
            <span><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
            <a href="logout.php">Tancar sessió</a>
        <?php else: ?>
            <a href="login.php">Iniciar sessió</a>
        <?php endif; ?>
    </div>
</nav>

<main id="discover-container">
    <p>Carregant projectes...</p>
</main>

<script src="js/utils.js"></script>
<script src="js/discover.js?v=<?php echo filemtime('js/discover.js'); ?>"></script>
<?php if (isset($flash) && is_array($flash)): ?>
<script>
    window.addEventListener('DOMContentLoaded', function() {
        <?php
        $tipo = $flash['tipo'] ?? 'info';
        $titulo = json_encode($flash['titulo'] ?? '');
        $descripcion = json_encode($flash['descripcion'] ?? '');

        if ($tipo === 'exito') {
            echo "mostrarExito($titulo, $descripcion);";
        } elseif ($tipo === 'error') {
            echo "mostrarError($titulo, $descripcion);";
        } elseif ($tipo === 'warning') {
            echo "mostrarAdvertencia($titulo, $descripcion);";
        } else {
            echo "mostrarInfo($titulo, $descripcion);";
        }
        ?>
    });
</script>
<?php unset($_SESSION['flash_message']); endif; ?>
</body>
</html>
