<?php
session_start();
require_once(__DIR__ . '/../config/conexion.php');
$con = conectar();
$usuario = trim($_POST['usuario'] ?? ($_GET['usuario'] ?? ''));
$pass = $_POST['contrasena'] ?? ($_GET['contrasena'] ?? '');
$response = ['status' => 'error'];
if ($usuario !== '' && $pass !== '') {
    $stmt = $con->prepare("SELECT u.*, r.rol AS nombreRol FROM usuarios u LEFT JOIN roles r ON r.idRol = u.idRol WHERE (u.codigo_empleado = ? OR u.email = ?) AND u.activo = 1 LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("ss", $usuario, $usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $stored = $row['contrasena'];
            $ok = false;
            if (strpos($stored, 'HASH_') === 0) {
                $ok = ($pass === substr($stored, 5));
            } else {
                $ok = password_verify($pass, $stored);
            }
            if ($ok) {
                $_SESSION['idUsuario'] = intval($row['id_usuario']);
                $_SESSION['codigoEmpleado'] = $row['codigo_empleado'];
                $_SESSION['nombre'] = $row['nombre'];
                $_SESSION['apellidoPaterno'] = $row['apellido_paterno'];
                $_SESSION['apellidoMaterno'] = $row['apellido_materno'];
                $_SESSION['nombreCompleto'] = trim($row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']);
                $_SESSION['email'] = $row['email'];
                $_SESSION['idRol'] = intval($row['idRol']);
                $_SESSION['nombreRol'] = $row['nombreRol'];
                $_SESSION['imagenPerfil'] = $row['imagen_perfil'] ?: 'default-avatar.png';
                $_SESSION['session_token'] = bin2hex(random_bytes(16));
                require_once(__DIR__ . '/register_sesion.php');
                require_once(__DIR__ . '/acces_control.php');
                $mods = obtenerModulosPermitidos($_SESSION['idRol']);
                $modulo = !empty($mods) ? $mods[0] : 'admin';
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '80') == '443' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
                $projectBase = rtrim(dirname(dirname($scriptName)), '/');
                if ($projectBase === '') { $projectBase = ''; }
                $redirect = "{$scheme}://{$host}" . ($projectBase ? "{$projectBase}" : "") . "/views/{$modulo}/index.php";
                $response = ['status' => 'success', 'redirectUrl' => $redirect];
                $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if (!$isAjax) {
                    header("Location: {$redirect}");
                    exit;
                }
            }
        }
        $stmt->close();
    }
}
mysqli_close($con);
header('Content-Type: application/json');
echo json_encode($response);
