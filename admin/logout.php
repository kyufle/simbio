<?php
require_once '/../includes/db.php';
require_once '/../includes/logger.php';

logout();
header('Location: login.php');
exit;
