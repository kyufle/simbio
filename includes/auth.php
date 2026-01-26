<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/logger.php';

function login($email, $password)
{
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT user_id, email, name, password_hash, is_active FROM user WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            log_warning("Intento de login con email inexistente: {$email}");
            return [
                'success' => false,
                'error' => 'Correu electrònic no registrat',
                'errors' => ['email' => 'No existeix cap compte amb aquest correu electrònic']
            ];
        }

        if (!$user['is_active']) {
            log_warning("Intento de login con cuenta inactiva: {$email}");
            return [
                'success' => false,
                'error' => 'Compte pendent de validació',
                'errors' => ['general' => 'La teva compte està pendent de validació. Revisa el teu correu per activar-la.']
            ];
        }

        if (hash("sha256", $password) !== $user['password_hash']) {
            log_warning("Intento de login con contraseña incorrecta para: {$email}");
            return [
                'success' => false,
                'error' => 'Contrasenya incorrecta',
                'errors' => ['password' => 'La contrasenya és incorrecta']
            ];
        }

        $_SESSION['user'] = [
            'id' => $user['user_id'],
            'email' => $user['email'],
            'name' => $user['name']
        ];
        log_info("Usuario autenticado: {$user['email']}");
        return ['success' => true];
    } catch (PDOException $e) {
        log_error("Error en BD durante login para {$email}: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Error de base de dades',
            'errors' => ['general' => 'Hi ha hagut un error en accedir a la base de dades']
        ];
    }
}

function isLogged()
{
    return isset($_SESSION['user']);
}

function logout()
{
    if (isset($_SESSION['user'])) {
        log_info("Usuario desconectado: " . $_SESSION['user']['email']);
    }
    session_unset();
    session_destroy();
}
