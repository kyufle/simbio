<?php
require_once 'auth.php';
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

if(!isLogged()){
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'No autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$projectId = (int)($data['project_id'] ?? 0);
$currentUserId = $_SESSION['user']['id'];

if($projectId <= 0){
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Proyecto inválido']);
    exit;
}

try {
    // 1️⃣ Guardar like (si no existe)
    $stmt = $conn->prepare("
        INSERT IGNORE INTO project_like (user_id, project_id)
        VALUES (:user_id, :project_id)
    ");
    $stmt->execute([':user_id'=>$currentUserId, ':project_id'=>$projectId]);

    // 2️⃣ Obtener propietario del proyecto
    $stmt = $conn->prepare("SELECT user_id, name FROM project WHERE project_id = :pid LIMIT 1");
    $stmt->execute([':pid'=>$projectId]);
    $owner = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$owner){
        echo json_encode(['success'=>false,'error'=>'Propietario no encontrado']);
        exit;
    }

    // Retornamos info para el toast / redirección
    echo json_encode([
        'success'=>true,
        'owner_id' => $owner['user_id'],
        'owner_name' => $owner['name']
    ]);

} catch(PDOException $e){
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
