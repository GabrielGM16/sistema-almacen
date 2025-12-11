<?php
require_once(__DIR__ . '/../../login/sesion.php');
include_once(__DIR__ . '/../../config/conexion.php');

// Obtener estadísticas del sistema
$con = conectar();

// Estadísticas de usuarios
$totalUsuarios = 0;
$usuariosActivos = 0;
$usuariosInactivos = 0;

if ($con) {
    // Total de usuarios
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM usuarios");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $totalUsuarios = $row['total'];
    }
    
    // Usuarios activos
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $usuariosActivos = $row['total'];
    }
    
    // Usuarios inactivos
    $usuariosInactivos = $totalUsuarios - $usuariosActivos;
    
    // Sesiones activas hoy
    $sesionesHoy = 0;
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM sesiones WHERE DATE(fechaInicio) = CURDATE()");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $sesionesHoy = $row['total'];
    }
    
    // Últimos usuarios registrados
    $ultimosUsuarios = [];
    $result = mysqli_query($con, "SELECT u.*, r.rol as nombreRol 
                                   FROM usuarios u 
                                   LEFT JOIN roles r ON u.idRol = r.idRol 
                                   ORDER BY u.fecha_creacion DESC 
                                   LIMIT 5");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $ultimosUsuarios[] = $row;
        }
    }
    
    // Actividad reciente (últimas sesiones)
    $actividadReciente = [];
    $result = mysqli_query($con, "SELECT s.*, u.nombre, u.apellido_paterno, u.codigo_empleado 
                                   FROM sesiones s 
                                   INNER JOIN usuarios u ON s.idUsuario = u.id_usuario 
                                   ORDER BY s.fechaInicio DESC 
                                   LIMIT 10");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $actividadReciente[] = $row;
        }
    }
    
    // Total de roles
    $totalRoles = 0;
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM roles");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $totalRoles = $row['total'];
    }
}

// Obtener hora del día para el saludo
$hora = date('H');
if ($hora >= 6 && $hora < 12) {
    $saludo = "Buenos días";
    $iconoSaludo = "";
} elseif ($hora >= 12 && $hora < 19) {
    $saludo = "Buenas tardes";
    $iconoSaludo = "";
} else {
    $saludo = "Buenas noches";
    $iconoSaludo = "";
}

