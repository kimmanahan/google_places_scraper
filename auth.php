<?php
$admin_user = 'YOURNAME';
$admin_pass = 'YOURPASSWORD';

if (!isset($_SERVER['PHP_AUTH_USER']) ||
    $_SERVER['PHP_AUTH_USER'] !== $admin_user ||
    $_SERVER['PHP_AUTH_PW']   !== $admin_pass) {
    header('WWW-Authenticate: Basic realm="MyAttorneyList Admin"');
    header('HTTP/1.0 401 Unauthorized');
    die('Unauthorized');
}
