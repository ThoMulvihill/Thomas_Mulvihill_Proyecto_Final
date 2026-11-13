<?php

session_start();
require_once __DIR__. "/conexion.php";
require_once __DIR__. "/funciones.php";

if (!isset($_SESSION['usuario_id'])){
    header("Location: login.php");
    exit;
}

$usuario = false;
try{
    $stmt = $conexionbd->prepare('SELECT nombre, apellido, dni, email, rol, fecha_creacion FROM usuarios WHERE id = :id AND fecha_archivado IS NULL LIMIT 1');
    $stmt->execute(['id' => $_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
}catch (PDOException $e){
    error_log($e->getMessage());
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
    <?php if ($usuario !== false): ?>
    <div class="table-scroll">
        <table class="table">
            <thead>
            <tr>
                <th>Nombre</th>
                <th>DNI</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Miembro desde</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?= escapar($usuario['nombre']), " ", escapar($usuario['apellido']) ?></td>
                <td><?= escapar($usuario['dni']) ?></td>
                <td><?= escapar($usuario['email']) ?></td>
                <td><?= escapar($usuario['rol']) ?></td>
                <td><?= escapar($usuario['fecha_creacion']) ?></td>
            </tr>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p>No se pudieron cargar tus datos.</p>
    <?php endif; ?>
</main> 
</body>
</html>