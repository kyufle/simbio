<?php
/***********************
 * DEBUG
 ***********************/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db.php';
require_once 'includes/logger.php';
require_once 'includes/mail.php';

/**
 * Genera token seguro
 */
function generateValidationToken(): string {
    return bin2hex(random_bytes(16));
}

/**
 * VALIDAR CUENTA (?validate=XXXX)
 */
if (isset($_GET['validate'])) {
    $token = $_GET['validate'];

    $stmt = $conn->prepare("
        SELECT user_id, validation_expires
        FROM user
        WHERE validation_token = :token
          AND is_active = 0
    ");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Token inválido o ja utilitzat.");
    }

    if (new DateTime() > new DateTime($user['validation_expires'])) {
        die("L'enllaç ha caducat.");
    }

    $stmt = $conn->prepare("
        UPDATE user
        SET is_active = 1,
            validation_token = NULL,
            validation_expires = NULL
        WHERE user_id = :id
    ");
    $stmt->execute([':id' => $user['user_id']]);

    echo "Compte validat correctament. Ja pots iniciar sessió.";
    exit;
}

/**
 * REGISTRO
 */
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        $errors[] = "Nom, correu i contrasenya són obligatoris.";
    }

    $stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Aquest correu ja està registrat.";
    }

    if (!$errors) {
        $token = generateValidationToken();

        $stmt = $conn->prepare("
            INSERT INTO user (
                name, email, password_hash,
                is_active, validation_token, validation_expires
            ) VALUES (
                :name, :email, :pass,
                0, :token, :expires
            )
        ");

        $stmt->execute([
            ':name'    => $name,
            ':email'   => $email,
            ':pass'    => password_hash($password, PASSWORD_DEFAULT),
            ':token'   => $token,
            ':expires' => (new DateTime('+30 minutes'))->format('Y-m-d H:i:s')
        ]);

        if (sendRegistrationEmail($email, $name, $token)) {
            $success = "Registre completat. Revisa el correu.";
        } else {
            $errors[] = "No s'ha pogut enviar el correu.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Registre</title>
</head>
<body>

<h1>Registre</h1>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>

<?php if ($success): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post">
    <input name="name" placeholder="Nom" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Contrasenya" required><br>
    <button type="submit">Registrar</button>
</form>

</body>
</html>
