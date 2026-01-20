<?php
require_once __DIR__ . '/logger.php';

/**
 * Envía el mail de validación
 */
function sendRegistrationEmail(string $email, string $name, string $token): bool
{
    $link = "http://localhost/register.php?validate=" . urlencode($token);

    $subject = "Valida el teu compte - Simbio";
    $boundary = md5(uniqid());

    $headers  = "From: Simbio <no-reply@simbio.cat>\r\n";
    $headers .= "Reply-To: no-reply@simbio.cat\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";

    $message  = "--$boundary\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $message .= "Hola $name,\n\nActiva el teu compte:\n$link\n\n";

    $message .= "--$boundary\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $message .= "
        <p>Hola <b>$name</b>,</p>
        <p><a href='$link'>Activar compte</a></p>
    ";

    $message .= "\r\n--$boundary--";

    if (mail($email, $subject, $message, $headers)) {
        log_info("Mail validació enviat a $email");
        return true;
    }

    log_error("Error enviant mail a $email");
    return false;
}
