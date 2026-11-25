<?php
require_once 'conexion.php';
verificarSesion();

$destino = 'index.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // filter_input rechaza IDs y cantidades que no sean enteros válidos.
    // Conserva la página y búsqueda actuales para que el usuario vuelva al mismo lugar.
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]);

    $query = [];
    parse_str((string)($_POST['volver'] ?? ''), $previo);
    if (!empty($previo['pagina'])) $query['pagina'] = (int)$previo['pagina'];
    if (!empty($previo['buscar'])) $query['buscar'] = (string)$previo['buscar'];
    if ($query) $destino .= '?' . http_build_query($query);

    $calzado = null;
    if ($id) {
        $consulta = $pdo->prepare('SELECT id, modelo, precio FROM calzados WHERE id = :id');
        $consulta->execute(['id' => $id]);
        $calzado = $consulta->fetch();
    }

    if ($calzado && $cantidad) {
        // Inserta una copia histórica de modelo y precio para el comprobante futuro.
        $insertar = $pdo->prepare(
            'INSERT INTO compras (calzado_id, modelo, cantidad, precio_unitario, fecha_compra)
             VALUES (:calzado_id, :modelo, :cantidad, :precio, :fecha)'
        );
        $insertar->execute([
            'calzado_id' => $calzado['id'],
            'modelo' => $calzado['modelo'],
            'cantidad' => $cantidad,
            'precio' => $calzado['precio'],
            'fecha' => date('Y-m-d H:i:s'),
        ]);
        $_SESSION['flash'] = 'Compra registrada: ' . $cantidad . ' × ' . $calzado['modelo'];
    } else {
        $_SESSION['flash_error'] = 'No se pudo registrar la compra. Revisa el producto y la cantidad.';
    }
}
header('Location: ' . $destino);
exit;
