<?php
require_once(__DIR__ . '/../../../login/sesion.php');
require_once(__DIR__ . '/../../../config/conexion.php');

$con = conectar();
$mensaje = null;
$error = null;

function limpiar($v)
{
    return trim($v ?? '');
}

// Procesar acciones POST
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
                // Verificar si el código de empleado ya existe
                $checkStmt = $con->prepare("SELECT id_usuario FROM usuarios WHERE codigo_empleado = ?");
                $checkStmt->bind_param("s", $codigo);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                
                if ($checkResult->num_rows > 0) {
                    $error = "El código de empleado ya existe";
                } else {
                    $hash = password_hash($passI, PASSWORD_DEFAULT);
                    $stmt = $con->prepare("INSERT INTO usuarios (codigo_empleado, nombre, apellido_paterno, apellido_materno, email, contrasena, idRol) VALUES (?,?,?,?,?,?,?)");
                    
                    if ($stmt) {
                        $stmt->bind_param("ssssssi", $codigo, $nombreI, $apPat, $apMat, $emailI, $hash, $rolI);
                        
                        if ($stmt->execute()) {
                            $mensaje = "Usuario creado exitosamente";
                        } else {
                            $error = "Error al crear el usuario: " . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $error = "Error de preparación de consulta";
                    }
                }
                $checkStmt->close();
            } else {
                $error = "Todos los campos marcados son obligatorios";
            }
            
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
                        
                        if ($stmt->execute()) {
                            $mensaje = "Usuario actualizado exitosamente";
                        } else {
                            $error = "Error al actualizar el usuario";
                        }
                        $stmt->close();
                    }
                } else {
                    $stmt = $con->prepare("UPDATE usuarios SET codigo_empleado=?, nombre=?, apellido_paterno=?, apellido_materno=?, email=?, idRol=? WHERE id_usuario=?");
                    
                    if ($stmt) {
                        $stmt->bind_param("sssssii", $codigo, $nombreI, $apPat, $apMat, $emailI, $rolI, $idU);
                        
                        if ($stmt->execute()) {
                            $mensaje = "Usuario actualizado exitosamente";
                        } else {
                            $error = "Error al actualizar el usuario";
                        }
                        $stmt->close();
                    }
                }
            } else {
                $error = "Datos incompletos para actualizar";
            }
            
        } elseif ($action === 'toggle_activo') {
            $idU = intval($_POST['id_usuario'] ?? 0);
            $nuevo = intval($_POST['nuevo_activo'] ?? 0);
            
            if ($idU) {
                $stmt = $con->prepare("UPDATE usuarios SET activo=? WHERE id_usuario=?");
                
                if ($stmt) {
                    $stmt->bind_param("ii", $nuevo, $idU);
                    
                    if ($stmt->execute()) {
                        $mensaje = $nuevo ? "Usuario activado" : "Usuario desactivado";
                    } else {
                        $error = "Error al cambiar el estado";
                    }
                    $stmt->close();
                }
            }
            
        } elseif ($action === 'eliminar_usuario') {
            $idU = intval($_POST['id_usuario'] ?? 0);
            
            if ($idU) {
                // No permitir eliminar al usuario actual
                if ($idU == $idUsuario) {
                    $error = "No puedes eliminar tu propio usuario";
                } else {
                    $stmt = $con->prepare("DELETE FROM usuarios WHERE id_usuario=?");
                    
                    if ($stmt) {
                        $stmt->bind_param("i", $idU);
                        
                        if ($stmt->execute()) {
                            $mensaje = "Usuario eliminado exitosamente";
                        } else {
                            $error = "Error al eliminar: Puede que tenga registros relacionados";
                        }
                        $stmt->close();
                    }
                }
            }
        }
    } else {
        $error = "No tienes permisos para realizar esta acción";
    }
}

// Obtener roles
$roles = [];
$rsr = $con->query("SELECT idRol, rol, permisos FROM roles ORDER BY rol ASC");
if ($rsr) {
    while ($row = $rsr->fetch_assoc()) {
        $roles[] = $row;
    }
    $rsr->close();
}

