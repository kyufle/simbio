<?php
session_start();
require_once 'db.php';
require_once 'logger.php';

function login($email, $password)
{
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT id, email, name, password, role FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && md5($password) === $user['password']) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role']
            ];
            log_info("Usuario autenticado: {$user['email']}");
            return ['success' => true];
        }

        log_warning("Intento de login fallido para: {$email}");
        return ['success' => false, 'error' => 'Credencials incorrectes'];
    } catch (PDOException $e) {
        log_error("Error en BD durante login para {$email}: " . $e->getMessage());
        return ['success' => false, 'error' => 'Error de base de dades'];
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