// Nombre del usuario para el saludo
$nombrePrimero = explode(' ', $nombre ?? '')[0];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Panel de Administración - Sistema de Almacén</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <style>
        /* Estilos adicionales específicos para el dashboard */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 30px;
        }
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .activity-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 8px;
            transition: background 0.2s;
            margin-bottom: 8px;
        }
        
        .activity-item:hover {
            background: #f8f9fc;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .activity-info {
            flex: 1;
        }
        
        .activity-time {
            font-size: 12px;
            color: #95a5a6;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-card-mini {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: white;
            border-radius: 12px;
            border: 1px solid #e3e8ef;
            transition: all 0.3s;
        }
        
        .user-card-mini:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4a90e2, #357abd);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e3e8ef;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 8px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #50c878, #45b369);
            border-radius: 10px;
            transition: width 1s ease;
        }
        
        @media (max-width: 968px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 600px) {
            .stats-overview {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="title">Panel de Administración</div>
        <div class="user">
            <?php echo htmlspecialchars($nombreCompleto ?? 'Usuario'); ?> • 
            <span style="opacity: 0.8;"><?php echo htmlspecialchars($nombreRol ?? 'Admin'); ?></span>
        </div>
    </header>

    <!-- Contenedor Principal -->
    <div class="container">
        
        <!-- Mensaje de Bienvenida Personalizado -->
        <div class="welcome">
            <h2><?php echo $saludo; ?>, <?php echo htmlspecialchars($nombrePrimero); ?>!</h2>
            <p>Este es tu panel de control. Aquí puedes gestionar todos los aspectos del sistema de almacén.</p>
        </div>

        <!-- Estadísticas Generales -->
        <div class="stats-overview">
            <div class="stat-card" style="border-left-color: #4a90e2;">
                <span class="stat-value"><?php echo $totalUsuarios; ?></span>
                <span class="stat-label">Total Usuarios</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 100%;"></div>
                </div>
            </div>
            
            <div class="stat-card" style="border-left-color: #50c878;">
                <span class="stat-value"><?php echo $usuariosActivos; ?></span>
                <span class="stat-label">Usuarios Activos</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $totalUsuarios > 0 ? ($usuariosActivos / $totalUsuarios * 100) : 0; ?>%;"></div>
                </div>
            </div>
            
            <div class="stat-card" style="border-left-color: #f39c12;">
                <span class="stat-value"><?php echo $sesionesHoy; ?></span>
                <span class="stat-label">Sesiones Hoy</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo min(100, $sesionesHoy * 5); ?>%; background: linear-gradient(90deg, #f39c12, #e67e22);"></div>
                </div>
            </div>
            
            <div class="stat-card" style="border-left-color: #e74c3c;">
                <span class="stat-value"><?php echo $totalRoles; ?></span>
                <span class="stat-label">Roles del Sistema</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo min(100, $totalRoles * 10); ?>%; background: linear-gradient(90deg, #e74c3c, #c0392b);"></div>
                </div>
            </div>
        </div>

        <!-- Grid Principal: Contenido y Sidebar -->
        <div class="dashboard-grid">
            <!-- Columna Principal -->
            <div>
                <!-- Accesos Rápidos -->
                <h3 class="section-title">Accesos Rápidos</h3>
                <div class="quick-actions">
                    <div class="card card-primary">
                        <div class="card-icon">
                            <div class="icon"></div>
                            <div style="flex: 1;">
                                <h3>Gestión de Usuarios</h3>
                                <p>Administra usuarios, roles y permisos del sistema</p>
                            </div>
                        </div>
                        <a href="./usuarios/index.php" class="btn btn-block">Administrar Usuarios</a>
                    </div>

                    <div class="card card-success">
                        <div class="card-icon">
                            <div class="icon" style="background: linear-gradient(135deg, #50c878, #45b369);"></div>
                            <div style="flex: 1;">
                                <h3>Roles y Permisos</h3>
                                <p>Define roles y asigna permisos de acceso</p>
                            </div>
                        </div>
                        <a href="./roles/index.php" class="btn btn-secondary btn-block">Gestionar Roles</a>
                    </div>

                    <div class="card card-warning">
                        <div class="card-icon">
                            <div class="icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);"></div>
                            <div style="flex: 1;">
                                <h3>Reportes del Sistema</h3>
                                <p>Consulta estadísticas y genera reportes</p>
                            </div>
                        </div>
                        <a href="./reportes/index.php" class="btn btn-block">Ver Reportes</a>
                    </div>

                    <div class="card card-danger">
                        <div class="card-icon">
                            <div class="icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);"></div>
                            <div style="flex: 1;">
                                <h3>Configuración</h3>
                                <p>Ajustes generales del sistema</p>
                            </div>
                        </div>
                        <a href="./configuracion/index.php" class="btn btn-block">Configurar</a>
                    </div>

                    <div class="card">
                        <div class="card-icon">
                            <div class="icon" style="background: linear-gradient(135deg, #3498db, #2980b9);"></div>
                            <div style="flex: 1;">
                                <h3>Auditoría</h3>
                                <p>Revisa logs y actividad del sistema</p>
                            </div>
                        </div>
                        <a href="./auditoria/index.php" class="btn btn-outline btn-block">Ver Auditoría</a>
                    </div>

                    <div class="card">
                        <div class="card-icon">
                            <div class="icon" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);"></div>
                            <div style="flex: 1;">
                                <h3>Notificaciones</h3>
                                <p>Gestiona alertas y notificaciones</p>
                            </div>
                        </div>
                        <a href="./notificaciones/index.php" class="btn btn-outline btn-block">Ver Notificaciones</a>
                    </div>
                </div>

                <!-- Actividad Reciente -->
                <h3 class="section-title">Actividad Reciente del Sistema</h3>
                <div class="activity-card">
                    <?php if (empty($actividadReciente)): ?>
                        <p class="muted text-center">No hay actividad registrada</p>
                    <?php else: ?>
                        <?php foreach ($actividadReciente as $actividad): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <?php
                                    // Icono según el estado de la sesión
                                    if ($actividad['estado'] == 'activa') echo '';
                                    elseif ($actividad['estado'] == 'cerrada') echo '';
                                    else echo '';
                                    ?>
                                </div>
                                <div class="activity-info">
                                    <strong><?php echo htmlspecialchars($actividad['nombre'] . ' ' . $actividad['apellido_paterno']); ?></strong>
                                    <div style="font-size: 13px; color: #6c757d;">
                                        <?php 
                                        if ($actividad['estado'] == 'activa') {
                                            echo 'Inició sesión';
                                        } elseif ($actividad['estado'] == 'cerrada') {
                                            echo 'Cerró sesión';
                                        } else {
                                            echo 'Sesión expirada';
                                        }
                                        ?>
                                        • <?php echo htmlspecialchars($actividad['modulo'] ?? 'Sistema'); ?>
                                    </div>
                                    <div class="activity-time">
                                        <?php 
                                        $fecha = new DateTime($actividad['fechaInicio']);
                                        echo $fecha->format('d/m/Y H:i'); 
                                        ?>
                                        • <?php echo htmlspecialchars($actividad['dispositivo'] ?? 'Desktop'); ?>
                                    </div>
                                </div>
                                <span class="badge <?php echo $actividad['estado'] == 'activa' ? 'badge-success' : ($actividad['estado'] == 'cerrada' ? 'badge-danger' : 'badge-warning'); ?>">
                                    <?php echo ucfirst($actividad['estado']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar Derecho -->
            <div>
                <!-- Últimos Usuarios Registrados -->
                <h3 class="section-title">Últimos Registros</h3>
                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 30px;">
                    <?php if (empty($ultimosUsuarios)): ?>
                        <p class="muted">No hay usuarios registrados</p>
                    <?php else: ?>
                        <?php foreach ($ultimosUsuarios as $usuario): ?>
                            <div class="user-card-mini">
                                <div class="user-avatar">
                                    <?php 
                                    // Iniciales del usuario
                                    $iniciales = strtoupper(substr($usuario['nombre'], 0, 1) . substr($usuario['apellido_paterno'], 0, 1));
                                    echo $iniciales;
                                    ?>
                                </div>
                                <div style="flex: 1;">
                                    <strong style="font-size: 14px;">
                                        <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido_paterno']); ?>
                                    </strong>
                                    <div style="font-size: 12px; color: #6c757d;">
                                        <?php echo htmlspecialchars($usuario['codigo_empleado']); ?>
                                    </div>
                                    <div style="font-size: 11px; color: #95a5a6;">
                                        <?php echo htmlspecialchars($usuario['nombreRol'] ?? 'Sin rol'); ?>
                                    </div>
                                </div>
                                <span class="badge <?php echo $usuario['activo'] ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Información del Sistema -->
                <h3 class="section-title">Información del Sistema</h3>
                <div class="card">
                    <h3>Estado del Sistema</h3>
                    <div style="margin: 16px 0;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="font-size: 14px; color: #6c757d;">Usuarios Activos</span>
                            <strong><?php echo round($totalUsuarios > 0 ? ($usuariosActivos / $totalUsuarios * 100) : 0); ?>%</strong>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $totalUsuarios > 0 ? ($usuariosActivos / $totalUsuarios * 100) : 0; ?>%;"></div>
                        </div>
                    </div>
                    
                    <div style="border-top: 1px solid #e3e8ef; padding-top: 16px; margin-top: 16px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                            <span style="font-size: 14px; color: #6c757d;">Versión</span>
                            <strong>v1.0.0</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                            <span style="font-size: 14px; color: #6c757d;">Última actualización</span>
                            <strong><?php echo date('d/m/Y'); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 14px; color: #6c757d;">Estado del servidor</span>
                            <span class="badge badge-success">Operativo</span>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas del Sidebar -->
                <div class="card mt-3">
                    <h3>Herramientas Rápidas</h3>
                    <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 12px;">
                        <a href="./backup.php" class="btn btn-sm btn-outline btn-block">Respaldo del Sistema</a>
                        <a href="./limpiar_sesiones.php" class="btn btn-sm btn-outline btn-block">Limpiar Sesiones</a>
                        <a href="./logs.php" class="btn btn-sm btn-outline btn-block">Ver Logs</a>
                        <a href="../../logout.php" class="btn btn-sm danger btn-block" onclick="return confirm('¿Deseas cerrar la sesión?');">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Animación de las barras de progreso al cargar
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-fill');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
            
            // Actualizar hora actual
            function actualizarHora() {
                const ahora = new Date();
                const opciones = { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                };
                const fechaHora = ahora.toLocaleDateString('es-MX', opciones);
                // console.log(fechaHora); // Puedes mostrar esto en algún elemento si quieres
            }
            
            actualizarHora();
            setInterval(actualizarHora, 60000); // Actualizar cada minuto
        });
    </script>
</body>
</html>
<?php
if ($con) {
    mysqli_close($con);
}
?>
