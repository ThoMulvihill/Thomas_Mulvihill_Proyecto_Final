<?php

session_start();

require_once __DIR__. "/conexion.php";
require_once __DIR__. "/funciones.php";

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$errores = [];
$denominacion = '';
$descripcion = '';
$metal = '';
$anio = '';
$pais = '';
$estado = '';
$mensaje = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$denominacion = trim($_POST['denominacion'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$metal = trim($_POST['metal'] ?? '');
$anio = trim($_POST['anio'] ?? '');
$pais = trim($_POST['pais'] ?? '');
$estado = trim($_POST['estado'] ?? '');

if (!preg_match('/^[0-9]{1,4}$/', $anio) || (int) $anio < 1 || (int) $anio > (int) date('Y')) {
    $errores[] = 'El año debe ser un número entre 1 y ' . date('Y') . '.';
}

if (empty($errores)) {
try {
    $sql = 'INSERT INTO coleccion (usuario_id, denominacion, anio, pais, metal, estado, descripcion) VALUES (:usuario_id,:denominacion, :anio, :pais, :metal, :estado, :descripcion)';
    $stmt = $conexionbd->prepare($sql);
    $stmt->execute([
        'usuario_id'   => $_SESSION['usuario_id'],
        'denominacion' => $denominacion,
        'metal' => $metal,
        'anio' => (int) $anio,
        'pais' => $pais,
        'estado' => $estado,
        'descripcion' => $descripcion,
    ]);
    $mensaje = 'Moneda registrada correctamente.';
    $denominacion = '';
    $descripcion = '';
    $metal = '';
    $anio = '';
    $pais = '';
    $estado = '';
} catch (PDOException $e) {
    error_log($e->getMessage());
    $errores[] = 'Ocurrió un error al guardar los datos.';
}


if ($mensaje === 'Moneda registrada correctamente.'){
    header("Location: panel.php");
    exit;
}
}
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar moneda</title>
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
    <?php if ($mensaje !== ''): ?>
    <p><?= escapar($mensaje) ?></p>
    <?php endif; ?>
    <?php if (!empty($errores)): ?>
    <ul  class="errores">
    <?php foreach ($errores as $error): ?>
    <li><?= escapar($error) ?></li>
    <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <h1>Registrar nueva moneda</h1>

    <form action="coleccion_crear.php" method="POST" novalidate class="formulario">
    <label for="denominacion">Denominacion:</label>
    <input type="text" id="denominacion" name="denominacion" maxlength="100" value="<?= escapar($denominacion)?>" >

    <label for="anio">Año:</label>
    <input type="text" id="anio" name="anio" maxlength="6" value="<?= escapar($anio) ?>" >

    <label for="pais">País:</label>
    <input type="text" id="pais" name="pais" maxlength="80" value="<?= escapar($pais) ?>" >
    
    <label for="metal">Metal:</label>
    <input type="text" id="metal" name="metal" maxlength="50" value="<?= escapar($metal) ?>" >

    <label for="estado">Estado:</label>
    <input type="text" id="estado" name="estado" maxlength="30" value="<?= escapar($estado) ?>" >

    <label for="descripcion">Descripción:</label>
    <textarea id="descripcion" name="descripcion" maxlength="255"><?= escapar($descripcion) ?></textarea>

    <button type="submit">Guardar</button>
    <a href="panel.php">Cancelar</a>
</form>
</main>   
</body>
</html>