<?php

if (!isset($_SESSION['usuario_id']) || !isset($conexionbd)) {
    exit; 
}

$error = '';
$coleccion = [];
$buscar = trim($_GET['buscar'] ?? '');

try {
    $sql = 'SELECT id, denominacion, anio, pais, metal, estado, descripcion FROM coleccion WHERE usuario_id = :usuario_id AND fecha_archivado IS NULL';
    $parametros = ['usuario_id' => $_SESSION['usuario_id']];

    if ($buscar !== '') {
        $sql .= ' AND (denominacion LIKE :b1 OR anio LIKE :b2 OR pais LIKE :b3 OR metal LIKE :b4 OR estado LIKE :b5 OR descripcion LIKE :b6)';
        $like = '%' . $buscar . '%';
        $parametros = array_merge($parametros, [
            'b1' => $like, 'b2' => $like, 'b3' => $like,
            'b4' => $like, 'b5' => $like, 'b6' => $like,
        ]);
    }
    $sql .= ' ORDER BY denominacion';

    $stmt = $conexionbd->prepare($sql);
    $stmt->execute($parametros);
    $coleccion = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'No se pudo cargar tu colección.';
}
?>

<form method="GET" action="panel.php" class="buscador">
    <input type="search" name="buscar" maxlength="100"
           placeholder="Denominación, año, país, etc."
           value="<?= escapar($buscar) ?>">
    <button type="submit">Buscar</button>
    <?php if ($buscar !== ''): ?>
        <a href="panel.php">Limpiar búsqueda</a>
    <?php endif; ?>
</form>

<?php if ($error !== ''): ?>
    <p class="errores"><?= escapar($error) ?></p>

<?php elseif (empty($coleccion)): ?>
    <?php if ($buscar !== ''): ?>
        <p>No se encontraron monedas para "<?= escapar($buscar) ?>".
           <a href="panel.php">Ver todas</a></p>
    <?php else: ?>
        <p>Todavía no cargaste ninguna moneda.</p>
    <?php endif; ?>

<?php else: ?>
    <div class="tabla-scroll">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Denominación</th>
                    <th>Año</th>
                    <th>País</th>
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
                    <td><?= escapar((string) $moneda['anio']) ?></td>
                    <td><?= escapar($moneda['pais']) ?></td>
                    <td><?= escapar($moneda['metal'] ?? '') ?></td>
                    <td><?= escapar($moneda['estado'] ?? '') ?></td>
                    <td><?= escapar($moneda['descripcion'] ?? '') ?></td>
                    <td>
                        <a href="coleccion_editar.php?id=<?= (int) $moneda['id'] ?>">Editar</a>

                        <form method="POST" action="coleccion_archivar.php"
                              onsubmit="return confirm('¿Apartar esta moneda?');">
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