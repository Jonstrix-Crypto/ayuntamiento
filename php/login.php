<?php
session_start();

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'ciudadania_digital';
$user = 'root';
$pass = 'Master22!';

try {
    // Conexión a la base de datos usando PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Error en la conexión a la base de datos: " . $e->getMessage());
}

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura de datos del formulario
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    // Verificar credenciales sin password_hash()
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND password = ?");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch();

    if ($user) {
        // Credenciales correctas
        $_SESSION['user_email'] = $email;

        // Redirigir según el rol del usuario
        if ($user['user'] === 'admin') {
            header("Location: administracion.php");
        } else {
            header("Location: bienvenida.php");
        }
        exit; // Asegúrate de usar exit después de la redirección
    } else {
        // Credenciales incorrectas
        echo "<script>
                alert('Correo o contraseña incorrectos');
                window.location.href = 'login.php';
              </script>";
    }
}
?>

