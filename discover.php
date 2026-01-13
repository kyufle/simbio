<?php
require_once 'includes/auth.php';
session_start();

// Si no está logeado, redirige a login.php
/*
if (!isLogged()) {
    header('Location: login.php');
    exit;
}
    */
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
<nav class="sidebar">
    <!-- Links a otras páginas a la izquierda -->
    <ul class="nav-links">
        <li><a href="discover.php">Discover</a></li>
        <li><a href="profile.php">Perfil</a></li>
        <li><a href="messages.php">Converses</a></li>
    </ul>

    <!-- Info de sesión a la derecha -->
    <div class="session-info">
        <?php if (isLogged()): ?>
            <span><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
            <a href="logout.php">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php">Iniciar sesión</a>
        <?php endif; ?>
    </div>
</nav>

<!--
<header>
    <h1>Discover</h1>
    <?php if (isLogged()): ?>
        <p>Bienvenido, <?= htmlspecialchars($_SESSION['user']['name']) ?></p>
        <a href="logout.php">Cerrar sesión</a>
    <?php else: ?>
        <a href="login.php">Iniciar sesión</a>
    <?php endif; ?>
    <hr>
</header>
    -->
<main id="discover-container">
    <p>Cargando proyectos...</p>
</main>

<script src="js/discover.js"></script>
</body>
</html>
