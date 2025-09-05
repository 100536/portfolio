<?php
// php/logout.php
session_start();
$_SESSION = [];
session_destroy();
header('Location: ../html/login.html?status=ok');
exit;
