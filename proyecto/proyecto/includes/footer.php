</main>
<?php
// Cierra el área principal y presenta el panel lateral solo para usuarios autenticados.
?>
<?php
$comprasRecientes = [];
if (isset($pdo, $_SESSION['usuario_id'])) {
    // Consulta preparada no necesaria porque no recibe entrada del usuario.
    // LIMIT 8 mantiene una lista breve y las dos columnas de orden muestran
    // primero las fechas más nuevas y después el ID más reciente.
    $comprasRecientes = $pdo->query(
        'SELECT id, modelo, cantidad, precio_unitario, fecha_compra
         FROM compras ORDER BY fecha_compra DESC, id DESC LIMIT 8'
    )->fetchAll();
}
?>
<?php if (isset($_SESSION['usuario_id'])): ?>
<aside class="side-panel" aria-label="Fecha, hora y compras recientes">
    <!-- reloj-hora y reloj-fecha son actualizados desde JavaScript cada segundo. -->
    <section class="clock-card" aria-live="off">
        <p class="clock-time" id="reloj-hora">--:--:--</p>
        <p class="clock-date" id="reloj-fecha">&nbsp;</p>
    </section>
    <section class="purchases-card">
        <h2 class="h6 purchases-title">Compras recientes</h2>
        <?php if (!$comprasRecientes): ?>
            <p class="purchases-empty">Aún no hay compras. Usa «Registrar compra» en un calzado.</p>
        <?php else: ?>
            <ul class="purchase-list">
                <?php foreach ($comprasRecientes as $compra): ?>
                    <?php $momento = new DateTime($compra['fecha_compra']); ?>
                    <!-- data-* conserva los valores que el modal mostrará al abrir el recibo. -->
                    <li class="purchase-item">
                        <button class="receipt-open" type="button"
                            data-numero="<?= (int)$compra['id'] ?>"
                            data-modelo="<?= htmlspecialchars($compra['modelo'], ENT_QUOTES) ?>"
                            data-cantidad="<?= (int)$compra['cantidad'] ?>"
                            data-precio="<?= htmlspecialchars((string)$compra['precio_unitario'], ENT_QUOTES) ?>"
                            data-fecha="<?= $momento->format('d/m/Y H:i') ?>"
                            aria-label="Abrir recibo de <?= htmlspecialchars($compra['modelo'], ENT_QUOTES) ?>">
                            <span class="purchase-name"><?= htmlspecialchars($compra['modelo']) ?></span>
                            <span class="purchase-qty"><?= (int)$compra['cantidad'] ?> × S/ <?= number_format((float)$compra['precio_unitario'], 2) ?></span>
                            <span class="purchase-total">S/ <?= number_format($compra['cantidad'] * $compra['precio_unitario'], 2) ?></span>
                            <time class="purchase-time" datetime="<?= $momento->format('c') ?>"><?= $momento->format('d/m/Y H:i') ?></time>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</aside>
<?php endif; ?>
</div>
<footer class="border-top py-3 text-center text-secondary">
    <small>Sistema de Catálogo y Gestión de Marcas - 2026</small>
</footer>
<div class="modal fade" id="reciboModal" tabindex="-1" aria-labelledby="reciboTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content receipt-shell">
        <div class="modal-header border-0"><h2 class="modal-title fs-5" id="reciboTitulo">Recibo de compra</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <div class="modal-body receipt-body" id="reciboContenido"></div>
        <div class="modal-footer receipt-actions"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button><button type="button" class="btn btn-primary" id="imprimirRecibo">Imprimir / guardar PDF</button></div>
    </div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Primer bloque: reloj en tiempo real con zona horaria de Lima.
