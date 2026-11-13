<?php

session_start();

require_once __DIR__. "/conexion.php";
require_once __DIR__. "/funciones.php";

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'admin'){
    http_response_code(403);
    exit('Acceso denegado.');
}

$esPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$id = filter_var($esPost ? ($_POST['id'] ?? '') : ($_GET['id'] ?? ''), FILTER_VALIDATE_INT);

if ($id === false || $id <= 0) {
    header('Location: admin_usuarios.php');
    exit;
}

$usuario = false;
try {
    $stmt = $conexionbd->prepare('SELECT id, nombre, apellido, dni, email, rol FROM usuarios WHERE id = :id AND fecha_archivado IS NULL LIMIT 1');
    $stmt->execute(['id' => $id]);
    $usuario = $stmt->fetch();
} catch (PDOException $e) {
    error_log($e->getMessage());
}

if ($usuario === false) {
    header('Location: admin_usuarios.php');
    exit;
}

$errores = [];
$nombre = $usuario['nombre'];
$apellido = $usuario['apellido'];
$dni = $usuario['dni'];
$email = $usuario['email'];
$rol = $usuario['rol'];


if ($esPost){
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol = $_POST['rol'] ?? '';
    $password = $_POST['password'] ?? '';
    $conf_password = $_POST['conf_password'] ?? '';

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }
    if ($apellido === '') {
        $errores[] = 'El apellido es obligatorio.';
    }
    if (!preg_match('/^[0-9]{7,8}$/', $dni)) {
        $errores[] = 'El DNI debe tener 7 u 8 números.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo válido.';
    }
    if (!in_array($rol, ['usuario', 'admin'], true)) {
        $errores[] = 'El rol no es válido.';
    }
    if ($id === (int) $_SESSION['usuario_id'] && $rol !== 'admin') {
        $errores[] = 'No podés quitarte a vos mismo el rol de administrador.';
    }
    if ($password !== '') {
        if (mb_strlen($password) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if ($password !== $conf_password) {
            $errores[] = 'Las contraseñas no coinciden.';
        }
    }


    if (empty($errores)) {
        try {
            $stmt = $conexionbd->prepare('SELECT id FROM usuarios WHERE dni = :dni AND id <> :id');
            $stmt->execute(['dni' => $dni, 'id' => $id]);
            if ($stmt->fetch()) {
                $errores[] = 'Ese DNI ya está registrado.';
            }

            $stmt = $conexionbd->prepare('SELECT id FROM usuarios WHERE email = :email AND id <> :id');
            $stmt->execute(['email' => $email, 'id' => $id]);
            if ($stmt->fetch()) {
                $errores[] = 'Ese correo ya está registrado.';
            }
        } catch (PDOException $e){
            error_log($e->getMessage());
            $errores[] = 'Ocurrió un error al verificar los datos.';
        }
    }


    if (empty($errores)) {
        try {
            $datos = [
                'nombre' => $nombre,
                'apellido' => $apellido,
                'dni' => $dni,
                'email' => $email,
                'rol' => $rol,
                'id' => $id,
            ];

            if ($password !== '') {
                $sql = 'UPDATE usuarios SET nombre = :nombre, apellido = :apellido, dni = :dni, email = :email, rol = :rol, password = :password WHERE id = :id';
                $datos['password'] = password_hash($password, PASSWORD_DEFAULT);
            } else {
                $sql = 'UPDATE usuarios SET nombre = :nombre, apellido = :apellido, dni = :dni, email = :email, rol = :rol WHERE id = :id';
            }

            $stmt = $conexionbd->prepare($sql);
            $stmt->execute($datos);

            if ($id === (int) $_SESSION['usuario_id']) {
                $_SESSION['usuario_nombre'] = $nombre;
            }

            header('Location: admin_usuarios.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errores[] = 'El DNI o el correo ya están registrados.';
            } else {
                error_log($e->getMessage());
                $errores[] = 'Ocurrió un error al guardar los datos.';
            }
        }
    }
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
<h1>Editar usuario</h1>

<?php if (!empty($errores)): ?>
    <ul>
        <?php foreach ($errores as $error): ?>
        <li class="errores"><?= escapar($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

    <form action="usuarios_editar.php" method="POST" class="formulario">
        <input type="hidden" name="id" value="<?= (int) $id ?>">

        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" maxlength="80" value="<?= escapar($nombre) ?>" required>

        <label for="apellido">Apellido:</label>
        <input type="text" id="apellido" name="apellido" maxlength="80" value="<?= escapar($apellido) ?>" required>

        <label for="dni">DNI:</label>
        <input type="text" id="dni" name="dni" inputmode="numeric" pattern="[0-9]{7,8}" maxlength="8" value="<?= escapar($dni) ?>" required>

        <label for="email">Correo electrónico:</label>
        <input type="email" id="email" name="email" maxlength="254" value="<?= escapar($email) ?>" required>

        <label for="rol">Rol:</label>
        <select id="rol" name="rol">
            <option value="usuario" <?= $rol === 'usuario' ? 'selected' : '' ?>>usuario</option>
            <option value="admin" <?= $rol === 'admin' ? 'selected' : '' ?>>admin</option>
        </select>

        <label for="password">Nueva contraseña (dejar vacío para no cambiarla):</label>
        <input type="password" id="password" name="password" maxlength="30">

        <label for="conf_password">Confirmar nueva contraseña:</label>
        <input type="password" id="conf_password" name="conf_password" maxlength="30">

        <button type="submit">Guardar cambios</button>
        <a href="admin_usuarios.php">Cancelar</a>
    </form>
</main> 
</body>
</html>