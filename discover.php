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
<?php if (isset($flash) && is_array($flash)): ?>
<script>
    // Debug: verificar que se ejecuta
    console.log('Flash message encontrado:', <?php echo json_encode($flash); ?>);
    
    // Esperar a que utils.js esté completamente cargado
    setTimeout(function() {
        <?php
        $tipo = $flash['tipo'] ?? 'info';
        $titulo = json_encode($flash['titulo'] ?? '');
        $descripcion = json_encode($flash['descripcion'] ?? '');

        echo "console.log('Tipo:', '$tipo', 'Titulo:', $titulo, 'Descripcion:', $descripcion);";

        if ($tipo === 'exito') {
            echo "if (typeof window.mostrarExito === 'function') { window.mostrarExito($titulo, $descripcion); } else { console.error('mostrarExito no está disponible'); }";
        } elseif ($tipo === 'error') {
            echo "if (typeof window.mostrarError === 'function') { window.mostrarError($titulo, $descripcion); } else { console.error('mostrarError no está disponible'); }";
        } elseif ($tipo === 'warning') {
            echo "if (typeof window.mostrarAdvertencia === 'function') { window.mostrarAdvertencia($titulo, $descripcion); } else { console.error('mostrarAdvertencia no está disponible'); }";
        } else {
            echo "if (typeof window.mostrarInfo === 'function') { window.mostrarInfo($titulo, $descripcion); } else { console.error('mostrarInfo no está disponible'); }";
        }
        ?>
    }, 100);
</script>
<?php unset($_SESSION['flash_message']); endif; ?>
</body>
</html>
