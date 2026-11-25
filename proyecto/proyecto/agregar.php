<?php
require_once 'conexion.php';
// Redirige al login si el usuario no inició sesión antes de mostrar o guardar datos.
verificarSesion();

// $errors acumula todos los problemas detectados en el servidor.
$errors = [];
// El selector de marca se construye con los registros existentes y ordenados.
$marcas = $pdo->query('SELECT id_marca, nombre_marca FROM marcas ORDER BY nombre_marca')->fetchAll();

// Solo procesa el formulario al recibir una solicitud POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Normaliza el texto eliminando espacios exteriores.
    $modelo = trim($_POST['modelo'] ?? '');
    // Convierte tamaño, precio y marca a enteros o decimales validados.
    $talla = filter_input(INPUT_POST, 'talla', FILTER_VALIDATE_INT);
    $color = trim($_POST['color'] ?? '');
    $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $idMarca = filter_input(INPUT_POST, 'id_marca', FILTER_VALIDATE_INT);

    // Estas reglas se repiten en el servidor aunque el formulario tenga required/min.
    if ($modelo === '') $errors[] = 'El modelo es obligatorio.';
    if (!$talla) $errors[] = 'La talla es obligatoria.';
    if ($color === '') $errors[] = 'El color es obligatorio.';
    if ($precio === false || $precio < 0) $errors[] = 'El precio no es válido.';
    if (!$idMarca) $errors[] = 'Seleccione una marca.';

    $foto = null;
    if (!empty($_FILES['foto']['name'])) {
        // Solo procesa la imagen si el navegador envió un archivo.
        $archivo = $_FILES['foto'];
        // El valor derecho define la extensión permitida guardada en el servidor.
        $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        // Inspecciona el contenido real: no confía en la extensión escrita por el usuario.
        $tipo = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $archivo['tmp_name']);
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error al subir la imagen.';
        } elseif (!isset($permitidos[$tipo])) {
            $errors[] = 'Solo se permiten imágenes JPG, PNG o WebP.';
        } elseif ($archivo['size'] > 2 * 1024 * 1024) {
            $errors[] = 'La imagen no puede superar los 2 MB.';
        } else {
            // Crea uploads si no existe y genera un nombre único para evitar sobrescritura.
            if (!is_dir('uploads')) mkdir('uploads', 0775, true);
            $foto = 'uploads/' . uniqid('bata_', true) . '.' . $permitidos[$tipo];
        }
    }

    if (!$errors) {
        // Inserta el producto mediante parámetros nombrados, no concatenación SQL.
        $statement = $pdo->prepare(
            'INSERT INTO calzados (modelo, talla, color, precio, id_marca, foto)
             VALUES (:modelo, :talla, :color, :precio, :id_marca, :foto)'
        );
        $statement->execute([
            'modelo' => $modelo,
            'talla' => $talla,
            'color' => $color,
            'precio' => $precio,
            'id_marca' => $idMarca,
            'foto' => $foto,
        ]);
        if (!empty($_FILES['foto']['name'])) {
            // Mueve el archivo temporal validado a la ruta registrada en MySQL.
            move_uploaded_file($_FILES['foto']['tmp_name'], $foto);
        }
        // Evita mantener el envío POST activo al regresar con el navegador.
        header('Location: index.php');
        exit;
    }
}

$pageTitle = 'Nuevo calzado';
require_once 'includes/header.php';
?>
<!-- Encabezado del editor y navegación de regreso al catálogo. -->
<div class="page-titlebar mb-4">
    <div>
        <p class="page-titlebar-eyebrow">GESTIÓN DE PRODUCTOS</p>
        <h1 class="h3 mb-0">Nuevo calzado</h1>
    </div>
    <a class="btn btn-secondary" href="index.php">Volver</a>
</div>
<?php foreach ($errors as $error): ?>
    <!-- Cada error se escapa antes de insertarse en el HTML. -->
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endforeach; ?>
<!-- enctype multipart/form-data permite incluir el archivo de foto. -->
<div class="card shadow-sm product-editor-card"><div class="card-body">
<form method="post" enctype="multipart/form-data">
    <div class="row g-3">
        <!-- Cada value conserva los datos escritos si el servidor rechazó el formulario. -->
        <div class="col-md-6"><label class="form-label" for="modelo">Modelo</label><input class="form-control" id="modelo" name="modelo" value="<?= htmlspecialchars($_POST['modelo'] ?? '') ?>" required></div>
        <div class="col-md-3"><label class="form-label" for="talla">Talla</label><input class="form-control" type="number" min="1" id="talla" name="talla" value="<?= htmlspecialchars($_POST['talla'] ?? '') ?>" required></div>
        <div class="col-md-3"><label class="form-label" for="color">Color</label><input class="form-control" id="color" name="color" value="<?= htmlspecialchars($_POST['color'] ?? '') ?>" required></div>
        <div class="col-md-6"><label class="form-label" for="precio">Precio (S/)</label><input class="form-control" type="number" min="0" step="0.01" id="precio" name="precio" value="<?= htmlspecialchars($_POST['precio'] ?? '') ?>" required></div>
        <!-- La marca seleccionada identifica la llave foránea id_marca. -->
        <div class="col-md-6"><label class="form-label" for="id_marca">Marca</label><select class="form-select" id="id_marca" name="id_marca" required><option value="">Seleccione...</option><?php foreach ($marcas as $marca): ?><option value="<?= (int)$marca['id_marca'] ?>" <?= (($_POST['id_marca'] ?? '') == $marca['id_marca']) ? 'selected' : '' ?>><?= htmlspecialchars($marca['nombre_marca']) ?></option><?php endforeach; ?></select></div>
        <!-- accept limita la selección visual; PHP vuelve a validar el MIME real. -->
        <div class="col-12"><label class="form-label" for="foto">Foto</label><input class="form-control" type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp"></div>
    </div>
    <button class="btn btn-primary mt-4" type="submit">Guardar</button>
</form>
</div></div>
<?php require_once 'includes/footer.php';
