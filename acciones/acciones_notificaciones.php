<?php

    session_start(); /* Inicio la sesión para poder acceder a las variables de sesión */
    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */

    // Función para obtener todas las notificaciones de un usuario
    function obtenerNotificaciones($conexion, $id_usuario) {
        try { /* Inicio bloque try para capturar errores */
            $consulta = $conexion->prepare("
                SELECT id, id_juego, mensaje, tipo, leido, creado_en
                FROM notificaciones
                WHERE id_usuario = :id_usuario
                ORDER BY leido ASC, creado_en DESC
            "); /* Preparo la consulta para obtener todas las notificaciones del usuario ordenadas por leídas/no leídas y fecha */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */

            $notificaciones = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todas las notificaciones */
            
            return $notificaciones; /* Retorno el array de notificaciones */
        } catch (PDOException $e) { /* Si hay error al obtener las notificaciones */
            return ["error" => "Error de base de datos: " . $e->getMessage()]; /* Retorno error */
        }
    }

    // Función para marcar una notificación como vista
    function marcarComoVista($conexion, $id_notificacion, $id_usuario) {
        try { /* Inicio bloque try para capturar errores */
            // Verificar que la notificación pertenece al usuario
            $consulta = $conexion->prepare("SELECT id_usuario FROM notificaciones WHERE id = :id_notificacion"); /* Preparo la consulta */
            $consulta->bindParam(':id_notificacion', $id_notificacion, PDO::PARAM_INT); /* Vinculo el parámetro */
            $consulta->execute(); /* Ejecuto la consulta */
            $notificacion = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo la notificación */

            if(!$notificacion || $notificacion['id_usuario'] != $id_usuario) { /* Si no existe o no pertenece al usuario */
                return ["error" => "Notificación no encontrada o acceso denegado"]; /* Retorno error */
            }

            // Marcar como vista
            $consulta = $conexion->prepare("UPDATE notificaciones SET leido = 1 WHERE id = :id_notificacion"); /* Preparo la consulta */
            $consulta->bindParam(':id_notificacion', $id_notificacion, PDO::PARAM_INT); /* Vinculo el parámetro */
            $consulta->execute(); /* Ejecuto la consulta */

            return ["exito" => "Notificación marcada como vista"]; /* Retorno éxito */
        } catch (PDOException $e) { /* Si hay error */
            return ["error" => "Error de base de datos: " . $e->getMessage()]; /* Retorno error */
        }
    }

    // Función para eliminar una notificación
    function eliminarNotificacion($conexion, $id_notificacion, $id_usuario) {
        try { /* Inicio bloque try para capturar errores */
            // Verificar que la notificación pertenece al usuario
            $consulta = $conexion->prepare("SELECT id_usuario FROM notificaciones WHERE id = :id_notificacion"); /* Preparo la consulta */
            $consulta->bindParam(':id_notificacion', $id_notificacion, PDO::PARAM_INT); /* Vinculo el parámetro */
            $consulta->execute(); /* Ejecuto la consulta */
            $notificacion = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo la notificación */

            if(!$notificacion || $notificacion['id_usuario'] != $id_usuario) { /* Si no existe o no pertenece al usuario */
                return ["error" => "Notificación no encontrada o acceso denegado"]; /* Retorno error */
            }

            // Eliminar la notificación
            $consulta = $conexion->prepare("DELETE FROM notificaciones WHERE id = :id_notificacion"); /* Preparo la consulta */
            $consulta->bindParam(':id_notificacion', $id_notificacion, PDO::PARAM_INT); /* Vinculo el parámetro */
            $consulta->execute(); /* Ejecuto la consulta */

            return ["exito" => "Notificación eliminada correctamente"]; /* Retorno éxito */
        } catch (PDOException $e) { /* Si hay error */
            return ["error" => "Error de base de datos: " . $e->getMessage()]; /* Retorno error */
        }
    }

    // Función para marcar todas las notificaciones como vistas
    function marcarTodasVistas($conexion, $id_usuario) {
        try { /* Inicio bloque try para capturar errores */
            $consulta = $conexion->prepare("UPDATE notificaciones SET leido = 1 WHERE id_usuario = :id_usuario AND leido = 0"); /* Preparo la consulta */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro */
            $consulta->execute(); /* Ejecuto la consulta */

            $cantidad = $consulta->rowCount(); /* Obtengo la cantidad de filas afectadas */

            return ["exito" => "Se marcaron $cantidad notificaciones como vistas", "cantidad" => $cantidad]; /* Retorno éxito con cantidad */
        } catch (PDOException $e) { /* Si hay error */
            return ["error" => "Error de base de datos: " . $e->getMessage()]; /* Retorno error */
        }
    }

    // Función para eliminar todas las notificaciones
    function eliminarTodas($conexion, $id_usuario) {
        try { /* Inicio bloque try para capturar errores */
            $consulta = $conexion->prepare("DELETE FROM notificaciones WHERE id_usuario = :id_usuario"); /* Preparo la consulta */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro */
            $consulta->execute(); /* Ejecuto la consulta */

            $cantidad = $consulta->rowCount(); /* Obtengo la cantidad de filas afectadas */

            return ["exito" => "Se eliminaron $cantidad notificaciones", "cantidad" => $cantidad]; /* Retorno éxito con cantidad */
        } catch (PDOException $e) { /* Si hay error */
            error_log("Error al eliminar todas las notificaciones: " . $e->getMessage()); /* Registro el error */
            return ["error" => "Error de base de datos: " . $e->getMessage()]; /* Retorno error */
        }
    }

    // Procesar la acción solicitada
    if(isset($_POST['accion'])) { /* Verifico que llegue la acción a realizar */
        $accion = $_POST['accion']; /* Obtengo la acción a realizar */
        $id_usuario = $_SESSION['id_usuario']; /* Obtengo el ID del usuario de la sesión */

        switch($accion) { /* Según la acción a realizar */
            case 'obtener': /* Si la acción es obtener las notificaciones */
                $notificaciones = obtenerNotificaciones($conexion, $id_usuario); /* Obtengo las notificaciones */
                echo json_encode($notificaciones); /* Retorno las notificaciones en formato JSON */
                break;

            case 'marcar_vista': /* Si la acción es marcar como vista */
                if(!isset($_POST['id_notificacion'])) { /* Si no se proporcionó el ID de la notificación */
                    echo json_encode(["error" => "ID de notificación no proporcionado"]); /* Retorno error */
                    break;
                }
                $id_notificacion = (int)$_POST['id_notificacion']; /* Obtengo el ID de la notificación */
                $resultado = marcarComoVista($conexion, $id_notificacion, $id_usuario); /* Marco como vista */
                echo json_encode($resultado); /* Retorno el resultado en formato JSON */
                break;

            case 'eliminar': /* Si la acción es eliminar */
                if(!isset($_POST['id_notificacion'])) { /* Si no se proporcionó el ID de la notificación */
                    echo json_encode(["error" => "ID de notificación no proporcionado"]); /* Retorno error */
                    break;
                }
                $id_notificacion = (int)$_POST['id_notificacion']; /* Obtengo el ID de la notificación */
                $resultado = eliminarNotificacion($conexion, $id_notificacion, $id_usuario); /* Elimino la notificación */
                echo json_encode($resultado); /* Retorno el resultado en formato JSON */
                break;

            case 'marcar_todas_vistas': /* Si la acción es marcar todas como vistas */
                $resultado = marcarTodasVistas($conexion, $id_usuario); /* Marco todas como vistas */
                echo json_encode($resultado); /* Retorno el resultado en formato JSON */
                break;

            case 'eliminar_todas': /* Si la acción es eliminar todas */
                $resultado = eliminarTodas($conexion, $id_usuario); /* Elimino todas las notificaciones */
                echo json_encode($resultado); /* Retorno el resultado en formato JSON */
                break;

            default: /* Si la acción no es reconocida */
                echo json_encode(["error" => "Acción no válida"]); /* Retorno un error en formato JSON */
                break;
        }
    } else { /* Si no se recibió ninguna acción */
        echo json_encode(["error" => "No se recibió ninguna acción"]); /* Retorno un error en formato JSON */
    }

?>
