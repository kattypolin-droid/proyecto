<?php
// Una cuadrícula responsiva muestra el estado vacío o una tarjeta por cada producto.
?>
<div class="row g-3 g-lg-4">
    <?php if (!$calzados): ?>
        <div class="col-12">
            <div class="alert alert-light border text-center py-5 mb-0">No se encontraron calzados. Prueba otra búsqueda o agrega un producto.</div>
        </div>
    <?php endif; ?>
    <?php foreach ($calzados as $calzado): ?>
        <?php require __DIR__ . '/tarjeta-calzado.php'; ?>
    <?php endforeach; ?>
</div>
