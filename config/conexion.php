<?php

    // Parámetros de conexión
    $host       = "sql104.infinityfree.com";   // <- Host MySQL real
    $usuario    = "if0_40650969";        // <- Usuario MySQL (tu valor)
    $contrasena = "Carpuri715";             // <- Contraseña MySQL
    $base_datos = "if0_40650969_clcgames";        // <- Nombre BD
    $charset    = "utf8mb4";

    // Forzar puerto 3306 para evitar sockets
    $dsn = "mysql:host=$host;port=3306;dbname=$base_datos;charset=$charset";

    try {
        // Crear conexión PDO
        $conexion = new PDO($dsn, $usuario, $contrasena); /* Creo la conexión usando PDO */
        
        // Configurar PDO para que lance excepciones en caso de error
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); /* Para que me avise de errores */
        
        // Configurar PDO para que devuelva arrays asociativos por defecto
        $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); /* Para obtener arrays con nombres de campo */
        
        // Desactivar la emulación de prepared statements para mejor rendimiento
        $conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); /* Para usar prepared statements reales */
        
    } catch (PDOException $e) {
        // Manejo de errores de conexión
        die("Error de conexión a la base de datos: " . $e->getMessage()); /* Si hay error, muestro mensaje y termino */
    }

?>
