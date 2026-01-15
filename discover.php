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
    <div id="script-info">Script: pendiente...</div>
</div>

<?php if (isset($flash) && is_array($flash)): ?>
<script>
    console.log('=== SCRIPT DE FLASH EJECUTÁNDOSE ===');
    console.log('Flash data:', <?php echo json_encode($flash); ?>);
    
    // Mostrar que se va a ejecutar
    document.getElementById('script-info').textContent = 'Script GENERADO';
    
    setTimeout(function() {
        console.log('Timeout ejecutándose...');
        document.getElementById('script-info').textContent = 'Timeout ejecutado';
        
        console.log('mostrarExito existe:', typeof window.mostrarExito);
        console.log('mostrarError existe:', typeof window.mostrarError);
        console.log('mostrarAdvertencia existe:', typeof window.mostrarAdvertencia);
        
        <?php
        $tipo = $flash['tipo'] ?? 'info';
        $titulo = json_encode($flash['titulo'] ?? '');
        $descripcion = json_encode($flash['descripcion'] ?? '');
        
        echo "console.log('Tipo: $tipo, Titulo: $titulo, Descripcion: $descripcion');\n";

        if ($tipo === 'exito') {
            echo "console.log('Llamando mostrarExito...');\n";
            echo "window.mostrarExito($titulo, $descripcion);\n";
        } elseif ($tipo === 'error') {
            echo "console.log('Llamando mostrarError...');\n";
            echo "window.mostrarError($titulo, $descripcion);\n";
        } elseif ($tipo === 'warning') {
            echo "console.log('Llamando mostrarAdvertencia...');\n";
            echo "window.mostrarAdvertencia($titulo, $descripcion);\n";
        } else {
            echo "console.log('Llamando mostrarInfo...');\n";
            echo "window.mostrarInfo($titulo, $descripcion);\n";
        }
        ?>
    }, 100);
</script>
<?php endif; ?>

<script>
    // Verificar que todo esté cargado
    setTimeout(function() {
        document.getElementById('contenedor-check').textContent = document.getElementById('contenedor-toast') ? 'SÍ' : 'NO';
        document.getElementById('utils-check').textContent = typeof window.mostrarExito === 'function' ? 'SÍ' : 'NO';
    }, 50);
</script>

<?php unset($_SESSION['flash_message']); ?>
</body>
</html>
