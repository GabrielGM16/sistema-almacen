<?php
require_once(__DIR__ . '/../../login/sesion.php');
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Administración</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>

<body>
    <header>
        <div class="title">Panel de Administración</div>
        <div class="user"><?php echo htmlspecialchars($nombreCompleto ?? ''); ?> • <?php echo htmlspecialchars($nombreRol ?? ''); ?></div>
    </header>
    <div class="container">
        <div class="welcome">Bienvenido al panel de administración.</div>
        <div class="grid">
            <div class="card">
                <h3>Usuarios</h3>
                <p>Alta, edición, activación y eliminación de usuarios.</p>
                <a class="btn" href="./usuarios/index.php">Administrar usuarios</a>
            </div>
        </div>
    </div>
</body>

</html>