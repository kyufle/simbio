<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/logger.php';

// Comprobar que el admin está logueado
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Acción eliminar o recuperar
if (isset($_GET['action'], $_GET['id'])) {
    $project_id = (int)$_GET['id'];
    if ($_GET['action'] === 'delete') {
        $stmt = $db->prepare("UPDATE project SET deleted=1 WHERE project_id=?");
        $stmt->execute([$project_id]);
    } elseif ($_GET['action'] === 'restore') {
        $stmt = $db->prepare("UPDATE project SET deleted=0 WHERE project_id=?");
        $stmt->execute([$project_id]);
    }
}

// Obtener lista de proyectos
$projects = $db->query("SELECT p.*, u.name as name FROM project p JOIN user u ON p.user_id=u.user_id ORDER BY p.project_id DESC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Gestió de Projectes</title>
    <link rel="stylesheet" href="../styles.css?v=<?php echo time(); ?>">
    <style>
        /* -------- PROJECTS ADMIN -------- */
        body.login-page-admin main {
            max-width: 1000px;
            padding: 2rem;
        }

        #user-projects {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .project-card {
            background-color: #F5F7FA; /* admin panel background */
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(57, 72, 103, 0.18), 0 2px 8px rgba(33,42,62,0.08);
            padding: 15px;
            width: 250px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .project-title {
            font-size: 18px;
            margin-bottom: 10px;
            color: #212A3E;
            font-weight: 700;
        }

        .project-image {
            width: 100%;
            height: auto;
            max-height: 180px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
        }

        .project-buttons {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-top: auto;
            gap: 5px;
        }

        .project-buttons .btn {
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            color: #212A3E;
            transition: all 0.3s ease;
            flex: 1;
            text-align: center;
        }

        .btn-preview {
            background: linear-gradient(135deg, #FFD966 40%, #FFB200 100%);
            box-shadow: 0 4px 15px rgba(255, 217, 102, 0.4);
        }

        .btn-preview:hover {
            box-shadow: 0 6px 20px rgba(255, 178, 0, 0.6);
        }

        .btn-delete {
            background: linear-gradient(135deg, #FF6B6B 40%, #FF3B3B 100%);
            color: #fff;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
        }

        .btn-delete:hover {
            box-shadow: 0 6px 20px rgba(255, 59, 59, 0.6);
        }

        .btn-restore {
            background: linear-gradient(135deg, #6BCB77 40%, #4CAF50 100%);
            color: #fff;
            box-shadow: 0 4px 15px rgba(107, 203, 119, 0.4);
        }

        .btn-restore:hover {
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.6);
        }

        video {
            width: 100%;
            border-radius: 8px;
            display: none;
            margin-top: 5px;
        }
    </style>
</head>
<body class="login-page-admin">
<main>
    <h1>Gestió de Projectes</h1>

    <div id="user-projects">
        <?php if(empty($projects)): ?>
            <p style="text-align:center; color:#394867;">No hi ha projectes registrats.</p>
        <?php else: ?>
            <?php foreach($projects as $project): ?>
                <div class="project-card">
                    <div class="project-title"><?= htmlspecialchars($project['title']) ?></div>
                    <img src="<?= htmlspecialchars($project['image_path']) ?>" alt="<?= htmlspecialchars($project['title']) ?>" class="project-image" onclick="toggleVideo('video<?= $project['project_id'] ?>')">

                    <?php if($project['video_path']): ?>
                        <video id="video<?= $project['project_id'] ?>" controls>
                            <source src="<?= htmlspecialchars($project['video_path']) ?>" type="video/mp4">
                            El teu navegador no suporta la reproducció de vídeo.
                        </video>
                    <?php endif; ?>

                    <div class="project-buttons">
                        <?php if(!empty($project['deleted'])): ?>
                            <a href="?action=restore&id=<?= $project['project_id'] ?>" class="btn btn-restore">Recuperar</a>
                        <?php else: ?>
                            <a href="?action=delete&id=<?= $project['project_id'] ?>" onclick="return confirm('Segur que vols eliminar aquest projecte?')" class="btn btn-delete">Eliminar</a>
                        <?php endif; ?>

                        <?php if($project['video_path']): ?>
                            <a href="javascript:void(0)" class="btn btn-preview" onclick="toggleVideo('video<?= $project['project_id'] ?>')">Preview</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<script>
function toggleVideo(id) {
    const video = document.getElementById(id);
    if(video.style.display === 'block') {
        video.pause();
        video.style.display = 'none';
    } else {
        video.style.display = 'block';
        video.play();
    }
}
</script>
</body>
</html>