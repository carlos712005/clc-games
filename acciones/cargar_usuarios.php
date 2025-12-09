<?php

    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */
    session_start(); /* Inicio la sesión para acceder a las variables de usuario */

    // Verificar que el usuario esté logueado y sea administrador
    if(!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
        echo '<div class="error"><h2>Acceso no autorizado</h2></div>'; /* Devuelvo error */
        exit; /* Termino la ejecución */
    }

    $_SESSION['modo_edicion'] = 'usuarios'; /* Indico que estamos en modo edición de usuarios */

    try { /* Inicio bloque try para capturar errores */
        // Verificar si hay una búsqueda activa
        if(isset($_SESSION['datos_busqueda']) && isset($_SESSION['datos_busqueda']['usuarios_encontrados'])) {
            $ids_usuarios = $_SESSION['datos_busqueda']['usuarios_encontrados']; /* Obtengo los IDs de usuarios encontrados */
            
            // Preparar una consulta con los IDs de usuarios encontrados
            $cantidad = count($ids_usuarios); /* Cantidad de usuarios encontrados */
            $signos = array_fill(0, $cantidad, '?'); /* Creo un array de forma ['?', '?', '?', ...] */
            $cadena = implode(',', $signos); /* Uno con comas: '?,?,?' */
            $consulta = $conexion->prepare("SELECT * FROM usuarios WHERE id IN ($cadena) ORDER BY creado_en DESC"); /* Preparo consulta para obtener los usuarios encontrados, ordenados por fecha de creación */
            foreach($ids_usuarios as $indice => $id) { /* Recorro los IDs de usuarios */
                $consulta->bindValue($indice + 1, $id, PDO::PARAM_INT); /* Vinculo cada ID de usuario (empezando en posición 1) */
            }
        } else { /* No hay búsqueda activa */
            // Verificar si existen datos de búsqueda para limpiar
            if(isset($_SESSION['texto_busqueda']) && isset($_SESSION['datos_busqueda'])) {
                unset($_SESSION['texto_busqueda']); /* Elimino el texto de búsqueda */
                unset($_SESSION['datos_busqueda']); /* Elimino los datos de búsqueda */
            }
            // Obtener todos los usuarios
            $consulta = $conexion->prepare("SELECT * FROM usuarios ORDER BY creado_en DESC"); /* Preparo consulta para obtener usuarios */
        }
        $consulta->execute(); /* Ejecuto la consulta */
        $usuarios = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los usuarios */
        
        // Incluir función de mostrar usuarios
        require_once __DIR__ . "/../funciones/mostrar_usuarios.php"; /* Incluyo la función */
        
        // Generar el HTML de los usuarios
        mostrarUsuarios($usuarios, $conexion); /* Llamo a la función que genera HTML */
        
    } catch (PDOException $e) { /* Si hay error en la consulta */
        echo '<div class="error"><h2>Error al cargar los usuarios: ' . htmlspecialchars($e->getMessage()) . '</h2></div>'; /* Devuelvo error */
    }
    
?>
