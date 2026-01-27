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
                <li><a href="orders.php" class="button-link">Gestió de comandes</a></li>
                <li><a href="settings.php" class="button-link">Configuració</a></li>
                <li><a href="login.php" class="button-link">Tancar sessió</a></li>
            </ul>
        </nav>
    </main>

    <style>
        /* Botones estilo login */
        .button-link {
            display: block;
            text-align: center;
            padding: 0.9rem;
            background: linear-gradient(135deg, #FFD966 40%, #FFB200 100%);
            color: #212A3E;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            box-shadow: 0 6px 28px 0 rgba(255, 188, 51, 0.15), 0 1.5px 4px rgba(33,42,62,0.09);
            transition: all 0.3s ease;
            font-family: 'Josefin Sans', sans-serif;
        }

        .button-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px 0 rgba(255, 178, 0, 0.18), 0 2px 8px rgba(33,42,62,0.12);
            background: linear-gradient(135deg, #FFDF77 40%, #FFD966 100%);
            color: #1a2233;
        }
    </style>
</body>
</html>