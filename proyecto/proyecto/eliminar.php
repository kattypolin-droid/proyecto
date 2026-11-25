<?php
require_once 'conexion.php';
verificarSesion();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id) {
    // Obtiene la foto principal antes de borrar el registro para eliminar el archivo.
    $select = $pdo->prepare('SELECT foto FROM calzados WHERE id = :id');
    $select->execute(['id' => $id]);
    $calzado = $select->fetch();

    if ($calzado) {
        // Recupera solo rutas locales asociadas a este calzado.
        $gallerySelect = $pdo->prepare(
            'SELECT foto FROM calzado_imagenes WHERE calzado_id = :calzado_id AND foto IS NOT NULL'
        );
        $gallerySelect->execute(['calzado_id' => $id]);
        $imagenesAlternativas = $gallerySelect->fetchAll(PDO::FETCH_COLUMN);

        $delete = $pdo->prepare('DELETE FROM calzados WHERE id = :id');
        $delete->execute(['id' => $id]);

        if (!empty($calzado['foto']) && is_file($calzado['foto'])) {
            unlink($calzado['foto']);
        }
        foreach ($imagenesAlternativas as $rutaImagen) {
            if (is_file($rutaImagen)) {
                unlink($rutaImagen);
            }
        }
    }
}
header('Location: index.php');
exit;
