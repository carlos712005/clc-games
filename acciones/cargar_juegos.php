<?php

    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */
    session_start(); /* Inicio la sesión para acceder a las variables de usuario */

    // Verificar que el usuario esté logueado y sea administrador
    if(!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
        echo '<div class="error"><h2>Acceso no autorizado</h2></div>'; /* Devuelvo error */
        exit; /* Termino la ejecución */
    }

    $_SESSION['modo_edicion'] = 'juegos'; /* Indico que estamos en modo edición de juegos */

    try { /* Inicio bloque try para capturar errores */
        
        // Obtener todos los juegos
        $consulta = $conexion->prepare("SELECT id, nombre, portada, tipo, activo, precio, resumen FROM juegos ORDER BY actualizado_en DESC"); /* Preparo consulta para obtener juegos */
        $consulta->execute(); /* Ejecuto la consulta */
        $juegos = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los juegos */
        
        // Incluir función de mostrar juegos
        require_once __DIR__ . "/../funciones/mostrar_juegos.php"; /* Incluyo la función */
        
        // Generar el HTML de los juegos indicando que es el panel de administrador
        mostrarJuegos($juegos, $conexion, true); /* Llamo a la función que genera HTML pasando true para panel admin */
        
    } catch (PDOException $e) { /* Si hay error en la consulta */
        echo '<div class="error"><h2>Error al cargar los juegos: ' . htmlspecialchars($e->getMessage()) . '</h2></div>'; /* Devuelvo error */
    }
    
?>
