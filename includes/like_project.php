<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/mail_match.php';
require_once __DIR__ . '/bd_profile.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLogged()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$userId = $_SESSION['user']['id'];
$projectId = $_POST['project_id'] ?? null;

if (!$projectId || !is_numeric($projectId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de proyecto inválido']);
    exit;
}

// 1. Insertar el like si no existe
$stmt = $conn->prepare("SELECT COUNT(*) FROM project_like WHERE user_id = ? AND project_id = ?");
$stmt->execute([$userId, $projectId]);
if ($stmt->fetchColumn() == 0) {
    $stmt = $conn->prepare("INSERT INTO project_like (user_id, project_id) VALUES (?, ?)");
    $stmt->execute([$userId, $projectId]);
}

// 2. Detectar si hay match (el propietario del proyecto también ha dado like a un proyecto del usuario actual)
$stmt = $conn->prepare("SELECT user_id FROM project WHERE project_id = ? LIMIT 1");
$stmt->execute([$projectId]);
$ownerId = $stmt->fetchColumn();

if (!$ownerId) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Proyecto no encontrado']);
    exit;
}


// Obtener tags del usuario desde su perfil (user_tags)
$stmt = $conn->prepare("SELECT tag_id FROM user_tags WHERE user_id = ?");
$stmt->execute([$userId]);
$userTags = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Obtener tags del proyecto actual
$stmt = $conn->prepare("SELECT tag_id FROM project_tags WHERE project_id = ?");
$stmt->execute([$projectId]);
$projectTags = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Verificar coincidencia de etiquetas
$match = count(array_intersect($userTags, $projectTags)) > 0;

if ($match) {
    // Obtener datos de ambos usuarios y del proyecto para el email
    $stmt = $conn->prepare("SELECT email, name FROM user WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->execute([$ownerId]);
    $owner = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $conn->prepare("SELECT title, description, image_path FROM project WHERE project_id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    $img = $project['image_path'] ? ("/uploads/" . $project['image_path']) : '';
    enviarCorreoMatch($user['email'], $user['name'], $owner['email'], $owner['name'], $project['title'], $project['description'], $img);
}

echo json_encode(['success' => true, 'match' => $match]);
?>