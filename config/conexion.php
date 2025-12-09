<?php

    // Parámetros de conexión
    $host = "localhost"; /* Servidor de base de datos, uso localhost porque está en mi equipo */
    $usuario = "root"; /* Usuario de MySQL, root es el administrador por defecto */
    $contrasena = ""; /* Contraseña vacía porque en XAMPP root no tiene contraseña */
    $base_datos = "clcgames"; /* Nombre de mi base de datos del proyecto */
    $charset = "utf8mb4"; /* Charset para compatibilidad total con UTF-8 y emojis */

    // DSN (Data Source Name) para PDO
    $dsn = "mysql:host=$host;dbname=$base_datos;charset=$charset"; /* Cadena de conexión con todos los parámetros */

    try { /* Inicio bloque try para capturar errores */
        // Crear conexión PDO
        $conexion = new PDO($dsn, $usuario, $contrasena); /* Creo la conexión usando PDO */
        
        // Configurar PDO para que lance excepciones en caso de error
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); /* Para que me avise de errores */
        
        // Configurar PDO para que devuelva arrays asociativos por defecto
        $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); /* Para obtener arrays con nombres de campo */
        
        // Desactivar la emulación de prepared statements para mejor rendimiento
        $conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); /* Para usar prepared statements reales */
        
    } catch (PDOException $e) { /* Inicio bloque catch para capturar errores */
        // Manejo de errores de conexión
        die("Error de conexión a la base de datos: " . $e->getMessage()); /* Si hay error, muestro mensaje y termino */
    }

?>
