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
	$enlace = "https://$dominio/confirm_email.php?validate=" . urlencode($token);
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
	<p style="font-size: 14px; color: #888;">Este enlace caduca en 48 horas.</p>
	<hr style="margin: 32px 0; border: none; border-top: 1px solid #eee;">
	<p style="font-size: 13px; color: #aaa;">Si no has solicitado este registro, ignora este mensaje.</p>
	</div></body></html>';

	$mail = new PHPMailer(true);
	try {
		// Configuración SMTP (ajusta estos valores)
		$mail->isSMTP();
		$mail->Host = 'smtp.gmail.com';
		$mail->SMTPAuth = true;
		$mail->Username = 'davidperera2006@gmail.com';
		$mail->Password = 'ifce dvsr tkws iytv';
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;

		$mail->setFrom('davidperera2006@gmail.com', 'Simbio');
		$mail->addAddress($email, $nombre);
		$mail->isHTML(true);
		$mail->Subject = $asunto;
		$mail->Body    = $mensaje;
		$mail->AltBody = 'Hola ' . $nombre . ", para activar tu cuenta visita: $enlace";

		$mail->SMTPDebug = 0; // No mostrar información de depuración en producción
		// $mail->Debugoutput = function($str, $level) { echo "><br>" . htmlspecialchars($str) . "<br>\n"; };
		   $mail->send();
		   return true;
	   } catch (Exception $e) {
		   // Solo registrar el error en el log, no mostrarlo al usuario
		   error_log('Mailer Error: ' . $mail->ErrorInfo);
		   return false;
	}
}



function enviarCorreoCodigoTemporal($email, $codigo, $nombre = "") {
	$asunto = "Tu código temporal de acceso a Simbio";
	$mensaje = '<!DOCTYPE html>
	<html lang="es">
	<head><meta charset="UTF-8"><title>Código temporal de acceso</title></head>
	<body style="font-family: Arial, sans-serif; background: #f7f7f7; margin:0; padding:0;">
	<div style="max-width: 500px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 32px;">
	<h2 style="color: #2a7ae4;">Código temporal de acceso</h2>
	<p style="font-size: 16px; color: #333;">Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>
	<p style="font-size: 16px; color: #333;">Hemos recibido una solicitud para acceder a tu cuenta de Simbio. Utiliza el siguiente código temporal para iniciar sesión:</p>
	<div style="text-align: center; margin: 32px 0;">
		<span style="display: inline-block; font-size: 32px; letter-spacing: 8px; background: #f0f4fa; color: #2a7ae4; padding: 16px 32px; border-radius: 8px; font-weight: bold;">' . $codigo . '</span>
	</div>
	<p style="font-size: 15px; color: #888;">Este código caduca en 15 minutos. Si no has solicitado este acceso, puedes ignorar este mensaje.</p>
	<hr style="margin: 32px 0; border: none; border-top: 1px solid #eee;">
	<p style="font-size: 13px; color: #aaa;">No respondas a este correo. Si tienes dudas, contacta con el soporte de Simbio.</p>
	</div></body></html>';

	$mail = new PHPMailer(true);
	try {
		// Configuración SMTP (igual que arriba)
		$mail->isSMTP();
		$mail->Host = 'smtp.gmail.com';
		$mail->SMTPAuth = true;
		$mail->Username = 'davidperera2006@gmail.com';
		$mail->Password = 'ifce dvsr tkws iytv';
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;

		$mail->setFrom('davidperera2006@gmail.com', 'Simbio');
		$mail->addAddress($email, $nombre);
		$mail->isHTML(true);
		$mail->Subject = $asunto;
		$mail->Body    = $mensaje;
		$mail->AltBody = 'Hola ' . $nombre . ", tu código temporal de acceso a Simbio es: $codigo";

		$mail->SMTPDebug = 0;
		$mail->send();
		return true;
	} catch (Exception $e) {
		error_log('Mailer Error: ' . $mail->ErrorInfo);
		return false;
	}
}

function enviarCorreoDigest($email, $nombre, $body) {
	$asunto = "Resumen diario de interacciones en Simbio";
	$mensaje = '<!DOCTYPE html>
	<html lang="es">
	<head><meta charset="UTF-8"><title>Resumen diario</title></head>
	<body style="font-family: Arial, sans-serif; background: #f7f7f7; margin:0; padding:0;">
	<div style="max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 32px;">
	<h2 style="color: #2a7ae4;">Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</h2>' . $body . '
	<hr style="margin: 32px 0; border: none; border-top: 1px solid #eee;">
	<p style="font-size: 13px; color: #aaa;">Este es tu resumen diario automático de Simbio.</p>
	</div></body></html>';

	$mail = new PHPMailer(true);
	try {
		$mail->isSMTP();
		$mail->Host = 'smtp.gmail.com';
		$mail->SMTPAuth = true;
		$mail->Username = 'davidperera2006@gmail.com';
		$mail->Password = 'ifce dvsr tkws iytv';
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;

		$mail->setFrom('davidperera2006@gmail.com', 'Simbio');
		$mail->addAddress($email, $nombre);
		$mail->isHTML(true);
		$mail->Subject = $asunto;
		$mail->Body    = $mensaje;
		$mail->AltBody = 'Hola ' . $nombre . ", este es tu resumen diario de Simbio.";

		$mail->SMTPDebug = 0;
		$mail->send();
		return true;
	} catch (Exception $e) {
		error_log('Mailer Error: ' . $mail->ErrorInfo);
		return false;
	}
}
?>
