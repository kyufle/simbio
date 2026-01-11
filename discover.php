<?php
require_once 'includes/auth.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Discover</title>
</head>
<body>

<h1>Discover</h1>

<?php if (isLogged()): ?>
    <p>Bienvenido, <?= htmlspecialchars($_SESSION['user']['name']) ?></p>
    <a href="logout.php">Cerrar sesión</a>
<?php else: ?>
    <a href="login.php">Iniciar sesión</a>
<?php endif; ?>

<hr>

<p>Aquí irán los proyectos…</p>

</body>
</html>
