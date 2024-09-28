<?php
// forgot_password.php

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

$email = $_POST['forgot-email'];

// Comprobar si el correo existe en la base de datos
$sql = "SELECT * FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Generar un token único para el restablecimiento de contraseña
    $token = bin2hex(random_bytes(50));
    $expiry = date("Y-m-d H:i:s", strtotime('+1 hour')); // El enlace expira en 1 hora

    // Insertar el token en la base de datos junto con la expiración
    $sql = "INSERT INTO password_resets (email, token, expiry) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $email, $token, $expiry);
    $stmt->execute();

    // Enviar correo electrónico al usuario
    $resetLink = "https://tusitio.com/reset_password.php?token=" . $token;
    $subject = "Recuperar Contraseña";
    $message = "Haz clic en el siguiente enlace para restablecer tu contraseña: " . $resetLink;
    $headers = "From: no-reply@tusitio.com\r\n";
    
    if (mail($email, $subject, $message, $headers)) {
        echo json_encode(['status' => 'success', 'message' => 'Te hemos enviado un enlace para restablecer tu contraseña a tu correo.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo enviar el correo electrónico.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No encontramos ese correo electrónico.']);
}

$stmt->close();
$conn->close();
?>
