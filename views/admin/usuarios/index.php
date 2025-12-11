<?php
require_once(__DIR__ . '/../../../login/sesion.php');
require_once(__DIR__ . '/../../../config/conexion.php');
$con = conectar();
$mensaje = null;
$error = null;
function limpiar($v){return trim($v ?? '');}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = limpiar($_POST['action'] ?? '');
    if ($idRol == 1) {
        if ($action === 'crear_usuario') {
            $codigo = limpiar($_POST['codigo_empleado'] ?? '');
            $nombreI = limpiar($_POST['nombre'] ?? '');
            $apPat = limpiar($_POST['apellido_paterno'] ?? '');
            $apMat = limpiar($_POST['apellido_materno'] ?? '');
            $emailI = limpiar($_POST['email'] ?? '');
            $passI = $_POST['contrasena'] ?? '';
            $rolI = intval($_POST['idRol'] ?? 0);
            if ($codigo && $nombreI && $apPat && $emailI && $passI && $rolI) {
                $hash = password_hash($passI, PASSWORD_DEFAULT);
                $stmt = $con->prepare("INSERT INTO usuarios (codigo_empleado, nombre, apellido_paterno, apellido_materno, email, contrasena, idRol) VALUES (?,?,?,?,?,?,?)");
                if ($stmt) {
                    $stmt->bind_param("ssssssi", $codigo, $nombreI, $apPat, $apMat, $emailI, $hash, $rolI);
                    if ($stmt->execute()) {$mensaje="Usuario creado";} else {$error="Error al crear";}
                    $stmt->close();
                } else {$error="Error de preparación";}
            } else {$error="Datos incompletos";}
        } elseif ($action === 'actualizar_usuario') {
            $idU = intval($_POST['id_usuario'] ?? 0);
            $codigo = limpiar($_POST['codigo_empleado'] ?? '');
            $nombreI = limpiar($_POST['nombre'] ?? '');
            $apPat = limpiar($_POST['apellido_paterno'] ?? '');
            $apMat = limpiar($_POST['apellido_materno'] ?? '');
            $emailI = limpiar($_POST['email'] ?? '');
            $rolI = intval($_POST['idRol'] ?? 0);
            $passI = $_POST['contrasena'] ?? '';
            if ($idU && $codigo && $nombreI && $apPat && $emailI && $rolI) {
                if ($passI !== '') {
                    $hash = password_hash($passI, PASSWORD_DEFAULT);
                    $stmt = $con->prepare("UPDATE usuarios SET codigo_empleado=?, nombre=?, apellido_paterno=?, apellido_materno=?, email=?, contrasena=?, idRol=? WHERE id_usuario=?");
                    if ($stmt) {
                        $stmt->bind_param("ssssssii", $codigo, $nombreI, $apPat, $apMat, $emailI, $hash, $rolI, $idU);
                        if ($stmt->execute()) {$mensaje="Usuario actualizado";} else {$error="Error al actualizar";}
                        $stmt->close();
                    } else {$error="Error de preparación";}
                } else {
                    $stmt = $con->prepare("UPDATE usuarios SET codigo_empleado=?, nombre=?, apellido_paterno=?, apellido_materno=?, email=?, idRol=? WHERE id_usuario=?");
                    if ($stmt) {
                        $stmt->bind_param("sssssii", $codigo, $nombreI, $apPat, $apMat, $emailI, $rolI, $idU);
                        if ($stmt->execute()) {$mensaje="Usuario actualizado";} else {$error="Error al actualizar";}
                        $stmt->close();
                    } else {$error="Error de preparación";}
                }
            } else {$error="Datos incompletos";}
        } elseif ($action === 'toggle_activo') {
            $idU = intval($_POST['id_usuario'] ?? 0);
            $nuevo = intval($_POST['nuevo_activo'] ?? 0);
            if ($idU) {
                $stmt = $con->prepare("UPDATE usuarios SET activo=? WHERE id_usuario=?");
                if ($stmt) {
                    $stmt->bind_param("ii", $nuevo, $idU);
                    if ($stmt->execute()) {$mensaje="Estado actualizado";} else {$error="Error al actualizar";}
                    $stmt->close();
                } else {$error="Error de preparación";}
            } else {$error="ID inválido";}
        } elseif ($action === 'eliminar_usuario') {
            $idU = intval($_POST['id_usuario'] ?? 0);
            if ($idU) {
                $stmt = $con->prepare("DELETE FROM usuarios WHERE id_usuario=?");
                if ($stmt) {
                    $stmt->bind_param("i", $idU);
                    if ($stmt->execute()) {$mensaje="Usuario eliminado";} else {$error="Error al eliminar";}
                    $stmt->close();
                } else {$error="Error de preparación";}
            } else {$error="ID inválido";}
        }
    } else {$error="Sin permisos";}
}
$roles = [];
$rsr = $con->query("SELECT idRol, rol FROM roles ORDER BY rol ASC");
if ($rsr) {while ($row = $rsr->fetch_assoc()) {$roles[]=$row;} $rsr->close();}
$usuarios = [];
$rsu = $con->query("SELECT u.id_usuario, u.codigo_empleado, u.nombre, u.apellido_paterno, u.apellido_materno, u.email, u.idRol, u.activo, u.fecha_creacion, r.rol FROM usuarios u LEFT JOIN roles r ON r.idRol = u.idRol ORDER BY u.id_usuario ASC");
if ($rsu) {while ($row = $rsu->fetch_assoc()) {$usuarios[]=$row;} $rsu->close();}
mysqli_close($con);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Admin • Usuarios</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{font-family:Arial,Helvetica,sans-serif;margin:0;background:#f5f7fb;color:#222}
header{background:#2c3e50;color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center}
header .title{font-size:18px;font-weight:700}
header .user{font-size:14px;opacity:.9}
.container{max-width:1100px;margin:24px auto;padding:0 16px}
.grid{display:grid;grid-template-columns:1fr;gap:16px}
.card{background:#fff;border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,.08);overflow:hidden}
.card header{background:#fff;color:#222;padding:14px 16px;border-bottom:1px solid #eee}
.card header .title{font-size:16px}
.card .content{padding:16px}
.alert{padding:12px 16px;border-radius:8px;margin-bottom:12px;font-size:14px}
.alert.success{background:#e9f7ef;color:#2e7d32}
.alert.error{background:#fdecea;color:#c62828}
.form-row{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:12px}
.form-row .field{display:flex;flex-direction:column}
.field label{font-size:12px;margin-bottom:6px;color:#444}
.field input,.field select{padding:10px;border:1px solid #ddd;border-radius:8px;font-size:14px}
.btn{border:none;border-radius:8px;padding:10px 14px;font-weight:600;cursor:pointer}
.btn.primary{background:#667eea;color:#fff}
.btn.secondary{background:#f0f2f8}
.btn.warn{background:#ffc107}
.btn.danger{background:#e74c3c;color:#fff}
.table{width:100%;border-collapse:collapse}
.table th,.table td{padding:10px;border-bottom:1px solid #eee;font-size:14px;text-align:left}
.status{display:inline-block;padding:6px 10px;border-radius:20px;font-size:12px;font-weight:700}
.status.on{background:#e9f7ef;color:#2e7d32}
.status.off{background:#fdecea;color:#c62828}
.muted{color:#667}
.nowrap{white-space:nowrap}
.center{display:flex;justify-content:center;align-items:center}
details summary{list-style:none}
details summary::-webkit-details-marker{display:none}
@media (max-width:900px){.form-row{grid-template-columns:1fr 1fr}.nowrap{white-space:normal}}
@media (max-width:600px){.form-row{grid-template-columns:1fr}}
</style>
</head>
<body>
<header>
  <div class="title">Administración • Usuarios</div>
  <div class="user"><?php echo htmlspecialchars($nombreCompleto ?? ''); ?> • <?php echo htmlspecialchars($nombreRol ?? ''); ?></div>
</header>
<div class="container">
  <div class="grid">
    <div class="card">
      <header><div class="title">Gestión de Usuarios</div></header>
      <div class="content">
        <?php if ($mensaje): ?><div class="alert success"><?php echo htmlspecialchars($mensaje); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($idRol == 1): ?>
        <form method="post">
          <input type="hidden" name="action" value="crear_usuario">
          <div class="form-row">
            <div class="field"><label>Código</label><input name="codigo_empleado" required></div>
            <div class="field"><label>Nombre</label><input name="nombre" required></div>
            <div class="field"><label>Apellido Paterno</label><input name="apellido_paterno" required></div>
          </div>
          <div class="form-row">
            <div class="field"><label>Apellido Materno</label><input name="apellido_materno"></div>
            <div class="field"><label>Email</label><input type="email" name="email" required></div>
            <div class="field"><label>Contraseña</label><input type="password" name="contrasena" required></div>
          </div>
          <div class="form-row">
            <div class="field">
              <label>Rol</label>
              <select name="idRol" required>
                <option value="">Selecciona</option>
                <?php foreach ($roles as $r): ?>
                  <option value="<?php echo intval($r['idRol']); ?>"><?php echo htmlspecialchars($r['rol']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field center">
              <button class="btn primary" type="submit">Crear Usuario</button>
            </div>
          </div>
        </form>
        <?php else: ?>
        <div class="alert muted">Solo lectura</div>
        <?php endif; ?>
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Código</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Rol</th>
              <th>Activo</th>
              <th>Creación</th>
              <th class="nowrap">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $u): ?>
            <tr>
              <td><?php echo intval($u['id_usuario']); ?></td>
              <td><?php echo htmlspecialchars($u['codigo_empleado']); ?></td>
              <td><?php echo htmlspecialchars(trim($u['nombre'].' '.$u['apellido_paterno'].' '.$u['apellido_materno'])); ?></td>
              <td><?php echo htmlspecialchars($u['email']); ?></td>
              <td><?php echo htmlspecialchars($u['rol'] ?? ''); ?></td>
              <td><span class="status <?php echo $u['activo'] ? 'on' : 'off'; ?>"><?php echo $u['activo'] ? 'Activo' : 'Inactivo'; ?></span></td>
              <td><?php echo htmlspecialchars($u['fecha_creacion']); ?></td>
              <td class="nowrap">
                <?php if ($idRol == 1): ?>
                <form method="post" style="display:inline-block">
                  <input type="hidden" name="action" value="toggle_activo">
                  <input type="hidden" name="id_usuario" value="<?php echo intval($u['id_usuario']); ?>">
                  <input type="hidden" name="nuevo_activo" value="<?php echo $u['activo'] ? 0 : 1; ?>">
                  <button class="btn <?php echo $u['activo'] ? 'warn' : 'primary'; ?>" type="submit"><?php echo $u['activo'] ? 'Desactivar' : 'Activar'; ?></button>
                </form>
                <details style="display:inline-block;margin-left:6px">
                  <summary class="btn secondary">Editar</summary>
                  <form method="post" style="margin-top:8px">
                    <input type="hidden" name="action" value="actualizar_usuario">
                    <input type="hidden" name="id_usuario" value="<?php echo intval($u['id_usuario']); ?>">
                    <div class="form-row">
                      <div class="field"><label>Código</label><input name="codigo_empleado" value="<?php echo htmlspecialchars($u['codigo_empleado']); ?>" required></div>
                      <div class="field"><label>Nombre</label><input name="nombre" value="<?php echo htmlspecialchars($u['nombre']); ?>" required></div>
                      <div class="field"><label>Apellido Paterno</label><input name="apellido_paterno" value="<?php echo htmlspecialchars($u['apellido_paterno']); ?>" required></div>
                    </div>
                    <div class="form-row">
                      <div class="field"><label>Apellido Materno</label><input name="apellido_materno" value="<?php echo htmlspecialchars($u['apellido_materno']); ?>"></div>
                      <div class="field"><label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($u['email']); ?>" required></div>
                      <div class="field"><label>Nueva Contraseña</label><input type="password" name="contrasena" placeholder="Opcional"></div>
                    </div>
                    <div class="form-row">
                      <div class="field">
                        <label>Rol</label>
                        <select name="idRol" required>
                          <?php foreach ($roles as $r): ?>
                            <option value="<?php echo intval($r['idRol']); ?>" <?php echo intval($u['idRol']) === intval($r['idRol']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($r['rol']); ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="field center">
                        <button class="btn primary" type="submit">Guardar</button>
                      </div>
                    </div>
                  </form>
                </details>
                <form method="post" style="display:inline-block;margin-left:6px" onsubmit="return confirm('¿Eliminar usuario?');">
                  <input type="hidden" name="action" value="eliminar_usuario">
                  <input type="hidden" name="id_usuario" value="<?php echo intval($u['id_usuario']); ?>">
                  <button class="btn danger" type="submit">Eliminar</button>
                </form>
                <?php else: ?>
                <span class="muted">Sin permisos</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>
