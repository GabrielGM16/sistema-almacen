<?php
/**
 * Script de limpieza de sesiones expiradas
 * Este script debe ejecutarse periódicamente (por ejemplo, mediante cron)
 * para mantener la tabla de sesiones limpia
 */

include("config/conexion.php");

$con = conectar();

if (!$con) {
    error_log("Error: No se pudo conectar a la base de datos para limpiar sesiones");
    exit(1);
}

// 1. Marcar como expiradas las sesiones activas sin actividad en más de 30 minutos
$query1 = "UPDATE sesiones 
           SET estado = 'expirada', 
               fechaFin = ultimaActividad 
           WHERE estado = 'activa' 
           AND ultimaActividad < DATE_SUB(NOW(), INTERVAL 30 MINUTE)";

$result1 = mysqli_query($con, $query1);

if ($result1) {
    $sesionesExpiradas = mysqli_affected_rows($con);
    error_log("Sesiones marcadas como expiradas: $sesionesExpiradas");
} else {
    error_log("Error al marcar sesiones como expiradas: " . mysqli_error($con));
}

// 2. Eliminar sesiones cerradas o expiradas con más de 30 días de antigüedad
$query2 = "DELETE FROM sesiones 
           WHERE estado IN ('cerrada', 'expirada') 
           AND fechaInicio < DATE_SUB(NOW(), INTERVAL 30 DAY)";

$result2 = mysqli_query($con, $query2);

if ($result2) {
    $sesionesEliminadas = mysqli_affected_rows($con);
    error_log("Sesiones antiguas eliminadas: $sesionesEliminadas");
} else {
    error_log("Error al eliminar sesiones antiguas: " . mysqli_error($con));
}

// 3. Mantener solo las últimas 10 sesiones por usuario (para usuarios con muchas sesiones)
$query3 = "DELETE s1 FROM sesiones s1
           LEFT JOIN (
               SELECT idUsuario, idSesion
               FROM (
                   SELECT idUsuario, idSesion, 
                          ROW_NUMBER() OVER (PARTITION BY idUsuario ORDER BY fechaInicio DESC) as rn
                   FROM sesiones
               ) AS ranked
               WHERE rn <= 10
           ) s2 ON s1.idSesion = s2.idSesion
           WHERE s2.idSesion IS NULL
           AND s1.estado IN ('cerrada', 'expirada')";

$result3 = mysqli_query($con, $query3);

if ($result3) {
    $sesionesLimitadas = mysqli_affected_rows($con);
    error_log("Sesiones excedentes por usuario eliminadas: $sesionesLimitadas");
} else {
    error_log("Error al limitar sesiones por usuario: " . mysqli_error($con));
}

mysqli_close($con);

echo "Limpieza de sesiones completada.\n";
echo "- Sesiones expiradas: $sesionesExpiradas\n";
echo "- Sesiones antiguas eliminadas: $sesionesEliminadas\n";
echo "- Sesiones excedentes eliminadas: $sesionesLimitadas\n";

exit(0);
?>