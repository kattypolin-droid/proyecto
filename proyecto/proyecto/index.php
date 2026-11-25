<?php
require_once 'conexion.php';
verificarSesion();

$porPagina = 8;
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$busqueda = trim($_GET['buscar'] ?? '');

// Construye FROM/JOIN y WHERE; el texto de búsqueda nunca se concatena a SQL.
if ($busqueda !== '') {
    $sqlBase = 'FROM calzados c INNER JOIN marcas m ON c.id_marca = m.id_marca
                WHERE c.modelo LIKE :buscar OR c.color LIKE :buscar OR m.nombre_marca LIKE :buscar';
    $params = ['buscar' => '%' . $busqueda . '%'];
} else {
    $sqlBase = 'FROM calzados c INNER JOIN marcas m ON c.id_marca = m.id_marca';
    $params = [];
}

// Cuenta coincidencias antes de calcular el rango permitido de paginación.
$contador = $pdo->prepare('SELECT COUNT(*) ' . $sqlBase);
$contador->execute($params);
$total = (int)$contador->fetchColumn();
$totalPaginas = max(1, (int)ceil($total / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

// LIMIT e OFFSET seleccionan solo los productos de la página actual.
$consulta = $pdo->prepare(
    'SELECT c.*, m.nombre_marca ' . $sqlBase .
    ' ORDER BY c.id DESC LIMIT :limite OFFSET :offset'
);
foreach ($params as $clave => $valor) {
    $consulta->bindValue($clave, $valor);
}
$consulta->bindValue('limite', $porPagina, PDO::PARAM_INT);
$consulta->bindValue('offset', $offset, PDO::PARAM_INT);
$consulta->execute();
$calzados = $consulta->fetchAll();

// Agrupa las imágenes alternativas por el ID del calzado al que pertenecen.
$imagenesPorCalzado = [];
if ($calzados) {
    $calzadoIds = array_column($calzados, 'id');
    $placeholders = implode(',', array_fill(0, count($calzadoIds), '?'));
    $imagenesConsulta = $pdo->prepare(
        'SELECT calzado_id, foto, url_imagen, color, fuente_url, licencia, atribucion
         FROM calzado_imagenes
         WHERE calzado_id IN (' . $placeholders . ')
         ORDER BY orden, id'
    );
    $imagenesConsulta->execute($calzadoIds);
    foreach ($imagenesConsulta->fetchAll() as $imagen) {
        $imagenesPorCalzado[$imagen['calzado_id']][] = $imagen;
    }
}

// Obtiene el inventario completo para resumen, modal y exportación JSON.
$inventario = $pdo->query(
    'SELECT c.id, c.modelo, c.talla, c.color, c.precio, c.foto, m.nombre_marca
     FROM calzados c INNER JOIN marcas m ON c.id_marca = m.id_marca
     ORDER BY c.modelo, c.id'
)->fetchAll();
$cantidadMarcas = count(array_unique(array_column($inventario, 'nombre_marca')));
$valorCatalogo = array_sum(array_map(static function ($producto) {
    return (float)$producto['precio'];
}, $inventario));
$precioPromedio = $inventario ? $valorCatalogo / count($inventario) : 0;

$pageTitle = 'Catálogo de calzados';
require_once 'includes/header.php';
require __DIR__ . '/includes/modulos/encabezado-catalogo.php';
require __DIR__ . '/includes/modulos/busqueda-catalogo.php';
require __DIR__ . '/includes/modulos/lista-calzados.php';
require __DIR__ . '/includes/modulos/paginacion-catalogo.php';
require __DIR__ . '/includes/modulos/modal-inventario.php';
require __DIR__ . '/includes/modulos/scripts-catalogo.php';
require_once 'includes/footer.php';
