<?php
require_once 'includes/auth.php';

logout();
header('Location: discover.php');
exit;
