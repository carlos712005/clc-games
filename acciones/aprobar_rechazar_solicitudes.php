<?php

    session_start(); /* Inicio la sesión para poder acceder a las variables de sesión */
    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */
    require_once __DIR__ . "/../funciones/funciones_notificaciones.php"; /* Incluyo las funciones de notificaciones */

    // Función para aprobar una solicitud de devolución o reserva
    function aprobarSolicitud($conexion, $id_historial, $id_detalle, $tipo) {
        try { /* Inicio bloque try para capturar errores */

            // Verificar si el historial existe y está pendiente de revisión
            if($tipo === 'SOLICITUD_DEVOLUCION') { /* Si el tipo es SOLICITUD_DEVOLUCION */
                $consulta = $conexion->prepare("SELECT COUNT(*) FROM historial WHERE id = :id_historial AND tipo = :tipo AND estado = 'PENDIENTE_REVISION'"); /* Preparo la consulta para verificar el historial */
            } else if($tipo === 'RESERVA') { /* Si el tipo es RESERVA */
                $consulta = $conexion->prepare("SELECT COUNT(*) FROM historial WHERE id = :id_historial AND tipo = :tipo AND estado = 'PENDIENTE'"); /* Preparo la consulta para verificar el historial */
            }
            $consulta->bindParam(':id_historial', $id_historial, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
            $consulta->bindParam(':tipo', $tipo, PDO::PARAM_STR); /* Vinculo el parámetro del tipo */
            $consulta->execute(); /* Ejecuto la consulta */
                 
            $cantidad = $consulta->fetchColumn(); /* Obtengo la cantidad de registros */
            
            // Si no existe el registro, retorno un error
            if($cantidad <= 0) {
                throw new Exception("La solicitud no existe o ya ha sido procesada."); /* Lanzo una excepción indicando que la solicitud no existe o ya fue procesada */
            }
            
            // Actualizar el estado del historial a APROBADA
            $estado_enum = 'APROBADA'; /* Estado de la acción */
            $consulta = $conexion->prepare("UPDATE historial SET estado = :estado WHERE id = :id_historial"); /* Preparo la consulta para actualizar el historial */
            $consulta->bindParam(':estado', $estado_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta->bindParam(':id_historial', $id_historial, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
            $consulta->execute(); /* Ejecuto la consulta */

            // Actualizar el estado del detalle en historial_compras a APROBADA
            $estado_juego_enum = 'APROBADA'; /* Estado del juego */
            $consulta_juego = $conexion->prepare("UPDATE historial_compras SET estado = :estado WHERE id = :id_detalle"); /* Preparo la consulta para actualizar el detalle */
            $consulta_juego->bindParam(':estado', $estado_juego_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta_juego->bindParam(':id_detalle', $id_detalle, PDO::PARAM_INT); /* Vinculo el parámetro del ID del detalle */
            $consulta_juego->execute(); /* Ejecuto la consulta */

            // Establecer mensaje de éxito
            $_SESSION['mensaje_exito'] = "La solicitud ha sido aprobada exitosamente."; /* Mensaje de éxito */
        
            $consulta = $conexion->prepare("SELECT id_usuario FROM historial WHERE id = :id_historial LIMIT 1"); /* Preparo la consulta para obtener el ID del usuario */
            $consulta->bindParam(':id_historial', $id_historial, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
            $consulta->execute(); /* Ejecuto la consulta */
            $id_usuario = (int)$consulta->fetchColumn(); /* Obtengo el ID del usuario */

            $consulta = $conexion->prepare("SELECT id_juego FROM historial_compras WHERE id = :id_detalle LIMIT 1"); /* Preparo la consulta para obtener el ID del juego */
            $consulta->bindParam(':id_detalle', $id_detalle, PDO::PARAM_INT); /* Vinculo el parámetro del ID del detalle */
            $consulta->execute(); /* Ejecuto la consulta */
            $id_juego = (int)$consulta->fetchColumn(); /* Obtengo el ID del juego */

            $nombre_juego = obtenerNombreJuego($conexion, $id_juego); /* Obtengo el nombre del juego */
            $mensaje_notificacion = ""; /* Inicializo el mensaje de la notificación */
            $mensaje_notificacion_admin = ""; /* Inicializo el mensaje de la notificación para el administrador */
            $nombre_usuario = obtenerNombreUsuario($conexion, $id_usuario); /* Obtengo el nombre del usuario */
            $id_administrador = obtenerIdAdministrador($conexion); /* Obtengo el ID del administrador */

            if($tipo === 'RESERVA') { /* Si el tipo es RESERVA */
                $mensaje_notificacion = "Tu solicitud de reserva para el juego $nombre_juego ha sido aprobada."; /* Mensaje de la notificación */
                $mensaje_notificacion_admin = "La solicitud de reserva del usuario $nombre_usuario para el juego $nombre_juego ha sido aprobada correctamente."; /* Mensaje de la notificación para el administrador */
            } elseif($tipo === 'SOLICITUD_DEVOLUCION') { /* Si el tipo es SOLICITUD_DEVOLUCION */
                $mensaje_notificacion = "Tu solicitud de devolución para el juego $nombre_juego ha sido aprobada."; /* Mensaje de la notificación */
                $mensaje_notificacion_admin = "La solicitud de devolución del usuario $nombre_usuario para el juego $nombre_juego ha sido aprobada correctamente."; /* Mensaje de la notificación para el administrador */
            }

            crearNotificacion($conexion, $id_usuario, $id_juego, $mensaje_notificacion, 'ALERTA'); /* Creo la notificación para el usuario */
            crearNotificacion($conexion, $id_administrador, $id_juego, $mensaje_notificacion_admin, 'SISTEMA'); /* Creo la notificación para el administrador */

        } catch (Exception $e) { /* Si hay error al aprobar la solicitud */
            $_SESSION["error_general"] = ['Error' => $e->getMessage()]; /* Retorno el error en formato JSON */
        }

        // Redireccionar al panel de administrador
        header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel */
        exit; /* Termino la ejecución */
    }

    // Función para rechazar una solicitud de devolución o reserva
    function rechazarSolicitud($conexion, $id_historial, $id_detalle, $tipo, $motivo) {
        try { /* Inicio bloque try para capturar errores */

            // Verificar si el historial existe y está pendiente de revisión
            if($tipo === 'SOLICITUD_DEVOLUCION') { /* Si el tipo es SOLICITUD_DEVOLUCION */
                $consulta = $conexion->prepare("SELECT COUNT(*) FROM historial WHERE id = :id_historial AND tipo = :tipo AND estado = 'PENDIENTE_REVISION'"); /* Preparo la consulta para verificar el historial */
            } else if($tipo === 'RESERVA') { /* Si el tipo es RESERVA */
                $consulta = $conexion->prepare("SELECT COUNT(*) FROM historial WHERE id = :id_historial AND tipo = :tipo AND estado = 'PENDIENTE'"); /* Preparo la consulta para verificar el historial */
            }
            $consulta->bindParam(':id_historial', $id_historial, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
            $consulta->bindParam(':tipo', $tipo, PDO::PARAM_STR); /* Vinculo el parámetro del tipo */
            $consulta->execute(); /* Ejecuto la consulta */
                 
            $cantidad = $consulta->fetchColumn(); /* Obtengo la cantidad de registros */
            
            // Si no existe el registro, retorno un error
            if($cantidad <= 0) {
                throw new Exception("La solicitud no existe o ya ha sido procesada."); /* Lanzo una excepción indicando que la solicitud no existe o ya fue procesada */
            }

            if($tipo === 'SOLICITUD_DEVOLUCION') { /* Si el tipo es SOLICITUD_DEVOLUCION */
                $consulta = $conexion->prepare("SELECT comentario FROM historial WHERE id = :id_historial AND tipo = :tipo AND estado = 'PENDIENTE_REVISION'"); /* Preparo la consulta para verificar el historial */
            } else if($tipo === 'RESERVA') { /* Si el tipo es RESERVA */
                $consulta = $conexion->prepare("SELECT comentario FROM historial WHERE id = :id_historial AND tipo = :tipo AND estado = 'PENDIENTE'"); /* Preparo la consulta para verificar el historial */
            }
            $consulta->bindParam(':id_historial', $id_historial, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
            $consulta->bindParam(':tipo', $tipo, PDO::PARAM_STR); /* Vinculo el parámetro del tipo */
            $consulta->execute(); /* Ejecuto la consulta */
            
            $comentario = $consulta->fetchColumn(); /* Obtengo el comentario */
            
            if (!$comentario) { /* Si no hay comentario previo */
                $nuevo_comentario = "\n - Motivo del rechazo: " . $motivo; /* Creo el nuevo comentario con el motivo de rechazo */
            } else { /* Si ya hay un comentario previo */
                $nuevo_comentario = "\n - Motivo de la solicitud: " . $comentario . "\n - Motivo del rechazo: " . $motivo; /* Creo el nuevo comentario con el motivo de rechazo */
            }

            // Actualizar el estado del historial a RECHAZADA
            $estado_enum = 'RECHAZADA'; /* Estado de la acción */
            $consulta = $conexion->prepare("UPDATE historial SET estado = :estado, comentario = :comentario WHERE id = :id_historial"); /* Preparo la consulta para actualizar el historial */
            $consulta->bindParam(':estado', $estado_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta->bindParam(':comentario', $nuevo_comentario, PDO::PARAM_STR); /* Vinculo el parámetro del nuevo comentario */
            $consulta->bindParam(':id_historial', $id_historial, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
            $consulta->execute(); /* Ejecuto la consulta */

            // Actualizar el estado del detalle en historial_compras a RECHAZADA
            $estado_juego_enum = 'RECHAZADA'; /* Estado del juego */
            $consulta_juego = $conexion->prepare("UPDATE historial_compras SET estado = :estado, comentario = :comentario WHERE id = :id_detalle"); /* Preparo la consulta para actualizar el detalle */
            $consulta_juego->bindParam(':estado', $estado_juego_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta_juego->bindParam(':comentario', $nuevo_comentario, PDO::PARAM_STR); /* Vinculo el parámetro del nuevo comentario */
            $consulta_juego->bindParam(':id_detalle', $id_detalle, PDO::PARAM_INT); /* Vinculo el parámetro del ID del detalle */
            $consulta_juego->execute(); /* Ejecuto la consulta */

            // Establecer mensaje de éxito
            $_SESSION['mensaje_exito'] = "La solicitud ha sido rechazada exitosamente."; /* Mensaje de éxito */

            $consulta = $conexion->prepare("SELECT id_usuario FROM historial WHERE id = :id_historial LIMIT 1"); /* Preparo la consulta para obtener el ID del usuario */
            $consulta->bindParam(':id_historial', $id_historial, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
            $consulta->execute(); /* Ejecuto la consulta */
            $id_usuario = (int)$consulta->fetchColumn(); /* Obtengo el ID del usuario */

            $consulta = $conexion->prepare("SELECT id_juego FROM historial_compras WHERE id = :id_detalle LIMIT 1"); /* Preparo la consulta para obtener el ID del juego */
            $consulta->bindParam(':id_detalle', $id_detalle, PDO::PARAM_INT); /* Vinculo el parámetro del ID del detalle */
            $consulta->execute(); /* Ejecuto la consulta */
            $id_juego = (int)$consulta->fetchColumn(); /* Obtengo el ID del juego */

            $nombre_juego = obtenerNombreJuego($conexion, $id_juego); /* Obtengo el nombre del juego */
            $mensaje_notificacion = ""; /* Inicializo el mensaje de la notificación */
            $mensaje_notificacion_admin = ""; /* Inicializo el mensaje de la notificación para el administrador */
            $nombre_usuario = obtenerNombreUsuario($conexion, $id_usuario); /* Obtengo el nombre del usuario */
            $id_administrador = obtenerIdAdministrador($conexion); /* Obtengo el ID del administrador */

            if($tipo === 'RESERVA') { /* Si el tipo es RESERVA */
                $mensaje_notificacion = "Tu solicitud de reserva para el juego $nombre_juego ha sido rechazada.\n Motivo del rechazo: $motivo"; /* Mensaje de la notificación */
                $mensaje_notificacion_admin = "La solicitud de reserva del usuario $nombre_usuario para el juego $nombre_juego ha sido rechazada correctamente."; /* Mensaje de la notificación para el administrador */
            } elseif($tipo === 'SOLICITUD_DEVOLUCION') { /* Si el tipo es SOLICITUD_DEVOLUCION */
                $mensaje_notificacion = "Tu solicitud de devolución para el juego $nombre_juego ha sido rechazada.\n Motivo del rechazo: $motivo"; /* Mensaje de la notificación */
                $mensaje_notificacion_admin = "La solicitud de devolución del usuario $nombre_usuario para el juego $nombre_juego ha sido rechazada correctamente."; /* Mensaje de la notificación para el administrador */
            }

            crearNotificacion($conexion, $id_usuario, $id_juego, $mensaje_notificacion, 'ALERTA'); /* Creo la notificación para el usuario */
            crearNotificacion($conexion, $id_administrador, $id_juego, $mensaje_notificacion_admin, 'SISTEMA'); /* Creo la notificación para el administrador */

        } catch (Exception $e) { /* Si hay error al rechazar la solicitud */
            $_SESSION["error_general"] = ['Error' => $e->getMessage()]; /* Retorno el error en formato JSON */
        }

        // Redireccionar al panel de administrador
        header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel */
        exit; /* Termino la ejecución */
    }

    if(isset($_POST['accion'])) { /* Verifico que llegue la acción a realizar */
        $accion = $_POST['accion']; /* Obtengo la acción a realizar */
        $id_historial = intval($_POST['id_historial']); /* Obtengo el ID del historial */
        $id_detalle = intval($_POST['id_detalle']); /* Obtengo el ID del detalle */
        $tipo = $_POST['tipo']; /* Obtengo el tipo de solicitud */

        switch($accion) { /* Según la acción a realizar */
            case 'aprobar': /* Si la acción es aprobar */
                aprobarSolicitud($conexion, $id_historial, $id_detalle, $tipo); /* Llamo a la función para aprobar la solicitud */
                break;
            case 'rechazar': /* Si la acción es rechazar */
                $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : ''; /* Obtengo el motivo del rechazo si está presente */
                rechazarSolicitud($conexion, $id_historial, $id_detalle, $tipo, $motivo); /* Llamo a la función para rechazar la solicitud */
                break;
            default: /* Si la acción no es válida */
                $_SESSION["error_general"] = ['Error' => 'Acción no válida.']; /* Retorno el error en formato JSON */
                header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel */
                exit; /* Termino la ejecución */
        }
    } else { /* Si no llega la acción */
        $_SESSION["error_general"] = ['Error' => 'No se ha especificado una acción.']; /* Retorno el error en formato JSON */
        header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel */
        exit; /* Termino la ejecución */
    }

?>