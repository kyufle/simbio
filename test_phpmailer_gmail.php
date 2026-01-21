<?php
// test_phpmailer_gmail.php
// Prueba simple de envío de correo con PHPMailer y Gmail

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

$to = 'davidperera2006@gmail.com'; // Correo de prueba
$subject = 'Prueba PHPMailer Gmail';
$body = 'Este es un correo de prueba enviado desde PHPMailer con Gmail SMTP.';

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'dpereragonzalez2.eb@iesesteveterradas.cat';
    $mail->Password = 'vsgs cqdt mzth pzjk';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom('dpereragonzalez2.eb@iesesteveterradas.cat', 'Prueba Simbio');
    $mail->addAddress($to);
    $mail->isHTML(false);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function($str, $level) { echo "><br>" . htmlspecialchars($str) . "<br>\n"; };

    $mail->send();
    echo '<div style="color:green;font-family:monospace;font-size:1.2em;">Correo enviado correctamente.</div>';
} catch (Exception $e) {
    echo '<div style="color:red;font-family:monospace;font-size:1.2em;">Mailer Error: ' . htmlspecialchars($mail->ErrorInfo) . '</div>';
}
