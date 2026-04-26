<?php
if (str_contains($_SERVER['REQUEST_URI'], '/api/admin.php')) {
    session_name('admin_session');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}