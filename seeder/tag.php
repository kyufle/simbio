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
    ('Arts Gràfiques', NULL),
    ('Administració i Gestió', NULL),
    ('Agrària', NULL),
    ('Comerç i Màrqueting', NULL),
    ('Energia i Aigua', NULL),
    ('Electricitat i Electrònica', NULL),
    ('Fabricació Mecànica', NULL),
    ('Fusta, Moble i Suro', NULL),
    ('Hoteleria i Turisme', NULL),
    ('Informàtica i Comunicacions', NULL),
    ('Instal·lació i Manteniment', NULL),
    ('Imatge Personal', NULL),
    ('Imatge i So', NULL),
    ('Seguretat i Medi Ambient', NULL),
    ('Transport i Manteniment de Vehicles', NULL),
    ('Activitats físiques i esportives', NULL),
    ('Química', NULL),
    ('Sanitat', NULL),
    ('Edificació i Obra Civil', NULL);

    //cicles
    ('Operacions de procés de pasta i paper', 'Arts Gràfiques'),
    ('Indústries de procés de pasta i paper', 'Arts Gràfiques'),
    ('Preimpressió digital', 'Arts Gràfiques'),
    ('Impressió gràfica', 'Arts Gràfiques'),
    ('Postimpressió i acabats gràfics', 'Arts Gràfiques'),
    ('Disseny i edició de publicacions impreses i multimèdia', 'Arts Gràfiques'),
    ('Operacions de fabricació de productes farmacèutics', 'Química'),
    ('Química ambiental', 'Química'),
    ('Cures auxiliars d''infermeria', 'Sanitat'),
    ('Òptica d''ullera', 'Sanitat'),
    ('Dietètica', 'Sanitat'),
    ('Salut ambiental', 'Sanitat'),
    ('Laboratori d''imatge', 'Sanitat'),
    ('Animació d''activitats físiques i esportives', 'Activitats físiques i esportives'),
    ('Conducció d''activitats fisicoesportives en el medi natural', 'Activitats físiques i esportives'),
    ('Manteniment aeromecànic', 'Transport i Manteniment de Vehicles'),
    ('Manteniment d''aviònica', 'Transport i Manteniment de Vehicles'),
    ('Electromecànica de vehicles automòbils', 'Transport i Manteniment de Vehicles'),
    ('Carrosseria', 'Transport i Manteniment de Vehicles'),
    ('Conducció de vehicles de transport per carretera', 'Transport i Manteniment de Vehicles'),
    ('Serveis al consumidor', 'Comerç i Màrqueting'),
    ('Activitats comercials', 'Comerç i Màrqueting'),
    ('Gestió de vendes i espais comercials', 'Comerç i Màrqueting'),
    ('Comerç internacional', 'Comerç i Màrqueting'),
    ('Transport i logística', 'Comerç i Màrqueting'),
    ('Màrqueting i publicitat', 'Comerç i Màrqueting'),
    ('Realització i plans d''obres', 'Edificació i Obra Civil'),
    ('Gestió administrativa', 'Administració i Gestió'),
    ('Assistència a la direcció', 'Administració i Gestió'),
    ('Administració i finances', 'Administració i Gestió'),
    ('Producció agropecuària', 'Agrària'),
    ('Producció agroecològica', 'Agrària'),
    ('Aprofitament i conservació del medi natural', 'Agrària'),
    ('Jardineria i floristeria', 'Agrària'),
    ('Activitats eqüestres', 'Agrària'),
    ('Sistemes microinformàtics i xarxes', 'Informàtica i Comunicacions'),
    ('Administració de sistemes informàtics en xarxa', 'Informàtica i Comunicacions'),
    ('Desenvolu	pament d''aplicacions multiplataforma', 'Informàtica i Comunicacions'),
    ('Desenvolupament d''aplicacions web', 'Informàtica i Comunicacions'),
    ('Cuina i gastronomia', 'Hoteleria i Turisme'),
    ('Serveis en restauració', 'Hoteleria i Turisme'),
    ('Agències de viatges i gestió d''esdeveniments', 'Hoteleria i Turisme'),
    ('Gestió d''allotjaments turístics', 'Hoteleria i Turisme'),
    ('Direcció de cuina', 'Hoteleria i Turisme'),
    ('Perruqueria i cosmètica capil·lar', 'Imatge Personal'),
    ('Estètica i bellesa', 'Imatge Personal'),
    ('Assessoria d''imatge personal i corporativa', 'Imatge Personal'),
    ('Vídeo, discjòquei i so', 'Imatge i So'),
    ('Realització de projectes d''audiovisuals i espectacles', 'Imatge i So'),
    ('Animacions 3D, jocs i entorns interactius', 'Imatge i So'),
    ('Soldadura i caldereria', 'Fabricació Mecànica'),
    ('Mecanització', 'Fabricació Mecànica'),
    ('Construccions metàl·liques', 'Fabricació Mecànica'),
    ('Prevenció de riscos professionals', 'Seguretat i Medi Ambient');
SQL;

    // Ejecutamos el SQL
    $pdo->exec($sql);

    echo "¡Seeder ejecutado con éxito! Se han insertado las familias y los ciclos.\n";

} catch (PDOException $e) {
    // missatges d'error
    echo "ERROR DE BASE DE DATOS: " . $e->getMessage() . "\n";
}
