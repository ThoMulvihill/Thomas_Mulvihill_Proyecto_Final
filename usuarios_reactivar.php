<?php

session_start();

require_once __DIR__. "/conexion.php";
require_once __DIR__. "/funciones.php";

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'admin'){
    http_response_code(403);
    exit('Acceso denegado.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_usuarios.php');
    exit;
}

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

if ($id === false || $id <= 0) {
    header('Location: admin_usuarios.php');
    exit;
}

if ($id === (int) $_SESSION['usuario_id']) {
    header('Location: admin_usuarios.php');
    exit;
}

try {
    $stmt = $conexionbd->prepare('UPDATE usuarios SET fecha_archivado = NULL WHERE id = :id');
    $stmt->execute(['id' => $id]);
}catch (PDOException $e){
    error_log($e->getMessage());
}

header('Location: admin_usuarios.php');
exit;