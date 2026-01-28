<?php
require_once 'auth.php';
require_once 'db.php';
require_once 'logger.php';
require_once 'mail_match.php';

header('Content-Type: application/json; charset=utf-8');

if(!isLogged()){
    log_warning("Acceso denegado a like_project.php: no autenticado");
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

    // 2️⃣ Obtener propietario del proyecto y datos del proyecto
    $stmt = $conn->prepare("
        SELECT u.user_id, u.name, u.surnames, u.email, p.title, p.description, p.image_path
        FROM project p
        JOIN user u ON p.user_id = u.user_id
        WHERE p.project_id = :pid
        LIMIT 1
    ");
    $stmt->execute([':pid' => $projectId]);
    $owner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$owner) {
        echo json_encode(['success' => false, 'error' => 'Propietario no encontrado']);
        exit;
    }

    // 3️⃣ Detectar MATCH: El propietario ha dado like a algún proyecto del usuario actual?
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM project_like pl 
        INNER JOIN project p ON pl.project_id = p.project_id 
        WHERE pl.user_id = :owner_id AND p.user_id = :current_user_id
    ");
    $stmt->execute([
        ':owner_id' => $owner['user_id'],
        ':current_user_id' => $currentUserId
    ]);
    $match = $stmt->fetchColumn() > 0;

    // 4️⃣ Si hay match, enviar correos a ambos usuarios
    if ($match) {
        // Obtener datos del usuario actual
        $stmt = $conn->prepare("SELECT email, name, surnames FROM user WHERE user_id = :uid");
        $stmt->execute([':uid' => $currentUserId]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $projectImg = $owner['image_path'] ? ("/uploads/" . $owner['image_path']) : '';
        
        // Enviar correo de match
        enviarCorreoMatch(
            $currentUser['email'], 
            $currentUser['name'] . ' ' . $currentUser['surnames'],
            $owner['email'], 
            $owner['name'] . ' ' . $owner['surnames'],
            $owner['title'], 
            $owner['description'], 
            $projectImg
        );
        
        log_info("Match detectado entre usuario {$currentUserId} y propietario {$owner['user_id']} - Correos enviados");
    }

    // Retornamos info para el toast / redirección
    echo json_encode([
        'success' => true,
        'owner_id' => $owner['user_id'],
        'owner_name' => $owner['name'] . ' ' . $owner['surnames'],
        'match' => $match
    ]);

} catch(PDOException $e){
    log_error("Error al registrar like para proyecto {$projectId} por usuario {$currentUserId}: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}