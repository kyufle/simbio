<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/logger.php';

header('Content-Type: application/json');

// Verificar que el usuario está logueado
if (!isLogged()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$action = $_GET['action'] ?? '';
$currentUserId = $_SESSION['user']['id'];

try {
    if ($action === 'send') {
        // Enviar un nuevo mensaje
        $data = json_decode(file_get_contents('php://input'), true);
        
        $text = trim($data['text'] ?? '');
        $toUserId = (int)($data['to_user_id'] ?? 0);
        
        if (empty($text) || $toUserId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
            exit;
        }
        
        // Insertar el mensaje en la BD
        $stmt = $conn->prepare("
            INSERT INTO message (text, user_from_id, user_to_id, sent_at)
            VALUES (:text, :from_id, :to_id, NOW())
        ");
        
        $result = $stmt->execute([
            ':text' => $text,
            ':from_id' => $currentUserId,
            ':to_id' => $toUserId
        ]);
        
        if ($result) {
            $messageId = $conn->lastInsertId();
            echo json_encode([
                'success' => true,
                'message_id' => $messageId,
                'text' => htmlspecialchars($text),
                'sent_at' => date('Y-m-d H:i:s'),
                'user_from_id' => $currentUserId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error al guardar el mensaje']);
        }
        
    } elseif ($action === 'get') {
        // Obtener mensajes entre dos usuarios
        $otherUserId = (int)($_GET['user_id'] ?? 0);
        $lastMessageId = (int)($_GET['last_message_id'] ?? 0);
        
        if ($otherUserId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'user_id inválido']);
            exit;
        }
        
        // Construir la consulta
        $query = "
            SELECT message_id, text, sent_at, user_from_id, user_to_id
            FROM message
            WHERE (
                (user_from_id = :user1 AND user_to_id = :user2) OR
                (user_from_id = :user2 AND user_to_id = :user1)
            )
        ";
        
        $params = [
            ':user1' => $currentUserId,
            ':user2' => $otherUserId
        ];
        
        // Si se proporciona last_message_id, obtener solo mensajes más nuevos
        if ($lastMessageId > 0) {
            $query .= " AND message_id > :last_id";
            $params[':last_id'] = $lastMessageId;
        }
        
        $query .= " ORDER BY message_id ASC LIMIT 100";
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar y sanitizar los mensajes
        $processedMessages = array_map(function($msg) {
            return [
                'message_id' => (int)$msg['message_id'],
                'text' => htmlspecialchars($msg['text']),
                'sent_at' => $msg['sent_at'],
                'user_from_id' => (int)$msg['user_from_id'],
                'user_to_id' => (int)$msg['user_to_id'],
                'isSent' => (int)$msg['user_from_id'] === $_SESSION['user']['id']
            ];
        }, $messages);
        
        echo json_encode([
            'success' => true,
            'messages' => $processedMessages,
            'count' => count($processedMessages)
        ]);
        
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
    error_log("Error en chat API: " . $e->getMessage());
}
?>
