<?php

session_start();

require_once __DIR__. "/conexion.php";
require_once __DIR__. "/funciones.php";

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: panel.php');
    exit;
}

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
if ($id === false || $id <= 0) {
    header('Location: coleccion_archivados.php');
    exit;
}


try {
    $stmt = $conexionbd->prepare('UPDATE coleccion SET fecha_archivado = NULL WHERE id = :id AND usuario_id = :usuario_id AND fecha_archivado IS NOT NULL');
    $stmt->execute(['id' => $id, 'usuario_id' => $_SESSION['usuario_id']]);
}catch (PDOException $e){
    error_log($e->getMessage());
}

header('Location: coleccion_archivados.php');
exit;