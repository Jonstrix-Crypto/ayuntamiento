<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit;
}

// Conexión a la base de datos
$host = 'localhost';
$dbname = 'ciudadania_digital';
$username = 'root'; // Ajusta el usuario según tu configuración
$password = 'Master22!';     // Ajusta la contraseña según tu configuración

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Comprobar si se envió el formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'];
        $telefono = $_POST['telefono'];
        $direccion = $_POST['direccion'];
        $area = $_POST['area'];
        $descripcion = $_POST['reporte'];
        $estatus='pendiente'; //estatus inicial

        // Verificar si se subió la imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            // Leer el archivo de la imagen
            $imageData = file_get_contents($_FILES['imagen']['tmp_name']);
            $imageType = $_FILES['imagen']['type'];

            // Comprobar si el archivo es una imagen
            if (strpos($imageType, 'image/') === 0) {
                // Preparar la consulta SQL
                $sql = "INSERT INTO reportes (nombre, telefono, direccion, area, descripcion, imagen, estatus) VALUES (:nombre, :telefono, :direccion, :area, :descripcion, :imagen, :estatus)";
                $stmt = $conn->prepare($sql);

                // Ejecutar la consulta
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':telefono' => $telefono,
                    ':direccion' => $direccion,
                    ':area' => $area,
                    ':descripcion' => $descripcion,
                    ':imagen' => $imageData,
                    ':estatus' => $estatus //estatus inicial
                ]);

                echo "¡Reporte enviado correctamente!";
            } else {
                echo "El archivo subido no es una imagen válida.";
            }
        } else {
            echo "Error al subir la imagen.";
        }
    }

} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>
