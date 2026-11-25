<?php
// Título del catálogo y acciones rápidas para crear o consultar productos.
?>
<div class="catalog-heading d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div class="catalog-heading-copy">
        <p class="catalog-eyebrow">COLECCIÓN BATA <span>GESTIÓN DE TIENDA</span></p>
        <h1 class="h2 mb-0">Catálogo de calzados</h1>
        <p class="catalog-description">Encuentra tus modelos y gestiona cada venta desde un mismo lugar.</p>
    </div>
    <div class="catalog-heading-actions">
        <span class="catalog-count"><?= $total ?> productos</span>
        <a class="btn btn-primary btn-lg" href="agregar.php">Nuevo calzado</a>
        <button class="btn btn-outline-primary btn-lg" type="button" data-bs-toggle="modal" data-bs-target="#inventarioModal">Inventario</button>
    </div>
</div>
