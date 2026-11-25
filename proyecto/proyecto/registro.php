<?php
require_once 'conexion.php';

// Una sesión activa no necesita crear otra cuenta.
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['registro_csrf'])) {
    // bin2hex permite enviar el token aleatorio dentro de un campo HTML oculto.
    $_SESSION['registro_csrf'] = bin2hex(random_bytes(32));
}

// Los errores se acumulan para mostrar todos los problemas disponibles.
$errores = [];
$usuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Se toman los campos enviados y se normalizan solo los que permiten texto.
    $usuarioEnviado = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmarPassword = $_POST['confirmar_password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    $usuario = is_string($usuarioEnviado) ? strtolower(trim($usuarioEnviado)) : '';
    $password = is_string($password) ? $password : '';
    $confirmarPassword = is_string($confirmarPassword) ? $confirmarPassword : '';

    if (!is_string($token) || !hash_equals($_SESSION['registro_csrf'], $token)) {
        // hash_equals compara el token sin depender del tiempo de ejecución.
        $errores[] = 'La solicitud expiró. Recarga la página e inténtalo de nuevo.';
    }
    if (!filter_var($usuario, FILTER_VALIDATE_EMAIL) || strlen($usuario) > 100) {
        $errores[] = 'Ingresa un correo electrónico válido de hasta 100 caracteres.';
    }
    $longitudPassword = preg_match_all('/./us', $password);
    if ($longitudPassword === false || $longitudPassword < 8 || strlen($password) > 72) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres y no superar 72 bytes.';
    }
    if ($password !== $confirmarPassword) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (!$errores) {
        // Verifica duplicados antes de insertar, además de la restricción UNIQUE de MySQL.
        $buscar = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = :usuario');
        $buscar->execute(['usuario' => $usuario]);
        if ($buscar->fetch()) {
            $errores[] = 'Ese correo ya tiene una cuenta registrada.';
        } else {
            try {
                $crear = $pdo->prepare('INSERT INTO usuarios (usuario, password) VALUES (:usuario, :password)');
                $crear->execute([
                    'usuario' => $usuario,
                    // PASSWORD_DEFAULT genera un hash seguro y permite evolucionar el algoritmo.
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                ]);
            } catch (PDOException $error) {
                if ($error->getCode() !== '23000') {
                    throw $error;
                }
                $errores[] = 'Ese correo ya tiene una cuenta registrada.';
            }

            if (!$errores) {
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = (int)$pdo->lastInsertId();
                $_SESSION['usuario'] = $usuario;
                unset($_SESSION['registro_csrf']);
                header('Location: index.php');
                exit;
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/styles.css?v=20261124-site-polish2" rel="stylesheet">
</head>
<body class="login-page">
<main class="auth-shell">
    <!-- Bloque institucional asociado a la creación de una cuenta nueva. -->
    <section class="auth-brand" aria-label="Catálogo Bata">
        <div class="auth-brand-orbit auth-brand-orbit-one" aria-hidden="true"></div>
        <div class="auth-brand-orbit auth-brand-orbit-two" aria-hidden="true"></div>
        <div class="auth-brand-content">
            <div class="auth-brand-mark"><img src="assets/logo-bata.png" alt="Bata" width="150" height="100"></div>
            <div class="auth-brand-copy">
                <p class="auth-kicker">COLECCIÓN BATA</p>
                <p class="auth-brand-heading">Un nuevo paso<br>para tu tienda.</p>
                <p>Crea tu cuenta y gestiona el catálogo desde un solo lugar.</p>
            </div>
        </div>
        <div class="auth-brand-footer"><span class="auth-brand-dot"></span> Gestión simple para cada paso</div>
    </section>
    <!-- Formulario de alta: el token oculto protege contra solicitudes CSRF. -->
    <section class="auth-panel">
        <div class="card login-card">
            <div class="card-body">
                <p class="auth-eyebrow">Empieza aquí</p>
                <h1>Crear cuenta</h1>
                <p class="auth-subtitle">Al registrarte obtendrás acceso administrativo al catálogo.</p>
                <?php foreach ($errores as $error): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['registro_csrf']) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="usuario">Correo electrónico</label>
                        <input class="form-control" type="email" id="usuario" name="usuario" value="<?= htmlspecialchars($usuario) ?>" maxlength="100" autocomplete="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Contraseña</label>
                        <input class="form-control" type="password" id="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="confirmar_password">Confirmar contraseña</label>
                        <input class="form-control" type="password" id="confirmar_password" name="confirmar_password" minlength="8" maxlength="72" autocomplete="new-password" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Crear mi cuenta</button>
                </form>
                <p class="auth-switch">¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
            </div>
        </div>
    </section>
</main>
</body>
</html>
