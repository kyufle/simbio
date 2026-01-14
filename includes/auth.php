<?php
session_start();
require_once 'db.php';

function login($email, $password)
{
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT user_id, email, name, password_hash FROM user WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && hash("sha256", $password) === $user['password_hash']) {
            $_SESSION['user'] = [
                'id' => $user['user_id'],
                'email' => $user['email'],
                'name' => $user['name']
            ];
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Credencials incorrectes'];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Error de base de dades'];
    }
}

function isLogged()
{
    return isset($_SESSION['user']);
}

function logout()
{
    session_unset();
    session_destroy();
}
