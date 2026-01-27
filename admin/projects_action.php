<?php
    // Acción eliminar o recuperar
    if (isset($_GET['action'], $_GET['id'])) {
        $project_id = (int)$_GET['id'];
        if ($_GET['action'] === 'delete') {
            $stmt = $db->prepare("UPDATE projects SET deleted=1 WHERE project_id=?");
            $stmt->execute([$project_id]);
        } elseif ($_GET['action'] === 'restore') {
            $stmt = $db->prepare("UPDATE projects SET deleted=0 WHERE project_id=?");
            $stmt->execute([$project_id]);
        }
    }
?>