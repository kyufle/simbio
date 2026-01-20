<?php
// mail.php - Envío de correos usando PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/logger.php';

// CONFIGURACIÓN SMTP
$smtpConfig = [
    'host' => 'smtp.gmail.com',        // Servidor SMTP
    'user' => 'tu_correo@gmail.com',   // Usuario SMTP
    'pass' => 'tu_contraseña_app',     // Contraseña SMTP (App Password si Gmail)
    'port' => 587,                     // Puerto SMTP
    'secure' => PHPMailer::ENCRYPTION_STARTTLS,
    'from_email' => 'no-reply@simbio.cat',
    'from_name'  => 'Simbio'
];

function sendEmail($to, $subject, $body, $altBody = null) {
    global $smtpConfig;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $smtpConfig['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpConfig['user'];
        $mail->Password   = $smtpConfig['pass'];
        $mail->SMTPSecure = $smtpConfig['secure'];
        $mail->Port       = $smtpConfig['port'];

        $mail->setFrom($smtpConfig['from_email'], $smtpConfig['from_name']);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?? strip_tags($body);

        $mail->send();
        log_info("Correo enviado a {$to} - Asunto: {$subject}");
        return true;

    } catch (Exception $e) {
        log_error("Error enviando correo a {$to} - PHPMailer: {$mail->ErrorInfo}");
        return false;
    }
}

function sendRegistrationEmail($userEmail, $userName, $validationToken) {
    $link = "https://tusitio.cat/register.php?validate=" . urlencode($validationToken);
    $subject = "Valida el teu correu a Simbio";
    $body = "<p>Hola <strong>" . htmlspecialchars($userName) . "</strong>,</p>
             <p>Gràcies per registrar-te! Per activar el teu compte, fes clic al següent enllaç:</p>
             <p><a href='{$link}'>Activar compte</a></p>
             <p>L'enllaç caduca en 30 minuts.</p>
             <p>Salutacions,<br>Simbio</p>";

    return sendEmail($userEmail, $subject, $body);
}
