<?php
require_once 'auth.php';
require_once 'bd_profile.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLogged()) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$email = $_SESSION['user']['email'];

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

$projects = [];

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $project) {
    $projects[] = [
        'id'      => $project['project_id'],
        'title'   => $project['title'],
        'image'   => '/uploads/' . $project['image_path'],
        'video'   => $project['video_path'] ? '/uploads/' . $project['video_path'] : null,
        'deleted' => (int)$project['deleted'],
    ];
}

echo json_encode($projects);