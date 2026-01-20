<?php
require_once 'auth.php';
header('Content-Type: application/json; charset=utf-8');

// Si no està connectat, redirigeix a login.php
if (!isLogged()) {
    header('Location: login.php');
    exit;
}

global $conn;
try {
    $stmt = $conn->prepare("SELECT project_id, title, description, project.image_path, video_path, project.user_id, entity, type FROM project join user on project.user_id = user.user_id where video_path is not null;");
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $allProjects = []; 
    foreach ($projects as $project) {
        $mappedProject = array();
        $mappedProject['id'] = $project['project_id'];
        $mappedProject['entity'] = $project['entity'];
        $mappedProject['type'] = $project['type'];
        $mappedProject['title'] = $project['title'];
        $mappedProject['description'] = $project['description'];
        $mappedProject['image'] = "/uploads/".$project['image_path'];
        $mappedProject['video'] = "/uploads/".$project['video_path'];

        $statementTag = $conn->prepare("SELECT name FROM project_tags join tag on project_tags.tag_id = tag.tag_id where project_id = :project_id;");
        $statementTag->bindParam(':project_id', $mappedProject['id'] , PDO::PARAM_STR);
        $statementTag->execute();
        $tags = $statementTag->fetchAll(PDO::FETCH_COLUMN);
        // tags
        $mappedProject['tags'] = $tags;
        array_push($allProjects, $mappedProject);
    }

    echo json_encode($allProjects);
} catch (PDOException $e) {
    die("Error de base de datos");
}
?>