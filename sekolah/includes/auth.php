<?php
if(session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

function is_logged_in(){ return isset($_SESSION['user']); }
function current_user(){ return $_SESSION['user'] ?? null; }

function require_login(){ if(!is_logged_in()){ header('Location: /login.php'); exit; } }
function require_role($role){ require_login(); if(current_user()['role'] !== $role){ http_response_code(403); echo 'Akses ditolak.'; exit; } }
?>