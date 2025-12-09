<?php

    session_start(); /* Inicio la sesión para poder acceder a las variables de sesión */
    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */
    require_once __DIR__ . "/../funciones/funciones_notificaciones.php"; /* Incluyo las funciones de notificaciones */
    
    error_reporting(0); /* Suprimo errores para evitar que rompan el JSON */
    header('Content-Type: application/json; charset=utf-8'); /* Establezco el tipo de contenido como JSON */

    // Función para agregar un juego al carrito
    function agregarAlCarrito($conexion, $id_usuario, $id_juego) {
        try { /* Inicio bloque try para capturar errores */
            // Verificar que el juego no esté ya en el carrito
            $consulta = $conexion->prepare("SELECT id FROM carrito WHERE id_usuario = :id_usuario AND id_juego = :id_juego"); /* Preparo la consulta para verificar si el juego ya está en el carrito */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */

            $existe_juego = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el ID del juego si existe */

            if (!$existe_juego) { /* Si el juego no está en el carrito, lo agrego */
                $consulta = $conexion->prepare("INSERT INTO carrito (id_usuario, id_juego) VALUES (:id_usuario, :id_juego)"); /* Preparo la consulta para insertar un nuevo juego en el carrito */
                $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
                $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */

                $consulta->execute(); /* Ejecuto la consulta */
            }
            echo json_encode(['exito' => true, 'total_notificaciones_no_leidas' => contarNotificacionesNoLeidas($conexion, $id_usuario)]); /* Respondo con JSON */
        } catch (PDOException $e) { /* Si hay error al agregar al carrito */
            echo json_encode(["error" => "Error al agregar al carrito: " . $e->getMessage()]); /* Retorno el error en formato JSON */
        }
    }

    //Función para eliminar un juego del carrito
    function eliminarDelCarrito($conexion, $id_usuario, $id_juego) {
        try { /* Inicio bloque try para capturar errores */
            $consulta = $conexion->prepare("DELETE FROM carrito WHERE id_juego = :id_juego AND id_usuario = :id_usuario"); /* Preparo la consulta para eliminar el juego del carrito */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */

            $consulta->execute(); /* Ejecuto la consulta */
            echo json_encode(['exito' => true, 'total_notificaciones_no_leidas' => contarNotificacionesNoLeidas($conexion, $id_usuario)]); /* Respondo con JSON */
        } catch (PDOException $e) { /* Si hay error al eliminar del carrito */
            echo json_encode(["error" => "Error al eliminar del carrito: " . $e->getMessage()]); /* Retorno el error en formato JSON */
        }
    }

    //Función para obtener los juegos del carrito con sus datos completos
    function obtenerCarrito($conexion) {
        try { /* Inicio bloque try para capturar errores */
            $consulta = $conexion->prepare("SELECT id_juego FROM carrito WHERE id_usuario = :id_usuario ORDER BY creado_en DESC"); /* Preparo la consulta ordenada por fecha de creación descendente (más recientes primero) */
            $consulta->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */

            $id_juego = $consulta->fetchAll(PDO::FETCH_COLUMN); /* Obtengo todos los IDs de juegos en el carrito */

            $carrito = []; /* Inicializo el array del carrito */

            foreach ($id_juego as $clave => $valor) { /* Recorro los IDs de juegos */
                $id_juego[$clave] = intval($valor); /* Convierto cada ID a entero */

                $consulta = $conexion->prepare("SELECT nombre, portada, tipo, precio, resumen FROM juegos WHERE id = :id_juego"); /* Preparo la consulta */
                $consulta->bindParam(':id_juego', $id_juego[$clave], PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
                $consulta->execute(); /* Ejecuto la consulta */

                $juego = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del juego */

                if ($juego) { /* Si el juego existe */
                    // Obtener el nombre del tipo del juego
                    $consulta_tipo = $conexion->prepare("SELECT nombre FROM filtros WHERE clave = :clave"); /* Preparo la consulta para obtener el nombre del tipo */
                    $consulta_tipo->bindParam(':clave', $juego['tipo'], PDO::PARAM_STR); /* Vinculo el parámetro del tipo del juego */
                    $consulta_tipo->execute(); /* Ejecuto la consulta */

                    $tipo_resultado = $consulta_tipo->fetch(PDO::FETCH_ASSOC); /* Obtengo el nombre del tipo del juego */
                    $nombre_tipo = $tipo_resultado ? $tipo_resultado['nombre'] : $juego['tipo']; /* Uso el nombre si existe, sino la clave */

                    $carrito[] = [
                        'id' => $id_juego[$clave], /* ID del juego */
                        'nombre' => $juego['nombre'], /* Nombre del juego */
                        'portada' => $juego['portada'], /* Portada del juego */
                        'tipo' => $nombre_tipo, /* Uso el nombre del tipo en lugar de la clave */
                        'precio' => $juego['precio'], /* Precio del juego */
                        'resumen' => $juego['resumen'] /* Resumen del juego */
                    ]; /* Agrego los datos del juego al carrito */
                }
            }

            return $carrito; /* Retorno el carrito */

        } catch (PDOException $e) { /* Si hay error al obtener los elementos del carrito */
            echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()]); /* Retorno el error en formato JSON */
            return []; /* Retorno array vacío */
        }
    }

    //Función para vaciar el carrito
    function vaciarCarrito($conexion, $id_usuario) {
        try { /* Inicio bloque try para capturar errores */
            $consulta = $conexion->prepare("DELETE FROM carrito WHERE id_usuario = :id_usuario"); /* Preparo la consulta para vaciar el carrito */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */

            $consulta->execute(); /* Ejecuto la consulta */
            echo json_encode(['exito' => true, 'total_notificaciones_no_leidas' => contarNotificacionesNoLeidas($conexion, $id_usuario)]); /* Respondo con JSON */
        } catch (PDOException $e) { /* Si hay error al vaciar el carrito */
            echo json_encode(["error" => "Error al vaciar el carrito: " . $e->getMessage()]); /* Retorno el error en formato JSON */
        }
    }

    //Función para cancelar un pedido y registrar la acción en el historial
    function cancelarPedido($conexion, $id_usuario, $carrito, $total) {
        try { /* Inicio bloque try para capturar errores */
            // Los valores de tipo y estado deben coincidir con los ENUM definidos en la base de datos
            $tipo_enum = 'COMPRA'; /* Tipo de acción */
            $estado_enum = 'CANCELADA'; /* Estado de la acción */
            $estado_juego_enum = 'CANCELADO'; /* Estado del juego */

            $consulta = $conexion->prepare("INSERT INTO historial (id_usuario, tipo, estado, total) VALUES (:id_usuario, :tipo, :estado, :total)"); /* Preparo la consulta para insertar en historial */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':tipo', $tipo_enum, PDO::PARAM_STR); /* Vinculo el parámetro del tipo */
            $consulta->bindParam(':estado', $estado_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta->bindParam(':total', $total, PDO::PARAM_STR); /* Vinculo el parámetro del total */
            $consulta->execute();

            $id_cancelacion = $conexion->lastInsertId(); /* Obtengo el ID de la cancelación */
            $errores_juegos = []; /* Inicializo el array de errores */
            foreach ($carrito as $juego) { /* Recorro los juegos del carrito */
                $consulta_juego = $conexion->prepare("INSERT INTO historial_compras (id_historial, id_juego, precio, estado) VALUES (:id_historial, :id_juego, :precio, :estado)"); /* Preparo la consulta para insertar en historial_compras */
                $consulta_juego->bindParam(':id_historial', $id_cancelacion, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
                $consulta_juego->bindParam(':id_juego', $juego['id'], PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
                $consulta_juego->bindParam(':precio', $juego['precio'], PDO::PARAM_STR); /* Vinculo el parámetro del precio */
                $consulta_juego->bindParam(':estado', $estado_juego_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
                if (!$consulta_juego->execute()) { /* Ejecuto la consulta y verifico si hubo error */
                    $errores_juegos[] = $juego['id']; /* Agrego el ID del juego al array de errores */
                }
            }

            $mensaje_notificacion = "Tu pedido ha sido cancelado correctamente."; /* Mensaje de la notificación */
            crearNotificacion($conexion, $id_usuario, null, $mensaje_notificacion, 'INFO'); /* Creo la notificación para el usuario */

            // Devuelvo el id de la cancelación y posibles errores para depuración
            echo json_encode([
                'id_cancelacion' => $id_cancelacion, /* ID de la cancelación */
                'errores_juegos' => $errores_juegos /* Array de errores */
            ]); /* Retorno el resultado en formato JSON */
        } catch (Exception $e) { /* Si hay error al cancelar el pedido */
            echo json_encode(['error' => $e->getMessage()]); /* Retorno el error en formato JSON */
        }
    }

    //Función para realizar un pedido y registrar la acción en el historial
    function realizarPedido($conexion, $id_usuario, $carrito, $total) {
        try { /* Inicio bloque try para capturar errores */
            $existe_juego_biblioteca = false; /* Inicializo la variable para verificar juegos repetidos */

            $consulta_tipo = $conexion->prepare("SELECT id_juego FROM biblioteca WHERE id_usuario = :id_usuario"); /* Preparo la consulta para obtener los juegos de la biblioteca del usuario */
            $consulta_tipo->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta_tipo->execute(); /* Ejecuto la consulta */

            $juegos_biblioteca = $consulta_tipo->fetchAll(PDO::FETCH_COLUMN); /* Obtengo todos los IDs de juegos en la biblioteca */

            foreach ($carrito as $juego) { /* Recorro los juegos del carrito */
                if (in_array($juego['id'], $juegos_biblioteca)) { /* Si el juego ya está en la biblioteca */
                    $existe_juego_biblioteca = true; /* Marco que existe un juego repetido */
                    break; /* Salgo del bucle */
                }
            }

            if($existe_juego_biblioteca) { /* Si existe un juego repetido */
                echo json_encode(['error' => 'Uno o más juegos del carrito ya están en tu biblioteca. Por favor, revisa tu carrito.']); /* Retorno el error en formato JSON */
                return; /* Salgo de la función sin realizar el pedido */
            }
            
            // Los valores de tipo y estado deben coincidir con los ENUM definidos en la base de datos
            $tipo_enum = 'COMPRA'; /* Tipo de acción */
            $estado_enum = 'PAGADA'; /* Estado de la acción */
            $estado_juego_enum = 'PAGADO'; /* Estado del juego */

            $metodo_pago = isset($_SESSION['pago_paypal']) ? 'paypal' : 'tarjeta'; /* Método de pago utilizado */
            $paypal_order_id   = $_SESSION['pago_paypal']['order_id'] ?? null; /* ID de la orden de PayPal */
            $paypal_capture_id = $_SESSION['pago_paypal']['capture_id'] ?? null; /* ID de la captura de PayPal */
            $paypal_email      = $_SESSION['pago_paypal']['payer_email'] ?? null; /* Email asociado a la cuenta PayPal */

            $consulta = $conexion->prepare("INSERT INTO historial (id_usuario, tipo, estado, total, metodo_pago, paypal_order_id, paypal_capture_id, paypal_email) VALUES (:id_usuario, :tipo, :estado, :total, :metodo_pago, :paypal_order_id, :paypal_capture_id, :paypal_email)"); /* Preparo la consulta para insertar en historial */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':tipo', $tipo_enum, PDO::PARAM_STR); /* Vinculo el parámetro del tipo */
            $consulta->bindParam(':estado', $estado_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta->bindParam(':total', $total, PDO::PARAM_STR); /* Vinculo el parámetro del total */
            $consulta->bindParam(':metodo_pago', $metodo_pago, PDO::PARAM_STR); /* Vinculo el parámetro del método de pago */
            $consulta->bindParam(':paypal_order_id', $paypal_order_id, PDO::PARAM_STR); /* Vinculo el parámetro del ID de la orden de PayPal */
            $consulta->bindParam(':paypal_capture_id', $paypal_capture_id, PDO::PARAM_STR); /* Vinculo el parámetro del ID de la captura de PayPal */
            $consulta->bindParam(':paypal_email', $paypal_email, PDO::PARAM_STR); /* Vinculo el parámetro del email de PayPal */
            $consulta->execute(); /* Ejecuto la consulta */

            unset($_SESSION['pago_paypal']); /* Limpio la información de pago de la sesión */

            $id_pedido = $conexion->lastInsertId(); /* Obtengo el ID del pedido */
            $errores_juegos = []; /* Inicializo el array de errores */
            foreach ($carrito as $juego) { /* Recorro los juegos del carrito */
                $consulta_juego = $conexion->prepare("INSERT INTO historial_compras (id_historial, id_juego, precio, estado) VALUES (:id_historial, :id_juego, :precio, :estado)"); /* Preparo la consulta para insertar en historial_compras */
                $consulta_juego->bindParam(':id_historial', $id_pedido, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
                $consulta_juego->bindParam(':id_juego', $juego['id'], PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
                $consulta_juego->bindParam(':precio', $juego['precio'], PDO::PARAM_STR); /* Vinculo el parámetro del precio */
                $consulta_juego->bindParam(':estado', $estado_juego_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
                if (!$consulta_juego->execute()) { /* Ejecuto la consulta y verifico si hubo error */
                    $errores_juegos[] = $juego['id']; /* Agrego el ID del juego al array de errores */
                }
            }

            $consulta = $conexion->prepare("INSERT INTO biblioteca (id_usuario, id_juego, precio_pagado) VALUES (:id_usuario, :id_juego, :precio_pagado)"); /* Preparo la consulta para insertar en biblioteca */
            foreach ($carrito as $juego) {
                // Inserto cada juego en la biblioteca
                $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
                $consulta->bindParam(':id_juego', $juego['id'], PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
                $consulta->bindParam(':precio_pagado', $juego['precio'], PDO::PARAM_STR); /* Vinculo el parámetro del precio pagado */
                $consulta->execute(); /* Ejecuto la consulta */
            }

            $mensaje_notificacion = "Tu pedido ha sido completado correctamente."; /* Mensaje de la notificación */
            crearNotificacion($conexion, $id_usuario, null, $mensaje_notificacion, 'INFO'); /* Creo la notificación para el usuario */

            $_SESSION['notificaciones_sin_leer'] = contarNotificacionesNoLeidas($conexion, $id_usuario); /* Actualizo el contador de notificaciones no leídas en la sesión */

            // Devuelvo el id del pedido y posibles errores para depuración
            echo json_encode([
                'exito' => true, /* Indico que la operación fue exitosa */
                'id_pedido' => $id_pedido, /* ID del pedido */
                'total_notificaciones_no_leidas' => contarNotificacionesNoLeidas($conexion, $id_usuario), /* Total de notificaciones no leídas */
                'errores_juegos' => $errores_juegos /* Array de errores */
            ]); /* Retorno el resultado en formato JSON */
        } catch (Exception $e) { /* Si hay error al realizar el pedido */
            echo json_encode(['error' => $e->getMessage()]); /* Retorno el error en formato JSON */
        }
    }

    //Función para eliminar un juego único (juego que ha sido comprado directamente) del carrito
    function eliminarJuegoUnicoDelCarrito($conexion, $id_usuario, $id_juego) {
        try { /* Inicio bloque try para capturar errores */
            $consulta = $conexion->prepare("SELECT id_juego FROM carrito WHERE id_usuario = :id_usuario ORDER BY creado_en DESC"); /* Preparo la consulta ordenada por fecha de creación descendente (más recientes primero) */
            $consulta->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */

            $id_juegos_carrito = $consulta->fetchAll(PDO::FETCH_COLUMN); /* Obtengo todos los IDs de juegos en el carrito */

            if (in_array($id_juego, $id_juegos_carrito)) { /* Si el juego está en el carrito */
                $consulta = $conexion->prepare("DELETE FROM carrito WHERE id_juego = :id_juego AND id_usuario = :id_usuario"); /* Preparo la consulta para eliminar el juego del carrito */
                $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
                $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
                $consulta->execute(); /* Ejecuto la consulta */
            }
        } catch (PDOException $e) { /* Si hay error al eliminar del carrito */
            echo json_encode(["error" => "Error al eliminar del carrito: " . $e->getMessage()]); /* Retorno el error en formato JSON */
        }
    }

    //Función para cancelar una devolución y registrar la acción en el historial
    function cancelarDevolucion($conexion, $id_usuario, $id_juego, $total) {
        try { /* Inicio bloque try para capturar errores */
            // Los valores de tipo y estado deben coincidir con los ENUM definidos en la base de datos
            $tipo_enum = 'DEVOLUCION'; /* Tipo de acción */
            $estado_enum = 'CANCELADA'; /* Estado de la acción */
            $estado_juego_enum = 'DEVOLUCION_CANCELADA'; /* Estado del juego */

            $consulta = $conexion->prepare("INSERT INTO historial (id_usuario, tipo, estado, total) VALUES (:id_usuario, :tipo, :estado, :total)"); /* Preparo la consulta para insertar en historial */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':tipo', $tipo_enum, PDO::PARAM_STR); /* Vinculo el parámetro del tipo */
            $consulta->bindParam(':estado', $estado_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta->bindParam(':total', $total, PDO::PARAM_STR); /* Vinculo el parámetro del total */
            $consulta->execute();

            $id_cancelacion = $conexion->lastInsertId(); /* Obtengo el ID de la cancelación */
            
            $consulta_juego = $conexion->prepare("INSERT INTO historial_compras (id_historial, id_juego, precio, estado) VALUES (:id_historial, :id_juego, :precio, :estado)"); /* Preparo la consulta para insertar en historial_compras */
            $consulta_juego->bindParam(':id_historial', $id_cancelacion, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
            $consulta_juego->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta_juego->bindParam(':precio', $total, PDO::PARAM_STR); /* Vinculo el parámetro del precio */
            $consulta_juego->bindParam(':estado', $estado_juego_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado del juego */
            $consulta_juego->execute();

            $nombre_juego = obtenerNombreJuego($conexion, $id_juego); /* Obtengo el nombre del juego */
            $mensaje_notificacion = "Tu devolución del juego $nombre_juego ha sido cancelada correctamente."; /* Mensaje de la notificación */
            crearNotificacion($conexion, $id_usuario, $id_juego, $mensaje_notificacion, 'INFO'); /* Creo la notificación para el usuario */

            $_SESSION['notificaciones_sin_leer'] = contarNotificacionesNoLeidas($conexion, $id_usuario); /* Actualizo el contador de notificaciones no leídas en la sesión */

            // Devuelvo el id de la cancelación y posibles errores para depuración
            echo json_encode([
                'id_cancelacion' => $id_cancelacion, /* ID de la cancelación */
                'errores_juegos' => $errores_juegos /* Array de errores */
            ]); /* Retorno el resultado en formato JSON */
        } catch (Exception $e) { /* Si hay error al cancelar la devolución */
            echo json_encode(['error' => $e->getMessage()]); /* Retorno el error en formato JSON */
        }
    }

    //Función para realizar una devolución y registrar la acción en el historial
    function realizarDevolucion($conexion, $id_usuario, $id_juego, $total) {
        try { /* Inicio bloque try para capturar errores */
            $consulta = $conexion->prepare("DELETE FROM biblioteca WHERE id_juego = :id_juego AND id_usuario = :id_usuario"); /* Preparo la consulta para eliminar el juego de la biblioteca */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */

            $metodo_pago = isset($_SESSION['metodo_reembolso']) ? 'paypal' : 'tarjeta'; /* Método de pago utilizado */
            $paypal_order_id   = $_SESSION['paypal_info_reembolso']['paypal_order_id'] ?? null; /* ID de la orden de PayPal */
            $paypal_capture_id = $_SESSION['paypal_info_reembolso']['paypal_capture_id'] ?? null; /* ID de la captura de PayPal */
            $paypal_email      = $_SESSION['paypal_info_reembolso']['paypal_email'] ?? null; /* Email asociado a la cuenta PayPal */

            // Obtengo el ID del historial pendiente de devolución correspondiente al usuario y juego
            $consulta = $conexion->prepare("
                SELECT h.id
                FROM historial h
                INNER JOIN historial_compras hc ON hc.id_historial = h.id
                WHERE h.id_usuario = :id_usuario AND hc.id_juego = :id_juego AND h.tipo = 'SOLICITUD_DEVOLUCION' AND h.estado = 'APROBADA'
                ORDER BY h.creado_en DESC
                LIMIT 1
            "); /* Selecciono el último historial de devolución pendiente */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */
            $id_historial_pendiente = $consulta->fetchColumn(); /* ID del historial pendiente */

            // Los valores de tipo y estado deben coincidir con los ENUM definidos en la base de datos
            $tipo_enum = 'DEVOLUCION'; /* Tipo de acción */
            $estado_enum = 'COMPLETADA'; /* Estado de la acción */
            $estado_juego_enum = 'DEVUELTO'; /* Estado del juego */

            // Actualizo el historial de la devolución correspondiente
            $consulta = $conexion->prepare("UPDATE historial SET tipo = :tipo, estado = :estado, metodo_pago = :metodo_pago, paypal_order_id = :paypal_order_id, paypal_capture_id = :paypal_capture_id, paypal_email = :paypal_email WHERE id = :id_historial"); /* Preparo la consulta para actualizar el historial */
            $consulta->bindParam(':tipo', $tipo_enum, PDO::PARAM_STR); /* Vinculo el parámetro del tipo */
            $consulta->bindParam(':estado', $estado_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta->bindParam(':metodo_pago', $metodo_pago, PDO::PARAM_STR); /* Vinculo el parámetro del método de pago */
            $consulta->bindParam(':paypal_order_id', $paypal_order_id, PDO::PARAM_STR); /* Vinculo el parámetro del ID de la orden de PayPal */
            $consulta->bindParam(':paypal_capture_id', $paypal_capture_id, PDO::PARAM_STR); /* Vinculo el parámetro del ID de la captura de PayPal */
            $consulta->bindParam(':paypal_email', $paypal_email, PDO::PARAM_STR); /* Vinculo el parámetro del email de PayPal */
            $consulta->bindParam(':id_historial', $id_historial_pendiente, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
            $consulta->execute(); /* Ejecuto la consulta */

            unset($_SESSION['pago_paypal']); /* Limpio la información de pago de la sesión */

            $consulta_juego = $conexion->prepare("UPDATE historial_compras SET estado = :estado WHERE id_historial = :id_historial AND id_juego = :id_juego"); /* Preparo la consulta para actualizar el estado del juego en historial_compras */
            $consulta_juego->bindParam(':id_historial', $id_historial_pendiente, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
            $consulta_juego->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta_juego->bindParam(':estado', $estado_juego_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta_juego->execute(); /* Ejecuto la consulta */

            $nombre_juego = obtenerNombreJuego($conexion, $id_juego); /* Obtengo el nombre del juego */
            $mensaje_notificacion = "Tu devolución del juego $nombre_juego ha sido completada correctamente."; /* Mensaje de la notificación */
            crearNotificacion($conexion, $id_usuario, $id_juego, $mensaje_notificacion, 'INFO'); /* Creo la notificación para el usuario */

            $nombre_usuario = obtenerNombreUsuario($conexion, $id_usuario); /* Obtengo el nombre del usuario */
            $id_administrador = obtenerIdAdministrador($conexion); /* Obtengo el ID del administrador */
            $mensaje_notificacion_admin = "El usuario $nombre_usuario ha completado la devolución del juego $nombre_juego."; /* Mensaje de la notificación para el administrador */
            crearNotificacion($conexion, $id_administrador, $id_juego, $mensaje_notificacion_admin, 'SISTEMA'); /* Creo la notificación para el administrador */
            
            $_SESSION['notificaciones_sin_leer'] = contarNotificacionesNoLeidas($conexion, $id_usuario); /* Actualizo el contador de notificaciones no leídas en la sesión */

            // Devuelvo el id de la devolución y posibles errores para depuración
            echo json_encode([
                'id_devolucion' => $id_historial_pendiente, /* ID de la devolución */
                'total_notificaciones_no_leidas' => contarNotificacionesNoLeidas($conexion, $id_usuario), /* Total de notificaciones no leídas */
                'errores_juegos' => $errores_juegos /* Array de errores */
            ]); /* Retorno el resultado en formato JSON */
        } catch (Exception $e) { /* Si hay error al realizar la devolución */
            echo json_encode(['error' => $e->getMessage()]); /* Retorno el error en formato JSON */
        }
    }

    //Función para realizar una reserva y registrar la acción en el historial
    function realizarReserva($conexion, $id_usuario, $id_juego, $total) {
        try { /* Inicio bloque try para capturar errores */
            $metodo_pago = isset($_SESSION['pago_paypal']) ? 'paypal' : 'tarjeta'; /* Método de pago utilizado */
            $paypal_order_id   = $_SESSION['pago_paypal']['order_id'] ?? null; /* ID de la orden de PayPal */
            $paypal_capture_id = $_SESSION['pago_paypal']['capture_id'] ?? null; /* ID de la captura de PayPal */
            $paypal_email      = $_SESSION['pago_paypal']['payer_email'] ?? null; /* Email asociado a la cuenta PayPal */

            // Obtengo el ID del historial de reserva aprobada correspondiente al usuario y juego
            $consulta = $conexion->prepare("
                SELECT h.id
                FROM historial h
                INNER JOIN historial_compras hc ON hc.id_historial = h.id
                WHERE h.id_usuario = :id_usuario AND hc.id_juego = :id_juego AND h.tipo = 'RESERVA' AND h.estado = 'APROBADA'
                ORDER BY h.creado_en DESC
                LIMIT 1
            "); /* Selecciono el último historial de reserva aprobada */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */
            $id_historial_pendiente = $consulta->fetchColumn(); /* ID del historial pendiente */

            // Los valores de tipo y estado deben coincidir con los ENUM definidos en la base de datos
            $tipo_enum = 'RESERVA'; /* Tipo de acción */
            $estado_enum = 'RESERVADA'; /* Estado de la acción */
            $estado_juego_enum = 'RESERVADO'; /* Estado del juego */

            // Actualizo el historial de la reserva correspondiente
            $consulta = $conexion->prepare("UPDATE historial SET tipo = :tipo, estado = :estado, metodo_pago = :metodo_pago, paypal_order_id = :paypal_order_id, paypal_capture_id = :paypal_capture_id, paypal_email = :paypal_email WHERE id = :id_historial"); /* Preparo la consulta para actualizar el historial */
            $consulta->bindParam(':tipo', $tipo_enum, PDO::PARAM_STR); /* Vinculo el parámetro del tipo */
            $consulta->bindParam(':estado', $estado_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta->bindParam(':metodo_pago', $metodo_pago, PDO::PARAM_STR); /* Vinculo el parámetro del método de pago */
            $consulta->bindParam(':paypal_order_id', $paypal_order_id, PDO::PARAM_STR); /* Vinculo el parámetro del ID de la orden de PayPal */
            $consulta->bindParam(':paypal_capture_id', $paypal_capture_id, PDO::PARAM_STR); /* Vinculo el parámetro del ID de la captura de PayPal */
            $consulta->bindParam(':paypal_email', $paypal_email, PDO::PARAM_STR); /* Vinculo el parámetro del email de PayPal */
            $consulta->bindParam(':id_historial', $id_historial_pendiente, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
            $consulta->execute(); /* Ejecuto la consulta */

            unset($_SESSION['pago_paypal']); /* Limpio la información de pago de la sesión */

            $consulta_juego = $conexion->prepare("UPDATE historial_compras SET estado = :estado WHERE id_historial = :id_historial AND id_juego = :id_juego"); /* Preparo la consulta para actualizar el estado del juego en historial_compras */
            $consulta_juego->bindParam(':id_historial', $id_historial_pendiente, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
            $consulta_juego->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta_juego->bindParam(':estado', $estado_juego_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta_juego->execute(); /* Ejecuto la consulta */

            // Agregar el juego a la biblioteca del usuario
            $consulta = $conexion->prepare("INSERT INTO biblioteca (id_usuario, id_juego, precio_pagado) VALUES (:id_usuario, :id_juego, :precio_pagado)"); /* Preparo la consulta para insertar en biblioteca */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->bindParam(':precio_pagado', $total, PDO::PARAM_STR); /* Vinculo el parámetro del precio pagado */
            $consulta->execute(); /* Ejecuto la consulta */

            $nombre_juego = obtenerNombreJuego($conexion, $id_juego); /* Obtengo el nombre del juego */
            $mensaje_notificacion = "Tu reserva del juego $nombre_juego ha sido completada correctamente."; /* Mensaje de la notificación */
            crearNotificacion($conexion, $id_usuario, $id_juego, $mensaje_notificacion, 'INFO'); /* Creo la notificación para el usuario */

            $nombre_usuario = obtenerNombreUsuario($conexion, $id_usuario); /* Obtengo el nombre del usuario */
            $id_administrador = obtenerIdAdministrador($conexion); /* Obtengo el ID del administrador */
            $mensaje_notificacion_admin = "El usuario $nombre_usuario ha completado la reserva del juego $nombre_juego."; /* Mensaje de la notificación para el administrador */
            crearNotificacion($conexion, $id_administrador, $id_juego, $mensaje_notificacion_admin, 'SISTEMA'); /* Creo la notificación para el administrador */
            
            $_SESSION['notificaciones_sin_leer'] = contarNotificacionesNoLeidas($conexion, $id_usuario); /* Actualizo el contador de notificaciones no leídas en la sesión */

            // Devuelvo el resultado
            echo json_encode([
                'exito' => true, /* Indico que la operación fue exitosa */
                'id_reserva' => $id_historial_pendiente, /* ID de la reserva */
                'total_notificaciones_no_leidas' => contarNotificacionesNoLeidas($conexion, $id_usuario) /* Total de notificaciones no leídas */
            ]); /* Retorno el resultado en formato JSON */
        } catch (Exception $e) { /* Si hay error al realizar la reserva */
            echo json_encode(['error' => $e->getMessage()]); /* Retorno el error en formato JSON */
        }
    }

    //Función para solicitar una reserva de un juego aún no lanzado o solicitar una devolución y registrar la acción en el historial
    function solicitar($conexion, $id_usuario, $id_juego, $tipo, $estado, $estado_juego, $total, $motivo = null) {
        try { /* Inicio bloque try para capturar errores */
                        
            // Los valores de tipo y estado deben coincidir con los ENUM definidos en la base de datos
            $tipo_enum = $tipo; /* Tipo de acción */
            $estado_enum = $estado; /* Estado de la acción */
            $estado_juego_enum = $estado_juego; /* Estado del juego */

            if($motivo === null) { /* Si no hay motivo */
                $consulta = $conexion->prepare("INSERT INTO historial (id_usuario, tipo, estado, total) VALUES (:id_usuario, :tipo, :estado, :total)"); /* Preparo la consulta para insertar en historial */
            } else { /* Si hay motivo */
                $consulta = $conexion->prepare("INSERT INTO historial (id_usuario, tipo, estado, total, comentario) VALUES (:id_usuario, :tipo, :estado, :total, :comentario)"); /* Preparo la consulta para insertar en historial */
                $consulta->bindParam(':comentario', $motivo, PDO::PARAM_STR); /* Vinculo el parámetro del comentario */
            }
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':tipo', $tipo_enum, PDO::PARAM_STR); /* Vinculo el parámetro del tipo */
            $consulta->bindParam(':estado', $estado_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta->bindParam(':total', $total, PDO::PARAM_STR); /* Vinculo el parámetro del total */
            $consulta->execute(); /* Ejecuto la consulta */

            $id_pedido = $conexion->lastInsertId(); /* Obtengo el ID del pedido */
            
            if($motivo === null) { /* Si no hay motivo */
                $consulta_juego = $conexion->prepare("INSERT INTO historial_compras (id_historial, id_juego, precio, estado) VALUES (:id_historial, :id_juego, :precio, :estado)"); /* Preparo la consulta para insertar en historial_compras */
            } else { /* Si hay motivo */
                $consulta_juego = $conexion->prepare("INSERT INTO historial_compras (id_historial, id_juego, precio, estado, comentario) VALUES (:id_historial, :id_juego, :precio, :estado, :comentario)"); /* Preparo la consulta para insertar en historial_compras */
                $consulta_juego->bindParam(':comentario', $motivo, PDO::PARAM_STR); /* Vinculo el parámetro del comentario */
            }
            $consulta_juego->bindParam(':id_historial', $id_pedido, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
            $consulta_juego->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta_juego->bindParam(':precio', $total, PDO::PARAM_STR); /* Vinculo el parámetro del precio */
            $consulta_juego->bindParam(':estado', $estado_juego_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta_juego->execute(); /* Ejecuto la consulta */
            
            $nombre_juego = obtenerNombreJuego($conexion, $id_juego); /* Obtengo el nombre del juego */
            $mensaje_notificacion = ""; /* Inicializo el mensaje de la notificación */
            $mensaje_notificacion_admin = ""; /* Inicializo el mensaje de la notificación para el administrador */
            $nombre_usuario = obtenerNombreUsuario($conexion, $id_usuario); /* Obtengo el nombre del usuario */
            $id_administrador = obtenerIdAdministrador($conexion); /* Obtengo el ID del administrador */

            if($tipo === 'RESERVA') { /* Si el tipo es RESERVA */
                $mensaje_notificacion = "Tu solicitud de reserva para el juego $nombre_juego ha sido recibida y está en estado '$estado_enum'."; /* Mensaje de la notificación */
                $mensaje_notificacion_admin = "El usuario $nombre_usuario ha solicitado una reserva para el juego $nombre_juego."; /* Mensaje de la notificación para el administrador */
                if($motivo !== null) { /* Si hay motivo */
                    $mensaje_notificacion_admin .= "\nMotivo: $motivo."; /* Agrego el motivo al mensaje de la notificación para el administrador */
                }
            } elseif($tipo === 'SOLICITUD_DEVOLUCION') { /* Si el tipo es SOLICITUD_DEVOLUCION */
                $mensaje_notificacion = "Tu solicitud de devolución para el juego $nombre_juego ha sido recibida y está en estado '$estado_enum'."; /* Mensaje de la notificación */
                $mensaje_notificacion_admin = "El usuario $nombre_usuario ha solicitado una devolución para el juego $nombre_juego."; /* Mensaje de la notificación para el administrador */
                if($motivo !== null) { /* Si hay motivo */
                    $mensaje_notificacion_admin .= "\nMotivo: $motivo."; /* Agrego el motivo al mensaje de la notificación para el administrador */
                }
            }

            crearNotificacion($conexion, $id_usuario, $id_juego, $mensaje_notificacion, 'INFO'); /* Creo la notificación para el usuario */
            crearNotificacion($conexion, $id_administrador, $id_juego, $mensaje_notificacion_admin, 'SISTEMA'); /* Creo la notificación para el administrador */

            $_SESSION['notificaciones_sin_leer'] = contarNotificacionesNoLeidas($conexion, $id_usuario); /* Actualizo el contador de notificaciones no leídas en la sesión */

            // Devuelvo el id del pedido para confirmación
            echo json_encode([
                'exito' => true, /* Indico que la operación fue exitosa */
                'id_pedido' => $id_pedido, /* ID del pedido */
                'total_notificaciones_no_leidas' => contarNotificacionesNoLeidas($conexion, $id_usuario), /* Total de notificaciones no leídas */
            ]); /* Retorno el resultado en formato JSON */
        } catch (Exception $e) { /* Si hay error al solicitar la reserva */
            echo json_encode(['error' => $e->getMessage()]); /* Retorno el error en formato JSON */
        }
    }

    /* Función para cancelar una solicitud de reserva o devolución y registrar la acción en el historial */
    function cancelarSolicitud($conexion, $id_usuario, $id_juego, $tipo, $estado, $total, $motivo) {
        try { /* Inicio bloque try para capturar errores */
            
            $consulta = $conexion->prepare("
                SELECT h.id
                FROM historial h
                INNER JOIN historial_compras hc ON hc.id_historial = h.id
                WHERE h.id_usuario = :id_usuario AND hc.id_juego = :id_juego AND h.tipo = :tipo AND h.estado = :estado
                ORDER BY h.creado_en DESC
                LIMIT 1
            "); /* Selecciono el último historial de reserva o solicitud de devolución pendiente */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->bindParam(':tipo', $tipo, PDO::PARAM_STR); /* Vinculo el parámetro del tipo */
            $consulta->bindParam(':estado', $estado, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta->execute();
            $id_historial_pendiente = $consulta->fetchColumn(); /* ID del historial pendiente */

            if(!$id_historial_pendiente) { /* Si no existe historial pendiente */
                echo json_encode(['error' => "No se encontró una solicitud de $tipo pendiente para cancelar"]); /* Retorno error */
                return; /* Salgo de la función */
            }

            // Actualizar historial: estado CANCELADA y comentario
            $consulta_actualizar = $conexion->prepare("UPDATE historial SET estado = 'CANCELADA', comentario = :comentario WHERE id = :id_historial"); /* Preparo la consulta para actualizar el historial */
            $consulta_actualizar->bindParam(':comentario', $motivo, PDO::PARAM_STR); /* Vinculo el parámetro del comentario */
            $consulta_actualizar->bindParam(':id_historial', $id_historial_pendiente, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
            $consulta_actualizar->execute();

            // Actualizar detalle en historial_compras: estado CANCELADO y comentario
            $consulta_detalle = $conexion->prepare("UPDATE historial_compras SET estado = 'CANCELADO', comentario = :comentario WHERE id_historial = :id_historial AND id_juego = :id_juego"); /* Preparo la consulta para actualizar el detalle del historial */
            $consulta_detalle->bindParam(':comentario', $motivo, PDO::PARAM_STR); /* Vinculo el parámetro del comentario */
            $consulta_detalle->bindParam(':id_historial', $id_historial_pendiente, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
            $consulta_detalle->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta_detalle->execute();

            $nombre_juego = obtenerNombreJuego($conexion, $id_juego); /* Obtengo el nombre del juego */
            $mensaje_notificacion = ""; /* Inicializo el mensaje de la notificación */
            $mensaje_notificacion_admin = ""; /* Inicializo el mensaje de la notificación para el administrador */
            $nombre_usuario = obtenerNombreUsuario($conexion, $id_usuario); /* Obtengo el nombre del usuario */
            $id_administrador = obtenerIdAdministrador($conexion); /* Obtengo el ID del administrador */

            if($tipo === 'RESERVA') { /* Si el tipo es RESERVA */
                $mensaje_notificacion = "Tu solicitud de reserva para el juego $nombre_juego ha sido cancelada correctamente."; /* Mensaje de la notificación */
                $mensaje_notificacion_admin = "El usuario $nombre_usuario ha cancelado su solicitud de reserva para el juego $nombre_juego.\nMotivo: $motivo"; /* Mensaje de la notificación para el administrador */
            } elseif($tipo === 'SOLICITUD_DEVOLUCION') { /* Si el tipo es SOLICITUD_DEVOLUCION */
                $mensaje_notificacion = "Tu solicitud de devolución para el juego $nombre_juego ha sido cancelada correctamente."; /* Mensaje de la notificación */
                $mensaje_notificacion_admin = "El usuario $nombre_usuario ha cancelado su solicitud de devolución para el juego $nombre_juego.\nMotivo: $motivo"; /* Mensaje de la notificación para el administrador */
            }

            crearNotificacion($conexion, $id_usuario, $id_juego, $mensaje_notificacion, 'INFO'); /* Creo la notificación para el usuario */
            crearNotificacion($conexion, $id_administrador, $id_juego, $mensaje_notificacion_admin, 'SISTEMA'); /* Creo la notificación para el administrador */

            $_SESSION['notificaciones_sin_leer'] = contarNotificacionesNoLeidas($conexion, $id_usuario); /* Actualizo el contador de notificaciones no leídas en la sesión */

            // Respuesta de éxito
            echo json_encode([
                'exito' => true, /* Indico que la operación fue exitosa */
                'id_historial' => $id_historial_pendiente, /* ID del historial actualizado */
                'total_notificaciones_no_leidas' => contarNotificacionesNoLeidas($conexion, $id_usuario), /* Total de notificaciones no leídas */
            ]); /* Retorno el resultado en formato JSON */
        } catch (Exception $e) { /* Si hay error al cancelar la solicitud */
            echo json_encode(['error' => $e->getMessage()]); /* Retorno el error en formato JSON */
        }
    }

    if(isset($_POST['accion'])) { /* Verifico que llegue la acción a realizar */
        $accion = $_POST['accion']; /* Obtengo la acción a realizar */
        switch($accion) { /* Según la acción a realizar */
            case 'agregar': /* Si la acción es agregar un elemento al carrito */
                if(isset($_POST['id_juego'])) { /* Verifico que lleguen los datos necesarios */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    agregarAlCarrito($conexion, $_SESSION['id_usuario'], $id_juego); /* Llamo a la función para agregar al carrito */
                }
                break;
            case 'eliminar': /* Si la acción es eliminar un elemento del carrito */
                if(isset($_POST['id_juego'])) { /* Verifico que llegue el ID del juego */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    eliminarDelCarrito($conexion, $_SESSION['id_usuario'], $id_juego); /* Llamo a la función para eliminar del carrito */
                }
                break;
            case 'obtener': /* Si la acción es obtener los elementos del carrito */
                $carrito = obtenerCarrito($conexion); /* Llamo a la función para obtener el carrito */
                echo json_encode($carrito); /* Retorno el carrito en formato JSON */
                break;
            case 'vaciar': /* Si la acción es vaciar el carrito */
                vaciarCarrito($conexion, $_SESSION['id_usuario']); /* Llamo a la función para vaciar el carrito */
                break;
            case 'cancelar_pedido': /* Si la acción es cancelar el pedido */
                if(isset($_POST['carrito']) && isset($_POST['total'])) { /* Verifico que lleguen los datos necesarios */
                    $carrito = json_decode($_POST['carrito'], true); /* Obtengo el carrito y lo decodifico */
                    // Aseguro que el total sea float y con punto decimal
                    $total = floatval(str_replace(',', '.', $_POST['total']));
                    cancelarPedido($conexion, $_SESSION['id_usuario'], $carrito, $total); /* Llamo a la función para cancelar el pedido */
                }
                break;
            case 'realizar_pedido': /* Si la acción es realizar el pedido */
                if(isset($_POST['carrito']) && isset($_POST['total'])) { /* Verifico que lleguen los datos necesarios */
                    $carrito = json_decode($_POST['carrito'], true); /* Obtengo el carrito y lo decodifico */
                    // Aseguro que el total sea float y con punto decimal
                    $total = floatval(str_replace(',', '.', $_POST['total']));
                    realizarPedido($conexion, $_SESSION['id_usuario'], $carrito, $total); /* Llamo a la función para realizar el pedido */
                    if(isset($_POST['compra_unica']) && $_POST['compra_unica'] === 'si') { /* Si es una compra única */
                        eliminarJuegoUnicoDelCarrito($conexion, $_SESSION['id_usuario'], $carrito[0]['id']); /* Elimino el juego único del carrito */
                    } else { /* Si no es una compra única */
                        vaciarCarrito($conexion, $_SESSION['id_usuario']); /* Vacío el carrito */
                    }
                }
                break;
            case 'cancelar_devolucion': /* Si la acción es cancelar una devolución */
                if(isset($_POST['id_juego']) && isset($_POST['total'])) { /* Verifico que lleguen los datos necesarios */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    // Aseguro que el total sea float y con punto decimal
                    $total = floatval(str_replace(',', '.', $_POST['total']));
                    cancelarDevolucion($conexion, $_SESSION['id_usuario'], $id_juego, $total); /* Llamo a la función para cancelar la devolución */
                }
                break;
            case 'realizar_devolucion': /* Si la acción es realizar una devolución */
                if(isset($_POST['id_juego']) && isset($_POST['total'])) { /* Verifico que lleguen los datos necesarios */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    // Aseguro que el total sea float y con punto decimal
                    $total = floatval(str_replace(',', '.', $_POST['total']));
                    realizarDevolucion($conexion, $_SESSION['id_usuario'], $id_juego, $total); /* Llamo a la función para realizar la devolución */
                }
                break;
            case 'realizar_reserva': /* Si la acción es realizar una reserva */
                if(isset($_POST['id_juego']) && isset($_POST['total'])) { /* Verifico que lleguen los datos necesarios */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    // Aseguro que el total sea float y con punto decimal
                    $total = floatval(str_replace(',', '.', $_POST['total']));
                    realizarReserva($conexion, $_SESSION['id_usuario'], $id_juego, $total); /* Llamo a la función para realizar la reserva */
                }
                break;
            case 'solicitar_reserva': /* Si la acción es solicitar reserva */
                if(isset($_POST['id_juego'])) { /* Verifico que llegue el ID del juego */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    $total = floatval(str_replace(',', '.', $_POST['total'])); /* Normalizo el total */
                    solicitar($conexion, $_SESSION['id_usuario'], $id_juego, 'RESERVA', 'PENDIENTE', 'RESERVADO', $total); /* Llamo a la función para solicitar la reserva */
                }
                break;
            case 'cancelar_solicitud_reserva': /* Si la acción es cancelar solicitud de reserva */
                if(isset($_POST['id_juego']) && isset($_POST['total'])) { /* Verifico que lleguen los datos necesarios */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    // Aseguro que el total sea float y con punto decimal
                    $total = floatval(str_replace(',', '.', $_POST['total']));
                    $motivo = ""; /* Inicializo la variable motivo */
                    if(isset($_POST['motivo'])) { /* Verifico que llegue el motivo */
                        $motivo = trim($_POST['motivo']); /* Obtengo el motivo y le quito espacios en blanco */
                    } else { /* Si no se proporciona un motivo */
                        $motivo = "Sin motivo especificado"; /* Asigno un valor por defecto */
                    }
                    cancelarSolicitud($conexion, $_SESSION['id_usuario'], $id_juego, 'RESERVA', 'PENDIENTE', $total, $motivo); /* Llamo a la función para cancelar la solicitud de reserva */
                }                
                break;
            case 'solicitar_devolucion': /* Si la acción es solicitar devolución */
                if(isset($_POST['id_juego']) && isset($_POST['total']) && isset($_POST['motivo'])) { /* Verifico que lleguen los datos necesarios */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    // Aseguro que el total sea float y con punto decimal
                    $total = floatval(str_replace(',', '.', $_POST['total']));
                    $motivo = trim($_POST['motivo']); /* Obtengo el motivo y le quito espacios en blanco */
                    solicitar($conexion, $_SESSION['id_usuario'], $id_juego, 'SOLICITUD_DEVOLUCION', 'PENDIENTE_REVISION', 'PENDIENTE_REVISION', $total, $motivo); /* Llamo a la función para solicitar la devolución */
                }
                break;
            case 'cancelar_solicitud_devolucion': /* Si la acción es cancelar solicitud de devolución */
                if(isset($_POST['id_juego']) && isset($_POST['total'])) { /* Verifico que lleguen los datos necesarios */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    // Aseguro que el total sea float y con punto decimal
                    $total = floatval(str_replace(',', '.', $_POST['total']));
                    $motivo = ""; /* Inicializo la variable motivo */
                    if(isset($_POST['motivo'])) { /* Verifico que llegue el motivo */
                        $motivo = trim($_POST['motivo']); /* Obtengo el motivo y le quito espacios en blanco */
                    } else { /* Si no se proporciona un motivo */
                        $motivo = "Sin motivo especificado"; /* Asigno un valor por defecto */
                    }
                    cancelarSolicitud($conexion, $_SESSION['id_usuario'], $id_juego, 'SOLICITUD_DEVOLUCION', 'PENDIENTE_REVISION', $total, $motivo); /* Llamo a la función para cancelar la solicitud de devolución */
                }                
                break;
            default: /* Si la acción no es válida */
                echo json_encode(["error" => "Acción no válida"]); /* Retorno un error en formato JSON */
                break;
        }
    } else { /* Si no se recibió ninguna acción */
        echo json_encode(["error" => "No se recibió ninguna acción"]); /* Retorno un error en formato JSON */
    }
    
?>