<?php
require_once 'conexion.php';
verificarSesion();

// Obtiene todos los calzados con su marca para mostrar el detalle completo.
$calzados = $pdo->query(
    'SELECT c.id, c.modelo, c.talla, c.color, c.precio, m.nombre_marca
     FROM calzados c
     INNER JOIN marcas m ON c.id_marca = m.id_marca
     ORDER BY m.nombre_marca, c.modelo'
)->fetchAll();

$resumen = $pdo->query(
    'SELECT m.nombre_marca, COUNT(c.id) AS cantidad, SUM(c.precio) AS total,
            AVG(c.precio) AS promedio
     FROM marcas m
     LEFT JOIN calzados c ON m.id_marca = c.id_marca
     GROUP BY m.id_marca, m.nombre_marca
     ORDER BY m.nombre_marca'
)->fetchAll();
    // Convierte cantidades a enteros y usa el mayor valor para calcular las barras.
    $cantidadesPorMarca = array_map('intval', array_column($resumen, 'cantidad'));
    $maxProductosMarca = max(1, $cantidadesPorMarca ? max($cantidadesPorMarca) : 1);

// Métricas generales utilizadas por el encabezado ejecutivo del reporte.
$valorTotalCatalogo = array_sum(array_column($calzados, 'precio'));
$precioPromedioCatalogo = $calzados
    ? $valorTotalCatalogo / count($calzados)
    : 0;
$marcasConProductos = count(array_filter($resumen, static function ($fila) {
    return (int)$fila['cantidad'] > 0;
}));

