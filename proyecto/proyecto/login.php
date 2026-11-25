<?php
require_once 'conexion.php';

// Un mensaje vacío significa que todavía no hubo un intento incorrecto.
$error = '';

// Si el usuario ya inició sesión, no vuelve a mostrar el formulario.
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // trim elimina espacios exteriores del correo antes de buscarlo.
    $usuario = trim($_POST['usuario'] ?? '');
    // La contraseña se lee, valida y nunca se vuelve a imprimir en la página.
    $password = $_POST['password'] ?? '';

    // :usuario es un parámetro preparado para evitar inyección SQL.
    $statement = $pdo->prepare('SELECT * FROM usuarios WHERE usuario = :usuario');
    $statement->execute(['usuario' => $usuario]);
    // fetch devuelve la cuenta o false si no existe.
    $cuenta = $statement->fetch();

    // Inicia sin coincidencia válida; solo cambia a true después de verificar.
    $valida = false;
    if ($cuenta) {
        // Usa password_verify para un hash moderno y hash_equals para texto legado.
        $valida = password_get_info($cuenta['password'])['algo'] !== 0
            ? password_verify($password, $cuenta['password'])
            : hash_equals($cuenta['password'], $password);
    }

    if ($valida) {
        // Cambia el ID de sesión para impedir reutilizar uno fijado antes del acceso.
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $cuenta['id'];
        $_SESSION['usuario'] = $cuenta['usuario'];
        header('Location: index.php');
        exit;
    }

    $error = 'Usuario o contraseña incorrectos.';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/styles.css?v=20261124-site-polish2" rel="stylesheet">
</head>
<body class="login-page">
<main class="auth-shell">
    <!-- Bloque institucional: muestra identidad visual y mensaje de bienvenida. -->
    <section class="auth-brand" aria-label="Catálogo Bata">
        <div class="auth-brand-orbit auth-brand-orbit-one" aria-hidden="true"></div>
        <div class="auth-brand-orbit auth-brand-orbit-two" aria-hidden="true"></div>
        <div class="auth-brand-content">
            <div class="auth-brand-mark"><img src="assets/logo-bata.png" alt="Bata" width="150" height="100"></div>
            <div class="auth-brand-copy">
                <p class="auth-kicker">COLECCIÓN BATA</p>
                <p class="auth-brand-heading">Tu tienda,<br>un paso adelante.</p>
                <p>Administra tu catálogo y registra tus ventas desde un solo lugar.</p>
            </div>
        </div>
        <div class="auth-brand-footer"><span class="auth-brand-dot"></span> Gestión simple para cada paso</div>
    </section>
    <!-- Formulario autenticado: el correo se conserva si falla y la contraseña no. -->
    <section class="auth-panel">
        <div class="card login-card">
            <div class="card-body">
                <p class="auth-eyebrow">Bienvenido de nuevo</p>
                <h1>Acceso administrativo</h1>
                <p class="auth-subtitle">Ingresa tus datos para continuar con tu tienda.</p>
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post" novalidate>
                    <div class="mb-3">
                        <label class="form-label" for="usuario">Correo electrónico</label>
                        <input class="form-control" type="email" id="usuario" name="usuario" value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>" autocomplete="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Contraseña</label>
                        <input class="form-control" type="password" id="password" name="password" autocomplete="current-password" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Ingresar a mi cuenta</button>
                </form>
                <p class="auth-switch">¿Aún no tienes una cuenta? <a href="registro.php">Regístrate</a></p>
            </div>
        </div>
    </section>
</main>
</body>
</html>
