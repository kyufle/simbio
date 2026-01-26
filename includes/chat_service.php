<?php
require_once 'db.php';
require_once 'logger.php';

/**
 * Obtiene todas las conversaciones de un usuario
 * Devuelve el último mensaje de cada conversación junto con datos del otro usuario
 */
function getUserConversations(int $userId, int $limit = 50): array {
    global $conn;

    try {
        $stmt = $conn->prepare("
            SELECT 
                -- Identificar al otro usuario
                CASE 
                    WHEN m.user_from_id = :user_id THEN m.user_to_id
                    ELSE m.user_from_id
                END AS other_user_id,
                u.name,
                u.surnames,
                u.entity,
                u.type,
                u.image_path,
                -- Último mensaje de la conversación
                (SELECT text 
                 FROM message 
                 WHERE (user_from_id = :user_id AND user_to_id = CASE WHEN m.user_from_id = :user_id THEN m.user_to_id ELSE m.user_from_id END)
                    OR (user_from_id = CASE WHEN m.user_from_id = :user_id THEN m.user_to_id ELSE m.user_from_id END AND user_to_id = :user_id)
                 ORDER BY sent_at DESC
                 LIMIT 1
                ) AS last_message_text,
                (SELECT sent_at 
                 FROM message 
                 WHERE (user_from_id = :user_id AND user_to_id = CASE WHEN m.user_from_id = :user_id THEN m.user_to_id ELSE m.user_from_id END)
                    OR (user_from_id = CASE WHEN m.user_from_id = :user_id THEN m.user_to_id ELSE m.user_from_id END AND user_to_id = :user_id)
                 ORDER BY sent_at DESC
                 LIMIT 1
                ) AS last_message_time
            FROM message m
            JOIN user u ON u.user_id = CASE 
                WHEN m.user_from_id = :user_id THEN m.user_to_id
                ELSE m.user_from_id
            END
            WHERE m.user_from_id = :user_id OR m.user_to_id = :user_id
            GROUP BY other_user_id
            ORDER BY last_message_time DESC
            LIMIT :limit
        ");

        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        log_error("Error al obtener conversaciones del usuario {$userId}: " . $e->getMessage());
        return [];
    }
}

/**
 * Elimina todos los mensajes entre dos usuarios
 */
function deleteConversation(int $userId, int $otherUserId): bool {
    global $conn;

    try {
        $stmt = $conn->prepare("
            DELETE FROM message
            WHERE (user_from_id = :user_id AND user_to_id = :other_id)
               OR (user_from_id = :other_id AND user_to_id = :user_id)
        ");

        return $stmt->execute([
            ':user_id' => $userId,
            ':other_id' => $otherUserId
        ]);
    } catch (PDOException $e) {
        log_error("Error al eliminar conversación: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene estadísticas básicas de chat de un usuario
 */
function getChatStatistics(int $userId): array {
    global $conn;

    try {
        $stmt = $conn->prepare("
            SELECT
                COUNT(DISTINCT CASE WHEN user_to_id = :user_id THEN user_from_id END) AS users_written_to,
                COUNT(DISTINCT CASE WHEN user_from_id = :user_id THEN user_to_id END) AS users_received_from,
                COUNT(*) AS total_messages,
                SUM(CASE WHEN user_from_id = :user_id THEN 1 ELSE 0 END) AS messages_sent,
                SUM(CASE WHEN user_to_id = :user_id THEN 1 ELSE 0 END) AS messages_received
            FROM message
            WHERE user_from_id = :user_id OR user_to_id = :user_id
        ");

        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?? [];
    } catch (PDOException $e) {
        log_error("Error al obtener estadísticas de chat: " . $e->getMessage());
        return [];
    }
}
?>