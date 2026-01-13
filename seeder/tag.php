<?php
$host     = 'localhost';
$db       = 'simbio';
$user     = 'root';
$password = '';
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    // Conexión PDO
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Conectado a la base de datos...\n";

    $sql = <<<SQL
    INSERT IGNORE INTO tag (name, parent) VALUES 
    ('AF', NULL),
    ('AG', NULL),
    ('AR', NULL),
    ('CM', NULL),
    ('EA', NULL),
    ('EE', NULL),
    ('E0', NULL),
    ('FM', NULL),
    ('FS', NULL),
    ('EA', NULL),
    ('HT', NULL),
    ('IA', NULL),
    ('IC', NULL),
    ('IE', NULL),
    ('EA', NULL),
    ('IM', NULL),
    ('IP', NULL),
    ('IS', NULL),
    ('MP', NULL),
    ('QU', NULL),
    ('SA', NULL),
    ('SC', NULL),
    ('SM', NULL),
    ('TM', NULL),
    ('TX', NULL),
    ('AE', NULL),
    
    --LOE
    ('Preimpressió digital', 'AF'),
    
SQL;

    // Ejecutamos el SQL
    $pdo->exec($sql);

    echo "¡Seeder executat amb exit! S'han insertat les familias i els ciclos.\n";

} catch (PDOException $e) {
    // missatges d'error
    echo "ERROR DE BASE DE DADES: " . $e->getMessage() . "\n";
}
