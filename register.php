
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'includes/db.php';
require_once 'includes/logger.php';
require_once 'includes/mail.php';

function generateValidationToken(): string {
    return bin2hex(random_bytes(16));
}

// VALIDAR CUENTA (?validate=XXXX)
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
        die("Token invàlid o ja utilitzat.");
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

$errors = [];
$success = '';

// Campos requeridos
$fields = [
    'name' => '',
    'surnames' => '',
    'entity' => '',
    'city' => '',
    'phone_number' => '',
    'type' => '',
    'email' => '',
    'password' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($fields as $k => $_) {
        $fields[$k] = trim($_POST[$k] ?? '');
    }

    // Validación básica
    foreach (['name','surnames','entity','city','phone_number','type','email','password'] as $f) {
        if (!$fields[$f]) {
            $errors[] = "El camp '" . htmlspecialchars($f) . "' és obligatori.";
        }
    }

    // Validar tipo
    if ($fields['type'] !== 'Empresa' && $fields['type'] !== 'Centre') {
        $errors[] = "El tipus ha de ser 'Empresa' o 'Centre'.";
    }

    // Email único
    $stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
    $stmt->execute([$fields['email']]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Aquest correu ja està registrat.";
    }

    if (!$errors) {
        $token = generateValidationToken();
        $stmt = $conn->prepare("
            INSERT INTO user (
                name, surnames, entity, city, phone_number, type, email, password_hash,
                is_active, validation_token, validation_expires
            ) VALUES (
                :name, :surnames, :entity, :city, :phone_number, :type, :email, :pass,
                0, :token, :expires
            )
        ");
        $stmt->execute([
            ':name' => $fields['name'],
            ':surnames' => $fields['surnames'],
            ':entity' => $fields['entity'],
            ':city' => $fields['city'],
            ':phone_number' => $fields['phone_number'],
            ':type' => $fields['type'],
            ':email' => $fields['email'],
            ':pass' => password_hash($fields['password'], PASSWORD_DEFAULT),
            ':token' => $token,
            ':expires' => (new DateTime('+30 minutes'))->format('Y-m-d H:i:s')
        ]);
        if (sendRegistrationEmail($fields['email'], $fields['name'], $token)) {
            $success = "Registre completat. Revisa el correu per validar el teu compte.";
        } else {
            $errors[] = "No s'ha pogut enviar el correu de validació.";
        }
    }
}

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


<form method="post" style="max-width:400px">
    <input name="name" placeholder="Nom" value="<?= htmlspecialchars($fields['name']) ?>" required><br>
    <input name="surnames" placeholder="Cognoms" value="<?= htmlspecialchars($fields['surnames']) ?>" required><br>
    <input name="entity" placeholder="Entitat" value="<?= htmlspecialchars($fields['entity']) ?>" required><br>
    <input name="city" placeholder="Població" value="<?= htmlspecialchars($fields['city']) ?>" required><br>
    <input name="phone_number" placeholder="Telèfon" value="<?= htmlspecialchars($fields['phone_number']) ?>" required><br>
    <select name="type" required>
        <option value="">Tipus</option>
        <option value="Empresa" <?= $fields['type']==='Empresa'?'selected':'' ?>>Empresa</option>
        <option value="Centre" <?= $fields['type']==='Centre'?'selected':'' ?>>Centre</option>
    </select><br>
    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($fields['email']) ?>" required><br>
    <input type="password" name="password" placeholder="Contrasenya" required><br>
    <button type="submit">Registrar</button>
</form>

</body>
</html>
