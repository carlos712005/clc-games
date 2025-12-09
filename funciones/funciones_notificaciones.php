<?php
    
    // Función para obtener el nombre de un juego por su ID
    function obtenerNombreJuego($conexion, $id_juego) {
        try { /* Inicio bloque try para capturar errores */
            $consulta = $conexion->prepare("SELECT nombre FROM juegos WHERE id = :id_juego"); /* Preparo la consulta */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */

            $juego = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del juego */

            return $juego ? $juego['nombre'] : null; /* Retorno el nombre del juego o null si no existe */
        } catch (PDOException $e) { /* Si hay error al obtener el nombre del juego */
            echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()]); /* Retorno el error en formato JSON */
            return null; /* Retorno null */
        }
    }

    // Función para obtener el nombre de un usuario por su ID
    function obtenerNombreUsuario($conexion, $id_usuario) {
        try { /* Inicio bloque try para capturar errores */
            $consulta = $conexion->prepare("SELECT acronimo FROM usuarios WHERE id = :id_usuario"); /* Preparo la consulta */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */

            $usuario = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del usuario */

            return $usuario ? $usuario['nombre_usuario'] : null; /* Retorno el nombre del usuario o null si no existe */
        } catch (PDOException $e) { /* Si hay error al obtener el nombre del usuario */
            echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()]); /* Retorno el error en formato JSON */
            return null; /* Retorno null */
        }
    }

    // Función para obtener el ID del administrador
    function obtenerIdAdministrador($conexion) {
        try { /* Inicio bloque try para capturar errores */
            $consulta = $conexion->prepare("SELECT id FROM usuarios WHERE id_rol = 1 LIMIT 1"); /* Preparo la consulta para obtener el ID del administrador */
            $consulta->execute(); /* Ejecuto la consulta */

            $admin = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del administrador */

            return $admin ? $admin['id'] : null; /* Retorno el ID del administrador o null si no existe */
        } catch (PDOException $e) { /* Si hay error al obtener el ID del administrador */
            echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()]); /* Retorno el error en formato JSON */
            return null; /* Retorno null */
        }
    }

    // Función para crear una notificación
    function crearNotificacion($conexion, $id_usuario, $id_juego, $mensaje, $tipo) {
        try { /* Inicio bloque try para capturar errores */
            $consulta = $conexion->prepare("INSERT INTO notificaciones (id_usuario, id_juego, mensaje, tipo) VALUES (:id_usuario, :id_juego, :mensaje, :tipo)"); /* Preparo la consulta para insertar la notificación */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->bindParam(':mensaje', $mensaje, PDO::PARAM_STR); /* Vinculo el parámetro del mensaje */
            $consulta->bindParam(':tipo', $tipo, PDO::PARAM_STR); /* Vinculo el parámetro del tipo */

            $consulta->execute(); /* Ejecuto la consulta */
        } catch (PDOException $e) { /* Si hay error al crear la notificación */
            echo json_encode(["error" => "Error al crear la notificación: " . $e->getMessage()]); /* Retorno el error en formato JSON */
        }
    }

    // Función para contar las notificaciones no leídas de un usuario
    function contarNotificacionesNoLeidas($conexion, $id_usuario) {
        try { /* Inicio bloque try para capturar errores */
            if(isset($_SESSION['modo_admin']) && $_SESSION['modo_admin']) { /* Si es administrador */
                $consulta = $conexion->prepare("SELECT COUNT(*) AS total_no_leidas FROM notificaciones WHERE id_usuario = :id_usuario AND leido = 0 AND tipo = 'SISTEMA'"); /* Cuento solo SISTEMA */
            } else { /* Si no es administrador */
                $consulta = $conexion->prepare("SELECT COUNT(*) AS total_no_leidas FROM notificaciones WHERE id_usuario = :id_usuario AND leido = 0 AND tipo != 'SISTEMA'"); /* Cuento todo excepto SISTEMA */
            }
            
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */

            $resultado = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el resultado */

            return $resultado ? (int)$resultado['total_no_leidas'] : 0; /* Retorno el total de notificaciones no leídas o 0 si no hay resultados */
        } catch (PDOException $e) { /* Si hay error al contar las notificaciones no leídas */
            echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()]); /* Retorno el error en formato JSON */
            return 0; /* Retorno 0 */
        }
    }

?>