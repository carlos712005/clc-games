<?php

    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */
    session_start(); /* Inicio la sesión */

    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error_general'] = 'Método no permitido.'; /* Mensaje de error */
        header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel */
        exit; /* Termino la ejecución */
    }

    // Verificar que el usuario esté logueado y sea administrador
    if(!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
        header('Location: ../publico/index.php'); /* Redirijo si no es admin */
        exit; /* Termino la ejecución */
    }

    // Verificar que llegue el ID del juego
    if(!isset($_POST['id_juego']) || empty($_POST['id_juego'])) {
        $_SESSION['error_general'] = 'No se especificó el juego a eliminar.'; /* Mensaje de error */
        header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel */
        exit; /* Termino la ejecución */
    }

    $id_juego = (int)$_POST['id_juego']; /* Convierto a entero el ID del juego */

    try { /* Inicio bloque try para capturar errores */
        
        // Verificar que el juego existe
        $consulta = $conexion->prepare("SELECT id, nombre FROM juegos WHERE id = :id_juego"); /* Preparo consulta */
        $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el ID */
        $consulta->execute(); /* Ejecuto la consulta */
        
        $juego = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del juego */
        
        if(!$juego) { /* Si no existe el juego */
            $_SESSION['error_general'] = 'El juego especificado no existe.'; /* Mensaje de error */
            header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel */
            exit; /* Termino la ejecución */
        }

        // Marcar el juego como inactivo en lugar de eliminarlo físicamente
        $consulta = $conexion->prepare("UPDATE juegos SET activo = 0 WHERE id = :id_juego"); /* Preparo consulta de actualización */
        $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el ID del juego */
        $consulta->execute(); /* Ejecuto la actualización */

        // Eliminar el juego de la tabla carrito
        $consulta = $conexion->prepare("DELETE FROM carrito WHERE id_juego = :id_juego"); /* Preparo la consulta para eliminar el juego del carrito */
        $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
        $consulta->execute(); /* Ejecuto la consulta */

        // Eliminar el juego de la tabla favoritos
        $consulta = $conexion->prepare("DELETE FROM favoritos WHERE id_juego = :id_juego"); /* Preparo la consulta para eliminar el juego de favoritos */
        $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
        $consulta->execute(); /* Ejecuto la consulta */

        // Establecer mensaje de éxito
        $_SESSION['mensaje_exito'] = 'Juego "' . htmlspecialchars($juego['nombre']) . '" eliminado correctamente.'; /* Mensaje de éxito */

    } catch (PDOException $e) { /* Si hay error en la base de datos */
        $_SESSION["error_general"] = 'Error al eliminar el juego: ' . $e->getMessage(); /* Mensaje de error con detalles */
    }

    // Redireccionar al panel de administrador
    header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel */
    exit; /* Termino la ejecución */

?>