// Obtener usuarios con información de sus sesiones
$usuarios = [];
$query = "SELECT 
            u.id_usuario, 
            u.codigo_empleado, 
            u.nombre, 
            u.apellido_paterno, 
            u.apellido_materno, 
            u.email, 
            u.idRol, 
            u.activo, 
            u.fecha_creacion,
            u.imagen_perfil,
            r.rol,
            (SELECT COUNT(*) FROM sesiones WHERE idUsuario = u.id_usuario AND estado = 'activa') as sesiones_activas
          FROM usuarios u 
          LEFT JOIN roles r ON r.idRol = u.idRol 
          ORDER BY u.fecha_creacion DESC";

$rsu = $con->query($query);
if ($rsu) {
    while ($row = $rsu->fetch_assoc()) {
        $usuarios[] = $row;
    }
    $rsu->close();
}

mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Sistema de Almacén</title>
    <link rel="stylesheet" href="../../../assets/css/admin.css">
    <link rel="stylesheet" href="../../../assets/css/admin-usuarios.css">
    <style>
        /* Estilos adicionales específicos para esta página */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }
        
        .stat-box:hover {
            transform: translateY(-8px);
        }
        
        .stat-box.secondary {
            background: linear-gradient(135deg, #2dce89 0%, #24a46d 100%);
        }
        
        .stat-box.warning {
            background: linear-gradient(135deg, #fb6340 0%, #ea5230 100%);
        }
        
        .stat-box.info {
            background: linear-gradient(135deg, #11cdef 0%, #0da5c0 100%);
        }
        
        .stat-value {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.95;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .actions-cell {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        
        .btn-icon-only {
            padding: 8px 12px;
            min-width: auto;
        }
        
        .user-avatar-mini {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #667eea;
        }
        
        .table-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .hidden {
            display: none !important;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <div class="title">Sistema de Almacén • Gestión de Usuarios</div>
        <div class="user">
            <?php echo htmlspecialchars($nombreCompleto ?? ''); ?> • 
            <strong><?php echo htmlspecialchars($nombreRol ?? ''); ?></strong>
        </div>
    </header>

    <!-- Contenedor Principal -->
    <div class="container">
        
        <!-- Estadísticas Rápidas -->
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-value"><?php echo count($usuarios); ?></div>
                <div class="stat-label">Total Usuarios</div>
            </div>
            <div class="stat-box secondary">
                <div class="stat-value">
                    <?php echo count(array_filter($usuarios, function($u) { return $u['activo'] == 1; })); ?>
                </div>
                <div class="stat-label">Usuarios Activos</div>
            </div>
            <div class="stat-box warning">
                <div class="stat-value">
                    <?php echo count(array_filter($usuarios, function($u) { return $u['activo'] == 0; })); ?>
                </div>
                <div class="stat-label">Usuarios Inactivos</div>
            </div>
            <div class="stat-box info">
                <div class="stat-value"><?php echo count($roles); ?></div>
                <div class="stat-label">Roles Disponibles</div>
            </div>
        </div>

        <!-- Alertas -->
        <?php if ($mensaje): ?>
            <div class="alert success" id="alertMessage">
                <div>
                    <strong>¡Éxito!</strong> <?php echo htmlspecialchars($mensaje); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error" id="alertError">
                <div>
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Grid Principal -->
        <div class="grid">
            
            <?php if ($idRol == 1): ?>
            <!-- Card: Formulario de Nuevo Usuario -->
            <div class="card card-elevated">
                <header>
                    <span class="title">Registrar Nuevo Usuario</span>
                </header>
                <div class="content">
                    <form method="post" id="formCrearUsuario" onsubmit="return validarFormulario(this)">
                        <input type="hidden" name="action" value="crear_usuario">
                        
                        <!-- Información Básica -->
                        <h4 style="margin-bottom: 16px; color: #344767;">  Información Básica</h4>
                        <div class="form-row">
                            <div class="field">
                                <label class="required">Código de Empleado</label>
                                <input type="text" 
                                       name="codigo_empleado" 
                                       placeholder="Ej: EMP001" 
                                       maxlength="20" 
                                       required
                                       pattern="[A-Za-z0-9]+"
                                       title="Solo letras y números">
                            </div>
                            <div class="field">
                                <label class="required">Nombre(s)</label>
                                <input type="text" 
                                       name="nombre" 
                                       placeholder="Nombre completo" 
                                       required
                                       maxlength="60">
                            </div>
                            <div class="field">
                                <label class="required">Apellido Paterno</label>
                                <input type="text" 
                                       name="apellido_paterno" 
                                       placeholder="Apellido paterno" 
                                       required
                                       maxlength="60">
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="form-row">
                            <div class="field">
                                <label>Apellido Materno</label>
                                <input type="text" 
                                       name="apellido_materno" 
                                       placeholder="Apellido materno (opcional)"
                                       maxlength="60">
                            </div>
                            <div class="field">
                                <label class="required">Correo Electrónico</label>
                                <input type="email" 
                                       name="email" 
                                       placeholder="usuario@empresa.com" 
                                       required
                                       maxlength="120">
                            </div>
                            <div class="field">
                                <label class="required">Rol</label>
                                <select name="idRol" required>
                                    <option value="">Seleccionar rol...</option>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?php echo intval($r['idRol']); ?>">
                                            <?php echo htmlspecialchars($r['rol']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Seguridad -->
                        <h4 style="margin: 20px 0 16px; color: #344767;"> Seguridad</h4>
                        <div class="form-row form-row-2">
                            <div class="field">
                                <label class="required">Contraseña</label>
                                <input type="password" 
                                       name="contrasena" 
                                       placeholder="Mínimo 8 caracteres" 
                                       required
                                       minlength="8"
                                       id="password">
                                <small class="muted">Debe contener al menos 8 caracteres</small>
                            </div>
                            <div class="field">
                                <label class="required">Confirmar Contraseña</label>
                                <input type="password" 
                                       placeholder="Repite la contraseña" 
                                       required
                                       minlength="8"
                                       id="password_confirm">
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="btn-group mt-3">
                            <button type="submit" class="btn primary btn-icon icon-save">
                                Guardar Usuario
                            </button>
                            <button type="reset" class="btn secondary">
                                Limpiar Formulario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <!-- Mensaje para usuarios sin permisos -->
            <div class="alert info">
                <div>
                    <strong>Información:</strong> Solo tienes permisos de lectura en este módulo.
                </div>
            </div>
            <?php endif; ?>

            <!-- Card: Listado de Usuarios -->
            <div class="card card-elevated">
                <header>
                    <span class="title">Listado de Usuarios Registrados</span>
                </header>
                <div class="content">
                    
                    <!-- Barra de búsqueda y filtros -->
                    <div class="table-actions">
                        <div class="search-bar" style="flex: 1; max-width: 400px; margin-bottom: 0;">
                            <input type="text" 
                                   id="searchInput" 
                                   placeholder="Buscar por nombre, código o email..." 
                                   onkeyup="filtrarTabla()">
                        </div>
                        
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <label style="font-weight: 600; color: #344767;">Filtrar:</label>
                            <select id="filtroEstado" onchange="filtrarTabla()" style="padding: 10px; border-radius: 8px; border: 2px solid #e3e8ef;">
                                <option value="todos">Todos</option>
                                <option value="activos">Solo Activos</option>
                                <option value="inactivos">Solo Inactivos</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tabla de Usuarios -->
                    <div class="table-container">
                        <table class="table" id="tablaUsuarios">
                            <thead>
                                <tr>
                                    <th>Avatar</th>
                                    <th>ID</th>
                                    <th>Código</th>
                                    <th>Nombre Completo</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Sesiones</th>
                                    <th>Fecha Registro</th>
                                    <?php if ($idRol == 1): ?>
                                    <th class="text-center">Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center muted">
                                            No hay usuarios registrados
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($usuarios as $u): ?>
                                        <tr data-activo="<?php echo $u['activo']; ?>">
                                            <td>
                                                <img src="../../../assets/images/perfiles/<?php echo htmlspecialchars($u['imagen_perfil'] ?? 'default-avatar.png'); ?>" 
                                                     alt="Avatar" 
                                                     class="user-avatar-mini"
                                                     onerror="this.src='../../../assets/images/default-avatar.png'">
                                            </td>
                                            <td><strong><?php echo intval($u['id_usuario']); ?></strong></td>
                                            <td><code><?php echo htmlspecialchars($u['codigo_empleado']); ?></code></td>
                                            <td>
                                                <?php 
                                                $nombreCompleto = trim($u['nombre'] . ' ' . $u['apellido_paterno'] . ' ' . ($u['apellido_materno'] ?? ''));
                                                echo htmlspecialchars($nombreCompleto); 
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?php echo htmlspecialchars($u['rol'] ?? 'Sin rol'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status <?php echo $u['activo'] ? 'on' : 'off'; ?>">
                                                    <?php echo $u['activo'] ? 'Activo' : 'Inactivo'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($u['sesiones_activas'] > 0): ?>
                                                    <span class="badge badge-success" title="Sesiones activas">
                                                        <?php echo $u['sesiones_activas']; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="nowrap">
                                                <?php 
                                                $fecha = new DateTime($u['fecha_creacion']);
                                                echo $fecha->format('d/m/Y H:i'); 
                                                ?>
                                            </td>
                                            <?php if ($idRol == 1): ?>
                                            <td>
                                                <div class="actions-cell">
                                                    <!-- Botón Activar/Desactivar -->
                                                    <form method="post" style="display:inline-block; margin: 0;">
                                                        <input type="hidden" name="action" value="toggle_activo">
                                                        <input type="hidden" name="id_usuario" value="<?php echo intval($u['id_usuario']); ?>">
                                                        <input type="hidden" name="nuevo_activo" value="<?php echo $u['activo'] ? 0 : 1; ?>">
                                                        <button class="btn <?php echo $u['activo'] ? 'warn' : 'success'; ?> btn-sm btn-icon-only" 
                                                                type="submit"
                                                                title="<?php echo $u['activo'] ? 'Desactivar' : 'Activar'; ?>">
                                                            <?php echo $u['activo'] ? ' ' : ' '; ?>
                                                        </button>
                                                    </form>
                                                    
                                                    <!-- Botón Editar -->
                                                    <button class="btn secondary btn-sm btn-icon-only" 
                                                            onclick="abrirModalEditar(<?php echo intval($u['id_usuario']); ?>, <?php echo htmlspecialchars(json_encode($u), ENT_QUOTES); ?>)"
                                                            title="Editar">
                                                         
                                                    </button>
                                                    
                                                    <!-- Botón Eliminar -->
                                                    <?php if ($u['id_usuario'] != $idUsuario): ?>
                                                    <form method="post" 
                                                          style="display:inline-block; margin: 0;" 
                                                          onsubmit="return confirm('  ¿Estás seguro de eliminar al usuario <?php echo htmlspecialchars($nombreCompleto); ?>?\n\nEsta acción no se puede deshacer.');">
                                                        <input type="hidden" name="action" value="eliminar_usuario">
                                                        <input type="hidden" name="id_usuario" value="<?php echo intval($u['id_usuario']); ?>">
                                                        <button class="btn danger btn-sm btn-icon-only" type="submit" title="Eliminar">
                                                             
                                                        </button>
                                                    </form>
                                                    <?php else: ?>
                                                    <button class="btn secondary btn-sm btn-icon-only" disabled title="No puedes eliminarte a ti mismo">
                                                         
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Información de tabla -->
                    <div style="margin-top: 20px; padding-top: 16px; border-top: 2px solid #f0f2f8;">
                        <p class="muted">
                            Mostrando <strong id="totalVisible"><?php echo count($usuarios); ?></strong> de 
                            <strong><?php echo count($usuarios); ?></strong> usuarios
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edición -->
    <div id="modalEditar" class="modal-overlay hidden" onclick="cerrarModalEditar(event)">
        <div class="modal" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 class="modal-title">Editar Usuario</h2>
            </div>
            
            <form method="post" id="formEditarUsuario" onsubmit="return validarFormularioEditar(this)">
                <input type="hidden" name="action" value="actualizar_usuario">
                <input type="hidden" name="id_usuario" id="edit_id">
                
                <div class="form-row">
                    <div class="field">
                        <label class="required">Código de Empleado</label>
                        <input type="text" name="codigo_empleado" id="edit_codigo" required maxlength="20">
                    </div>
                    <div class="field">
                        <label class="required">Nombre(s)</label>
                        <input type="text" name="nombre" id="edit_nombre" required maxlength="60">
                    </div>
                    <div class="field">
                        <label class="required">Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" id="edit_apellido_paterno" required maxlength="60">
                    </div>
                </div>

                <div class="form-row">
                    <div class="field">
                        <label>Apellido Materno</label>
                        <input type="text" name="apellido_materno" id="edit_apellido_materno" maxlength="60">
                    </div>
                    <div class="field">
                        <label class="required">Email</label>
                        <input type="email" name="email" id="edit_email" required maxlength="120">
                    </div>
                    <div class="field">
                        <label class="required">Rol</label>
                        <select name="idRol" id="edit_rol" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?php echo intval($r['idRol']); ?>">
                                    <?php echo htmlspecialchars($r['rol']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row form-row-1">
                    <div class="field">
                        <label>Nueva Contraseña</label>
                        <input type="password" name="contrasena" id="edit_password" placeholder="Dejar en blanco para no cambiar" minlength="8">
                        <small class="muted">Solo completa este campo si deseas cambiar la contraseña</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn secondary" onclick="cerrarModalEditar()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn primary">
                          Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Auto-ocultar alertas después de 5 segundos
        setTimeout(function() {
            const alertMessage = document.getElementById('alertMessage');
            const alertError = document.getElementById('alertError');
            
            if (alertMessage) {
                alertMessage.style.transition = 'opacity 0.5s ease';
                alertMessage.style.opacity = '0';
                setTimeout(() => alertMessage.remove(), 500);
            }
            
            if (alertError) {
                alertError.style.transition = 'opacity 0.5s ease';
                alertError.style.opacity = '0';
                setTimeout(() => alertError.remove(), 500);
            }
        }, 5000);

        // Validar formulario de creación
        function validarFormulario(form) {
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;
            
            if (password !== passwordConfirm) {
                alert('  Las contraseñas no coinciden. Por favor verifica.');
                return false;
            }
            
            if (password.length < 8) {
                alert('  La contraseña debe tener al menos 8 caracteres.');
                return false;
            }
            
            return confirm('¿Deseas crear este usuario?');
        }

        // Validar formulario de edición
        function validarFormularioEditar(form) {
            const password = document.getElementById('edit_password').value;
            
            if (password && password.length < 8) {
                alert('  La contraseña debe tener al menos 8 caracteres.');
                return false;
            }
            
            return confirm('¿Deseas guardar los cambios?');
        }

        // Abrir modal de edición
        function abrirModalEditar(id, datos) {
            document.getElementById('edit_id').value = datos.id_usuario;
            document.getElementById('edit_codigo').value = datos.codigo_empleado;
            document.getElementById('edit_nombre').value = datos.nombre;
            document.getElementById('edit_apellido_paterno').value = datos.apellido_paterno;
            document.getElementById('edit_apellido_materno').value = datos.apellido_materno || '';
            document.getElementById('edit_email').value = datos.email;
            document.getElementById('edit_rol').value = datos.idRol;
            document.getElementById('edit_password').value = '';
            
            document.getElementById('modalEditar').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Cerrar modal de edición
        function cerrarModalEditar(event) {
            if (!event || event.target === event.currentTarget) {
                document.getElementById('modalEditar').classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        // Cerrar modal con tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cerrarModalEditar();
            }
        });

        // Filtrar tabla
        function filtrarTabla() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const filtroEstado = document.getElementById('filtroEstado').value;
            const tabla = document.getElementById('tablaUsuarios');
            const filas = tabla.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            let contadorVisible = 0;
            
            for (let i = 0; i < filas.length; i++) {
                const fila = filas[i];
                const textoFila = fila.textContent.toLowerCase();
                const activo = fila.getAttribute('data-activo');
                
                let mostrarPorBusqueda = textoFila.includes(searchInput);
                let mostrarPorEstado = true;
                
                if (filtroEstado === 'activos') {
                    mostrarPorEstado = activo === '1';
                } else if (filtroEstado === 'inactivos') {
                    mostrarPorEstado = activo === '0';
                }
                
                if (mostrarPorBusqueda && mostrarPorEstado) {
                    fila.style.display = '';
                    contadorVisible++;
                } else {
                    fila.style.display = 'none';
                }
            }
            
            document.getElementById('totalVisible').textContent = contadorVisible;
        }

        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.animationDelay = (index * 0.1) + 's';
            });
        });
    </script>

</body>
</html>