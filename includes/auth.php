<?php
session_start();

function getUsers()
{
    return require __DIR__ . '/users_mock.php';
}

function login($email, $password)
{
    $users = getUsers();

    foreach ($users as $user) {

        // 1. Email existe
        if ($user['email'] === $email) {

            // 2. Password correcta
            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name'],
                    'role' => $user['role']
                ];
                return ['success' => true];
            }

            // Email bien, contraseña mal
            return ['success' => false, 'error' => 'Contraseña incorrecta'];
        }
    }

    // Email no encontrado
    return ['success' => false, 'error' => 'El usuario no existe'];
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
