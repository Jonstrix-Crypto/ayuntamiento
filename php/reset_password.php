<?php
// reset_password.php

// Conectar a la base de datos
$servername = "localhost"; 
$username = "root"; 
$password = "Master22!";
$dbname = "ciudadania_digital";
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verificar si el token es válido y no ha expirado
    $sql = "SELECT * FROM password_resets WHERE token = ? AND expiry > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Mostrar formulario para ingresar la nueva contraseña
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $row = $result->fetch_assoc();
            $email = $row['email'];

            // Actualizar la contraseña del usuario
            $sql = "UPDATE usuarios SET password = ? WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $new_password, $email);
            if ($stmt->execute()) {
                echo "Contraseña actualizada exitosamente.";
            } else {
                echo "Hubo un error al actualizar la contraseña.";
            }

            // Eliminar el token de restablecimiento
            $sql = "DELETE FROM password_resets WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
        }
    } else {
        echo "El enlace ha expirado o es inválido.";
    }
} else {
    echo "Token no proporcionado.";
}

$stmt->close();
$conn->close();
?>
