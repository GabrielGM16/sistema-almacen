<?php
/**
 * Ping de Sesión
 * Este archivo se llama periódicamente desde el frontend para mantener la sesión activa
 * y actualizar la última actividad en la base de datos
 */

session_start();

// Verificar si hay sesión activa
if (!isset($_SESSION['idUsuario']) || !isset($_SESSION['session_token'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'No hay sesión activa'
    ]);
    exit;
}

// Actualizar tiempo de último acceso
$_SESSION['ultimo_acceso'] = time();

// Actualizar última actividad en la base de datos
include_once(__DIR__ . '/../config/conexion.php');
$con = conectar();

if ($con) {
    $sessionId = session_id();
    $stmt = $con->prepare("UPDATE sesiones 
                          SET ultimaActividad = NOW() 
                          WHERE sessionId = ? 
                          AND estado = 'activa'");
    
    if ($stmt) {
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        
        if ($affected > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Sesión actualizada',
                'tiempo_restante' => 1800 // 30 minutos en segundos
            ]);
        } else {
            // Si no se actualizó ninguna fila, la sesión podría haber expirado
            echo json_encode([
                'status' => 'warning',
                'message' => 'Sesión no encontrada o expirada'
            ]);
        }
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al actualizar sesión'
        ]);
    }
    
    mysqli_close($con);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error de conexión a base de datos'
    ]);
}
?>