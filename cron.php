<?php
// cron.php - Conversió de vídeos a qualitat web (<20MB)
// S'executa cada 10 minuts via CRON
require_once __DIR__ . '/includes/logger.php';

$uploadsDir = __DIR__ . '/uploads';
$maxSizeMB = 20;
$maxSizeBytes = $maxSizeMB * 1024 * 1024;

function isWebQuality($file) {
    global $maxSizeBytes;
    // Considerem "web quality" si pesa menys de 20MB i resolució <= 1280px
    $sizeOK = filesize($file) <= $maxSizeBytes;
    // Comprobar resolución con ffprobe
    $cmd = "ffprobe -v error -select_streams v:0 -show_entries stream=width -of csv=p=0 " . escapeshellarg($file);
    $width = intval(trim(shell_exec($cmd)));
    $resOK = $width <= 1280;
    return $sizeOK && $resOK;
}

function convertToWebQuality($src, $dest) {
    $cmd = "ffmpeg -i " . escapeshellarg($src) .
           " -vf scale='min(1280,iw)':-2 -c:v libx264 -preset fast -crf 28 -c:a aac -b:a 96k " . escapeshellarg($dest) .
           " -y 2>&1";
    exec($cmd, $output, $ret);
    return $ret === 0;
}

$videoFiles = glob($uploadsDir . '/*.mp4');
$converted = 0;
if (empty($videoFiles)) {
    log_info('CRON: No hay vídeos para procesar.');
    echo "No hay vídeos para procesar.\n";
} else {
    foreach ($videoFiles as $file) {
        if (isWebQuality($file)) {
            // Ya está optimizado, no hacer nada
            continue;
        }
        $tmpDest = $file . '.web.mp4';
        log_info("CRON: Convirtiendo $file ...");
        echo "Convirtiendo $file ... ";
        if (convertToWebQuality($file, $tmpDest) && isWebQuality($tmpDest)) {
            unlink($file); // Esborra l'original
            rename($tmpDest, $file); // Deixa el nou amb el mateix nom
            log_info("CRON: OK: $file convertido correctamente.");
            echo "OK\n";
            $converted++;
        } else {
            if (file_exists($tmpDest)) unlink($tmpDest);
            log_error("CRON: ERROR: Fallo al convertir $file.");
            echo "ERROR\n";
        }
    }
    if ($converted === 0) {
        log_info('CRON: No había vídeos que requirieran conversión.');
        echo "No había vídeos que requirieran conversión.\n";
    }
}
?>
