<?php
// Archivo para registrar nueva sesión en la base de datos

if (!isset($_SESSION['idUsuario']) || !isset($_SESSION['session_token'])) {
    return false;
}

// Obtener información de la sesión
$idUsuario = $_SESSION['idUsuario'];
$sessionId = session_id();
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

// Detectar el módulo/rol al que pertenece
$modulo = 'Desconocido';
if (isset($_SESSION['nombreRol'])) {
    $modulo = $_SESSION['nombreRol'];
}

// Detectar tipo de dispositivo básico
$dispositivo = 'Desktop';
if (preg_match('/mobile|android|iphone|ipad|ipod/i', $userAgent)) {
    $dispositivo = 'Mobile';
} elseif (preg_match('/tablet/i', $userAgent)) {
    $dispositivo = 'Tablet';
}

// Preparar consulta para insertar sesión
$query = "INSERT INTO sesiones (idUsuario, sessionId, ipAddress, userAgent, modulo, dispositivo, estado) 
          VALUES (?, ?, ?, ?, ?, ?, 'activa')";

$stmt = mysqli_prepare($con, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "isssss", $idUsuario, $sessionId, $ipAddress, $userAgent, $modulo, $dispositivo);
    
    if (mysqli_stmt_execute($stmt)) {
        // Sesión registrada exitosamente
        $idSesion = mysqli_insert_id($con);
        $_SESSION['idSesion'] = $idSesion;
        
        // Opcional: Limpiar sesiones antiguas del mismo usuario (mantener solo las últimas 5)
        $cleanQuery = "DELETE FROM sesiones 
                       WHERE idUsuario = ? 
                       AND estado IN ('cerrada', 'expirada')
                       AND idSesion NOT IN (
                           SELECT idSesion FROM (
                               SELECT idSesion FROM sesiones 
                               WHERE idUsuario = ? 
                               ORDER BY fechaInicio DESC 
                               LIMIT 5
                           ) AS recent_sessions
                       )";
        
        $cleanStmt = mysqli_prepare($con, $cleanQuery);
        if ($cleanStmt) {
            mysqli_stmt_bind_param($cleanStmt, "ii", $idUsuario, $idUsuario);
            mysqli_stmt_execute($cleanStmt);
            mysqli_stmt_close($cleanStmt);
        }
        
        mysqli_stmt_close($stmt);
        return true;
    } else {
        error_log("Error al registrar sesión: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
} else {
    error_log("Error al preparar statement de sesión: " . mysqli_error($con));
    return false;
}
?>