<?php
require_once 'includes/auth.php';
require_once 'includes/chat_service.php';

// Verificar que el usuario está logueado
if (!isLogged()) {
    header('Location: login.php');
    exit;
}

$currentUserId = $_SESSION['user']['id'];

// Obtener todas las conversaciones del usuario
$conversations = getUserConversations($currentUserId, 50);

// Obtener información del usuario actual
try {
    $stmt = $conn->prepare("
        SELECT user_id, name, surnames 
        FROM user 
        WHERE user_id = :user_id 
        LIMIT 1
    ");
    $stmt->execute([':user_id' => $currentUserId]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener usuario: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missatges - Simbio</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/conversations.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="conversations-container">
        <!-- Header -->
        <div class="conversations-header">
            <h1>💬 Els Meus Missatges</h1>
            <a href="discover.php" class="btn-back">← Tornar</a>
        </div>

        <!-- Lista de conversaciones -->
        <div class="conversations-list">
            <?php if (empty($conversations)): ?>
                <div class="empty-state">
                    <div class="empty-icon">💭</div>
                    <p>No tens cap conversació encara</p>
                    <a href="discover.php" class="btn btn-primary">Descobrir usuaris</a>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <a href="chat.php?user_id=<?php echo $conv['other_user_id']; ?>" class="conversation-item">
                        <div class="conversation-avatar">
                            <?php if (!empty($conv['image_path'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($conv['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($conv['name']); ?>">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <?php echo substr($conv['name'], 0, 1); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="conversation-info">
                            <div class="conversation-header">
                                <h3><?php echo htmlspecialchars($conv['name'] . ' ' . $conv['surnames']); ?></h3>
                                <span class="conversation-time">
                                    <?php 
                                        $lastTime = strtotime($conv['last_message_time']);
                                        $now = time();
                                        $diff = $now - $lastTime;
                                        
                                        if ($diff < 60) {
                                            echo "Ara";
                                        } elseif ($diff < 3600) {
                                            echo floor($diff / 60) . " min";
                                        } elseif ($diff < 86400) {
                                            echo floor($diff / 3600) . " h";
                                        } elseif ($diff < 604800) {
                                            echo floor($diff / 86400) . " d";
                                        } else {
                                            echo date('d/m', $lastTime);
                                        }
                                    ?>
                                </span>
                            </div>
                            <p class="conversation-entity">
                                <?php echo htmlspecialchars($conv['entity'] . ' (' . $conv['type'] . ')'); ?>
                            </p>
                            <p class="conversation-preview">
                                <?php echo htmlspecialchars(substr($conv['last_message_text'] ?? 'Sin mensajes', 0, 50)); ?>
                                <?php if (strlen($conv['last_message_text'] ?? '') > 50): ?>...<?php endif; ?>
                            </p>
                        </div>
                        
                        <div class="conversation-arrow">
                            →
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <link rel="stylesheet" href="css/chat.css?v=<?php echo time(); ?>">
</body>
</html>
