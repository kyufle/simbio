<?php
require_once 'auth.php';
header('Content-Type: application/json; charset=utf-8');

// 1. Seguridad: Solo usuarios logueados
if (!isLogged()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

global $conn;

try {
    $userId = $_SESSION['user']['id'];

    // 2. Obtener los IDs de proyectos que ya le gustan al usuario (para el botón like)
    $stmtUserLikes = $conn->prepare("SELECT project_id FROM project_like WHERE user_id = :user_id");
    $stmtUserLikes->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmtUserLikes->execute();
    $userLikes = $stmtUserLikes->fetchAll(PDO::FETCH_COLUMN);

    // =================================================================================
    // 3. LA GRAN CONSULTA (EL ALGORITMO)
    // =================================================================================
    // Explicación de la lógica SQL:
    // A. Seleccionamos la tabla de proyectos (p).
    // B. Hacemos un LEFT JOIN con una subconsulta que obtiene SOLO los tags de TUS proyectos.
    // C. Si hay coincidencia, 'match_count' sumará 1, si no, será 0.
    // D. Ordenamos por 'match_count' descendente para que los intereses salgan primero.
    $sql = "
    SELECT 
        p.project_id,
        p.title,
        p.description,
        p.image_path,
        p.video_path,
        u.entity,
        u.type,
        COUNT(mt.tag_id) AS match_score,
        CASE WHEN COUNT(mt.tag_id) > 0 THEN 1 ELSE 0 END AS possible_match
    FROM project p
    JOIN user u ON p.user_id = u.user_id
    JOIN project_tags pt ON pt.project_id = p.project_id
    LEFT JOIN (
        SELECT DISTINCT pt_user.tag_id
        FROM project_tags pt_user
        JOIN project p_user ON pt_user.project_id = p_user.project_id
        WHERE p_user.user_id = :current_user_subquery
    ) mt ON pt.tag_id = mt.tag_id
    WHERE p.user_id != :current_user_main
    GROUP BY p.project_id
    ORDER BY match_score DESC, p.project_id DESC;
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':current_user_subquery', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':current_user_main', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Formatear datos para el Frontend
    $allProjects = [];

    foreach ($projects as $project) {
        // Obtener nombres de las etiquetas para mostrarlas
        $stmtTags = $conn->prepare("
            SELECT t.name 
            FROM project_tags pt 
            JOIN tag t ON pt.tag_id = t.tag_id 
            WHERE pt.project_id = :pid
        ");
        $stmtTags->bindParam(':pid', $project['project_id'], PDO::PARAM_INT);
        $stmtTags->execute();
        $projectTags = $stmtTags->fetchAll(PDO::FETCH_COLUMN);

        $isLiked = in_array($project['project_id'], $userLikes);

        $allProjects[] = [
            'id' => $project['project_id'],
            'title' => $project['title'],
            'description' => $project['description'],
            'image' => "/uploads/" . $project['image_path'],
            'video' => $project['video_path'] ? "/uploads/" . $project['video_path'] : null,
            'entity' => $project['entity'],
            'type' => $project['type'],
            'tags' => $projectTags,
            'match' => ($project['possible_match'] == 1), // 💖 esto activa el icono
            'liked' => $isLiked,
            'user_id' => $project['user_id'] // Añadido para enlaces de chat
        ];
    }

    echo json_encode($allProjects);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error DB: ' . $e->getMessage(),
        'sql' => $sql // agrega esto para ver si hay errores en la query
    ]);
    exit;
}

