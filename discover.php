<?php
require_once 'includes/auth.php';

// Si no está logeado, redirige a login.php
if (!isLogged()) {
    header('Location: login.php');
    exit;
}

$flash = $_SESSION['flash_message'] ?? null;
if ($flash) {
    unset($_SESSION['flash_message']);
}
    
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discover</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=1" />
</head>
<body class="discover-page">

<div id="contenedor-toast" class="contenedor-toast"></div>

<nav class="sidebar">
    <ul class="nav-links">
        <li><a href="discover.php">Discover</a></li>
        <li><a href="profile.php">Perfil</a></li>
        <li><a href="messages.php">Converses</a></li>
    </ul>
    <div class="session-info">
        <?php if (isLogged()): ?>
            <span><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
            <a href="logout.php">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php">Iniciar sesión</a>
        <?php endif; ?>
    </div>
</nav>

<main id="discover-container">
    <p>Cargando proyectos...</p>
</main>

<script src="utils.js"></script>
<script src="js/discover.js"></script>
<?php if (isset($flash) && is_array($flash)): ?>
<script>
    window.addEventListener('DOMContentLoaded', function() {
        <?php
        $tipo = $flash['tipo'] ?? 'info';
        $titulo = json_encode($flash['titulo'] ?? '');
        $descripcion = json_encode($flash['descripcion'] ?? '');
        if ($tipo === 'exito') {
            echo "mostrarExito($titulo, $descripcion);";
        } else if ($tipo === 'error') {
            echo "mostrarError($titulo, $descripcion);";
        } else if ($tipo === 'warning') {
            echo "mostrarAdvertencia($titulo, $descripcion);";
        } else {
            echo "mostrarInfo($titulo, $descripcion);";
        }
        ?>
    });
</script>
<?php endif; ?>
</body>
</html>
