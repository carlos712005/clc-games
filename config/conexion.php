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
        
        // Forzar timezone de MySQL para esta conexión (sesión) a UTC
        $zona = new DateTimeZone('Europe/Madrid'); /* Zona horaria deseada */
        $ahora = new DateTimeImmutable('now', $zona); /* Momento actual en esa zona */
        $offset = $zona->getOffset($ahora); /* Offset (la diferencia horaria) en segundos respecto a UTC */

        $signo = ($offset >= 0) ? '+' : '-'; /* Signo del offset (la diferencia horaria) */
        $offset = abs($offset); /* Valor absoluto del offset (la diferencia horaria) */
        $horas = str_pad((string) intdiv($offset, 3600), 2, '0', STR_PAD_LEFT); /* Horas del offset (la diferencia horaria) */
        $mins  = str_pad((string) intdiv($offset % 3600, 60), 2, '0', STR_PAD_LEFT); /* Minutos del offset (la diferencia horaria) */

        $conexion->exec("SET time_zone = '{$signo}{$horas}:{$mins}'"); /* Fijo la zona horaria de MySQL para esta conexión */
    } catch (PDOException $e) {
        // Manejo de errores de conexión
        die("Error de conexión a la base de datos: " . $e->getMessage()); /* Si hay error, muestro mensaje y termino */
    }

?>
