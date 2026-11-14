<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__. "/conexion.php";
require_once __DIR__. "/funciones.php";

if (!isset($_SESSION['usuario_id'])){
    header("Location: login.php");
    exit;
}

$error = '';
$coleccion = false;
$buscar = trim($_GET['buscar'] ?? '');

try{
    $stmt = $conexionbd->prepare('SELECT id, denominacion, anio, pais, metal, estado, descripcion FROM coleccion WHERE usuario_id = :usuario_id AND fecha_archivado IS NULL ORDER BY denominacion');
    $stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
    $coleccion = $stmt->fetchAll();
}catch (PDOException $e){
    error_log($e->getMessage());
    $error = 'No se pudo cargar tu colección.';
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

<main>

    <form method="GET" action="panel.php" class="buscador">
    <input type="search" name="buscar" maxlength="100" placeholder="Denominación, año, país, etc." value="<?= escapar($buscar) ?>">
    <button type="submit">Buscar</button>
        <?php if ($buscar !== ''): ?>
        <a href="panel.php">Limpiar búsqueda</a>
    <?php endif; ?>
    </form>

<?php if ($error !== ''): ?>
    <p>Falló la consulta.</p>
<?php elseif (empty($coleccion)): ?>
    <p>No se encontraron monedas en tu colección.</p>
<?php else: ?>
    <div class="table-scroll">
        <table class="tabla">
            <thead>
            <tr>
                <th>Denominación</th>
                <th>Año</th>
                <th>Pais</th>
                <th>Metal</th>
                <th>Estado</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
        <?php foreach ($coleccion as $moneda): ?>
            <tr>
                <td><?= escapar($moneda['denominacion']) ?></td>
                <td><?= escapar($moneda['anio']) ?></td>
                <td><?= escapar($moneda['pais']) ?></td>
                <td><?= escapar($moneda['metal']) ?? ''?></td>
                <td><?= escapar($moneda['estado']) ?? ''?></td>
                <td><?= escapar($moneda['descripcion']) ?? ''?></td>
                <td>
                    <a href="coleccion_editar.php?id=<?= (int) $moneda['id'] ?>">Editar</a>

                    <form method="POST" action="coleccion_archivar.php" onsubmit="return confirm('Apartar esta moneda?');">
                        <input type="hidden" name="id" value="<?= (int) $moneda['id'] ?>">
                        <button type="submit" class="peligro">Apartar</button>
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