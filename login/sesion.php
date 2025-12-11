<?php
session_start();

// Función auxiliar para obtener la ruta base del proyecto
function obtenerRutaBase() {
    // Detectar si estamos en un subdirectorio o en la raíz del servidor web
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    
    // Extraer el directorio base del proyecto
    $pathInfo = pathinfo($scriptName);
    $basePath = '';
    
    // Si el script está en views/ o subdirectorios, calcular la ruta base
    if (strpos($scriptName, '/views/') !== false) {
        // Contar cuántos niveles estamos dentro de views
        $viewsPos = strpos($scriptName, '/views/');
        $basePath = substr($scriptName, 0, $viewsPos);
    }
    
    return $basePath;
}

// CONTROL DE TIEMPO DE SESIÓN (30 minutos de inactividad)
$tiempo_inactividad = 1800; // 30 minutos en segundos

if (isset($_SESSION['ultimo_acceso'])) {
    $tiempo_transcurrido = time() - $_SESSION['ultimo_acceso'];
    
    if ($tiempo_transcurrido > $tiempo_inactividad) {
        // Marcar sesión como expirada en BD antes de destruir
        if (isset($_SESSION['idUsuario']) && isset($_SESSION['session_token'])) {
            include_once(__DIR__ . '/../config/conexion.php');
            $con = conectar();
            if ($con) {
                $sessionId = session_id();
                $stmt = $con->prepare("UPDATE sesiones SET fechaFin = NOW(), estado = 'expirada' WHERE sessionId = ? AND estado = 'activa'");
                if ($stmt) {
                    $stmt->bind_param("s", $sessionId);
                    $stmt->execute();
                    $stmt->close();
                }
                mysqli_close($con);
            }
        }
        
        // Destruir sesión
        session_unset();
        session_destroy();
        
        $basePath = obtenerRutaBase();
        header("Location: {$basePath}/index.php?session=expired");
        exit();
    }
}

// Actualizar tiempo de último acceso
$_SESSION['ultimo_acceso'] = time();

// Verificar si la sesión está activa
if (!isset($_SESSION['idUsuario'])) {
    $basePath = obtenerRutaBase();
    header("Location: {$basePath}/unauthorized.php");
    exit();
}

// Incluir el sistema de control de acceso
$access_control_path = __DIR__ . '/../access_control.php';

// Verificar si el archivo existe antes de incluirlo
if (file_exists($access_control_path)) {
    require_once $access_control_path;
    
    // Verificar permisos para la página actual solo si las funciones están disponibles
    if (function_exists('verificarAcceso')) {
        if (!verificarAcceso($_SESSION['idRol'] ?? null)) {
            // El usuario no tiene permisos para esta página
            $currentModule = function_exists('detectarModulo') ? detectarModulo() : 'unknown';
           
            // Log del intento de acceso no autorizado
            error_log("Acceso denegado - Usuario: {$_SESSION['nombreCompleto']} (Rol: {$_SESSION['idRol']}) intentó acceder a: {$_SERVER['REQUEST_URI']}");
           
            // Obtener ruta base para redirección
            $basePath = obtenerRutaBase();
            
            // Redirigir según el tipo de error
            if ($currentModule === 'unknown') {
                header("Location: {$basePath}/404.php");
            } else {
                header("Location: {$basePath}/403.php");
            }
            exit();
        }
        
        // Actualizar última actividad en BD
        if (isset($_SESSION['idUsuario']) && isset($_SESSION['session_token'])) {
            include_once(__DIR__ . '/../config/conexion.php');
            $con = conectar();
            if ($con) {
                $sessionId = session_id();
                $stmt = $con->prepare("UPDATE sesiones SET ultimaActividad = NOW() WHERE sessionId = ? AND estado = 'activa'");
                if ($stmt) {
                    $stmt->bind_param("s", $sessionId);
                    $stmt->execute();
                    $stmt->close();
                }
                mysqli_close($con);
            }
        }
    } else {
        // Log si la función no existe pero continúa
        error_log("Función verificarAcceso() no encontrada en access_control.php");
    }
} else {
    // Log si el archivo no existe pero continúa
    error_log("Archivo access_control.php no encontrado en: " . $access_control_path);
    // Continuar sin control de acceso para que el sistema funcione
}

// Obtener los datos de sesión
$datosSesion = array(
    'codigoEmpleado' => $_SESSION['codigoEmpleado'] ?? null,
    'idUsuario' => $_SESSION['idUsuario'] ?? null,
    'nombre' => $_SESSION['nombre'] ?? null,
    'apellidoPaterno' => $_SESSION['apellidoPaterno'] ?? null,
    'apellidoMaterno' => $_SESSION['apellidoMaterno'] ?? null,
    'nombreCompleto' => $_SESSION['nombreCompleto'] ?? null,
    'email' => $_SESSION['email'] ?? null,
    'idRol' => $_SESSION['idRol'] ?? null,
    'nombreRol' => $_SESSION['nombreRol'] ?? null,
    'imagenPerfil' => $_SESSION['imagenPerfil'] ?? 'default-avatar.png'
);

// Definir variables individuales
$codigoEmpleado = $datosSesion['codigoEmpleado'];
$idUsuario = $datosSesion['idUsuario'];
$nombre = $datosSesion['nombre'];
$apellidoPaterno = $datosSesion['apellidoPaterno'];
$apellidoMaterno = $datosSesion['apellidoMaterno'];
$nombreCompleto = $datosSesion['nombreCompleto'];
$email = $datosSesion['email'];
$idRol = $datosSesion['idRol'];
$nombreRol = $datosSesion['nombreRol'];
$imagenPerfil = $datosSesion['imagenPerfil'];

// Variable para logs y auditoría
$logUsuario = "$codigoEmpleado $nombre $apellidoPaterno $apellidoMaterno";
?>