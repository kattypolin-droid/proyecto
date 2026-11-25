<?php
session_start();
// Vaciar el arreglo elimina usuario_id, usuario y cualquier mensaje temporal.
$_SESSION = [];
// Si se usan cookies, también expira la cookie con el nombre actual de la sesión.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
// Redirige a la pantalla pública de inicio de sesión después de cerrar todo.
header('Location: login.php');
exit;
