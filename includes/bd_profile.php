<?php
    require_once 'db.php';
    require_once 'logger.php';

    function getUserProfile($userId) {
        global $conn;
        try {
            $stmt = $conn->prepare("SELECT user_id, name, surnames, email, entity, type, phone_number_entity, city, image_path FROM user WHERE user_id = :user_id LIMIT 1");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                log_warning("Intento de acceso a perfil de usuario inexistente: {$userId}");
                return null;
            }

            // Mapear datos del usuario
            $profile = array();
            $profile['name'] = $user['name'];
            $profile['surnames'] = $user['surnames'];
            $profile['email'] = $user['email'];
            $profile['entity'] = $user['entity'];
            $profile['type'] = $user['type'];
            $profile['phone_number'] = $user['phone_number_entity'];
            $profile['city'] = $user['city'];
            $profile['image'] = "/uploads/" . $user['image_path'];

            log_info("Perfil de usuario obtenido: {$userId}");
            return $profile;
        } catch (PDOException $e) {
            log_error("Error en BD al obtener perfil de usuario {$userId}: " . $e->getMessage());
            return null;
        }
    }
?>