<?php

session_start();

require_once __DIR__. "/conexion.php";
require_once __DIR__. "/funciones.php";


$error = '';
$coleccion = [];
$mensaje = '';
$buscar = trim($_GET['buscar'] ?? '');
$parametros = [];

try { 
    $sql = 'SELECT id, denominacion, anio, pais, metal, estado, descripcion, fecha_archivado FROM coleccion WHERE usuario_id = :usuario_id AND fecha_archivado is not NULL';
    $parametros = ['usuario_id' => $_SESSION['usuario_id']];

    if ($buscar !== '') {
    $sql .= ' AND (denominacion LIKE :b1 OR anio LIKE :b2 OR pais LIKE :b3 OR metal LIKE :b4 OR estado LIKE :b5 OR descripcion LIKE :b6)';
    $like = '%'. $buscar. '%';
    $parametros = array_merge($parametros,['b1' => $like, 'b2' => $like, 'b3' => $like, 'b4' => $like, 'b5' => $like, 'b6' => $like]);
    }
    $sql .= ' ORDER BY denominacion'; 

$stmt = $conexionbd->prepare($sql);
$stmt->execute($parametros);
$coleccion = $stmt->fetchAll();
}catch (PDOException $e){
    error_log($e->getMessage());
    $error = 'No se pudo cargar el listado de monedas.';
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de monedas apartadas</title>
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
    <h1>Monedas apartadas</h1>
    
    <form method="GET" action="coleccion_archivados.php" class="buscador">
    <input type="search" name="buscar" maxlength="100" placeholder="Denominación, año, país, etc." value="<?= escapar($buscar) ?>">
    <button type="submit">Buscar</button>
        <?php if ($buscar !== ''): ?>
        <a href="coleccion_archivados.php">Limpiar búsqueda</a>
    <?php endif; ?>
    </form>

    <?php if ($error !== ''): ?>
        <p class="errores"><?= escapar($error) ?></p>
    <?php endif; ?>

    <?php if (empty($coleccion)): ?>
        <p>No se encontraron monedas para: "<?= escapar($buscar) ?>"</p>
    <?php else: ?>
    <div class="table-scroll">
        <table class="table">
            <thead>
            <tr>
                <th>Denominación</th>
                <th>Año</th>
                <th>Pais</th>
                <th>Metal</th>
                <th>Estado</th>
                <th>Descripción</th>
                <th>Archivado desde</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($coleccion as $moneda): ?>
            <tr>
                <td><?= escapar($moneda['denominacion']) ?></td>
                <td><?= escapar($moneda['anio']) ?></td>
                <td><?= escapar($moneda['pais']) ?></td>
                <td><?= escapar($moneda['metal']) ?></td>
                <td><?= escapar($moneda['estado']) ?></td>
                <td><?= escapar($moneda['descripcion']) ?></td>
                <td><?= escapar($moneda['fecha_archivado']) ?></td>
                <td>

                    <form method="POST" action="coleccion_reactivar.php" onsubmit="return confirm('Reactivar esta moneda?');">
                        <input type="hidden" name="id" value="<?= (int) $moneda['id'] ?>">
                        <button type="submit">Reactivar</button>
                    </form>

                    <form method="POST" action="coleccion_eliminar.php" onsubmit="return confirm('Eliminar esta moneda permanentemente?');">
                        <input type="hidden" name="id" value="<?= (int) $moneda['id'] ?>">
                        <button type="submit"  class="peligro">Eliminar</button>
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