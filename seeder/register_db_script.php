<?php
// register_db_script.php
// Script para preparar la tabla `user` para registro con email

$host = 'localhost';
$db   = 'simbio';
$user = 'TU_USUARIO';  // <- Cambia esto
$pass = 'TU_PASSWORD';  // <- Cambia esto
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Conexión a la base de datos OK.\n";

    // Script ALTER TABLE
    $sql = "
        ALTER TABLE `user`
        ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 0 AFTER `type`,
        ADD COLUMN `validation_token` VARCHAR(64) DEFAULT NULL AFTER `is_active`,
        ADD COLUMN `validation_expires` DATETIME DEFAULT NULL AFTER `validation_token`;
    ";

    $pdo->exec($sql);
    echo "Campos añadidos correctamente: is_active, validation_token, validation_expires.\n";

} catch (\PDOException $e) {
    echo "ERROR: No se pudo modificar la tabla `user`.\n";
    echo "Detalles: " . $e->getMessage() . "\n";
    exit(1);
}
