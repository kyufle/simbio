<?php
// cron.php - Conversió de vídeos a qualitat web (<20MB)
// S'executa cada 10 minuts via CRON

$uploadsDir = __DIR__ . '/uploads';
$maxSizeMB = 20;
$maxSizeBytes = $maxSizeMB * 1024 * 1024;

function isWebQuality($file) {
    global $maxSizeBytes;
    // Considerem "web quality" si pesa menys de 20MB
    return filesize($file) <= $maxSizeBytes;
}

function convertToWebQuality($src, $dest) {
    // Utilitza ffmpeg per convertir a qualitat web (resolució 720p, bitrate baix)
    $cmd = "ffmpeg -i " . escapeshellarg($src) .
           " -vf scale='min(1280,iw)':-2 -c:v libx264 -preset fast -crf 28 -c:a aac -b:a 96k " . escapeshellarg($dest) .
           " -y 2>&1";
    exec($cmd, $output, $ret);
    return $ret === 0;
}

$videoFiles = glob($uploadsDir . '/*.mp4');
foreach ($videoFiles as $file) {
    if (isWebQuality($file)) continue; // Ja està en qualitat web
    $tmpDest = $file . '.web.mp4';
    echo "Converting $file ... ";
    if (convertToWebQuality($file, $tmpDest) && isWebQuality($tmpDest)) {
        unlink($file); // Esborra l'original
        rename($tmpDest, $file); // Deixa el nou amb el mateix nom
        echo "OK\n";
    } else {
        if (file_exists($tmpDest)) unlink($tmpDest);
        echo "ERROR\n";
    }
}
?>
