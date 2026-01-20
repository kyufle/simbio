<?php
$to = 'davidperera2006@gmail.com'; // Cambia esto por tu correo real
$subject = 'Prueba de mail() desde PHP';
$message = "Esto es un test de la función mail() en tu servidor.\nSi recibes este correo, mail() funciona correctamente.";
$headers = "From: test@simbio.local\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo "Correo enviado correctamente a $to";
} else {
    echo "Fallo al enviar el correo";
}
