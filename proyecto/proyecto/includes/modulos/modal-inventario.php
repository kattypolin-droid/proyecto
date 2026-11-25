<?php
// Modal de inventario: resume el catálogo y permite localizar una fila sin recargar.
?>
<div class="modal fade" id="inventarioModal" tabindex="-1" aria-labelledby="inventarioTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content inventory-shell">
            <div class="modal-header inventory-header">
                <div><span class="inventory-eyebrow">Control de tienda</span><h2 class="modal-title fs-5" id="inventarioTitulo">Inventario de calzados</h2></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body inventory-body">
                <div class="inventory-summary">
                    <article><span>Productos registrados</span><strong><?= count($inventario) ?></strong></article>
                    <article><span>Marcas</span><strong><?= $cantidadMarcas ?></strong></article>
                    <article><span>Precio promedio</span><strong>S/ <?= number_format($precioPromedio, 2) ?></strong></article>
                    <article><span>Valor referencial</span><strong>S/ <?= number_format($valorCatalogo, 2) ?></strong></article>
                </div>
                <p class="small text-secondary">El sistema no maneja cantidades de stock; el inventario refleja los productos registrados en el catálogo.</p>
                <div class="inventory-toolbar">
                    <label class="visually-hidden" for="inventarioBuscar">Buscar inventario</label>
                    <input class="form-control" id="inventarioBuscar" type="search" placeholder="Buscar modelo, marca o color">
                </div>
                <div class="inventory-table-wrap">
                    <table class="inventory-table">
                        <thead><tr><th>Producto</th><th>Talla</th><th>Precio</th><th>Acción</th></tr></thead>
                        <tbody id="inventarioTabla">
                        <?php foreach ($inventario as $producto): ?>
                            <?php $imagenInventario = $producto['foto'] ?: 'assets/logo-bata.png'; ?>
                            <tr data-search="<?= htmlspecialchars(strtolower($producto['modelo'] . ' ' . $producto['nombre_marca'] . ' ' . $producto['color'])) ?>">
                                <td><div class="inventory-product">
                                    <img src="<?= htmlspecialchars($imagenInventario) ?>" alt="" loading="lazy">
                                    <div><strong><?= htmlspecialchars($producto['modelo']) ?></strong><small><?= htmlspecialchars($producto['nombre_marca'] . ' · ' . $producto['color']) ?></small></div>
                                </div></td>
                                <td><?= (int)$producto['talla'] ?></td>
                                <td>S/ <?= number_format((float)$producto['precio'], 2) ?></td>
                                <td><a class="btn btn-outline-primary btn-sm" href="editar.php?id=<?= (int)$producto['id'] ?>">Editar</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$inventario): ?>
                            <tr><td colspan="4" class="inventory-empty">No hay productos registrados.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
