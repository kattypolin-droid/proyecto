<?php
// Scripts del catálogo: modal, filtro local y descarga JSON.
?>
<script type="application/json" id="catalogoExportar"><?= json_encode($inventario, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
<script>
document.getElementById('abrirInventario').addEventListener('click', function () {
    bootstrap.Modal.getOrCreateInstance(document.getElementById('inventarioModal')).show();
});
document.getElementById('inventarioBuscar').addEventListener('input', function (event) {
    var termino = event.target.value.trim().toLocaleLowerCase('es');
    document.querySelectorAll('#inventarioTabla tr[data-search]').forEach(function (fila) {
        fila.hidden = !fila.dataset.search.includes(termino);
    });
});
document.getElementById('exportarDatos').addEventListener('click', function () {
    var productos = JSON.parse(document.getElementById('catalogoExportar').textContent);
    var archivo = new Blob([JSON.stringify(productos, null, 2)], { type: 'application/json' });
    var enlace = document.createElement('a');
    enlace.href = URL.createObjectURL(archivo);
    enlace.download = 'catalogo-bata.json';
    enlace.click();
    URL.revokeObjectURL(enlace.href);
});
window.addEventListener('DOMContentLoaded', function () {
    var parametros = new URLSearchParams(window.location.search);
    if (parametros.get('ver_inventario') === '1') {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('inventarioModal')).show();
    }
    if (parametros.get('exportar') === '1') {
        document.getElementById('exportarDatos').click();
    }
});
</script>
