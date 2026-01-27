<?php
require_once '/../includes/auth.php';
require_once '/../includes/logger.php';

logoutAdmin();
header('Location: login.php', true, 302);
exit();