$pageTitle = 'Reportes';
require_once 'includes/header.php';
?>
    <!-- Contenedor que aplica espaciado y presentación específica de reportes. -->
    <div class="reports-page">
     <section class="reports-hero">
         <div class="reports-hero-head">
             <div>
                 <p class="reports-kicker"><span class="reports-kicker-dot"></span> CENTRO DE ANÁLISIS</p>
                 <h1 class="reports-title">Resumen del catálogo</h1>
                 <p class="reports-description">Indicadores comerciales y disponibilidad actual de tu inventario.</p>
             </div>
             <span class="reports-period">Actualizado al <?= date('d/m/Y') ?></span>
         </div>

         <!-- Tarjetas de indicadores principales: productos, marcas, valor y precio medio. -->
         <div class="reports-kpi-grid">
             <article class="reports-kpi">
                 <span class="reports-kpi-label">Productos</span>
                 <strong><?= count($calzados) ?></strong>
                 <small>modelos registrados</small>
             </article>
             <article class="reports-kpi">
                 <span class="reports-kpi-label">Marcas activas</span>
                 <strong><?= $marcasConProductos ?></strong>
                 <small>de <?= count($resumen) ?> marcas</small>
             </article>
             <article class="reports-kpi reports-kpi-accent">
                 <span class="reports-kpi-label">Valor referencial</span>
                 <strong>S/ <?= number_format((float)$valorTotalCatalogo, 2) ?></strong>
                 <small>precio total registrado</small>
             </article>
             <article class="reports-kpi">
                 <span class="reports-kpi-label">Precio promedio</span>
                 <strong>S/ <?= number_format((float)$precioPromedioCatalogo, 2) ?></strong>
                 <small>por producto</small>
             </article>
         </div>
     </section>

     <section class="reports-section" aria-labelledby="resumen-marcas-titulo">
         <div class="reports-section-heading">
             <div>
                 <p class="reports-section-kicker">RENDIMIENTO</p>
                 <h2 id="resumen-marcas-titulo">Resumen por marca</h2>
             </div>
             <span class="reports-section-count"><?= count($resumen) ?> marcas</span>
         </div>
         <div class="row g-3 report-brand-grid">
             <?php foreach ($resumen as $indice => $fila): ?>
                 <?php
                 // Calcula cantidad, porcentaje relativo y estado visual de la marca.
                 $cantidadProductos = (int)$fila['cantidad'];
                 $progresoMarca = $maxProductosMarca > 0
                     ? (int)round($cantidadProductos / $maxProductosMarca * 100)
                     : 0;
                 ?>
                 <div class="col-12 col-sm-6 col-xl-4">
                     <article class="card report-brand-card h-100" style="--report-delay: <?= (int)$indice * 70 ?>ms">
                         <div class="card-body">
                             <div class="report-brand-card-top">
                                 <span class="report-brand-icon" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($fila['nombre_marca'], 0, 1))) ?></span>
                                 <span class="report-brand-state <?= $cantidadProductos ? 'has-products' : '' ?>">
                                     <?= $cantidadProductos ? 'En catálogo' : 'Sin productos' ?>
                                 </span>
                             </div>
                             <h3><?= htmlspecialchars($fila['nombre_marca']) ?></h3>
                             <div class="report-product-total">
                                 <strong><?= $cantidadProductos ?></strong>
                                 <span><?= $cantidadProductos === 1 ? 'producto' : 'productos' ?></span>
                             </div>
                             <div class="report-meter" role="progressbar" aria-label="Productos de <?= htmlspecialchars($fila['nombre_marca']) ?>" aria-valuemin="0" aria-valuemax="<?= $maxProductosMarca ?>" aria-valuenow="<?= $cantidadProductos ?>">
                                 <span style="--report-progress: <?= $progresoMarca ?>%"></span>
                             </div>
                             <div class="report-data-row report-catalog-value">
                                 <span>Valor del catálogo</span>
                                 <!-- SUM agrupa el valor acumulado de los productos de esta marca. -->
                                 <strong>S/ <?= number_format((float)$fila['total'], 2) ?></strong>
                             </div>
                             <div class="report-data-row">
                                 <span>Precio promedio</span>
                                 <!-- AVG muestra el precio medio calculado por SQL. -->
                                 <strong>S/ <?= number_format((float)$fila['promedio'], 2) ?></strong>
                             </div>
                         </div>
                     </article>
                 </div>
             <?php endforeach; ?>
         </div>
     </section>

     <section class="report-table-card" aria-labelledby="detalle-catalogo-titulo">
         <div class="report-table-heading">
             <div>
                 <p class="reports-section-kicker">INVENTARIO</p>
                 <h2 id="detalle-catalogo-titulo">Detalle del catálogo</h2>
                 <p>Modelos, tallas, colores y precios registrados.</p>
             </div>
             <span class="reports-section-count"><?= count($calzados) ?> registros</span>
         </div>
         <div class="table-responsive">
             <!-- Tabla secundaria que presenta el detalle completo, no solo resumen por marca. -->
             <table class="table table-striped mb-0 report-table">
                 <thead class="table-dark"><tr><th>Modelo</th><th>Talla</th><th>Color</th><th>Precio</th><th>Marca</th></tr></thead>
                 <tbody>
                 <?php if (!$calzados): ?>
                     <tr><td colspan="5" class="text-center py-4">No hay productos para mostrar.</td></tr>
                 <?php endif; ?>
                 <?php foreach ($calzados as $indice => $calzado): ?>
                     <tr style="--report-delay: <?= (int)$indice * 45 ?>ms">
                         <td><?= htmlspecialchars($calzado['modelo']) ?></td>
                         <td><span class="report-size"><?= (int)$calzado['talla'] ?></span></td>
                         <td><?= htmlspecialchars($calzado['color']) ?></td>
                         <td class="report-table-price">S/ <?= number_format((float)$calzado['precio'], 2) ?></td>
                         <td><span class="report-table-brand"><?= htmlspecialchars($calzado['nombre_marca']) ?></span></td>
                     </tr>
                 <?php endforeach; ?>
                 </tbody>
             </table>
         </div>
     </section>
    </div>
</div>
<?php require_once 'includes/footer.php';
