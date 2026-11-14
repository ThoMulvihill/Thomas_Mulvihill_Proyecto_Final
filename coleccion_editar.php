<?php

session_start();

require_once __DIR__. "/conexion.php";
require_once __DIR__. "/funciones.php";

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$esPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$id = filter_var($esPost ? ($_POST['id'] ?? '') : ($_GET['id'] ?? ''), FILTER_VALIDATE_INT);

if ($id === false || $id <= 0) {
    header('Location: panel.php');
    exit;
}

$coleccion = false;
try {
    $stmt = $conexionbd->prepare('SELECT id, denominacion, anio, pais, metal, estado, descripcion FROM coleccion WHERE id = :id AND usuario_id = :usuario_id AND fecha_archivado IS NULL LIMIT 1');
    $stmt->execute(['id' => $id, 'usuario_id' => $_SESSION['usuario_id']]);
    $coleccion = $stmt->fetch();
} catch (PDOException $e) {
    error_log($e->getMessage());
}

if ($coleccion === false) {
    header('Location: panel.php');
    exit;
}

$errores = [];
$denominacion = $coleccion['denominacion'];
$anio = $coleccion['anio'];
$pais = $coleccion['pais'];
$metal = $coleccion['metal'] ?? '';
$estado = $coleccion['estado'] ?? '';
$descripcion = $coleccion['descripcion'] ?? '';


if ($esPost){
    $denominacion = trim($_POST['denominacion'] ?? '');
    $anio = trim($_POST['anio'] ?? '');
    $pais = trim($_POST['pais'] ?? '');
    $metal = trim($_POST['metal'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (!preg_match('/^[0-9]{1,4}$/', $anio) || (int) $anio < 1 || (int) $anio > (int) date('Y')) {
        $errores[] = 'El año debe ser un número entre 1 y ' . date('Y') . '.';
    }
    if (empty($errores)) {
        try {
            $datos = [
                'denominacion' => $denominacion,
                'anio' => (int) $anio,
                'pais' => $pais,
                'metal' => $metal,
                'estado' => $estado,
                'descripcion' => $descripcion,
                'id' => $id,
                'usuario_id' => $_SESSION['usuario_id'],
            ];
            $sql = 'UPDATE coleccion SET denominacion = :denominacion, anio = :anio, pais = :pais, metal = :metal, estado = :estado, descripcion = :descripcion WHERE id = :id AND usuario_id = :usuario_id';
            $stmt = $conexionbd->prepare($sql);
            $stmt->execute($datos);

            header('Location: panel.php');
            exit;
        } catch (PDOException $e) {
                error_log($e->getMessage());
                $errores[] = 'Ocurrió un error al guardar los datos.';
            }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de monedas activos</title>
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
<h1>Editar monedas</h1>

<?php if (!empty($errores)): ?>
    <ul class="errores">
        <?php foreach ($errores as $error): ?>
        <li ><?= escapar($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

    <form action="coleccion_editar.php" method="POST" class="formulario">
        <input type="hidden" name="id" value="<?= (int) $id ?>">

        <label for="denominacion">Denominacion:</label>
        <input type="text" id="denominacion" name="denominacion" maxlength="80" value="<?= escapar($denominacion) ?>" >

        <label for="anio">Año:</label>
        <input type="text" id="anio" name="anio" maxlength="4" value="<?= escapar($anio) ?>" >

        <label for="pais">País:</label>
        <input type="text" id="pais" name="pais" maxlength="100" value="<?= escapar($pais) ?>" >

        <label for="metal">Metal:</label>
        <input type="text" id="metal" name="metal" maxlength="100" value="<?= escapar($metal) ?>" >

        <label for="estado">Estado:</label>
        <input type="text" id="estado" name="estado" maxlength="100" value="<?= escapar($estado) ?>" >

        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" maxlength="255"><?= escapar($descripcion) ?></textarea>

        <button type="submit">Guardar cambios</button>
        <a href="panel.php">Cancelar</a>
    </form>
</main> 
</body>
</html>