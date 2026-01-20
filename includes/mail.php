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

    // Texto plano
    $text = "Hola $name,\n\n";
    $text .= "Gràcies per registrar-te a Simbio!\n\n";
    $text .= "Per activar el teu compte, fes clic a l'enllaç següent o còpia'l al navegador:\n$link\n\n";
    $text .= "Aquest enllaç caduca en 30 minuts.\n\n";
    $text .= "Si no has sol·licitat aquest registre, ignora aquest correu.";

    // HTML
    $html = '<!DOCTYPE html><html lang="ca"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Validació de compte</title></head><body style="font-family:sans-serif;background:#f9f9f9;padding:0;margin:0;">
    <div style="max-width:480px;margin:40px auto;background:#fff;border-radius:8px;box-shadow:0 2px 8px #0001;padding:32px 24px;">
        <h2 style="color:#2a7ae2;margin-top:0">Benvingut/da a Simbio!</h2>
        <p>Hola <b>' . htmlspecialchars($name) . '</b>,</p>
        <p>Gràcies per registrar-te. Per activar el teu compte, fes clic al botó:</p>
        <p style="text-align:center;margin:32px 0;">
            <a href="' . $link . '" style="background:#2a7ae2;color:#fff;text-decoration:none;padding:12px 28px;border-radius:5px;font-size:1.1em;display:inline-block;">Activar el meu compte</a>
        </p>
        <p>O copia i enganxa aquest enllaç al navegador:<br><a href="' . $link . '" style="color:#2a7ae2;word-break:break-all;">' . $link . '</a></p>
        <p style="color:#888;font-size:0.95em;">Aquest enllaç caduca en 30 minuts.</p>
        <hr style="border:none;border-top:1px solid #eee;margin:24px 0">
        <p style="color:#888;font-size:0.95em;">Si no has sol·licitat aquest registre, pots ignorar aquest correu.</p>
        <p style="color:#bbb;font-size:0.9em;text-align:center;margin-top:32px;">&copy; ' . date('Y') . ' Simbio</p>
    </div></body></html>';

    $message  = "--$boundary\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $message .= $text . "\r\n";
    $message .= "--$boundary\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $message .= $html . "\r\n";
    $message .= "--$boundary--";

    if (mail($email, $subject, $message, $headers)) {
        log_info("Mail validació enviat a $email");
        return true;
    }
    log_error("Error enviant mail a $email");
    return false;
}
