<?php
require_once 'db.php';
require_once 'logger.php';

/**
 * Obtiene un proyecto SOLO si pertenece al usuario
 */
function getProjectByIdAndUser(int $project_id, int $user_id): ?array {
    global $conn;

    $stmt = $conn->prepare("
        SELECT 
            project_id,
            title,
            description,
            image_path,
            video_path
        FROM project
        WHERE project_id = :project_id
          AND user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        ':project_id' => $project_id,
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
 */
function updateProject(
    int $project_id,
    int $user_id,
    string $title,
    string $description
): bool {
    global $conn;

    $stmt = $conn->prepare("
        UPDATE project
        SET title = :title,
            description = :description
        WHERE project_id = :project_id
          AND user_id = :user_id
    ");

    return $stmt->execute([
        ':title'      => $title,
        ':description'=> $description,
        ':project_id' => $project_id,
        ':user_id'    => $user_id
    ]);
}