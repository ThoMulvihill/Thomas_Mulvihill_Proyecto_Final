<?php

session_start();
require_once __DIR__. "/conexion.php";
require_once __DIR__. "/funciones.php";

if (!isset($_SESSION['usuario_id'])){
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Numismatica</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="panel.php">Mi colección</a></li>
            <li><a href="mis_datos.php">Mis datos</a></li>
            <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                <li><a href="admin_usuarios.php">Administración</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Cerrar sesión</a></li>
        </ul>
    </nav>

<main>

<h1>Hola, <?= escapar($_SESSION['usuario_nombre']) ?>!</h1>

<h2>Mi colección de monedas</h2>


</main> 
</body>
</html>