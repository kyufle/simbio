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

function convertToWebQuality($src, $dest, &$ffmpegOutput = null) {
    // Elimina el archivo de destino si ya existe para evitar bloqueos
    if (file_exists($dest)) {
        unlink($dest);
    }
    $cmd = "ffmpeg -i " . escapeshellarg($src) .
           " -vf scale=iw:-2 -c:v libx264 -preset fast -crf 28 -c:a aac -b:a 96k " . escapeshellarg($dest) .
           " -y";
    $descriptorspec = [
        1 => ['pipe', 'w'], // stdout
        2 => ['pipe', 'w']  // stderr
    ];
    $process = proc_open($cmd, $descriptorspec, $pipes);
    $output = '';
    $timeout = 120; // segundos
    $start = time();
    if (is_resource($process)) {
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        while (true) {
            $out = stream_get_contents($pipes[1]);
            $err = stream_get_contents($pipes[2]);
            if ($out !== false) $output .= $out;
            if ($err !== false) $output .= $err;
            $status = proc_get_status($process);
            if (!$status['running']) break;
            if ((time() - $start) > $timeout) {
                proc_terminate($process, 9);
                $output .= "\nERROR: ffmpeg superó el tiempo máximo de ejecución (timeout).";
                break;
            }
            usleep(200000); // 0.2s
        }
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
    } else {
        $output .= "No se pudo iniciar ffmpeg.";
    }
    $ffmpegOutput = $output;
    return (strpos($output, 'ERROR') === false && strpos($output, 'Conversion failed') === false);
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
        $ffmpegOutput = '';
        $success = convertToWebQuality($file, $tmpDest, $ffmpegOutput);
        $conversionOK = $success && file_exists($tmpDest) && isWebQuality($tmpDest);
        if ($conversionOK) {
            unlink($file); // Esborra l'original
            rename($tmpDest, $file); // Deixa el nou amb el mateix nom
            log_info("CRON: OK: $file convertido correctamente.");
            echo "OK\n";
            $converted++;
        } else {
            if (file_exists($tmpDest)) unlink($tmpDest);
            log_error("CRON: ERROR: Fallo al convertir $file.\nFFmpeg output:\n$ffmpegOutput");
            echo "ERROR: Fallo al convertir $file.\n";
            echo "FFmpeg output:\n$ffmpegOutput\n";
        }
    }
    if ($converted === 0) {
        log_info('CRON: No había vídeos que requirieran conversión.');
        echo "No había vídeos que requirieran conversión.\n";
    }
}
?>
