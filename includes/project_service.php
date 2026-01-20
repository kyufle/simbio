<?php
require_once 'db.php';
require_once 'logger.php';

/**
 * Obtiene un proyecto SOLO si pertenece al usuario
 */
function getProjectByIdAndUser(int $project_id, int $user_id): ?array {
    global $conn;

    $stmt = $conn->prepare("
        SELECT *
        FROM project
        WHERE project_id = :id
          AND user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $project_id,
        ':user_id'    => $user_id
    ]);

    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        log_warning("Acceso no autorizado al proyecto {$project_id} por user {$user_id}");
        return null;
    }

    return $project;
}

/**
 * Actualiza un proyecto del usuario
 * Solo actualiza image_path/video_path si se pasan nuevos valores
 */
function updateProject(
    int $project_id,
    int $user_id,
    string $title,
    string $description,
    ?string $image_path = null,
    ?string $video_path = null
): bool {
    global $conn;

    // Campos a actualizar
    $fields = [
        'title = :title',
        'description = :description'
    ];

    if ($image_path !== null) {
        $fields[] = 'image_path = :image_path';
    }

    if ($video_path !== null) {
        $fields[] = 'video_path = :video_path';
    }

    $sql = "
        UPDATE project
        SET " . implode(', ', $fields) . "
        WHERE project_id = :project_id
          AND user_id = :user_id
    ";

    $stmt = $conn->prepare($sql);

    // Bind obligatorios
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    // Bind opcionales
    if ($image_path !== null) {
        $stmt->bindParam(':image_path', $image_path);
    }
    if ($video_path !== null) {
        $stmt->bindParam(':video_path', $video_path);
    }

    return $stmt->execute();
}