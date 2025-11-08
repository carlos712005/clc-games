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
        
        // Obtener todos los usuarios
        $consulta = $conexion->prepare("SELECT * FROM usuarios ORDER BY creado_en DESC"); /* Preparo consulta para obtener usuarios */
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
