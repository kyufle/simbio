<?php
require_once 'includes/bd_profile.php';
require_once 'includes/logger.php';

// Si no està connectat, redirigeix a login.php
if (!isLogged()) {
    log_warning("Acceso denegado a discover.php - Usuario no autenticado");
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['id'];
$profile = getUserProfile($userId);
if (!$profile) {
    die("Error al carregar el perfil d'usuari.");
}
log_info("Usuario accedió a profile.php - ID: $userId");
function getUserTags($userId) {
    global $db;
    $stmt = $db->prepare("SELECT tag FROM user_tags WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
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
        <h1>Perfil d'Usuari</h1>
        <section class="user-info">
            <img src="<?php echo htmlspecialchars($profile['image']); ?>" alt="Imatge de perfil" class="profile-image">
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
                $tags = getUserTags($userId);
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
            <div class="projects-list">
                <script src="js/profile.js">
                    const userId = <?php echo json_encode($userId); ?>;
                    displayUserProjects(userId);
                </script>
            </div>
        </section>
        <nav class="profile-nav">
            <a href="chat.php" class="nav-link">Converses</a>
            <a href="discover.php" class="nav-link">Descobrir</a>
        </nav>
    </main>
</body>
</html>