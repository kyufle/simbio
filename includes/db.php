<?php
/* bd ejemplo */
$servername = "localhost";
$username = "tinder";
$password = "tinder123";
$dbname = "tinder_empresa";

try {
    $conn = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4", 
        $username, 
        $password, 
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    die("Error al conectar con la base de datos.");
}
?>