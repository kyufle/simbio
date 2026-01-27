<?php
require_once '/../includes/db.php';
require_once '/../includes/logger.php';

logout();
header('Location: index.php');
exit;
