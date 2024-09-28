<?php
session_start(); // Inicia la sesión

// Eliminar todas las variables de sesión
session_unset();

// Destruir la sesión
session_destroy();

// Redirigir al formulario de inicio de sesión
header("Location: ../index.html");
exit();
?>
