<?php
require_once 'includes/auth.php';
require_once 'includes/bd_profile.php';
require_once 'includes/logger.php';

// Si no està connectat, redirigeix a login.php
if (!isLogged()) {
    log_warning("Acceso denegado a discover.php - Usuario no autenticado");
    header('Location: login.php');
    exit;
}

$email = isset($_SESSION['user']['email']) ? trim($_SESSION['user']['email']) : null;

if (!$email) {
    log_error("Email vacío o no definido en sesión");
    die("Error: Email de sesión no disponible");
}

$profile = getUserProfileByEmail($email);
if (!$profile) {
    // Si no se encuentra el perfil, redirigir o mostrar un error
    log_error("Perfil de usuario no encontrado - Email: " . $email);
    die("Perfil de usuario no encontrado para el email: " . htmlspecialchars($email));
}
log_info("Usuario accedió a profile.php - Email: " . $_SESSION['user']['email']);
$tags = getUserTagsByEmail($email);
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil d'Usuari</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body class="profile-page">
    <main>
        <header class="profile-header">
            <nav class="profile-nav">
                <a href="chat.php" class="nav-link">Converses</a>
                <a href="discover.php" class="nav-link">Descobrir</a>
            </nav>
            <div class="session-info">
                <h1>Perfil <?php echo htmlspecialchars($profile['name']); ?></h1>
                <?php if (isLogged()): ?>
                <span><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                    <a href="logout.php">Tancar sessió</a>
                <?php else: ?>
                    <a href="login.php">Iniciar sessió</a>
                <?php endif; ?>
            </div>
        </header>
        <section class="user-info">
            <!-- <img src="<?php echo htmlspecialchars($profile['image']); ?>" alt="Imatge de perfil" class="profile-image"> -->
            <h2><?php echo htmlspecialchars($profile['name'] . ' ' . $profile['surnames']); ?></h2>
            <p><strong>Entitat:</strong> <?php echo htmlspecialchars($profile['entity']); ?></p>
            <p><strong>Població:</strong> <?php echo htmlspecialchars($profile['city']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($profile['email']); ?></p>
            <p><strong>Telèfon:</strong> <?php echo htmlspecialchars($profile['phone_number']); ?></p>
        </section>
        <!-- Etiquetes (families professionals i cicles) -->
        <section class="user-tags">
            <h2>Etiquetes</h2>
            <div class="tags-list">
                <?php
                $tags = getUserTagsByEmail($email);
                foreach ($tags as $tag): ?>
                    <div class="tag-item">
                        <span><?php echo htmlspecialchars($tag); ?></span>
                        <button class="remove-tag-btn" data-tag="<?php echo htmlspecialchars($tag); ?>">X</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button id="add-tag-btn" class="btn btn-secondary">+ Afegir</button>
        </section>
        <!-- Llista de projectes propis -->
        <section class="user-projects">
            <h2>Els meus projectes</h2>
            <a href="new_project.php" class="btn btn-primary">+ Nou projecte</a>
            <div id="user-projects">
                <script>
                    const userEmail = <?php echo json_encode($email); ?>;
                </script>
                <script src="js/profile.js?v=<?php echo time(); ?>"></script>
            </div>
        </section>
    </main>
</body>
</html>