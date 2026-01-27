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
    <link rel="stylesheet" href="../styles.css?v=<?php echo time(); ?>">
</head>
<body class="login-page-admin">
    <main>
        <h1>Hola, <?= htmlspecialchars($_SESSION['admin_user']['name']) ?>!</h1>
        <p style="text-align:center; color:#394867; margin-bottom:2rem;">Benvingut al panell d'administració</p>
        
        <nav>
            <ul style="list-style:none; padding:0; display:flex; flex-direction:column; gap:1rem;">
                <li><a href="users.php" class="button-link">Gestió d'usuaris</a></li>
                <li><a href="menus.php" class="button-link">Gestió de menús</a></li>
                <li><a href="projectes_admin.php" class="button-link">Gestió de projectes</a></li>
                <li><a href="settings.php" class="button-link">Configuració</a></li>
                <li><a href="login.php" class="button-link">Tancar sessió</a></li>
            </ul>
        </nav>
    </main>
</body>
</html>