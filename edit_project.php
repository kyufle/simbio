<?php
require_once 'includes/auth.php';
require_once 'includes/bd_profile.php';
require_once 'includes/project_service.php';

if (!isLogged()) {
    header('Location: login.php');
    exit;
}

$email = $_SESSION['user']['email'];

$profile = getUserProfileByEmail($email);
if (!$profile) {
    http_response_code(403);
    exit('Usuario no válido');
}

$user_id = $profile['user_id'];

$project_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$project_id) {
    http_response_code(400);
    exit('Proyecto inválido');
}

// 🔒 Seguridad: solo proyectos del usuario
$project = getProjectByIdAndUser($project_id, $user_id);

if (!$project) {
    http_response_code(403);
    exit('No tienes permiso para editar este proyecto');
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
<body>
    <h2>Editar proyecto</h2>
    <?php if ($save_message): ?>
        <div class="save-message success">
            <?= htmlspecialchars($save_message) ?>
        </div>
    <?php endif; ?>
    <form method="POST" class="profile-form">
        <div class="form-group">
            <label>Título</label>
            <input type="text" name="title" value="<?= htmlspecialchars($project['title']) ?>" required>
        </div>

        <div class="form-group">
            <label>Descripción</label>
            <textarea name="description"><?= htmlspecialchars($project['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Entidad</label>
            <input type="text" name="entity" value="<?= htmlspecialchars($project['entity']) ?>">
        </div>

        <div class="form-group">
            <label>Tipo</label>
            <input type="text" name="type" value="<?= htmlspecialchars($project['type']) ?>">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="profile.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</body>
</html>