<?php

session_start();

require_once __DIR__. "/conexion.php";
require_once __DIR__. "/funciones.php";

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'admin'){
    http_response_code(403);
    exit('Acceso denegado.');
}


$error = '';
$usuarios = [];
$mensaje = '';
$buscar = trim($_GET['buscar'] ?? '');
$parametros = [];

try { 
    $sql = 'SELECT id, nombre, apellido, dni, email, rol, fecha_creacion, fecha_archivado FROM usuarios WHERE fecha_archivado is NULL';

    if ($buscar !== '') {
    $sql .= ' AND (nombre LIKE :b1 OR apellido LIKE :b2 OR dni LIKE :b3 OR email LIKE :b4)';
    $like = '%'. $buscar. '%';
    $parametros = ['b1' => $like, 'b2' => $like, 'b3' => $like, 'b4' => $like];
    }
    $sql .= ' ORDER BY apellido, nombre';

$stmt = $conexionbd->prepare($sql);
$stmt->execute($parametros);
$usuarios = $stmt->fetchAll();
}catch (PDOException $e){
    error_log($e->getMessage());
    $error = 'No se pudo cargar el listado de usuarios.';
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de usuarios activos</title>
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
    <p>
        <a href="usuarios_crear.php">+ Nuevo usuario</a> |
        <a href="usuarios_archivados.php">Ver archivados</a>
    </p>

    <h1>Usuarios activos</h1>

    <form method="GET" action="admin_usuarios.php" class="buscador">
    <input type="search" name="buscar" maxlength="100" placeholder="Nombre, apellido, DNI o correo" value="<?= escapar($buscar) ?>">
    <button type="submit">Buscar</button>
    <?php if ($buscar !== ''): ?>
        <a href="admin_usuarios.php">Limpiar búsqueda</a>
    <?php endif; ?>
    </form>

    <?php if ($error !== ''): ?>
        <p class="errores"><?= escapar($error) ?></p>
    <?php endif; ?>

    <?php if (empty($usuarios)): ?>
        <p>No se encontraron usuarios para: "<?= escapar($buscar) ?>".</p>
    <?php else: ?>
    <div class="table-scroll">
        <table class="tabla">
            <thead>
            <tr>
                <th>Nombre</th>
                <th>DNI</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Miembro desde</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $user): ?>
            <tr>
                <td><?= escapar($user['nombre']), " ", escapar($user['apellido']) ?></td>
                <td><?= escapar($user['dni']) ?></td>
                <td><?= escapar($user['email']) ?></td>
                <td><?= escapar($user['rol']) ?></td>
                <td><?= escapar($user['fecha_creacion']) ?></td>
                <td>
                    <a href="usuarios_editar.php?id=<?= (int) $user['id'] ?>">Editar</a>

                    <form method="POST" action="usuarios_archivar.php" onsubmit="return confirm('¿Archivar este usuario?');">
                        <input type="hidden" name="id" value="<?= (int) $user['id'] ?>" class="peligro">
                        <button type="submit">Archivar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main> 

</body>

</html>
