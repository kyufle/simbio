<?php
// test_mail.php - Formulario de prueba de envío de email multipart
error_reporting(-1);
ini_set('display_errors', '1');

function enviar_mail_prueba($to, $subject, $from) {
    $boundary = md5(uniqid(rand()));
    $headers  = "From: Prueba <{$from}>\r\n";
    $headers .= "Reply-To: {$from}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: multipart/alternative; boundary=\"----=_NextPart_{$boundary}\"";

    $message = "This is a multipart message in MIME format.\n\n";
    $message .= "------=_NextPart_{$boundary}\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\n";
    $message .= "Content-Transfer-Encoding: 7bit\n\n";
    $message .= "Versió text pla.\n\n";
    $message .= "------=_NextPart_{$boundary}\n";
    $message .= "Content-Type: text/html; charset=UTF-8\n";
    $message .= "Content-Transfer-Encoding: 7bit\n\n";
    $message .= "<html><body><center><b>Versió HTML</b></center><p>Prova d'enviament des de test_mail.php</p></body></html>\n\n";
    $message .= "------=_NextPart_{$boundary}--";

    return mail($to, $subject, $message, $headers);
}

$sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = trim($_POST['to'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $from = trim($_POST['from'] ?? '');
    if (!$to || !$subject || !$from) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($to, FILTER_VALIDATE_EMAIL) || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email de destino y remitente deben ser válidos.';
    } else {
        $sent = enviar_mail_prueba($to, $subject, $from);
        if (!$sent) {
            $error = 'No se pudo enviar el correo (mail() falló).';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test envío de email PHP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css?v=<?= time() ?>">
</head>
<body class="pageSendEmail">
    <h1>Test envío de email PHP</h1>
    <?php if ($sent): ?>
        <div class="success">Correo enviado correctamente a <b><?= htmlspecialchars($to) ?></b></div>
    <?php elseif ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <div class="form-group">
            <label for="to">Para (destinatario):</label>
            <input type="email" name="to" id="to" required value="<?= htmlspecialchars($_POST['to'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="from">Remitente:</label>
            <input type="email" name="from" id="from" required value="<?= htmlspecialchars($_POST['from'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="subject">Asunto:</label>
            <input type="text" name="subject" id="subject" required value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
        </div>
        <button type="submit">Enviar Email</button>
    </form>
</body>
</html>
