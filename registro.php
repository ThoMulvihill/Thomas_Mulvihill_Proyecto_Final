<?php

require_once __DIR__. "/conexion.php";

$errores = [];
$nombre = '';
$apellido = '';
$dni = '';
$email = '';
$password = '';
$conf_password = '';
$mensaje = '';

require_once __DIR__ . '/funciones.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$dni = trim($_POST['dni'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = ($_POST['password'] ?? '');
$conf_password = ($_POST['conf_password'] ?? '');
if ($nombre === '') {
$errores[] = 'El nombre es obligatorio.';
}
if ($apellido === '') {
$errores[] = 'El apellido es obligatorio.';
}
if ($dni === '' || !preg_match('/^[0-9]{7,8}$/', $dni)) {
$errores[] = 'El DNI debe tener 7 u 8 números.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
$errores[] = 'Ingresá un correo válido.';
}
if ($password === '') {
$errores[] = 'La contraseña es obligatoria.';
}elseif(mb_strlen($password, "UTF-8")<8){
$errores[] = 'La contraseña debe tener al menos 8 digitos.';
}
if ($conf_password !== $password) {
$errores[] = 'Las contraseñas no coinciden';
}

if (empty($errores)) {
    try {
        $stmt = $conexionbd->prepare('SELECT id FROM usuarios WHERE dni = :dni');
        $stmt->execute(['dni' => $dni]);
        if ($stmt->fetch()) {
            $errores[] = 'Ese DNI ya está registrado.';
        }
        $stmt = $conexionbd->prepare('SELECT id FROM usuarios WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errores[] = 'Ese correo ya está registrado.';
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $errores[] = 'Ocurrió un error al verificar los datos.';
    }
}

$passwordhash = password_hash($password, PASSWORD_DEFAULT);

if (empty($errores)) {
try {
    $sql = 'INSERT INTO usuarios (nombre, apellido, dni, email, password) VALUES (:nombre, :apellido, :dni, :email, :passwordhash)';
    $stmt = $conexionbd->prepare($sql);
    $stmt->execute([
        'nombre' => $nombre,
        'apellido' => $apellido,
        'dni' => $dni,
        'email' => $email,
        'passwordhash' => $passwordhash,
    ]);
    $mensaje = 'Usuario registrado correctamente.';
    $nombre = '';
    $apellido = '';
    $dni = '';
    $email = '';
    $password = '';
    $conf_password = '';
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
    $errores[] = 'Ese DNI o el correo ya está registrado.';
    } else {
    error_log($e->getMessage());
    $errores[] = 'Ocurrió un error al guardar los datos.';
    }
}
}
}

if ($mensaje === 'Usuario registrado correctamente.'){
    header("Location: panel.php");
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
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

    <form action="registro.php" method="POST" novalidate class="formulario">
    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre" maxlength="80" value="<?= escapar($nombre)?>" required>

    <label for="apellido">Apellido:</label>
    <input type="text" id="apellido" name="apellido" maxlength="80" value="<?= escapar($apellido) ?>" required>

    <label for="dni">DNI:</label>
    <input type="text" id="dni" name="dni" inputmode="numeric" pattern="[0-9]{7,8}" maxlength="8" value="<?= escapar($dni) ?>" required>
    
    <label for="email">Correo electrónico:</label>
    <input type="email" id="email" name="email" maxlength="254" value="<?= escapar($email) ?>" required>

    <label for="password">Contraseña:</label>
    <input type="password" id="password" name="password" maxlength="30" required>
    <p>-Incluir numeros, letras mayusculas y minusculas y caracteres para mayor seguridad</p>    

    <label for="conf_password">Confirmar contraseña:</label>
    <input type="password" id="conf_password" name="conf_password" maxlength="30" required>
    
    <button type="submit">Registrarse</button>
</form>

</main> 
</body>
</html>