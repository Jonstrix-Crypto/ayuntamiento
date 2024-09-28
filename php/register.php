<?php
// register.php

// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
$servername = "localhost"; 
$username = "root"; 
$password = "Master22!";
$dbname = "ciudadania_digital";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar la conexión
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Conexión fallida: " . $conn->connect_error]));
}

// Obtener datos del formulario
$email = $_POST['reg-email'] ?? '';
$password = $_POST['reg-password'] ?? '';

// Verificar si el correo ya existe
$checkEmailStmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
if ($checkEmailStmt) {
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $result = $checkEmailStmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Error: La dirección de correo ya existe."]);
    } else {
        // Preparar y vincular la inserción, asignando el rol 'user' por defecto
        $stmt = $conn->prepare("INSERT INTO usuarios (email, password, user) VALUES (?, ?, 'user')");
        if ($stmt) {
            $stmt->bind_param("ss", $email, $password);
            // Ejecutar la consulta
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Registro exitoso."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error al registrar el usuario: " . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Error al preparar la consulta de inserción."]);
        }
    }
    $checkEmailStmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Error al preparar la consulta de verificación de correo."]);
}

// Cerrar la conexión
$conn->close();
?>


