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
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Gestió de Projectes</title>
    <link rel="stylesheet" href="../styles.css?v=<?php echo time(); ?>">
    <style>
        /* -------- DASHBOARD ADMIN PROJECTS -------- */
        body.login-page-admin header {
            position: fixed; /* fijo arriba */
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 30px;
            background: linear-gradient(135deg, #212A3E 0%, #394867 100%);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            z-index: 1000;
        }

        body.login-page-admin header .header-left,
        body.login-page-admin header .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        body.login-page-admin header .nav-link {
            text-decoration: none;
            font-weight: 600;
            color: #FFD966;
            padding: 6px 10px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        body.login-page-admin header .nav-link:hover {
            background-color: rgba(255, 217, 102, 0.2);
            text-decoration: none;
        }

        body.login-page-admin header .btn-logout {
            text-decoration: none;
            font-weight: 600;
            background-color: #FF6B6B;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        body.login-page-admin header .btn-logout:hover {
            background-color: #FF3B3B;
        }
        body.login-page-admin main {
            max-width: 1000px;
            padding: 2rem;
            margin-top: 80px;
        }

        #user-projects {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .project-card {
            background-color: #F5F7FA;
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
            margin-bottom: 10px;
        }
    </style>
</head>
<body class="login-page-admin">
    <header>
        <div class="header-left">
            <a href="index.php" class="nav-link">Inici</a>
            <a href="users.php" class="nav-link">Usuaris</a>
            <a href="projects.php" class="nav-link">Projectes</a>
            <a href="settings.php" class="nav-link">Configuració</a>
        </div>
        <div class="header-right">
            <?php if (isAdminLoggedIn()): ?>
                <a href="logout.php" class="btn-logout">Tancar sessió</a>
            <?php else: ?>
                <a href="login.php">Iniciar sessió</a>
            <?php endif; ?>
        </div>
    </header>
<main>
    <h1>Gestió de Projectes</h1>
    <div id="user-projects"></div>
</main>

<script>
async function loadProjects() {
    try {
        const res = await fetch('projects_json.php'); // Endpoint que devuelve JSON de proyectos
        const projects = await res.json();
        const container = document.getElementById('user-projects');
        container.innerHTML = '';

        if(projects.length === 0){
            container.innerHTML = '<p style="text-align:center; color:#394867;">No hi ha projectes registrats.</p>';
            return;
        }

        projects.forEach(project => {
            const card = document.createElement('div');
            card.className = 'project-card';

            card.innerHTML = `
                <div class="project-title">${project.title}</div>
                <div class="media-wrapper" style="position:relative; width:100%; height:auto;">
                    <img src="${project.image}" class="project-image" id="img${project.id}" style="display:block; width:100%; height:auto; cursor:pointer;">
                    ${project.video ? `<video id="video${project.id}" controls style="display:none; width:100%; height:auto;"></video>` : ''}
                </div>
                <div class="project-buttons">
                    ${project.deleted
                        ? `<a href="#" class="btn btn-restore">Recuperar</a>`
                        : `<a href="#" class="btn btn-delete" onclick="return confirm('Segur que vols eliminar aquest projecte?')">Eliminar</a>`
                    }
                    ${project.video ? `<a href="javascript:void(0)" class="btn btn-preview" onclick="toggleVideo('video${project.id}', 'img${project.id}', '${project.video}')">Preview</a>` : ''}
                </div>
            `;


            container.appendChild(card);
        });

    } catch (e) {
        console.error("Error cargando proyectos:", e);
        document.getElementById('user-projects').innerHTML = '<p style="text-align:center; color:#FF3B3B;">Error cargando proyectos.</p>';
    }
}
function toggleVideo(videoId, imgId, videoSrc) {
    const video = document.getElementById(videoId);
    const img = document.getElementById(imgId);

    if (video.style.display === 'block') {
        // Ocultar video y mostrar imagen
        video.pause();
        video.style.display = 'none';
        img.style.display = 'block';
    } else {
        // Mostrar video y ocultar imagen
        const sourceElement = video.querySelector('source');
        if(!sourceElement || sourceElement.src !== videoSrc){
            video.innerHTML = `<source src="${videoSrc}" type="video/mp4">`;
            video.load();
        }
        img.style.display = 'none';
        video.style.display = 'block';
        video.play();
    }
}

// Cargar los proyectos al iniciar
loadProjects();
</script>
</body>
</html>