<?php
require_once 'includes/auth.php';

$error = null;

if (isLogged()) {
    header('Location: discover.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Debes rellenar todos los campos';
    } else {
        $result = login($email, $password);

        if ($result['success']) {
            header('Location: discover.php');
            exit;
        } else {
            $error = $result['error'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="login-page">
    <main>
        <h1>Login</h1>

        <?php if ($error): ?>
            <div class="notification error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <label>
                Email
                <input type="email" name="email" required>
            </label><br><br>

            <label>
                Contraseña
                <input type="password" name="password" required>
            </label><br><br>

            <button type="submit">Entrar</button>
        </form>
    </main>
</body>
</html>
