<?php
require_once 'auth.php';
require_once 'project_service.php';

if (!isLogged()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['user_id'];

$project_id = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
if (!$project_id) {
    http_response_code(400);
    die('Proyecto inválido');
}

// Obtener proyecto SOLO si es del usuario
$project = getProjectByIdAndUser($project_id, $user_id);

if (!$project) {
    http_response_code(403);
    die('No tienes permiso para editar este proyecto');
}

// Guardado del formulario
$save_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (updateProject($project_id, $user_id, $title, $description)) {
        $save_message = 'Proyecto actualizado correctamente';
        $project = getProjectByIdAndUser($project_id, $user_id); // recargar
    } else {
        $save_message = 'Error al actualizar el proyecto';
    }
}
?>
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