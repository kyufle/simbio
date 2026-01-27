<?php
require_once '/../includes/db.php';
require_once '/../includes/logger.php';

logoutAdmin();
header('Location: login.php');
exit;
