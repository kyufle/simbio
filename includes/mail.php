<?php
require_once __DIR__ . '/logger.php';

/**
 * Envia el correu de validació de registre
 */
function sendRegistrationEmail(string $userEmail, string $userName, string $validationToken): bool
{
    $validateLink = "http://localhost/register.php?validate=" . urlencode($validationToken);

    $subject = "Valida el teu correu - Simbio";

    // Boundary
    $boundary = md5(uniqid(time()));

    // Headers
    $headers  = "From: Simbio <no-reply@simbio.cat>\r\n";
    $headers .= "Reply-To: no-reply@simbio.cat\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";

    // Message
    $message  = "--{$boundary}\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $message .= "Hola {$userName},\n\n";
    $message .= "Per activar el teu compte visita:\n";
    $message .= $validateLink . "\n\n";
    $message .= "Aquest enllaç caduca en 30 minuts.\n\n";
    $message .= "--{$boundary}\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $message .= "
        <p>Hola <strong>{$userName}</strong>,</p>
        <p>Per activar el teu compte fes clic aquí:</p>
        <p><a href='{$validateLink}'>Activar compte</a></p>
        <p><small>L'enllaç caduca en 30 minuts.</small></p>
    ";
    $message .= "\r\n--{$boundary}--";

    if (mail($userEmail, $subject, $message, $headers)) {
        log_info("Email de validació enviat a {$userEmail}");
        return true;
    } else {
        log_error("Error enviant email de validació a {$userEmail}");
        return false;
    }
}
