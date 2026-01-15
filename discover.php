<?php
session_start();
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

// DEBUG: Ver estado de sesión
error_log("SESSION: " . json_encode($_SESSION));
error_log("Flash message: " . json_encode($flash));

// Cargar proyectos desde PHP
try {
    $projects_file = __DIR__ . '/includes/projects.json';
    
    if (!file_exists($projects_file)) {
        throw new Exception('Archivo de proyectos no encontrado');
    }
    
    $projects_json = file_get_contents($projects_file);
    $projects = json_decode($projects_json, true);
    
    // Si no hay proyectos y no hay flash anterior, crear uno
    if (!$flash) {
        if (empty($projects)) {
            $_SESSION['flash_message'] = [
                'tipo' => 'warning',
                'titulo' => 'Sense més projectes',
                'descripcion' => 'No hi ha projectes disponibles en aquest moment'
            ];
            $flash = $_SESSION['flash_message'];
        }
    }
} catch (Exception $e) {
    log_error("Error cargando proyectos: " . $e->getMessage());
    if (!$flash) {
        $_SESSION['flash_message'] = [
            'tipo' => 'error',
            'titulo' => 'Error',
            'descripcion' => "No s'han pogut carregar els projectes"
        ];
        $flash = $_SESSION['flash_message'];
    }
}

    
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

<!-- DEBUG: Mostrar estado -->
<div id="debug-info" style="position: fixed; top: 10px; left: 10px; background: #222; color: #0f0; padding: 10px; font-size: 12px; font-family: monospace; z-index: 10000; max-width: 300px; border: 1px solid #0f0;">
    <div>Flash detectado: <?php echo isset($flash) ? 'SÍ' : 'NO'; ?></div>
    <?php if (isset($flash)): ?>
        <div>Tipo: <?php echo htmlspecialchars($flash['tipo']); ?></div>
        <div>Título: <?php echo htmlspecialchars($flash['titulo']); ?></div>
    <?php endif; ?>
    <div>Contenedor existe: <span id="contenedor-check">?</span></div>
    <div>Utils cargado: <span id="utils-check">?</span></div>
</div>

<script>
    // Verificar que todo esté cargado
    setTimeout(function() {
        document.getElementById('contenedor-check').textContent = document.getElementById('contenedor-toast') ? 'SÍ' : 'NO';
        document.getElementById('utils-check').textContent = typeof window.mostrarExito === 'function' ? 'SÍ' : 'NO';
        
        // Si flash existe, mostrar la notificación
        <?php if (isset($flash) && is_array($flash)): ?>
            <?php
            $tipo = $flash['tipo'] ?? 'info';
            $titulo = json_encode($flash['titulo'] ?? '');
            $descripcion = json_encode($flash['descripcion'] ?? '');

            if ($tipo === 'exito') {
                echo "window.mostrarExito($titulo, $descripcion);";
            } elseif ($tipo === 'error') {
                echo "window.mostrarError($titulo, $descripcion);";
            } elseif ($tipo === 'warning') {
                echo "window.mostrarAdvertencia($titulo, $descripcion);";
            } else {
                echo "window.mostrarInfo($titulo, $descripcion);";
            }
            ?>
        <?php endif; ?>
    }, 100);
</script>

<?php unset($_SESSION['flash_message']); ?>
</body>
</html>
