<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit;
}

// Conexión a la base de datos
$host = 'localhost';
$dbname = 'ciudadania_digital';
$username = 'root';
$password = 'Master22!';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Paginación
    $reportes_por_pagina = 10; // Número de reportes por página
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $inicio = ($pagina_actual > 1) ? ($pagina_actual * $reportes_por_pagina) - $reportes_por_pagina : 0;

    // Consulta para obtener el total de reportes con estatus 'realizado'
    $total_sql = "SELECT COUNT(*) FROM reportes WHERE estatus = 'realizado'";
    $total_stmt = $conn->prepare($total_sql);
    $total_stmt->execute();
    $total_reportes = $total_stmt->fetchColumn();

    // Calcular el número total de páginas
    $total_paginas = ceil($total_reportes / $reportes_por_pagina);

    // Consulta para obtener reportes con estatus 'realizado' con límite para paginación
    $sql = "SELECT nombre, telefono, direccion, area, descripcion, created_at 
            FROM reportes 
            WHERE estatus = 'realizado' 
            LIMIT :inicio, :reportes_por_pagina";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':inicio', $inicio, PDO::PARAM_INT);
    $stmt->bindParam(':reportes_por_pagina', $reportes_por_pagina, PDO::PARAM_INT);
    $stmt->execute();

    $reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Plataforma para reportar problemas en Ahualulco.">
    <meta name="keywords" content="ahualulco, reportes, ciudadanía digital">
    <title>GOBIERNO - Ciudadanía Digital</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>

    <header id="welcome-header">
        <h1><span class="highlight">Bienvenido,</span> <span class="user-text"><?php echo htmlspecialchars($_SESSION['user_email']); ?></span>!</h1>
        <a href="logout.php" class="logout-link">Cerrar sesión</a>
    </header>

    <main class="container">
        <div class="left-section">
            <p class="text-justify mb-3">A través del Sistema de <span class="highlight">Reportes Ciudadanos</span> de
                la Dirección de Servicios Municipales, puedes realizar tus reportes, quejas, solicitudes o propuestas
                con relación a los servicios públicos municipales. Tu participación es muy importante para nosotros, en
                el Ayuntamiento de Ahualulco de Mercado, Jal. Daremos seguimiento a tu trámite.</p>

            <p class="text-justify mb-3">Los ciudadanos de Ahualulco de Mercado, Jal. Pueden realizar
                reportes de servicios municipales que requieren atención, como: luminarias apagadas, baches, hundimientos en la
                carpeta asfáltica, basura vegetal, mantenimiento de parques, poda de árboles en vía pública, recolección de cacharros, entre otros.
                Un reporte de Atención Ciudadana se concluye cuando el asunto reportado por la ciudadanía ha sido atendido por el área correspondiente
                y el enlace responsable valida la correcta resolución y notifica al ciudadano.
                Horario de atención: De lunes a viernes de 09:00 a 15:00 horas. En línea puede levantar el reporte las 24 horas.</p>

            <p class="description">Para verificar la veracidad de su reporte, lo confirmaremos vía telefónica, por lo que es <span class="highlight">OBLIGATORIO</span> escribir su número de teléfono. El correo se utilizará para llevar el debido seguimiento del reporte una vez quede confirmado.</p>
        </div>
    </main>

    <div class="Text">
        <h2 style="text-align: center">Formulario de Reporte</h2>
    </div>

    <div class="bottom-section">
        <form action="../php/reportes.php" method="post" enctype="multipart/form-data">
            <div class="row">
                <div>
                    <label for="nombre">Nombre y apellidos</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div>
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" required>
                </div>
            </div>

            <div>
                <label for="direccion">Escriba la dirección donde se encuentra el desperfecto, además agregar entre qué calles se encuentran</label>
                <input type="text" id="direccion" name="direccion" required>
            </div>

            <div>
                <label for="area">Reporte Dirigido a:</label>
                <select id="area" name="area" required>
                    <option value="">Seleccione área</option>
                    <option value="Alumbrado">Alumbrado</option>
                    <option value="Parque">Parques</option>
                    <option value="Agua potable">Agua</option>
                </select>
            </div>

            <div>
                <label for="reporte">Describa el reporte</label>
                <textarea id="reporte" name="reporte" rows="4" required></textarea>
            </div>

            <div>
                <label for="imagen">Ingresar imagen del reporte</label>
                <input type="file" id="imagen" name="imagen" accept="image/*" required>
                <div id="preview-container">
                    <img id="preview-image" style="display:none; max-width: 200px; max-height: 200px;" />
                </div>
            </div>

            <div class="submit-container">
                <input type="submit" value="Enviar" class="submit-btn">
            </div>

        </form>

        <!-- JavaScript para previsualizar la imagen -->
        <script>
            document.getElementById('imagen').addEventListener('change', function(event) {
                const file = event.target.files[0];
                const preview = document.getElementById('preview-image');

                // Solo muestra si es una imagen
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block'; // Muestra la imagen
                    };
                    reader.readAsDataURL(file); // Convierte la imagen en base64
                } else {
                    preview.style.display = 'none'; // Esconde la imagen si no es válida
                }
            });
        </script>


    </div>

    <!-- Sección para mostrar los reportes con estatus 'realizado' y paginación -->
     <div class="Text">
        <h2 style="text-align: center">Reporte Realizados</h2>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Área</th>
                    <th>Descripción</th>
                    <th>Fecha de Creación</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportes as $reporte): ?>
                <tr>
                    <td><?php echo htmlspecialchars($reporte['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($reporte['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($reporte['direccion']); ?></td>
                    <td><?php echo htmlspecialchars($reporte['area']); ?></td>
                    <td><?php echo htmlspecialchars($reporte['descripcion']); ?></td>
                    <td><?php echo htmlspecialchars($reporte['created_at']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <div>
            <span>Items per page:</span>
            <select name="items_per_page">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="20">20</option>
            </select>
        </div>
        
        
        <div>
            <span><?php echo "$inicio-" . min($inicio + $reportes_por_pagina, $total_reportes) . " of $total_reportes"; ?></span>
            <button onclick="window.location.href='?pagina=1'">&lt;&lt;</button>
            <button onclick="window.location.href='?pagina=<?php echo max($pagina_actual - 1, 1); ?>'">&lt;</button>
            <button onclick="window.location.href='?pagina=<?php echo min($pagina_actual + 1, $total_paginas); ?>'">&gt;</button>
            <button onclick="window.location.href='?pagina=<?php echo $total_paginas; ?>'">&gt;&gt;</button>
        </div>
    </div>

    <footer id="welcome-footer">
        <p>&copy; 2024 GOBIERNO - Ciudadanía Digital. Todos los derechos reservados.</p>
        <p>Contacto: info@ahualulcodemercado.gob.mx</p>
        <p>Síguenos en nuestras redes sociales.</p>
        <a href="#" class="social-icon" target="_blank"><i class="fab fa-facebook"></i></a>
        <a href="#" class="social-icon" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="#" class="social-icon" target="_blank"><i class="fab fa-twitter"></i></a>
    </footer>

</body>

</html>