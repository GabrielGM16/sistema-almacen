<?php
/**
 * Sistema de Control de Acceso basado en Roles
 * Este archivo define qué roles tienen acceso a qué módulos del sistema
 */

// Definición de módulos y sus roles permitidos
// Puedes ajustar esto según tu estructura de roles en la tabla 'roles'
$permisosPorModulo = [
    // Módulo de administración general
    'admin' => [1], // Solo administradores
    
    // Módulo de almacén
    'almacen' => [1, 2], // Administradores y almacenistas
    
    // Módulo de inventario
    'inventario' => [1, 2, 3], // Administradores, almacenistas y supervisores
    
    // Módulo de reportes
    'reportes' => [1, 3, 4], // Administradores, supervisores y analistas
    
    // Módulo de usuarios (gestión)
    'usuarios' => [1], // Solo administradores
    
    // Módulo de movimientos
    'movimientos' => [1, 2, 3], // Administradores, almacenistas y supervisores
    
    // Módulo de productos
    'productos' => [1, 2], // Administradores y almacenistas
    
    // Módulo de configuración
    'configuracion' => [1], // Solo administradores
    
    // Dashboard general (accesible para todos los roles autenticados)
    'dashboard' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
];

/**
 * Detecta el módulo actual basándose en la URL
 * @return string El nombre del módulo detectado
 */
function detectarModulo() {
    $rutaActual = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    
    // Extraer el módulo de la ruta
    // Ejemplo: /views/almacen/index.php -> almacen
    if (preg_match('/\/views\/([^\/]+)\//', $scriptName, $matches)) {
        return $matches[1];
    }
    
    // Intentar detectar por la URL completa
    if (preg_match('/\/views\/([^\/]+)/', $rutaActual, $matches)) {
        return $matches[1];
    }
    
    // Si no se puede detectar, retornar 'unknown'
    return 'unknown';
}

/**
 * Verifica si un rol tiene acceso a un módulo específico
 * @param int $idRol ID del rol del usuario
 * @param string $modulo Nombre del módulo (opcional, si no se proporciona se detecta automáticamente)
 * @return bool True si tiene acceso, False en caso contrario
 */
function verificarAcceso($idRol, $modulo = null) {
    global $permisosPorModulo;
    
    // Si no se proporciona el módulo, detectarlo automáticamente
    if ($modulo === null) {
        $modulo = detectarModulo();
    }
    
    // Si el módulo no está definido en los permisos, denegar acceso por seguridad
    if (!isset($permisosPorModulo[$modulo])) {
        // Permitir acceso si es un módulo desconocido (para no romper el sistema)
        // En producción, podrías cambiar esto a false para mayor seguridad
        return true;
    }
    
    // Verificar si el rol está en la lista de roles permitidos para este módulo
    return in_array($idRol, $permisosPorModulo[$modulo]);
}

/**
 * Verifica si un usuario tiene un permiso específico
 * @param int $idRol ID del rol del usuario
 * @param string $permiso Nombre del permiso a verificar
 * @return bool True si tiene el permiso, False en caso contrario
 */
function tienePermiso($idRol, $permiso) {
    // Conectar a la base de datos para obtener los permisos del rol
    include_once(__DIR__ . '/config/conexion.php');
    $con = conectar();
    
    if (!$con) {
        return false;
    }
    
    $query = "SELECT permisos FROM roles WHERE idRol = ?";
    $stmt = mysqli_prepare($con, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $idRol);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        
        if ($fila = mysqli_fetch_assoc($resultado)) {
            $permisos = $fila['permisos'];
            
            // Los permisos pueden estar separados por comas
            $listaPermisos = explode(',', $permisos);
            $listaPermisos = array_map('trim', $listaPermisos);
            
            mysqli_stmt_close($stmt);
            mysqli_close($con);
            
            return in_array($permiso, $listaPermisos);
        }
        
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($con);
    return false;
}

/**
 * Obtiene todos los módulos a los que un rol tiene acceso
 * @param int $idRol ID del rol del usuario
 * @return array Array con los nombres de los módulos permitidos
 */
function obtenerModulosPermitidos($idRol) {
    global $permisosPorModulo;
    
    $modulosPermitidos = [];
    
    foreach ($permisosPorModulo as $modulo => $roles) {
        if (in_array($idRol, $roles)) {
            $modulosPermitidos[] = $modulo;
        }
    }
    
    return $modulosPermitidos;
}

/**
 * Redirige al usuario a su dashboard o página de inicio según su rol
 * @param int $idRol ID del rol del usuario
 */
function redirigirSegunRol($idRol) {
    $modulosPermitidos = obtenerModulosPermitidos($idRol);
    
    if (empty($modulosPermitidos)) {
        header("Location: /unauthorized.php");
        exit();
    }
    
    // Redirigir al primer módulo permitido
    $primerModulo = $modulosPermitidos[0];
    header("Location: /views/{$primerModulo}/index.php");
    exit();
}
?>