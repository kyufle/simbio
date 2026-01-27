<?php
require_once  __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/bd.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLogged()) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        p.project_id,
        p.title,
        p.image_path,
        p.video_path,
        p.deleted
    FROM project p
    INNER JOIN user u ON u.user_id = p.user_id
");

$stmt->execute();

$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$allProjects = [];
foreach ($projects as $project) {
    $allProjects[] = [
        'id'      => $project['project_id'],
        'title'   => $project['title'],
        'image'   => '/uploads/' . $project['image_path'],
        'video'   => $project['video_path'] ? '/uploads/' . $project['video_path'] : null,
        'deleted' => (int)$project['deleted'],
    ];
}

echo json_encode($allProjects);