(function () {
    var hora = document.getElementById('reloj-hora');
    var fecha = document.getElementById('reloj-fecha');
    if (!hora) return;
    var zona = { timeZone: 'America/Lima' };
    function tic() {
        var ahora = new Date();
        hora.textContent = ahora.toLocaleTimeString('es-PE', Object.assign({ hour12: false }, zona));
        var texto = ahora.toLocaleDateString('es-PE', Object.assign({ weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }, zona));
        fecha.textContent = texto.charAt(0).toUpperCase() + texto.slice(1);
    }
    tic();
    setInterval(tic, 1000);
})();
// Segundo bloque: calcula si la tienda está abierta entre 7:00 y 12:30.
(function () {
    var status = document.getElementById('storeStatus');
    if (!status) return;
    var text = status.querySelector('.status-text');
    function updateStatus() {
        var parts = new Intl.DateTimeFormat('en-GB', {
            timeZone: 'America/Lima',
            hour: '2-digit',
            minute: '2-digit',
            hourCycle: 'h23'
        }).formatToParts(new Date());
        var hour = Number(parts.find(function (part) { return part.type === 'hour'; }).value);
        var minute = Number(parts.find(function (part) { return part.type === 'minute'; }).value);
        var current = hour * 60 + minute;
        var open = current >= 420 || current <= 30;
        status.classList.toggle('is-open', open);
        status.classList.toggle('is-closed', !open);
        text.textContent = (open ? 'Abierto' : 'Cerrado') + ' · 7:00 a. m. a 12:30 a. m.';
    }
    updateStatus();
    setInterval(updateStatus, 30000);
})();
(function () {
    var modal = document.getElementById('reciboModal');
    var contenido = document.getElementById('reciboContenido');
    if (!modal || !contenido) return;
    function add(parent, tag, className, value) {
        var element = document.createElement(tag);
        if (className) element.className = className;
        if (value !== undefined) element.textContent = value;
        parent.appendChild(element);
        return element;
    }
    document.querySelectorAll('.receipt-open').forEach(function (button) {
        button.addEventListener('click', function () {
            var cantidad = Number(button.dataset.cantidad);
            var precio = Number(button.dataset.precio);
            var total = cantidad * precio;
            contenido.replaceChildren();
            var marca = add(contenido, 'div', 'receipt-brand');
            var logo = add(marca, 'img');
            logo.src = 'assets/logo-bata.png';
            logo.alt = 'Catálogo Bata';
            add(marca, 'span', '', 'COMPROBANTE DE COMPRA');
            var meta = add(contenido, 'div', 'receipt-meta');
            var recibo = add(meta, 'div');
            add(recibo, 'small', '', 'RECIBO');
            add(recibo, 'strong', '', 'B-' + String(button.dataset.numero).padStart(8, '0'));
            var fecha = add(meta, 'div');
            add(fecha, 'small', '', 'FECHA');
            add(fecha, 'strong', '', button.dataset.fecha);
            add(contenido, 'div', 'receipt-line');
            var tabla = add(contenido, 'table', 'receipt-table');
            var encabezado = add(tabla, 'thead');
            var filaEncabezado = add(encabezado, 'tr');
            ['Producto', 'Cant.', 'Precio', 'Importe'].forEach(function (etiqueta) {
                add(filaEncabezado, 'th', '', etiqueta);
            });
            var cuerpo = add(tabla, 'tbody');
            var fila = add(cuerpo, 'tr');
            [button.dataset.modelo, String(cantidad), 'S/ ' + precio.toFixed(2), 'S/ ' + total.toFixed(2)].forEach(function (valor) {
                add(fila, 'td', '', valor);
            });
            var totalEl = add(contenido, 'div', 'receipt-total');
            add(totalEl, 'span', '', 'Total');
            add(totalEl, 'strong', '', 'S/ ' + total.toFixed(2));
            add(contenido, 'p', 'receipt-thanks', 'Gracias por su compra');
            bootstrap.Modal.getOrCreateInstance(modal).show();
        });
    });
    document.getElementById('imprimirRecibo').addEventListener('click', function () {
        window.print();
    });
})();
</script>
</body>
</html>
