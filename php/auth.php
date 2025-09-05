<?php
// php/auth.php
session_start();

// Config
const SALT = 'LA-2025::';
const USER_HASH_HEX = '4e818388f2f7c31f911c0625d5cd781e5322bf635e2d870044319d6ad28fca6e'; // sha256(SALT . "LucasA")
const PASS_HASH_HEX = '98f4d4ebd0c479d4de82abe168b123ad625217732281fe85a381ea2520d6a0d3'; // sha256(SALT . "123")

function hash_hex($s) {
  return hash('sha256', $s);
}
function timing_safe_equal($a, $b) {
  if (strlen($a) !== strlen($b)) return false;
  $res = 0;
  for ($i = 0; $i < strlen($a); $i++) { $res |= ord($a[$i]) ^ ord($b[$i]); }
  return $res === 0;
}

// Alleen POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

$user = trim($_POST['user'] ?? '');
$pass = (string)($_POST['password'] ?? '');

// Honeypot (optioneel)
if (!empty($_POST['website'] ?? '')) {
  header('Location: ../html/login.html?status=error'); exit;
}

$user_ok = timing_safe_equal(hash_hex(SALT . $user), USER_HASH_HEX);
$pass_ok = timing_safe_equal(hash_hex(SALT . $pass), PASS_HASH_HEX);

if ($user_ok && $pass_ok) {
  $_SESSION['auth'] = true;
  header('Location: ../html/admin.php');
  exit;
}

header('Location: ../html/login.html?status=error');
exit;
