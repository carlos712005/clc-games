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
        // Verificar si hay una búsqueda activa
        if(isset($_SESSION['datos_busqueda']) && isset($_SESSION['datos_busqueda']['juegos_encontrados'])) {
            $ids_juegos = $_SESSION['datos_busqueda']['juegos_encontrados']; /* Obtengo los IDs de juegos encontrados */
            
            // Preparar una consulta con los IDs de juegos encontrados
            $cantidad = count($ids_juegos); /* Cantidad de juegos encontrados */
            $signos = array_fill(0, $cantidad, '?'); /* Creo un array de forma ['?', '?', '?', ...] */
            $cadena = implode(',', $signos); /* Uno con comas: '?,?,?' */
            $consulta = $conexion->prepare("
                SELECT j.id, j.nombre, j.fecha_lanzamiento, j.portada, j.tipo, j.activo, j.precio, j.resumen
                FROM juegos j
                WHERE j.id IN ($cadena)
                ORDER BY actualizado_en DESC
            "); /* Preparo consulta para obtener los juegos encontrados ordenados por fecha de actualización */
            foreach($ids_juegos as $indice => $id) { /* Recorro los IDs de juegos */
                $consulta->bindValue($indice + 1, $id, PDO::PARAM_INT); /* Vinculo cada ID de juego (empezando en posición 1) */
            }
        } else { /* No hay búsqueda activa */
            // Verificar si existen datos de búsqueda para limpiar
            if(isset($_SESSION['texto_busqueda']) && isset($_SESSION['datos_busqueda'])) {
                unset($_SESSION['texto_busqueda']); /* Elimino el texto de búsqueda */
                unset($_SESSION['datos_busqueda']); /* Elimino los datos de búsqueda */
            }
            // Obtener todos los juegos
            $consulta = $conexion->prepare("SELECT id, nombre, fecha_lanzamiento, portada, tipo, activo, precio, resumen FROM juegos ORDER BY actualizado_en DESC"); /* Preparo consulta para obtener juegos */
        }
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
