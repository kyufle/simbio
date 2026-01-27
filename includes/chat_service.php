<?php
require_once 'db.php';
require_once 'logger.php';

/**
 * Obtiene todas las conversaciones de un usuario
 * Devuelve el último mensaje de cada conversación junto con datos del otro usuario
 */
function getUserConversations(int $userId): array {
    global $conn;

    $stmt = $conn->prepare("
        SELECT *
        FROM message
        WHERE user_from_id = :id OR user_to_id = :id
        ORDER BY sent_at DESC
    ");

    $stmt->execute([':id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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