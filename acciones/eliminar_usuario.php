<?php

    session_start(); /* Inicio la sesión para manejar mensajes y verificar permisos */
    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */

    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error_general'] = 'Método no permitido.'; /* Mensaje de error */
        header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel */
        exit; /* Termino la ejecución */
    }

    // Verificar que el usuario esté logueado y sea administrador
    if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
        $_SESSION['error_general'] = 'No tienes permisos para realizar esta acción.'; /* Mensaje de error */
        header('Location: ../publico/index.php'); /* Redirijo al índice */
        exit; /* Termino la ejecución */
    }

    // Verificar que llegue el ID del usuario a eliminar
    if (!isset($_POST['id_usuario']) || !is_numeric($_POST['id_usuario'])) {
        $_SESSION['error_general'] = 'ID de usuario no válido.'; /* Mensaje de error */
        header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel */
        exit; /* Termino la ejecución */
    }

    $id_usuario = (int)$_POST['id_usuario']; /* Convierto a entero el ID del usuario */

    // No permitir que un admin se elimine a sí mismo
    if ($id_usuario === (int)$_SESSION['id_usuario']) {
        $_SESSION['error_general'] = 'No puedes eliminar tu propia cuenta de administrador.'; /* Mensaje de error */
        header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel */
        exit; /* Termino la ejecución */
    }

    try {
        // Obtener el acrónimo del usuario antes de eliminarlo para el mensaje
        $consulta = $conexion->prepare("SELECT acronimo FROM usuarios WHERE id = :id_usuario"); /* Preparo consulta para obtener el acrónimo */
        $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID */
        $consulta->execute(); /* Ejecuto la consulta */
        $usuario = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del usuario */
        
        // Verificar que el usuario exista
        if (!$usuario) {
            $_SESSION['error_general'] = 'Usuario no encontrado.'; /* Mensaje de error */
            header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel */
            exit; /* Termino la ejecución */
        }
        
        $acronimo = $usuario['acronimo']; /* Guardo el acrónimo para el mensaje */
        
        // Eliminar preferencias del usuario
        $consulta = $conexion->prepare("DELETE FROM preferencias_usuario WHERE id_usuario = :id_usuario"); /* Preparo consulta para eliminar preferencias del usuario */
        $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID */
        $consulta->execute(); /* Ejecuto la consulta */
        
        // Eliminar juegos de la biblioteca del usuario
        $consulta = $conexion->prepare("DELETE FROM biblioteca WHERE id_usuario = :id_usuario"); /* Preparo consulta para eliminar juegos de la biblioteca */
        $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID */
        $consulta->execute(); /* Ejecuto la consulta */

        // Eliminar juegos del carrito del usuario
        $consulta = $conexion->prepare("DELETE FROM carrito WHERE id_usuario = :id_usuario"); /* Preparo consulta para eliminar juegos del carrito */
        $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID */
        $consulta->execute(); /* Ejecuto la consulta */
        
        // Eliminar juegos de favoritos del usuario
        $consulta = $conexion->prepare("DELETE FROM favoritos WHERE id_usuario = :id_usuario"); /* Preparo consulta para eliminar juegos de favoritos */
        $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID */
        $consulta->execute(); /* Ejecuto la consulta */
        
        // Eliminar detalles del historial de compras del usuario
        $consulta = $conexion->prepare("
            DELETE hc FROM historial_compras hc
            INNER JOIN historial h ON hc.id_historial = h.id
            WHERE h.id_usuario = :id_usuario
        "); /* Preparo consulta para eliminar detalles del historial */
        $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID */
        $consulta->execute(); /* Ejecuto la consulta */
        
        // Eliminar historial de compras del usuario
        $consulta = $conexion->prepare("DELETE FROM historial WHERE id_usuario = :id_usuario"); /* Preparo consulta para eliminar el historial */
        $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID */
        $consulta->execute(); /* Ejecuto la consulta */

        // Finalmente, eliminar el usuario
        $consulta = $conexion->prepare("DELETE FROM usuarios WHERE id = :id_usuario"); /* Preparo consulta para eliminar el usuario */
        $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID */
        $consulta->execute(); /* Ejecuto la consulta */
        
        // Mensaje de éxito
        $_SESSION['mensaje_exito'] = "Usuario '$acronimo' eliminado correctamente."; /* Mensaje de éxito */
        header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel */
        exit; /* Termino la ejecución */
        
    } catch (PDOException $e) { /* Si hay error en la base de datos */
        $_SESSION['error_general'] = 'Error al eliminar el usuario: ' . $e->getMessage(); /* Mensaje de error */
        header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel */
        exit; /* Termino la ejecución */
    }

?>
