<?php
// Conexión compartida por todas las páginas que acceden a MySQL.
session_start();
// America/Lima es la zona horaria usada por fechas y horas de la aplicación.
date_default_timezone_set('America/Lima');

// Valores locales de WAMP para el equipo de desarrollo.
$host = 'localhost';
$dbName = 'db_bata';
$dbUser = 'root';
$dbPass = '';
$charset = 'utf8mb4';

// DSN y opciones separadas permiten cambiar el servidor sin tocar las consultas.
$dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    // PDO abre la conexión y usa las opciones anteriores para controlar errores y filas.
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $error) {
    die('No se pudo conectar a la base de datos: ' . htmlspecialchars($error->getMessage()));
}

function verificarSesion(): void
{
    // La ausencia de usuario_id indica que no existe una sesión autenticada.
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}
