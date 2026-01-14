<?php
session_start();
require_once 'db.php';

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
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Credenciales incorrectas'];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Error de base de datos'];
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
