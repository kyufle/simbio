<?php
require_once 'includes/auth.php';
require_once 'includes/project_service.php';
require_once 'includes/bd_profile.php';

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

// Manejo del formulario
$save_message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $tags        = $_POST['tags'] ?? [];

    // Archivos subidos
    $image_path = $_FILES['image']['name'] ?? null;
    $video_path = $_FILES['video']['name'] ?? null;

    // Validaciones básicas
    if (!$title) {
        $errors[] = "El título es obligatorio";
    }

    // Guardar archivos
    if ($image_path) {
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_path = uniqid() . "_" . basename($image_path);
        move_uploaded_file($image_tmp, "uploads/" . $image_path);
    }

    if ($video_path) {
        $video_tmp = $_FILES['video']['tmp_name'];
        $video_path = uniqid() . "_" . basename($video_path);
        move_uploaded_file($video_tmp, "uploads/" . $video_path);
    }

    if (empty($errors)) {
        $new_project_id = createProject($user_id, $title, $description, $image_path, $video_path);

        if ($new_project_id) {
            // Asignar tags
            if (!empty($tags)) {
                updateProjectTags($new_project_id, $tags);
            }

            $save_message = "Proyecto creado correctamente";
            // Limpiar formulario
            $_POST = [];
        } else {
            $errors[] = "Error al crear el proyecto";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear nuevo proyecto</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <h1>Crear nuevo proyecto</h1>

    <?php if ($save_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($save_message); ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $err) echo "<p>" . htmlspecialchars($err) . "</p>"; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="title">Título</label>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>

        <label for="description">Descripción</label>
        <textarea name="description" id="description" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>

        <label for="image">Imagen</label>
        <input type="file" name="image" id="image" accept="image/*">

        <label for="video">Vídeo</label>
        <input type="file" name="video" id="video" accept="video/*">

        <label>Etiquetas</label>
        <div class="tags-list">
            <?php foreach ($all_tags as $tag): ?>
                <label>
                    <input type="checkbox" name="tags[]" value="<?php echo htmlspecialchars($tag); ?>" 
                        <?php echo (in_array($tag, $_POST['tags'] ?? [])) ? 'checked' : ''; ?>>
                    <?php echo htmlspecialchars($tag); ?>
                </label>
            <?php endforeach; ?>
        </div>

        <button type="submit">Crear Proyecto</button>
    </form>
    <script>
        const allAvailableTags = <?php echo json_encode(getAllAvailableTags()); ?>;
    </script>
    <script src="js/edit_project.js?v=<?php echo time(); ?>"></script>
</div>
</body>
</html>