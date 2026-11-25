<?php
require_once 'conexion.php';
verificarSesion();

// El identificador llega en la URL: editar.php?id=N. FILTER_VALIDATE_INT evita
// que se consulte o modifique un registro usando texto o valores no numéricos.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Consulta preparada: vincula el parámetro :id y obtiene todos los campos del producto.
$statement = $pdo->prepare('SELECT * FROM calzados WHERE id = :id');
$statement->execute(['id' => $id]);
$calzado = $statement->fetch();

// Si el ID no existe o ya fue eliminado, se abandona el editor y se vuelve al catálogo.
if (!$calzado) {
    header('Location: index.php');
    exit;
}

// $errors acumula los mensajes visibles al usuario cuando falla una validación.
$errors = [];

// Se cargan todas las marcas para reconstruir el selector del formulario de edición.
$marcas = $pdo->query('SELECT id_marca, nombre_marca FROM marcas ORDER BY nombre_marca')->fetchAll();

// Esta consulta se prepara una sola vez y se ejecuta después de aplicar cambios.
// Solo obtiene imágenes cuyo calzado coincide con el ID que se está editando.
$galleryQuery = $pdo->prepare(
    'SELECT id, foto, url_imagen, color, fuente_url, licencia, atribucion
     FROM calzado_imagenes WHERE calzado_id = :id ORDER BY orden, id'
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // El campo oculto accion decide qué operación debe ejecutar este archivo.
    // editar_producto es el valor predeterminado si alguna versión antigua del
    // formulario no envía explícitamente el tipo de operación.
    $accion = $_POST['accion'] ?? 'editar_producto';

    if ($accion === 'agregar_imagen') {
        // Rama de alta de fotografía alternativa. Esta rama no cambia los campos
        // principales del calzado, únicamente crea un registro en calzado_imagenes.
        $colorImagen = trim($_POST['color_imagen'] ?? '');
        $archivoImagen = $_FILES['imagen_alternativa'] ?? null;

        // Validación independiente: permite recopilar varios errores antes de rechazar el envío.
        if ($colorImagen === '') {
            $errors[] = 'Indica el color de la imagen.';
        }
        if (!$archivoImagen || $archivoImagen['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Selecciona una imagen.';
        } elseif ($archivoImagen['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error al subir la imagen.';
        } else {
            // Lista de tipos MIME admitidos. El valor a la derecha se usa como extensión.
            // No basta revisar la extensión del nombre original: se inspecciona el
            // contenido real mediante FILEINFO_MIME_TYPE.
            $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $tipoImagen = finfo_file($finfo, $archivoImagen['tmp_name']);
            finfo_close($finfo);
            if (!isset($permitidos[$tipoImagen])) {
                $errors[] = 'Solo se permiten imágenes JPG, PNG o WebP.';
            } elseif ($archivoImagen['size'] > 2 * 1024 * 1024) {
                $errors[] = 'La imagen no puede superar los 2 MB.';
            }
        }

        if (!$errors) {
            // Crea la carpeta uploads si aún no existe. Los parámetros indican
            // permisos recursivos; la última comprobación evita asumir que mkdir tuvo éxito.
            if (!is_dir('uploads') && !mkdir('uploads', 0775, true) && !is_dir('uploads')) {
                $errors[] = 'No se pudo crear la carpeta de imágenes.';
            } else {
                // uniqid genera un nombre casi único y evita sobrescribir fotos existentes.
                // La extensión proviene del tipo MIME permitido, no del nombre enviado.
                $rutaImagen = 'uploads/' . uniqid('bata_', true) . '.' . $permitidos[$tipoImagen];
                if (!move_uploaded_file($archivoImagen['tmp_name'], $rutaImagen)) {
                    $errors[] = 'No se pudo guardar la imagen subida.';
                } else {
                    // Inserta la relación entre el calzado y su imagen alternativa.
                    // orden=1 permite que estas imágenes aparezcan al inicio del listado.
                    $insertImage = $pdo->prepare(
                        'INSERT INTO calzado_imagenes (calzado_id, foto, color, orden)
                         VALUES (:calzado_id, :foto, :color, 1)'
                    );
                    $insertImage->execute([
                        'calzado_id' => $id,
                        'foto' => $rutaImagen,
                        'color' => $colorImagen,
                    ]);
                    // Patrón Post/Redirect/Get: la respuesta HTTP envía al usuario de
                    // regreso al mismo editor sin conservar el POST en el historial.
                    header('Location: editar.php?id=' . $id);
                    exit;
                }
            }
        }
    } elseif ($accion === 'eliminar_imagen') {
        // Convierte el ID recibido a entero. Esta validación impide eliminar
        // referencias arbitrarias que no pertenecen al calzado mostrado.
        $imagenId = filter_input(INPUT_POST, 'imagen_id', FILTER_VALIDATE_INT);

        // La consulta exige que la imagen pertenezca simultáneamente al producto y
        // al ID solicitado; así no se puede usar este formulario para quitar otra foto.
        $findImage = $pdo->prepare(
            'SELECT id, foto FROM calzado_imagenes WHERE id = :imagen_id AND calzado_id = :calzado_id'
        );
        $findImage->execute(['imagen_id' => $imagenId, 'calzado_id' => $id]);
        $imagen = $findImage->fetch();
        if (!$imagen) {
            $errors[] = 'La imagen alternativa ya no existe.';
        } else {
            // Primero elimina la asociación en MySQL; luego elimina el archivo físico.
            // if (is_file) evita errores si la ruta ya fue borrada o es externa.
            $deleteImage = $pdo->prepare('DELETE FROM calzado_imagenes WHERE id = :id');
            $deleteImage->execute(['id' => $imagen['id']]);
            if ($imagen['foto'] && is_file($imagen['foto'])) {
                unlink($imagen['foto']);
            }
            header('Location: editar.php?id=' . $id);
            exit;
        }
    } else {
        // Rama predeterminada: procesa todos los datos editables del producto.
        // trim elimina espacios exteriores del texto; filter_input transforma y
        // valida números según las reglas indicadas por PHP.
        $modelo = trim($_POST['modelo'] ?? '');
        $talla = filter_input(INPUT_POST, 'talla', FILTER_VALIDATE_INT);
        $color = trim($_POST['color'] ?? '');
        $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
        $idMarca = filter_input(INPUT_POST, 'id_marca', FILTER_VALIDATE_INT);

        // Las reglas servidoras se mantienen aunque el navegador tenga required/min/max.
        // Esto protege contra solicitudes creadas manualmente fuera del formulario.
        if ($modelo === '') $errors[] = 'El modelo es obligatorio.';
        if (!$talla) $errors[] = 'La talla es obligatoria.';
        if ($color === '') $errors[] = 'El color es obligatorio.';
        if ($precio === false || $precio < 0) $errors[] = 'El precio no es válido.';
        if (!$idMarca) $errors[] = 'Seleccione una marca.';

        $foto = $calzado['foto'];
        if (!empty($_FILES['foto']['name'])) {
            // Solo se prepara una foto nueva si el campo de reemplazo contiene un archivo.
            // Si el usuario no selecciona nada, conserva exactamente la ruta anterior.
            $archivo = $_FILES['foto'];
            $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $tipo = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $archivo['tmp_name']);
            if ($archivo['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Error al subir la imagen.';
            } elseif (!isset($permitidos[$tipo])) {
                $errors[] = 'Solo se permiten imágenes JPG, PNG o WebP.';
            } elseif ($archivo['size'] > 2 * 1024 * 1024) {
                $errors[] = 'La imagen no puede superar los 2 MB.';
            } else {
                $nuevaFoto = 'uploads/' . uniqid('bata_', true) . '.' . $permitidos[$tipo];
            }
        }

        if (!$errors) {
            if (!empty($nuevaFoto)) {
                // Primero mueve el archivo nuevo a uploads. Solo si la operación termina,
                // se elimina la foto anterior para no perderla si falla la carga.
                move_uploaded_file($archivo['tmp_name'], $nuevaFoto);
                if ($foto && is_file($foto)) unlink($foto);
                $foto = $nuevaFoto;
            }
            // UPDATE modifica todos los campos editables del registro encontrado.
            // Los parámetros con dos puntos evitan concatenar datos directamente en SQL.
            $update = $pdo->prepare(
                'UPDATE calzados SET modelo=:modelo, talla=:talla, color=:color, precio=:precio,
                 id_marca=:id_marca, foto=:foto WHERE id=:id'
            );
            $update->execute([
                'modelo' => $modelo, 'talla' => $talla, 'color' => $color, 'precio' => $precio,
                'id_marca' => $idMarca, 'foto' => $foto, 'id' => $id,
            ]);
            header('Location: index.php');
            exit;
        }
    }
}

$galleryQuery->execute(['id' => $id]);
$galleryImages = $galleryQuery->fetchAll();

// Después de validar POST, los campos deben conservar lo escrito por el usuario
// si hubo errores. En GET o después de eliminar/agregar imágenes se usan los datos de BD.
$datos = $_SERVER['REQUEST_METHOD'] === 'POST' && ($accion ?? '') === 'editar_producto'
    ? $_POST
    : $calzado;
$pageTitle = 'Editar calzado';
require_once 'includes/header.php';
?>
<!-- Encabezado de la página: identifica la sección y ofrece una ruta visible de regreso. -->
<div class="page-titlebar mb-4">
    <div>
        <p class="page-titlebar-eyebrow">GESTIÓN DE PRODUCTOS</p>
        <h1 class="h3 mb-0">Editar calzado</h1>
    </div>
    <a class="btn btn-secondary" href="index.php">Volver</a>
</div>
<!-- Recorre el arreglo de errores generado por cada rama para mostrar mensajes separados. -->
<?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>

<!-- Tarjeta principal del formulario de actualización del producto. -->
<div class="card shadow-sm product-editor-card"><div class="card-body">
<form method="post" enctype="multipart/form-data">
    <!-- accion distingue esta operación de agregar o quitar una imagen alternativa. -->
    <input type="hidden" name="accion" value="editar_producto">
    <div class="row g-3">
        <!-- Modelo: texto editable que identifica comercialmente el calzado. -->
        <div class="col-md-6"><label class="form-label" for="modelo">Modelo</label><input class="form-control" id="modelo" name="modelo" value="<?= htmlspecialchars($datos['modelo'] ?? '') ?>" required></div>
        <!-- Talla: entero positivo exigido tanto por HTML como por validación PHP. -->
        <div class="col-md-3"><label class="form-label" for="talla">Talla</label><input class="form-control" type="number" min="1" id="talla" name="talla" value="<?= htmlspecialchars($datos['talla'] ?? '') ?>" required></div>
        <!-- Color: texto usado por el catálogo y como descripción de la foto principal. -->
        <div class="col-md-3"><label class="form-label" for="color">Color</label><input class="form-control" id="color" name="color" value="<?= htmlspecialchars($datos['color'] ?? '') ?>" required></div>
        <!-- Precio: decimal no negativo mostrado en soles y almacenado con dos decimales. -->
        <div class="col-md-6"><label class="form-label" for="precio">Precio (S/)</label><input class="form-control" type="number" min="0" step="0.01" id="precio" name="precio" value="<?= htmlspecialchars($datos['precio'] ?? '') ?>" required></div>
        <!-- Marca: el id seleccionado debe coincidir con una llave de la tabla marcas. -->
        <div class="col-md-6"><label class="form-label" for="id_marca">Marca</label><select class="form-select" id="id_marca" name="id_marca" required><?php foreach ($marcas as $marca): ?><option value="<?= (int)$marca['id_marca'] ?>" <?= (($datos['id_marca'] ?? '') == $marca['id_marca']) ? 'selected' : '' ?>><?= htmlspecialchars($marca['nombre_marca']) ?></option><?php endforeach; ?></select></div>
        <!-- Foto principal: permanece sin cambios si no se adjunta otro archivo válido.
             La ruta existente se muestra como referencia para el administrador. -->
        <div class="col-12"><label class="form-label" for="foto">Reemplazar foto</label><input class="form-control" type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp"><?php if ($calzado['foto']): ?><small class="text-secondary">Foto actual: <?= htmlspecialchars($calzado['foto']) ?></small><?php endif; ?></div>
    </div>
    <button class="btn btn-warning mt-4" type="submit">Actualizar</button>
</form>
</div></div>
<!-- Segundo bloque independiente: administra las imágenes adicionales por color.
     Los cambios aquí no modifican el modelo, talla, color, precio o marca. -->
<section class="card shadow-sm product-gallery-card mt-4">
    <div class="card-body">
        <h2 class="h5">Imágenes de otros colores</h2>
        <p class="text-secondary">Agrega una foto por color. Puedes repetir el proceso para incluir más variantes.</p>
        <?php if ($galleryImages): ?>
            <div class="row g-3 mb-4">
                <?php foreach ($galleryImages as $imagenAlternativa): ?>
                    <?php $srcAlternativa = $imagenAlternativa['url_imagen'] ?: $imagenAlternativa['foto']; ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="border rounded-3 p-2 h-100">
                            <img class="gallery-preview rounded" src="<?= htmlspecialchars($srcAlternativa) ?>" alt="<?= htmlspecialchars($calzado['modelo'] . ' color ' . $imagenAlternativa['color']) ?>">
                            <div class="d-flex justify-content-between align-items-start gap-2 mt-2">
                                <div>
                                    <strong><?= htmlspecialchars($imagenAlternativa['color']) ?></strong>
                                    <?php if ($imagenAlternativa['atribucion']): ?>
                                        <div class="small">
                                            <a href="<?= htmlspecialchars($imagenAlternativa['fuente_url']) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($imagenAlternativa['atribucion']) ?></a>
                                            <?php if ($imagenAlternativa['licencia']): ?><span> · <?= htmlspecialchars($imagenAlternativa['licencia']) ?></span><?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <form method="post">
                                    <input type="hidden" name="accion" value="eliminar_imagen">
                                    <input type="hidden" name="imagen_id" value="<?= (int)$imagenAlternativa['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Quitar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="alert alert-light border">Aún no hay imágenes adicionales para este calzado.</p>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="row g-3 align-items-end">
            <input type="hidden" name="accion" value="agregar_imagen">
            <div class="col-12 col-md-4">
                <label class="form-label" for="color_imagen">Color de la foto</label>
                <input class="form-control" id="color_imagen" name="color_imagen" maxlength="60" required>
            </div>
            <div class="col-12 col-md-5">
                <label class="form-label" for="imagen_alternativa">Imagen JPG, PNG o WebP (máx. 2 MB)</label>
                <input class="form-control" id="imagen_alternativa" name="imagen_alternativa" type="file" accept="image/jpeg,image/png,image/webp" required>
            </div>
            <div class="col-12 col-md-3 d-grid">
                <button class="btn btn-outline-primary" type="submit">Agregar color</button>
            </div>
        </form>
    </div>
</section>
<?php require_once 'includes/footer.php';
