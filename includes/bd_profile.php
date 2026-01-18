<?php
require_once 'db.php';
require_once 'logger.php';

function getUserProfileByEmail($email) {
    global $conn;
    try {
        // Debug: verificar si $conn existe
        if (!$conn) {
            log_error("Conexión a BD no disponible");
            return null;
        }

        $stmt = $conn->prepare("
            SELECT 
                user_id, name, surnames, email, entity, type, 
                phone_number, city, image_path 
            FROM user 
            WHERE email = :email 
            LIMIT 1
        ");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            log_warning("Perfil de usuario no encontrado para email: {$email}");
            return null;
        }

        // Mapear datos del usuario
        $profile = array(
            'name'         => $user['name'],
            'surnames'     => $user['surnames'],
            'email'        => $user['email'],
            'entity'       => $user['entity'],
            'type'         => $user['type'],
            'phone_number' => $user['phone_number'],
            'city'         => $user['city'],
            'image'        => "/uploads/" . $user['image_path']
        );

        log_info("Perfil de usuario obtenido por email: {$email}");
        return $profile;

    } catch (PDOException $e) {
        log_error("Error en BD al obtener perfil por email {$email}: " . $e->getMessage());
        return null;
    }
}
?>