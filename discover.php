<?php
require_once 'includes/auth.php';
session_start();

// Si no está logeado, redirige a login.php
if (!isLogged()) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Discover</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="discover-page">

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

<main id="discover-container">
    <p>Cargando proyectos...</p>
</main>

<script src="js/discover.js"></script>
</body>
</html>
