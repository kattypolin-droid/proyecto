<?php
// Los enlaces conservan la búsqueda y cambian únicamente el número de página.
?>
<?php if ($totalPaginas > 1): ?>
    <nav class="mt-4" aria-label="Paginación del catálogo">
        <ul class="pagination flex-wrap justify-content-center mb-0">
            <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?pagina=<?= $pagina - 1 ?>&buscar=<?= urlencode($busqueda) ?>">Anterior</a>
            </li>
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                    <a class="page-link" href="?pagina=<?= $i ?>&buscar=<?= urlencode($busqueda) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                <a class="page-link" href="?pagina=<?= $pagina + 1 ?>&buscar=<?= urlencode($busqueda) ?>">Siguiente</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
