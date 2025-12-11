<?php
session_start();

$idUsuario = isset($_SESSION['idUsuario']) ? (int)$_SESSION['idUsuario'] : 0;
$sessionId = session_id();

include("config/conexion.php");
$con = conectar();

// Actualizar estado de la sesión en la base de datos
if ($con instanceof mysqli && $idUsuario > 0 && !empty($sessionId)) {
    // Intentar actualizar la sesión específica del usuario con el sessionId actual
    $stmt = $con->prepare("UPDATE sesiones 
                           SET fechaFin = NOW(), 
                               ultimaActividad = NOW(), 
                               estado = 'cerrada' 
                           WHERE idUsuario = ? 
                           AND sessionId = ? 
                           AND estado = 'activa'");
    
    if ($stmt) {
        $stmt->bind_param("is", $idUsuario, $sessionId);
        $stmt->execute();
        $updated = $stmt->affected_rows;
        $stmt->close();
        
        // Si no se actualizó ningún registro (posible inconsistencia), intentar por sessionId solo
        if ($updated === 0) {
            $stmt2 = $con->prepare("UPDATE sesiones 
                                    SET fechaFin = NOW(), 
                                        ultimaActividad = NOW(), 
                                        estado = 'cerrada' 
                                    WHERE sessionId = ? 
                                    AND estado = 'activa'");
            
            if ($stmt2) {
                $stmt2->bind_param("s", $sessionId);
                $stmt2->execute();
                $stmt2->close();
            }
        }
    }
}

// Limpiar todas las variables de sesión
$_SESSION = array();

// Eliminar la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Cerrar conexión a la base de datos
if (isset($con) && $con instanceof mysqli) {
    $con->close();
}

// Redirigir al index con mensaje opcional
header("Location: index.php?logout=success");
exit;
?>