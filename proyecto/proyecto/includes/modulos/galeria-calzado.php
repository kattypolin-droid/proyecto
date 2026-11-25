<?php
// Carrusel Bootstrap generado para las imágenes recibidas desde la tarjeta.
?>
<?php $totalImagenes = count($imagenes); ?>
<?php if ($totalImagenes): ?>
    <div id="<?= $galeriaId ?>" class="carousel slide product-carousel" role="region" aria-roledescription="carrusel" aria-label="Imágenes de <?= htmlspecialchars($calzado['modelo']) ?>">
        <div class="carousel-inner">
            <?php foreach ($imagenes as $indice => $imagen): ?>
                <?php $srcImagen = $imagen['url_imagen'] ?: $imagen['foto']; ?>
                <div class="carousel-item <?= $indice === 0 ? 'active' : '' ?>">
                    <div class="product-image-wrap">
                        <img class="product-image" src="<?= htmlspecialchars($srcImagen) ?>" alt="<?= htmlspecialchars($calzado['modelo'] . ' color ' . $imagen['color']) ?>" loading="<?= $indice === 0 ? 'eager' : 'lazy' ?>">
                        <span class="gallery-color-badge"><?= htmlspecialchars($imagen['color']) ?></span>
                    </div>
                    <?php if ($imagen['atribucion']): ?>
                        <div class="image-attribution">
                            <a href="<?= htmlspecialchars($imagen['fuente_url']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($imagen['atribucion']) ?></a>
                            <?php if ($imagen['licencia']): ?><span> · <?= htmlspecialchars($imagen['licencia']) ?></span><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($totalImagenes > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#<?= $galeriaId ?>" data-bs-slide="prev" aria-label="Color anterior">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#<?= $galeriaId ?>" data-bs-slide="next" aria-label="Siguiente color">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="product-image-wrap"><div class="product-image-placeholder">Sin imagen</div></div>
<?php endif; ?>
