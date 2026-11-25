<?php
// El formulario GET conserva la búsqueda en la URL para compartir y recargar.
?>
<form class="catalog-search row g-2 mb-4" id="busquedaForm" method="get">
    <div class="col-12 col-md-8">
        <label class="visually-hidden" for="buscar">Buscar calzado</label>
        <input class="form-control form-control-lg" id="buscar" type="search" name="buscar" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar por modelo, color o marca">
    </div>
    <div class="col-12 col-md-4 d-flex gap-2">
        <button class="btn btn-outline-primary flex-grow-1 flex-md-grow-0" type="submit">Buscar</button>
        <?php if ($busqueda !== ''): ?><a class="btn btn-link" href="index.php">Limpiar</a><?php endif; ?>
    </div>
</form>
