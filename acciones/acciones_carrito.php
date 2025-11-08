<?php

    session_start(); /* Inicio la sesión para poder acceder a las variables de sesión */
    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */

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

            $consulta = $conexion->prepare("INSERT INTO historial (id_usuario, tipo, estado, total) VALUES (:id_usuario, :tipo, :estado, :total)"); /* Preparo la consulta para insertar en historial */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':tipo', $tipo_enum, PDO::PARAM_STR); /* Vinculo el parámetro del tipo */
            $consulta->bindParam(':estado', $estado_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta->bindParam(':total', $total, PDO::PARAM_STR); /* Vinculo el parámetro del total */
            $consulta->execute(); /* Ejecuto la consulta */

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

            // Devuelvo el id del pedido y posibles errores para depuración
            echo json_encode([
                'exito' => true, /* Indico que la operación fue exitosa */
                'id_pedido' => $id_pedido, /* ID del pedido */
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
    function realizarDevolucion($conexion, $id_usuario, $id_juego, $total, $motivo) {
        try { /* Inicio bloque try para capturar errores */
            $consulta = $conexion->prepare("DELETE FROM biblioteca WHERE id_juego = :id_juego AND id_usuario = :id_usuario"); /* Preparo la consulta para eliminar el juego de la biblioteca */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */
            
            // Los valores de tipo y estado deben coincidir con los ENUM definidos en la base de datos
            $tipo_enum = 'DEVOLUCION'; /* Tipo de acción */
            $estado_enum = 'APROBADA'; /* Estado de la acción */
            $estado_juego_enum = 'DEVUELTO'; /* Estado del juego */

            $consulta = $conexion->prepare("INSERT INTO historial (id_usuario, tipo, estado, total, comentario) VALUES (:id_usuario, :tipo, :estado, :total, :comentario)"); /* Preparo la consulta para insertar en historial */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':tipo', $tipo_enum, PDO::PARAM_STR); /* Vinculo el parámetro del tipo */
            $consulta->bindParam(':estado', $estado_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta->bindParam(':total', $total, PDO::PARAM_STR); /* Vinculo el parámetro del total */
            $consulta->bindParam(':comentario', $motivo, PDO::PARAM_STR); /* Vinculo el parámetro del comentario */
            $consulta->execute(); /* Ejecuto la consulta */

            $id_devolucion = $conexion->lastInsertId(); /* Obtengo el ID de la devolución */

            $consulta_juego = $conexion->prepare("INSERT INTO historial_compras (id_historial, id_juego, precio, estado, comentario) VALUES (:id_historial, :id_juego, :precio, :estado, :comentario)"); /* Preparo la consulta para insertar en historial_compras */
            $consulta_juego->bindParam(':id_historial', $id_devolucion, PDO::PARAM_INT); /* Vinculo el parámetro del ID del historial */
            $consulta_juego->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta_juego->bindParam(':precio', $total, PDO::PARAM_STR); /* Vinculo el parámetro del precio */
            $consulta_juego->bindParam(':estado', $estado_juego_enum, PDO::PARAM_STR); /* Vinculo el parámetro del estado */
            $consulta_juego->bindParam(':comentario', $motivo, PDO::PARAM_STR); /* Vinculo el parámetro del comentario */
            $consulta_juego->execute(); /* Ejecuto la consulta */

            // Devuelvo el id de la devolución y posibles errores para depuración
            echo json_encode([
                'id_devolucion' => $id_devolucion, /* ID de la devolución */
                'errores_juegos' => $errores_juegos /* Array de errores */
            ]); /* Retorno el resultado en formato JSON */
        } catch (Exception $e) { /* Si hay error al realizar la devolución */
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
                if(isset($_POST['id_juego']) && isset($_POST['total']) && isset($_POST['motivo'])) { /* Verifico que lleguen los datos necesarios */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    // Aseguro que el total sea float y con punto decimal
                    $total = floatval(str_replace(',', '.', $_POST['total']));
                    $motivo = trim($_POST['motivo']); /* Obtengo el motivo y le quito espacios en blanco */
                    realizarDevolucion($conexion, $_SESSION['id_usuario'], $id_juego, $total, $motivo); /* Llamo a la función para realizar la devolución */
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