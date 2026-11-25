<?php
// Tarjeta completa: galería, datos comerciales y acciones sobre el producto.
$imagenes = [];
if ($calzado['foto']) {
    $imagenes[] = [
        'foto' => $calzado['foto'],
        'url_imagen' => null,
        'color' => $calzado['color'],
        'fuente_url' => null,
        'licencia' => null,
        'atribucion' => null,
    ];
}
foreach ($imagenesPorCalzado[$calzado['id']] ?? [] as $imagen) {
    $imagenes[] = $imagen;
}
$galeriaId = 'galeria-' . (int)$calzado['id'];
?>
<div class="col-12 col-sm-6 col-xl-4 col-xxl-3">
    <article class="card product-card h-100">
        <?php require __DIR__ . '/galeria-calzado.php'; ?>
        <div class="card-body d-flex flex-column">
            <span class="badge rounded-pill text-bg-light align-self-start mb-2"><?= htmlspecialchars($calzado['nombre_marca']) ?></span>
            <h2 class="h5 product-title"><?= htmlspecialchars($calzado['modelo']) ?></h2>
            <p class="text-secondary mb-3"><?= htmlspecialchars($calzado['color']) ?> <span aria-hidden="true">·</span> Talla <?= (int)$calzado['talla'] ?></p>
            <p class="product-price mt-auto mb-3">S/ <?= number_format((float)$calzado['precio'], 2) ?></p>
            <form class="buy-form" method="post" action="comprar.php">
                <input type="hidden" name="id" value="<?= (int)$calzado['id'] ?>">
                <input type="hidden" name="volver" value="<?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?>">
                <label class="visually-hidden" for="cant-<?= (int)$calzado['id'] ?>">Cantidad</label>
                <input class="form-control" type="number" id="cant-<?= (int)$calzado['id'] ?>" name="cantidad" min="1" max="99" value="1">
                <button class="btn btn-primary" type="submit">Registrar compra</button>
            </form>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-primary flex-fill" href="editar.php?id=<?= (int)$calzado['id'] ?>">Editar</a>
                <a class="btn btn-outline-danger flex-fill" href="eliminar.php?id=<?= (int)$calzado['id'] ?>" onclick="return confirm('¿Eliminar este calzado?')">Eliminar</a>
            </div>
        </div>
    </article>
</div>
