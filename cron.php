<?php
// cron.php – Conversió de vídeos a qualitat web (<20MB)
// Execució cada 10 minuts

require_once __DIR__ . '/includes/logger.php';

$uploadsDir = __DIR__ . '/uploads';
$maxSize = 20 * 1024 * 1024; // 20MB
$ffmpeg = '/usr/bin/ffmpeg';
$ffprobe = '/usr/bin/ffprobe';

$videos = glob($uploadsDir . '/*.mp4');

if (!$videos) {
    log_info('CRON: No hi ha vídeos per processar');
    exit;
}

foreach ($videos as $video) {

    // Saltar vídeos ja convertits
    if (filesize($video) <= $maxSize) {
        continue;
    }

    $tmp = $video . '.tmp.mp4';

    log_info("CRON: Convertint $video");

    // Conversió simple i controlada
    $cmd = "$ffmpeg -i " . escapeshellarg($video) .
        " -vf scale=1280:-2 -c:v libx264 -preset fast -crf 28 " .
        " -c:a aac -b:a 96k -movflags +faststart " .
        escapeshellarg($tmp) . " -y 2>&1";

    exec($cmd, $output, $code);

    if ($code !== 0 || !file_exists($tmp)) {
        log_error("CRON: Error convertint $video\n" . implode("\n", $output));
        if (file_exists($tmp)) unlink($tmp);
        continue;
    }

    // Comprovació final
    if (filesize($tmp) <= $maxSize) {
        unlink($video);
        rename($tmp, $video);
        log_info("CRON: Conversió OK ($video)");
    } else {
        unlink($tmp);
        log_error("CRON: El vídeo supera els 20MB ($video)");
    }
}
