<?php 

session_start();

require_once __DIR__. "/conexion.php";
require_once __DIR__. "/funciones.php";

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'admin'){
    http_response_code(403);
    exit('Acceso denegado.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: usuarios_archivados.php');
    exit;
}

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

if ($id === false || $id <= 0) {
    header('Location: usuarios_archivados.php');
    exit;
}

if ($id === (int) $_SESSION['usuario_id']) {
    header('Location: usuarios_archivados.php');
    exit;
}

try {
    $stmt = $conexionbd->prepare('DELETE FROM usuarios WHERE id = :id AND fecha_archivado IS NOT NULL');
    $stmt->execute(['id' => $id]);
}catch (PDOException $e){
    error_log($e->getMessage());
}

header('Location: usuarios_archivados.php');
exit;