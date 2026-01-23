<?php
// includes/bd_register.php

require_once 'db.php';

/**
 * Obtener usuario por token de validación
 */
function getUserByValidationToken(string $token)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT user_id, validation_expires, is_active
        FROM user
        WHERE validation_token = ?
        LIMIT 1
    ");
    $stmt->execute([$token]);

    return $stmt->fetch();
}

/**
 * Activar usuario
 */
function activateUser(int $userId): void
{
    global $conn;

    $stmt = $conn->prepare("
        UPDATE user
        SET is_active = 1,
            validation_token = NULL,
            validation_expires = NULL
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
}

/**
 * Comprobar si un email ya existe
 */
function emailExists(string $email): bool
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT user_id
        FROM user
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);

    return (bool) $stmt->fetch();
}

/**
 * Crear usuario inactivo
 */
function createInactiveUser(
    string $email,
    string $passwordHash,
    string $nombre,
    string $apellidos,
    string $ciudad,
    string $telefono,
    string $entidad,
    string $tipo,
    string $token,
    string $expires
): void {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO user (
            email,
            password_hash,
            name,
            surnames,
            city,
            phone_number,
            entity,
            type,
            image_path,
            is_active,
            validation_token,
            validation_expires
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, 0, ?, ?)
    ");

    $stmt->execute([
        $email,
        $passwordHash,
        $nombre,
        $apellidos,
        $ciudad,
        $telefono,
        $entidad,
        $tipo,
        $token,
        $expires
    ]);
}

function assignTagsToUserByEmail(string $email, array $tags): bool
{
    global $conn;

    if (empty($tags)) {
        return true;
    }

    try {
        // Obtener user_id
        $stmtUser = $conn->prepare(
            "SELECT user_id FROM user WHERE email = ? LIMIT 1"
        );
        $stmtUser->execute([$email]);
        $user_id = $stmtUser->fetchColumn();

        if (!$user_id) {
            throw new Exception("Usuario no encontrado para el email: $email");
        }

        // Preparar consultas
        $stmtTag = $conn->prepare(
            "SELECT tag_id FROM tag WHERE name = ?"
        );

        $stmtInsert = $conn->prepare(
            "INSERT INTO user_tags (user_id, tag_id)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE tag_id = tag_id"
        );

        foreach ($tags as $tag_name) {

            $stmtTag->execute([$tag_name]);
            $tag_id = $stmtTag->fetchColumn();

            if ($tag_id) {
                $stmtInsert->execute([$user_id, $tag_id]);
            }
        }

        return true;

    } catch (Throwable $e) {
        error_log('assignTagsToUserByEmail ERROR: ' . $e->getMessage());
        return false;
    }
}

function enviarCorreoValidacion(string $email, string $token, string $nombre): bool
{
    $urlValidacion = "https://tudominio.com/register.php?validate=" . urlencode($token);

    $asunto = "Valida tu cuenta";
    $mensaje = "
        <p>Hola <strong>{$nombre}</strong>,</p>
        <p>Gracias por registrarte. Para activar tu cuenta, haz clic en el siguiente enlace:</p>
        <p>
            <a href='{$urlValidacion}'>Validar cuenta</a>
        </p>
        <p>Este enlace caduca en 48 horas.</p>
    ";

    try {
        return enviarCorreo($email, $asunto, $mensaje);
    } catch (Throwable $e) {
        error_log('enviarCorreoValidacion ERROR: ' . $e->getMessage());
        return false;
    }
}
