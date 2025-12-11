<?php
require_once(__DIR__ . '/../../login/sesion.php');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Administración</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#f5f7fb;color:#222}
header{background:#2c3e50;color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center}
header .title{font-size:18px;font-weight:700}
header .user{font-size:14px;opacity:.9}
.container{max-width:1100px;margin:24px auto;padding:0 16px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:16px}
.card{background:#fff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.08);padding:18px;display:flex;flex-direction:column;justify-content:space-between}
.card h3{margin:0 0 10px 0;font-size:16px}
.card p{margin:0 0 14px 0;font-size:13px;color:#555}
.btn{display:inline-block;text-decoration:none;background:#667eea;color:#fff;border-radius:10px;padding:10px 14px;font-weight:700}
.muted{color:#667}
.welcome{margin-bottom:20px}
</style>
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
