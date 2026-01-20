<?php
/***********************
 * DEBUG (IMPORTANTE)
 ***********************/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/***********************
 * INCLUDES
 ***********************/
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/logger.php';

/***********************
 * FUNCIONES
 ***********************/
function generateValidationToken(): string {
    return bin2hex(random_bytes(16)); // 32 chars
}

/***********************
 * VALIDACIÓN DE CUENTA
 ***********************/
if (isset($_GET['validate'])) {
    $token = trim($_GET['validate']);

    $stmt = $conn->prepare("
        SELECT user_id, validation_expires 
        FROM user 
        WHERE validation_token = :token 
          AND is_active = 0
        LIMIT 1
    ");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch();

    if (!$user) {
        die("❌ Enllaç invàlid o ja utilitzat.");
    }

    if (new DateTime() > new DateTime($user['validation_expires'])) {
        die("⏰ L'enllaç ha caducat.");
    }

    $stmt = $conn->prepare("
        UPDATE user 
        SET is_active = 1,
            validation_token = NULL,
            validation_expires = NULL
        WHERE user_id = :id
    ");
    $stmt->execute([':id' => $user['user_id']]);

    echo "✅ Compte validada correctament. Ja pots iniciar sessió.";
    exit;
}

/***********************
 * REGISTRE
 ***********************/
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = "Nom, correu i contrasenya són obligatoris.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format de correu invàlid.";
    }

    // Email duplicado
    $stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Aquest correu ja està registrat.";
    }

    if (empty($errors)) {
        $token = generateValidationToken();
        $expires = (new DateTime('+48 hours'))->format('Y-m-d H:i:s');

        $stmt = $conn->prepare("
            INSERT INTO user (
                name, email, password_hash,
                is_active, validation_token, validation_expires
            ) VALUES (
                :name, :email, :password,
                0, :token, :expires
            )
        ");

        $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':token'    => $token,
            ':expires'  => $expires
        ]);

        // 👇 SIMULAMOS el mail (por ahora)
        $validationLink = "http://localhost/register.php?validate=$token";
        log_info("LINK VALIDACIÓ: $validationLink");

        $success = "✅ Registre creat. Revisa el correu (o el log) per validar el compte.";
    }
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Registre</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<h1>Registre</h1>

<?php if ($errors): ?>
    <div style="color:red;">
        <?php foreach ($errors as $e): ?>
            <p><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div style="color:green;">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<form method="POST">
    <label>
        Nom<br>
        <input type="text" name="name" required>
    </label><br><br>

    <label>
        Correu electrònic<br>
        <input type="email" name="email" required>
    </label><br><br>

    <label>
        Contrasenya<br>
        <input type="password" name="password" required>
    </label><br><br>

    <button type="submit">Registrar</button>
</form>

</body>
</html>
