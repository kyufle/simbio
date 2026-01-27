<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/logger.php';
require_once __DIR__ . '/../includes/auth.php';

// Comprobar que el admin está logueado
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Panell d'Administrador</title>
    <link rel="stylesheet" href="styles.css?v=<?= time() ?>">
</head>
<body>
    <main>
        <h1>Hola, <?= htmlspecialchars($_SESSION['admin_user']['name']) ?>, estàs en admin/index.php</h1>
        <!-- Aquí iría tu panel de admin -->
        <nav>
            <ul>
                <li><a href="login.php">Tancar sessió</a></li>
            </ul>
        </nav>
    </main>
</body>
</html>
