<?php
require_once '/../includes/db.php';
require_once '/../includes/logger.php';

logout();
header('Location: /admin/login.php');
exit;
