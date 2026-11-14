<?php 
session_start();

require_once __DIR__. "/funciones.php";
require_once __DIR__. '/conexion.php';

$errores = [];
$email = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD']==='POST'){
$email = trim($_POST['email'] ?? '');
$password = ($_POST['password'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
$errores[] = 'Ingresá un correo válido.';
}
if ($password === '') {
$errores[] = 'La contraseña es obligatoria.';
}

if (empty($errores)){
    try{
    $sql = 'SELECT id, nombre, email, password, rol, fecha_archivado FROM usuarios WHERE email = :email LIMIT 1';
    $stmt = $conexionbd->prepare($sql);
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario !== false && password_verify($password, $usuario['password']) ){
        
        if($usuario['fecha_archivado'] !== NULL){
            $errores[]= "Tu cuenta ha sido archivada. Si crees que esto es un error o deseas reactivarla, por favor ponte en contacto con el administrador del sitio o el soporte técnico.";
        }else {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];

            if ($usuario['rol'] === 'admin') {
            header('Location: admin_usuarios.php');
            } else {
            header('Location: panel.php');
            }
            exit;
        }
    }else{
        $errores[] = 'Email o contraseña incorrectos.';
    }
    }catch (PDOException $e) {
            error_log($e->getMessage());
            $errores[] = 'Ocurrió un error. Intentá nuevamente.';
        }
}
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
    <?php if ($mensaje !== ''): ?>
    <p><?= escapar($mensaje) ?></p>
    <?php endif; ?>
    <?php if (!empty($errores)): ?>
    <ul>
    <?php foreach ($errores as $error): ?>
    <li class="errores"><?= escapar($error) ?></li>
    <?php endforeach; ?>
    </ul>
    <?php endif; ?>


    <form action="login.php" method="post" class="formulario">
        <label for="email">Correo electrónico:</label>
        <input type="email" id="email" name="email" maxlength="254" value="<?= escapar($email) ?>" required>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" maxlength="30" required>

        <button type="submit">Iniciar sesion</button>

        <p><a href="registro.php">Registrate</a></p>
    </form>

</main> 
</body>
</html>


