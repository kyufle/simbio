<?php
/***********************
 * DEBUG (IMPORTANTE)
 ***********************/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db.php';
require_once 'includes/logger.php';
require_once 'includes/mail.php';

// Función para generar un token único de validación
function generateValidationToken() {
    return bin2hex(random_bytes(16));
}

// Si viene ?validate=XXXX
if (isset($_GET['validate'])) {
    $token = $_GET['validate'];
    $stmt = $conn->prepare("SELECT user_id, email, name, validation_expires FROM user WHERE validation_token = :token AND is_active = 0");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        die("Token inválido o caducado.");
    }
    // Comprobar caducidad (30 minutos)
    $now = new DateTime();
    $expires = new DateTime($user['validation_expires']);
    if ($now > $expires) {
        die("El enlace ha caducado.");
    }
    // Activar usuario
    $stmt = $conn->prepare("UPDATE user SET is_active = 1, validation_token = NULL, validation_expires = NULL WHERE user_id = :id");
    $stmt->bindParam(':id', $user['user_id']);
    $stmt->execute();
    echo "Compte validada correctament. Ara ja pots iniciar sessió.";
    exit;
}

// Manejar envío de formulario
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $surnames = trim($_POST['surnames'] ?? '');
    $entity = trim($_POST['entity'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $phone = trim($_POST['phone_number'] ?? '');

    if (!$name || !$email || !$password) {
        $errors[] = "Nombre, email y contraseña son obligatorios.";
    }

    // Comprobar si ya existe
    $stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Ja existeix un usuari amb aquest correu.";
    }

    if (empty($errors)) {
        $token = generateValidationToken();
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $validationExpires = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');
        $stmt = $conn->prepare("
            INSERT INTO user (name, surnames, email, password_hash, entity, city, phone_number, is_active, validation_token, validation_expires)
            VALUES (:name, :surnames, :email, :password_hash, :entity, :city, :phone_number, 0, :token, :validation_expires)
        ");
        $stmt->execute([
            ':name' => $name,
            ':surnames' => $surnames,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':entity' => $entity,
            ':city' => $city,
            ':phone_number' => $phone,
            ':token' => $token,
            ':validation_expires' => $validationExpires
        ]);
        // Enviar correo
        if (sendRegistrationEmail($email, $name, $token)) {
            $success = "Registre completat. Revisa el teu correu per validar el teu compte.";
        } else {
            $errors[] = "Error enviant el correu de validació.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registre d'Usuari</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main class="register-page">
    <h1>Registre d'Usuari</h1>

    <?php if ($errors): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo "<p>" . htmlspecialchars($e) . "</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" class="register-form">
        <div class="form-group">
            <label for="name">Nom</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="surnames">Cognoms</label>
            <input type="text" id="surnames" name="surnames">
        </div>
        <div class="form-group">
            <label for="entity">Entitat</label>
            <input type="text" id="entity" name="entity">
        </div>
        <div class="form-group">
            <label for="city">Població</label>
            <input type="text" id="city" name="city">
        </div>
        <div class="form-group">
            <label for="phone_number">Telèfon</label>
            <input type="tel" id="phone_number" name="phone_number">
        </div>
        <div class="form-group">
            <label for="email">Correu electrònic</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Contrasenya</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">Registrar-se</button>
    </form>
</main>
</body>
</html>
