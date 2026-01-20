<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/logger.php';

function sendRegistrationEmail($userEmail, $userName, $validationToken) {

    $mail = new PHPMailer(true);

    try {
        // CONFIG SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'TU_CORREO@gmail.com';
        $mail->Password   = 'APP_PASSWORD_DE_GMAIL';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // REMITENTE Y DESTINO
        $mail->setFrom('no-reply@simbio.cat', 'Simbio');
        $mail->addAddress($userEmail, $userName);

        // CONTENIDO
        $link = "http://localhost/register.php?validate=" . urlencode($validationToken);

        $mail->isHTML(true);
        $mail->Subject = 'Valida el teu correu - Simbio';
        $mail->Body = "
            <p>Hola <strong>{$userName}</strong>,</p>
            <p>Per activar el teu compte fes clic aquí:</p>
            <p><a href='{$link}'>Activar compte</a></p>
            <p>L'enllaç caduca en 48 hores.</p>
        ";

        $mail->send();
        log_info("Email de validació enviat a {$userEmail}");
        return true;

    } catch (Exception $e) {
        log_error("Error enviant correu: {$mail->ErrorInfo}");
        return false;
    }
}
