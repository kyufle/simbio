<?php
// Seeder para actualizar la base de datos con los cambios recientes
// Añade la columna created_at a project_like y crea la tabla user_tags si no existe

$env = parse_ini_file('../.env');
$host     = 'localhost';
$db       = 'simbio';
$user     = $env['db_user'];
$password = $env['db_password'];
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "Conectado a la base de datos...\n";

    // Añadir columna created_at a project_like si no existe
    $sql = "ALTER TABLE project_like ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;";
    try {
        $pdo->exec($sql);
        echo "Columna created_at añadida a project_like.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "La columna created_at ya existe en project_like.\n";
        } else {
            throw $e;
        }
    }

    // Crear tabla user_tags si no existe
    $sql = "CREATE TABLE IF NOT EXISTS user_tags (
        user_id INT NOT NULL,
        tag_id INT NOT NULL,
        PRIMARY KEY (user_id, tag_id),
        FOREIGN KEY (user_id) REFERENCES user(user_id),
        FOREIGN KEY (tag_id) REFERENCES tag(tag_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql);
    echo "Tabla user_tags creada o ya existente.\n";

    // Verificar columna sent_at en message
    $sql = "ALTER TABLE message ADD COLUMN IF NOT EXISTS sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;";
    try {
        $pdo->exec($sql);
        echo "Columna sent_at añadida a message.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "La columna sent_at ya existe en message.\n";
        } else {
            throw $e;
        }
    }

    echo "Actualización de estructura completada.\n";

} catch (PDOException $e) {
    echo "ERROR DE BASE DE DADES: " . $e->getMessage() . "\n";
}
?>
