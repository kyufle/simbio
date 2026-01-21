
<?php
// includes/mail.php
// Envío de email de validación con PHPMailer (SMTP)
// Requiere: composer require phpmailer/phpmailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function enviarCorreoValidacion($email, $token, $nombre = "") {
	$asunto = "Verifica tu cuenta en Simbio";
	$dominio = $_SERVER['HTTP_HOST'] ?? 'localhost';
	$enlace = "https://$dominio/register.php?validate=" . urlencode($token);
	$mensaje = '<!DOCTYPE html>
	<html lang="es">
	<head><meta charset="UTF-8"><title>Verifica tu cuenta</title></head>
	<body style="font-family: Arial, sans-serif; background: #f7f7f7; margin:0; padding:0;">
	<div style="max-width: 500px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 32px;">
	<h2 style="color: #2a7ae4;">¡Bienvenido a Simbio!</h2>
	<p style="font-size: 16px; color: #333;">Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>
	<p style="font-size: 16px; color: #333;">Gracias por registrarte. Para activar tu cuenta, haz clic en el siguiente botón:</p>
	<p style="text-align: center; margin: 32px 0;">
	<a href="' . $enlace . '" style="background: #2a7ae4; color: #fff; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-size: 18px; display: inline-block;">Verificar cuenta</a>
	</p>
	<p style="font-size: 14px; color: #888;">Este enlace caduca en 30 minutos.</p>
	<hr style="margin: 32px 0; border: none; border-top: 1px solid #eee;">
	<p style="font-size: 13px; color: #aaa;">Si no has solicitado este registro, ignora este mensaje.</p>
	</div></body></html>';

	$mail = new PHPMailer(true);
	try {
		// Configuración SMTP (ajusta estos valores)
		$mail->isSMTP();
		$mail->Host = 'smtp.gmail.com';
		$mail->SMTPAuth = true;
		$mail->Username = 'dpereragonzalez2.eb@iesesteveterradas.cat';
		$mail->Password = 'vsgs cqdt mzth pzjk';
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		$mail->Port = 587;

		$mail->setFrom('dpereragonzalez2.eb@iesesteveterradas.cat', 'Simbio');
		$mail->addAddress($email, $nombre);
		$mail->isHTML(true);
		$mail->Subject = $asunto;
		$mail->Body    = $mensaje;
		$mail->AltBody = 'Hola ' . $nombre . ", para activar tu cuenta visita: $enlace";

		$mail->send();
		return true;
	} catch (Exception $e) {
		error_log('Mailer Error: ' . $mail->ErrorInfo);
		return false;
	}
}
