<?php
require_once '/../includes/db.php';
require_once '/../includes/logger.php';

logoutAdmin();
header('Location: index.php');
exit;
