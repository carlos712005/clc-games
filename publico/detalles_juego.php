<?php

    // Verificar si se ha proporcionado un ID de juego válido
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { /* Verifico que llegue un ID válido por GET */
        header('Location: index.php'); /* Si no hay ID válido, redirijo al index */
        exit; /* Termino la ejecución */
    }

    // Encabezado de la página
    include __DIR__ . '/../vistas/comunes/encabezado.php'; /* Incluyo el encabezado común con el menú y estilos base */

    echo '<link rel="stylesheet" href="../recursos/css/estilos_detalles_juego.css">'; /* Cargo los estilos específicos para la página de detalles */
        
    //Función para obtener el comentario del usuario
    function obtenerComentarioUsuario($conexion, $id_usuario, $id_juego) {
        try { /* Inicio bloque try para capturar errores */
            $consulta = $conexion->prepare("SELECT id, comentario FROM comentarios WHERE id_usuario = :id_usuario AND id_juego = :id_juego"); /* Preparo la consulta para obtener el comentario del usuario */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */

            $comentario = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el comentario */

            if ($comentario) { /* Si el comentario existe */
                return htmlspecialchars($comentario['comentario']); /* Retorno el comentario */
            } else { /* Si no existe el comentario */
                return null; /* Retorno null */            
            }
        } catch (PDOException $e) { /* Si hay error al obtener el comentario */
            $_SESSION['error_general'] = "Error al obtener el comentario: " . $e->getMessage(); /* Establezco mensaje de error */
            return null; /* Retorno null */
        }
    }

    //Función para obtener la valoración del usuario
    function obtenerValoracionUsuario($conexion, $id_usuario, $id_juego) {
        try { /* Inicio bloque try para capturar errores */
            $consulta = $conexion->prepare("SELECT id, valoracion FROM valoraciones WHERE id_usuario = :id_usuario AND id_juego = :id_juego"); /* Preparo la consulta para obtener la valoración del usuario */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */

            $valoracion = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo la valoración */

            if ($valoracion) { /* Si la valoración existe */
                return htmlspecialchars($valoracion['valoracion']); /* Retorno la valoración */
            } else { /* Si no existe la valoración */
                return null; /* Retorno null */
            }
        } catch (PDOException $e) { /* Si hay error al obtener la valoración */
            $_SESSION['error_general'] = "Error al obtener la valoración: " . $e->getMessage(); /* Establezco mensaje de error */
            return null; /* Retorno null */
        }
    }

    $id_juego = (int)$_GET['id']; /* Convierto el ID a entero para seguridad */

    try { /* Inicio bloque try para capturar errores de base de datos */
        // Obtener los datos del juego
        $consulta = $conexion->prepare("SELECT * FROM juegos WHERE id = :id_juego"); /* Preparo consulta para obtener todos los datos del juego */
        $consulta->bindParam(':id_juego', $id_juego); /* Vinculo el ID del juego */
        $consulta->execute(); /* Ejecuto la consulta */
        $juego = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del juego como array asociativo */

        if (!$juego) { /* Si no se encontró el juego */
            echo "Juego no encontrado."; /* Muestro mensaje de error */
            exit; /* Termino la ejecución */
        }

        // Determinar la URL de la página anterior
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'detalles_juego.php') === false) { /* Si hay una página anterior y no es otra página de detalles */
            $_SESSION['referer'] = $_SERVER['HTTP_REFERER']; /* Guardo la página de origen en la sesión para poder volver */
        }

        // Si el juego está inactivo y el usuario NO es admin, redirigir al index
        $es_admin = (isset($_SESSION['id_rol']) && (int)$_SESSION['id_rol'] === 1); /* Verifico si el usuario es admin */
        // Si el usuario no es admin o viene de la biblioteca y el juego está inactivo, redirijo
        if((!$es_admin || $_SESSION['referer'] === 'biblioteca.php') && isset($juego['activo']) && (int)$juego['activo'] === 0) {
            echo '<script>window.location.href = "index.php";</script>'; /* Redirijo con JavaScript */
            exit; /* Termino la ejecución */
        }

        // Obtener los filtros asociados al juego actual
        $consulta = $conexion->prepare("SELECT id_juego, id_filtro FROM juegos_filtros WHERE id_juego = :id_juego"); /* Preparo consulta para obtener los filtros del juego */
        $consulta->bindParam(':id_juego', $juego['id'], PDO::PARAM_INT); /* Vinculo el ID del juego */
        $consulta->execute(); /* Ejecuto la consulta */
        $filtros_juego = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los filtros del juego */
        
        // Obtener el ID del filtro de tipo a partir de la clave del tipo
        $consulta = $conexion->prepare("SELECT id_fijo FROM filtros WHERE clave = :tipo"); /* Preparo consulta para obtener el ID del tipo de juego */
        $consulta->bindParam(':tipo', $juego['tipo'], PDO::PARAM_STR); /* Vinculo la clave del tipo */
        $consulta->execute(); /* Ejecuto la consulta */
        $id_tipo = $consulta->fetchColumn(); /* Obtengo solo el ID del tipo */

        // Obtener el nombre del filtro de tipo a partir de la clave del tipo
        $consulta = $conexion->prepare("SELECT nombre FROM filtros WHERE id_fijo = :id_tipo"); /* Preparo consulta para obtener el nombre del tipo */
        $consulta->bindParam(':id_tipo', $id_tipo, PDO::PARAM_STR); /* Vinculo el ID del tipo */
        $consulta->execute(); /* Ejecuto la consulta */
        $nombre_tipo = $consulta->fetchColumn(); /* Obtengo solo el nombre del tipo */

        // Obtener los filtros asociados al juego actual                                
        $consulta = $conexion->prepare("
            SELECT 
                f.id_fijo,
                f.nombre,
                f.tipo_filtro,
                f.clave,
                f.orden
            FROM juegos_filtros jf
            INNER JOIN filtros f ON jf.id_filtro = f.id_fijo
            WHERE jf.id_juego = :id_juego
        "); /* Preparo consulta compleja para obtener todos los datos detallados de los filtros del juego */

        $consulta->bindParam(':id_juego', $juego['id'], PDO::PARAM_INT); /* Vinculo el ID del juego */
        $consulta->execute(); /* Ejecuto la consulta */
        $datos_filtros_juego = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los datos completos de los filtros */

        // Verificar si el juego está en favoritos, carrito o biblioteca para el usuario actual
        $esta_en_favoritos = false; /* Inicializo la variable que indica si el juego está en favoritos */
        $esta_en_carrito = false; /* Inicializo la variable que indica si el juego está en el carrito */
        $esta_en_biblioteca = false; /* Inicializo la variable que indica si el juego está en la biblioteca */
        $reserva_solicitada = false; /* Inicializo la variable que indica si el juego tiene una reserva solicitada */
        $devolucion_solicitada = false; /* Inicializo la variable que indica si el juego tiene una devolución solicitada */
        $reserva_aprobada = false; /* Inicializo la variable que indica si el juego tiene una reserva aprobada */
        $reserva_rechazada = false; /* Inicializo la variable que indica si el juego tiene una reserva rechazada */
        $devolucion_aprobada = false; /* Inicializo la variable que indica si el juego tiene una devolución aprobada */
        $devolucion_rechazada = false; /* Inicializo la variable que indica si el juego tiene una devolución rechazada */

        if(isset($_SESSION['id_usuario'])) { /* Si el usuario está logueado */
            // Verificar si el juego está en favoritos para el usuario actual
            $consulta = $conexion->prepare("SELECT id_juego FROM favoritos WHERE id_juego = :id_juego AND id_usuario = :id_usuario"); /* Preparo consulta */
            $consulta->bindParam(':id_juego', $juego['id']); /* Vinculo el ID del juego */
            $consulta->bindParam(':id_usuario', $_SESSION['id_usuario']); /* Vinculo el ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */
            $juego_en_favoritos = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del juego como array asociativo */

            if($juego_en_favoritos) $esta_en_favoritos = true; /* Marco que el juego está en favoritos */

            // Verificar si el juego está en el carrito para el usuario actual
            $consulta = $conexion->prepare("SELECT id_juego FROM carrito WHERE id_juego = :id_juego AND id_usuario = :id_usuario"); /* Preparo consulta */
            $consulta->bindParam(':id_juego', $juego['id']); /* Vinculo el ID del juego */
            $consulta->bindParam(':id_usuario', $_SESSION['id_usuario']); /* Vinculo el ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */
            $juego_en_carrito = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del juego como array asociativo */

            if($juego_en_carrito) $esta_en_carrito = true; /* Marco que el juego está en el carrito */
        
            // Verificar si el juego está en la biblioteca para el usuario actual
            $consulta = $conexion->prepare("SELECT id_juego FROM biblioteca WHERE id_juego = :id_juego AND id_usuario = :id_usuario"); /* Preparo consulta */
            $consulta->bindParam(':id_juego', $juego['id']); /* Vinculo el ID del juego */
            $consulta->bindParam(':id_usuario', $_SESSION['id_usuario']); /* Vinculo el ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */
            $juego_en_biblioteca = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del juego como array asociativo */

            if($juego_en_biblioteca) $esta_en_biblioteca = true; /* Marco que el juego está en la biblioteca */
        
            if($esta_en_biblioteca) { /* Si el juego está en la biblioteca */
                $consulta = $conexion->prepare("
                    SELECT h.metodo_pago
                    FROM historial_compras hc
                    INNER JOIN historial h ON hc.id_historial = h.id
                    WHERE hc.id_juego = :id_juego 
                    AND h.id_usuario = :id_usuario
                    AND ((h.tipo = 'COMPRA' AND h.estado = 'PAGADA') OR (h.tipo = 'RESERVA' AND h.estado = 'RESERVADA'))
                    ORDER BY h.creado_en DESC
                    LIMIT 1
                "); /* Preparo consulta para obtener el método de pago del juego */
                $consulta->bindParam(':id_juego', $juego['id']); /* Vinculo el ID del juego */
                $consulta->bindParam(':id_usuario', $_SESSION['id_usuario']); /* Vinculo el ID del usuario */
                $consulta->execute(); /* Ejecuto la consulta */
                $resultado_metodo_pago = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el método de pago */
                $metodo_pago_juego = $resultado_metodo_pago['metodo_pago'] ?? 'tarjeta'; /* Guardo el método de pago o 'tarjeta' por defecto */

                if($metodo_pago_juego === 'paypal') { /* Si el método de pago es PayPal */
                    $_SESSION['metodo_reembolso'] = 'paypal'; /* Marco el método de reembolso como PayPal */
                    $consulta = $conexion->prepare("
                        SELECT h.paypal_order_id, h.paypal_capture_id,
                        h.paypal_email, hc.precio
                        FROM historial_compras hc
                        INNER JOIN historial h ON hc.id_historial = h.id
                        WHERE hc.id_juego = :id_juego 
                        AND h.id_usuario = :id_usuario
                        AND ((h.tipo = 'COMPRA' AND h.estado = 'PAGADA') OR (h.tipo = 'RESERVA' AND h.estado = 'RESERVADA'))
                        ORDER BY h.creado_en DESC
                        LIMIT 1
                    "); /* Preparo consulta para obtener el método de pago del juego */
                    $consulta->bindParam(':id_juego', $juego['id']); /* Vinculo el ID del juego */
                    $consulta->bindParam(':id_usuario', $_SESSION['id_usuario']); /* Vinculo el ID del usuario */
                    $consulta->execute(); /* Ejecuto la consulta */
                    $_SESSION['paypal_info_reembolso'] = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el método de pago */
                } else { /* Si el método de pago es Tarjeta */
                    $_SESSION['metodo_reembolso'] = 'tarjeta'; /* Marco el método de reembolso como Tarjeta */
                }
            } else { /* Si el juego no está en la biblioteca */
                $_SESSION['metodo_reembolso'] = null; /* Marco el método de reembolso como nulo */
            }

            // Obtengo el ID del último registro en historial para este usuario+juego (cualquier tipo/estado)
            $consulta = $conexion->prepare("
                SELECT h.id
                FROM historial h
                INNER JOIN historial_compras hc ON hc.id_historial = h.id
                WHERE h.id_usuario = :id_usuario AND hc.id_juego = :id_juego
                ORDER BY h.creado_en DESC
                LIMIT 1
            "); /* Último historial asociado al juego para el usuario */
            $consulta->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT); /* Vinculo el ID del usuario */
            $consulta->bindParam(':id_juego', $juego['id'], PDO::PARAM_INT); /* Vinculo el ID del juego */
            $consulta->execute();
            $ultimo_historial_id = $consulta->fetchColumn(); /* ID del último historial (o false si no hay) */

            if($ultimo_historial_id) { /* Si existe algún historial previo del juego */
                // Función para verificar si el último historial coincide con tipo y estado dados
                function verificarExistencia($conexion, $ultimo_historial_id, $tipo, $estado) {
                    $consulta = $conexion->prepare("
                        SELECT h.id AS id_historial, h.id_usuario, h.tipo, h.estado, h.total, h.metodo_pago, h.comentario, h.creado_en,
                                hc.id AS id_detalle, hc.id_juego, hc.precio, hc.estado AS estado_detalle, hc.comentario AS comentario_detalle
                        FROM historial h
                        INNER JOIN historial_compras hc ON hc.id_historial = h.id
                        WHERE h.id = :id_historial AND h.tipo = :tipo AND h.estado = :estado
                        LIMIT 1
                    "); /* Verifico si el último historial coincide con el tipo y estado especificados */
                    $consulta->bindParam(':id_historial', $ultimo_historial_id, PDO::PARAM_INT); /* Vinculo el ID del historial */
                    $consulta->bindParam(':tipo', $tipo, PDO::PARAM_STR); /* Vinculo el tipo */
                    $consulta->bindParam(':estado', $estado, PDO::PARAM_STR); /* Vinculo el estado */
                    $consulta->execute(); /* Ejecuto la consulta */
                    return $consulta->fetch(PDO::FETCH_ASSOC); /* Retorno el resultado */
                }

                $reserva_en_solicitud = verificarExistencia($conexion, $ultimo_historial_id, 'RESERVA', 'PENDIENTE'); /* Verifico si el último historial es una reserva pendiente */
                $devolucion_en_solicitud = verificarExistencia($conexion, $ultimo_historial_id, 'SOLICITUD_DEVOLUCION', 'PENDIENTE_REVISION'); /* Verifico si el último historial es una devolución pendiente */
                $reserva_esta_aprobada = verificarExistencia($conexion, $ultimo_historial_id, 'RESERVA', 'APROBADA'); /* Verifico si el último historial es una reserva aprobada */
                $reserva_esta_rechazada = verificarExistencia($conexion, $ultimo_historial_id, 'RESERVA', 'RECHAZADA'); /* Verifico si el último historial es una reserva rechazada */
                $devolucion_esta_aprobada = verificarExistencia($conexion, $ultimo_historial_id, 'SOLICITUD_DEVOLUCION', 'APROBADA'); /* Verifico si el último historial es una devolución aprobada */
                $devolucion_esta_rechazada = verificarExistencia($conexion, $ultimo_historial_id, 'SOLICITUD_DEVOLUCION', 'RECHAZADA'); /* Verifico si el último historial es una devolución rechazada */
                if($reserva_en_solicitud) $reserva_solicitada = true; /* Si el último registro es reserva pendiente, marco la bandera */
                if($devolucion_en_solicitud) $devolucion_solicitada = true; /* Si el último registro es devolución pendiente, marco la bandera */
                if($reserva_esta_aprobada) $reserva_aprobada = true; /* Si el último registro es reserva aprobada, marco la bandera */
                if($reserva_esta_rechazada) $reserva_rechazada = true; /* Si el último registro es reserva rechazada, marco la bandera */
                if($devolucion_esta_aprobada) $devolucion_aprobada = true; /* Si el último registro es devolución aprobada, marco la bandera */
                if($devolucion_esta_rechazada) $devolucion_rechazada = true; /* Si el último registro es devolución rechazada, marco la bandera */
            }
        }
        
        // Obtener los comentarios del juego
        $limite_mostrar = 5; /* Límite de comentarios a mostrar inicialmente */
        $mostrar_mas = isset($_GET['ver_comentarios']) && $_GET['ver_comentarios'] === 'todos'; /* Verifico si se pide ver todos */

        /* Primero obtengo el total de comentarios */
        $consulta_total = $conexion->prepare("SELECT COUNT(*) FROM comentarios WHERE id_juego = :id_juego"); /* Cuento total de comentarios */
        $consulta_total->bindParam(':id_juego', $juego['id'], PDO::PARAM_INT); /* Vinculo el ID del juego */
        $consulta_total->execute(); /* Ejecuto la consulta */
        $total_comentarios = (int)$consulta_total->fetchColumn(); /* Obtengo el total */
        
        /* Luego obtengo los comentarios con o sin límite */
        if ($mostrar_mas) { /* Si se pide ver todos */
            $consulta = $conexion->prepare("SELECT c.id, c.comentario, c.creado_en, c.actualizado_en, u.acronimo FROM comentarios c INNER JOIN usuarios u ON c.id_usuario = u.id WHERE c.id_juego = :id_juego ORDER BY c.creado_en DESC"); /* Sin límite */
        } else { /* Solo los primeros 5 */
            $consulta = $conexion->prepare("SELECT c.id, c.comentario, c.creado_en, c.actualizado_en, u.acronimo FROM comentarios c INNER JOIN usuarios u ON c.id_usuario = u.id WHERE c.id_juego = :id_juego ORDER BY c.creado_en DESC LIMIT :limite"); /* Con límite */
        }
        $consulta->bindParam(':id_juego', $juego['id'], PDO::PARAM_INT); /* Vinculo el ID del juego */
        if (!$mostrar_mas) { /* Solo añado límite si no se pide ver todos */
            $consulta->bindParam(':limite', $limite_mostrar, PDO::PARAM_INT); /* Vinculo el límite */
        }
        $consulta->execute(); /* Ejecuto la consulta */
        $comentarios_juego = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo los comentarios */

        /* Media global y total de valoraciones */
        $consulta_media = $conexion->prepare("SELECT AVG(valoracion) AS media, COUNT(*) AS total FROM valoraciones WHERE id_juego = :id_juego"); /* Preparo consulta para obtener la media y total de valoraciones */
        $consulta_media->bindParam(':id_juego', $juego['id'], PDO::PARAM_INT); /* Vinculo el ID del juego */
        $consulta_media->execute(); /* Ejecuto la consulta */
        $datos_valoraciones = $consulta_media->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos de media y total */
        $media_valoraciones = $datos_valoraciones && $datos_valoraciones['media'] !== null ? round((float)$datos_valoraciones['media'], 2) : 0; /* Calculo la media redondeada a 2 decimales */
        $total_valoraciones = $datos_valoraciones ? (int)$datos_valoraciones['total'] : 0; /* Total de valoraciones */
    } catch (PDOException $e) { /* Si hay error en cualquier consulta */
        echo "Error al obtener los datos: " . $e->getMessage(); /* Muestro el mensaje de error */
        exit; /* Termino la ejecución */
    }

    // Determinar la URL de regreso (referer)
    if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'detalles_juego.php') === false) { /* Si hay una página anterior y no es otra página de detalles */
        $_SESSION['referer'] = $_SERVER['HTTP_REFERER']; /* Guardo la página de origen en sesión para poder volver */
    }

    if(isset($_SESSION['modo_admin'])) { /* Si está en modo admin */
        $rutaRegreso = $_SESSION['referer'] ?? '../vistas/panel_administrador.php'; /* Si no hay página anterior, uso el panel de admin */
    } else { /* Si no está en modo admin */
        $rutaRegreso = $_SESSION['referer'] ?? 'index.php'; /* Si no hay página anterior, uso el index por defecto */
    }

?>

<main> <!-- Contenedor principal de la página -->
    <h1><?php echo htmlspecialchars($juego['nombre']); ?></h1> <!-- Título del juego, escapado para seguridad -->

    <?php
        $es_proximamente = isset($juego['fecha_lanzamiento']) && strtotime($juego['fecha_lanzamiento']) > time(); /* Verifico si la fecha de lanzamiento es futura */
        if ($es_proximamente) { /* Si el juego es "Próximamente" */
            $fecha_formateada = date('d/m/Y', strtotime($juego['fecha_lanzamiento'])); /* Formateo la fecha de lanzamiento */ ?>
            <p class="etiqueta-proximamente">Próximamente - Lanzamiento: <?php echo htmlspecialchars($fecha_formateada, ENT_QUOTES, 'UTF-8'); ?></p> <!-- Muestro la etiqueta de "Próximamente" con la fecha formateada -->
        <?php }
    ?>

    <?php if(isset($juego['activo']) && $juego['activo'] == 0) { ?> <!-- Si el juego está descatalogado -->
        <p class="juego-descatalogado">Este juego está descatalogado</p> <!-- Mensaje de advertencia -->
    <?php } ?>

    <div class="detalles-contenido"> <!-- Contenedor principal del contenido -->
        <!-- Sección de la imagen -->
        <div class="imagen-seccion"> <!-- Contenedor para la imagen y precio -->
            <img src="../<?php echo htmlspecialchars($juego['portada']); ?>" alt="<?php echo htmlspecialchars($juego['nombre']); ?>" id="imagen-juego"> <!-- Imagen del juego con ruta relativa -->
            <div class="precio-destacado"> <!-- Contenedor destacado para el precio -->
                Precio: <?php if($juego['precio'] !== '0.00') echo str_replace('.', ',', htmlspecialchars($juego['precio'])) . " €"; else echo "Gratis"; ?> <!-- Precio del juego (mostrado con una coma en vez de un punto), si es 0 muestro "Gratis" -->
            </div>
        </div>

        <!-- Información general -->
        <div class="info-general"> <!-- Contenedor para la información básica del juego -->
            <p><strong>Desarrollador:</strong> <?php echo htmlspecialchars($juego['desarrollador']); ?></p> <!-- Desarrollador del juego -->
            <p><strong>Distribuidor:</strong> <?php echo htmlspecialchars($juego['distribuidor']); ?></p> <!-- Distribuidor del juego -->
            <p><strong>Fecha de lanzamiento:</strong> <?php echo htmlspecialchars($juego['fecha_lanzamiento']); ?></p> <!-- Fecha de lanzamiento -->
            <p><strong>Tipo de juego:</strong> <?php echo htmlspecialchars($nombre_tipo); ?></p> <!-- Tipo de juego obtenido anteriormente -->
            <p><strong>Géneros:</strong> <?php /* Inicio sección para mostrar géneros */
                            if(!empty($datos_filtros_juego)) { /* Si el juego tiene filtros asociados */
                                $generos = []; /* Array para almacenar los géneros */
                                foreach($datos_filtros_juego as $filtro) { /* Recorro todos los filtros */
                                    if($filtro['tipo_filtro'] === 'generos') { /* Si es un filtro de género */
                                        $generos[] = htmlspecialchars($filtro['nombre']); /* Añado el género al array */
                                    } 
                                }
                                if(!empty($generos)) { /* Si encontré géneros */
                                    echo implode(', ', $generos) . '.'; /* Los muestro separados por comas */
                                }
                            } ?></p> <!-- Fin de la sección de géneros -->
            <p><strong>Categorías:</strong> <?php /* Inicio sección para mostrar categorías */
                            if(!empty($datos_filtros_juego)) { /* Si el juego tiene filtros asociados */
                                $categorias = []; /* Array para almacenar las categorías */
                                foreach($datos_filtros_juego as $filtro) { /* Recorro todos los filtros */
                                    if($filtro['tipo_filtro'] === 'categorias') { /* Si es un filtro de categoría */
                                        $categorias[] = htmlspecialchars($filtro['nombre']); /* Añado la categoría al array */
                                    } 
                                }
                                if(!empty($categorias)) { /* Si encontré categorías */
                                    echo implode(', ', $categorias) . '.'; /* Las muestro separadas por comas */
                                }
                            } ?></p> <!-- Fin de la sección de categorías -->
            <p><strong>Modos:</strong> <?php /* Inicio sección para mostrar modos de juego */
                            if(!empty($datos_filtros_juego)) { /* Si el juego tiene filtros asociados */
                                $modos = []; /* Array para almacenar los modos */
                                foreach($datos_filtros_juego as $filtro) { /* Recorro todos los filtros */
                                    if($filtro['tipo_filtro'] === 'modos') { /* Si es un filtro de modo */
                                        $modos[] = htmlspecialchars($filtro['nombre']); /* Añado el modo al array */
                                    } 
                                }
                                if(!empty($modos)) { /* Si encontré modos */
                                    echo implode(', ', $modos) . '.'; /* Los muestro separados por comas */
                                }
                            } ?></p> <!-- Fin de la sección de modos -->
            <p><strong>Clasificaciones PEGI:</strong> <?php /* Inicio sección para mostrar clasificaciones PEGI */
                            if(!empty($datos_filtros_juego)) { /* Si el juego tiene filtros asociados */
                                $clasificaciones = []; /* Array para almacenar las clasificaciones */
                                foreach($datos_filtros_juego as $filtro) { /* Recorro todos los filtros */
                                    if($filtro['tipo_filtro'] === 'clasificacionPEGI') { /* Si es un filtro de clasificación PEGI */
                                        $clasificaciones[] = htmlspecialchars($filtro['nombre']); /* Añado la clasificación al array */
                                    } 
                                }
                                if(!empty($clasificaciones)) { /* Si encontré clasificaciones */
                                    echo implode(', ', $clasificaciones) . '.'; /* Las muestro separadas por comas */
                                }
                            } ?></p> <!-- Fin de la sección de clasificaciones PEGI -->
        </div>
    </div>

    <hr> <!-- Línea separadora -->
    <br> <!-- Espacio adicional -->

    <!-- Descripción completa -->
    <div class="descripcion-completa"> <!-- Contenedor para la descripción detallada -->
        <h2>Descripción detallada:</h2> <!-- Título de la sección -->
        <p><?php echo htmlspecialchars($juego['descripcion']); ?></p> <!-- Descripción completa del juego -->
    </div>

    <!-- Requisitos del sistema -->
    <div class="requisitos-sistema"> <!-- Contenedor para los requisitos del sistema -->
        <h2>Requisitos del sistema</h2> <!-- Título de la sección -->
        <p><?php echo htmlspecialchars($juego['requisitos']); ?></p> <!-- Requisitos del sistema -->
    </div>

    <div class="opciones"> <!-- Contenedor para las opciones de acción -->
        <h2>Opciones:</h2> <!-- Título de la sección de opciones -->
        <a href="<?php echo $rutaRegreso; ?>" class="boton-volver"> <!-- Botón para volver a la página anterior -->
            <img src="../recursos/imagenes/atras.png" alt="Icono de Volver" id="icono-volver"> <!-- Icono de volver -->
            <span>Volver atrás</span> <!-- Texto del botón -->
        </a>
        <?php if(!isset($_SESSION['modo_admin']) || $_SESSION['modo_admin'] === false) { ?> <!-- Si no está en modo admin -->
            <?php if($esta_en_favoritos) { ?> <!-- Si el juego ya está en favoritos -->
                <a href="#" onclick="mandarFavoritos('eliminar', <?php echo $juego['id']; ?>, 'modal1', '<h1>Juego eliminado de favoritos</h1>', false)" class="boton-favorito"> <!-- Enlace para eliminar de favoritos -->
                    <img src="../recursos/imagenes/en_favoritos_circulo.png" alt="Icono de Favoritos" id="detalles-icono-favoritos"> <!-- Icono de favoritos -->
                    <span>Eliminar de favoritos</span> <!-- Texto del botón -->
                </a>
            <?php } else { ?> <!-- Si el juego no está en favoritos -->
                <a <?php if(isset($_SESSION['id_usuario'])) { echo 'href="#" onclick="mandarFavoritos(\'agregar\', ' . $juego['id'] . ', \'modal1\', \'<h1>Juego añadido a favoritos</h1>\', false, null, this)"'; } else { echo 'href="../sesiones/formulario_autenticacion.php"'; } ?> class="boton-favorito"> <!-- Enlace para añadir a favoritos -->
                    <img src="../recursos/imagenes/favoritos_circulo.png" alt="Icono de Favoritos" id="detalles-icono-favoritos"> <!-- Icono de favoritos -->
                    <span>Añadir a favoritos</span> <!-- Texto del botón -->
                </a>
            <?php } ?>
            <?php if(!$esta_en_biblioteca) { ?> <!-- Si el juego no está en la biblioteca -->
                <?php if(!isset($es_proximamente) || !$es_proximamente) { ?> <!-- Si el juego no es "Próximamente" -->    
                    <?php if($esta_en_carrito) { ?> <!-- Si el juego ya está en el carrito -->
                        <a href="#" onclick="mandar('eliminar', <?php echo $juego['id']; ?>, 'modal1', '<h1>Juego eliminado del carrito</h1>', false)" id="tarjeta-eliminar<?php echo $juego['id']; ?>" class="detalles-boton-carrito"> <!-- Enlace para quitar del carrito -->
                            <img src="../recursos/imagenes/en_carrito2.png" alt="Icono de Carrito" id="detalles-icono-carrito"> <!-- Icono del carrito -->
                            <span>Quitar del carrito</span> <!-- Texto del botón -->
                        </a>
                    <?php } else { ?> <!-- Si el juego no está en el carrito -->
                        <a <?php if (isset($_SESSION['id_usuario'])) { echo 'href="#" onclick="mandar(\'agregar\', ' . $juego['id'] . ', \'modal1\', \'<h1>Juego añadido al carrito</h1>\', false , null, this)"'; } else { echo 'href="../sesiones/formulario_autenticacion.php"'; } ?> id="tarjeta-anadir<?php echo $juego['id']; ?>" class="detalles-boton-carrito"> <!-- Enlace para añadir al carrito -->
                            <img src="../recursos/imagenes/carrito2.png" alt="Icono de Carrito" id="detalles-icono-carrito"> <!-- Icono del carrito -->
                            <span>Añadir al carrito</span> <!-- Texto del botón -->
                        </a>
                    <?php } ?>
                    <?php 
                    $carrito_ficticio = []; /* Inicializo el array del carrito ficticio */
                    $carrito_ficticio[] = [ /* Agrego los datos del juego al carrito */
                        'id' => $id_juego, /* ID del juego */
                        'nombre' => $juego['nombre'], /* Nombre del juego */
                        'portada' => $juego['portada'], /* Portada del juego */
                        'tipo' => $nombre_tipo, /* Uso el nombre del tipo en lugar de la clave */
                        'precio' => $juego['precio'], /* Precio del juego */
                        'resumen' => $juego['resumen'] /* Resumen del juego */
                    ]; 
                    $carrito_json = htmlspecialchars(json_encode($carrito_ficticio), ENT_QUOTES, 'UTF-8'); /* Escapo el JSON para usarlo de forma segura en HTML */
                    ?>
                    <a <?php if(isset($_SESSION['id_usuario'])) { echo 'href="#" onclick="mostrarResumenPedido(\'' . $carrito_json . '\', true)"'; } else { echo 'href="../sesiones/formulario_autenticacion.php"'; } ?> class="boton-comprar"> <!-- Enlace para comprar directamente -->
                        <img src="../recursos/imagenes/comprar.png" alt="Icono de Comprar" id="icono-comprar"> <!-- Icono de comprar -->
                        <span>Comprar ya</span> <!-- Texto del botón -->
                    </a>
                <?php } else { /* Si el juego es "Próximamente" */
                    $reserva = []; /* Inicializo el array de la reserva */
                    $reserva[] = [ /* Agrego los datos del juego a la reserva */
                        'id' => $juego['id'], /* ID del juego */
                        'nombre' => $juego['nombre'], /* Nombre del juego */
                        'portada' => $juego['portada'], /* Portada del juego */
                        'tipo' => $nombre_tipo, /* Uso el nombre del tipo en lugar de la clave */
                        'precio' => $juego['precio'], /* Precio del juego */
                        'resumen' => $juego['resumen'] /* Resumen del juego */
                    ]; 
                    $reserva_json = htmlspecialchars(json_encode($reserva), ENT_QUOTES, 'UTF-8'); /* Escapo el JSON para usarlo de forma segura en HTML */ ?>
                    <input type="hidden" id="reserva-json<?php echo $juego['id']; ?>" value="<?php echo $reserva_json; ?>" /> <!-- Input oculto para conservar siempre el JSON de la reserva (independiente del estado del botón) -->
                    <?php if((!$reserva_solicitada || $reserva_rechazada) && !$reserva_aprobada) { /* Si no hay reserva solicitada */ ?>
                        <a <?php if(isset($_SESSION['id_usuario'])) { echo 'href="#" onclick="mostrarResumenPedido(\'' . $reserva_json . '\', true, \'reserva\', this)"'; } else { echo 'href="../sesiones/formulario_autenticacion.php"'; } ?> id="reserva-pedir<?php echo $juego['id']; ?>" class="boton-reservar"> <!-- Enlace de reservar -->
                            <img src="../recursos/imagenes/reservable.png" alt="Icono de Reservar" id="icono-reservar"> <!-- Icono de reservar -->
                            <span>Solicitar reserva</span> <!-- Texto del botón -->
                        </a>
                    <?php } else if($reserva_aprobada) { /* Si hay reserva solicitada y aprobada */ ?>
                        <a href="#" onclick="completarSolicitud('reserva', <?php echo $juego['id']; ?>)" id="reserva-cancelar<?php echo $juego['id']; ?>" class="completar-reserva"> <!-- Enlace de completar reserva -->
                            <img src="../recursos/imagenes/reservable.png" alt="Icono de Completar Reserva" id="icono-reservar"> <!-- Icono de Completar Reserva -->
                            <span>Completar reserva</span> <!-- Texto del botón -->
                        </a>
                    <?php } else { /* Si la reserva está solicitada pero no aprobada ni rechazada */ ?>
                        <a href="#" onclick="cancelarSolicitud('cancelar_solicitud_reserva', <?php echo $juego['id']; ?>, '<?php echo $juego['nombre']; ?>', <?php echo $juego['precio']; ?>, this)" id="reserva-cancelar<?php echo $juego['id']; ?>" class="boton-cancelar-solicitud-reserva"> <!-- Enlace de cancelar solicitud de reserva -->
                            <img src="../recursos/imagenes/cancelar_solicitud.png" alt="Icono de Cancelar Solicitud" id="icono-cancelar-solicitud"> <!-- Icono de Cancelar Solicitud -->
                            <span>Cancelar solicitud</span> <!-- Texto del botón -->
                        </a>
                    <?php }
                } ?>
            <?php } else {  /* Si el juego ya está en la biblioteca */ ?>
                <a href="#" onclick="opcionNoDisponible()" class="boton-jugar"> <!-- Enlace para jugar al juego -->
                    <img src="../recursos/imagenes/jugar.png" alt="Icono de Jugar" id="icono-jugar"> <!-- Icono de jugar -->
                    <span>Jugar</span> <!-- Texto del botón -->
                </a>
                <?php
                $datos_devolucion = []; /* Inicializo el array de devolución */
                $datos_devolucion[] = [ /* Agrego los datos del juego al array de devolución */
                    'id' => $juego['id'], /* ID del juego */
                    'nombre' => $juego['nombre'], /* Nombre del juego */
                    'portada' => $juego['portada'], /* Portada del juego */
                    'tipo' => $nombre_tipo, /* Uso el nombre del tipo en lugar de la clave */
                    'precio' => $juego['precio'], /* Precio del juego */
                    'resumen' => $juego['resumen'] /* Resumen del juego */
                ];
                $datos_devolucion_json = htmlspecialchars(json_encode($datos_devolucion), ENT_QUOTES, 'UTF-8'); /* Escapo el JSON para usarlo de forma segura en HTML */ ?>
                <input type="hidden" id="devolucion-json<?php echo $juego['id']; ?>" value="<?php echo $datos_devolucion_json; ?>" /> <!-- Input oculto para conservar siempre el JSON de la devolución (independiente del estado del botón) -->
                <?php if(!$devolucion_solicitada && !$devolucion_aprobada && !$devolucion_rechazada) { ?> <!-- Si no hay devolución solicitada -->
                    <a href="#" onclick="descambiarJuego(<?php echo $juego['id']; ?>, <?php echo $juego['precio']; ?>, '<?php echo $juego['nombre']; ?>', this)" id="devolucion-pedir<?php echo $juego['id']; ?>" class="boton-descambiar"> <!-- Enlace para solicitar devolución -->
                        <img src="../recursos/imagenes/descambiar.png" alt="Icono de Descambiar" id="icono-descambiar"> <!-- Icono de Descambiar -->
                        <span>Solicitar devolución</span> <!-- Texto del botón -->
                    </a>
                <?php } else { /* Si hay devolución solicitada */
                    if($devolucion_aprobada) { /* Si la devolución está aprobada */ ?>
                        <a href="#" onclick="completarSolicitud('devolucion', <?php echo $juego['id']; ?>)" class="boton-descambiar"> <!-- Enlace para completar devolución -->
                            <img src="../recursos/imagenes/descambiar.png" alt="Icono de Descambiar" id="icono-descambiar"> <!-- Icono de Descambiar -->
                            <span>Completar devolución</span> <!-- Texto del botón -->
                        </a>
                    <?php } else if($devolucion_rechazada) { /* Si la devolución está rechazada */ ?>
                        <p class="mensaje-rechazado">Tu solicitud de devolución fue rechazada. No podrás volver a solicitarla.</p> <!-- Mensaje de rechazo -->
                    <?php } else { /* Si la devolución está pendiente */ ?>
                        <a href="#" onclick="cancelarSolicitud('cancelar_solicitud_devolucion', <?php echo $juego['id']; ?>, '<?php echo $juego['nombre']; ?>', <?php echo $juego['precio']; ?>, this)" id="devolucion-cancelar<?php echo $juego['id']; ?>" class="boton-descambiar"> <!-- Enlace para cancelar solicitud de devolución -->
                            <img src="../recursos/imagenes/rechazar_devolucion.png" alt="Icono de Cancelar Devolución" id="icono-descambiar"> <!-- Icono de Cancelar Devolución -->
                            <span>Cancelar devolución</span> <!-- Texto del botón -->
                        </a>
                    <?php }
                } ?>
            <?php } ?>
        <?php } else { ?> <!-- Si está en modo admin -->
            <a href="../vistas/editar_juego.php?id=<?php echo $juego['id']; ?>" class="boton-editar-juego"> <!-- Enlace a editar del juego -->
                <img src="../recursos/imagenes/editar_juego.png" alt="Icono de Editar" id="icono-editar"> <!-- Icono de editar -->
                <span>Editar</span> <!-- Texto del botón -->
            </a>
            <?php if(isset($juego['activo']) && $juego['activo'] == 0) { ?> <!-- Si el juego está descatalogado -->
                <a href="#" onclick="reactivarJuego(<?php echo (int)$juego['id']; ?>, '<?php echo htmlspecialchars($juego['nombre'], ENT_QUOTES); ?>')" class="boton-reactivar"> <!-- Enlace para reactivar juego -->
                    <img src="../recursos/imagenes/reactivar.png" alt="Icono de Reactivar" id="icono-reactivar"> <!-- Icono de reactivar -->
                    <span>Reactivar</span> <!-- Texto del botón -->
                </a>
            <?php } else { ?>
                <a href="#" onclick="eliminarJuego(<?php echo (int)$juego['id']; ?>, '<?php echo htmlspecialchars($juego['nombre'], ENT_QUOTES); ?>')" class="boton-eliminar"> <!-- Enlace para eliminar juego -->
                    <img src="../recursos/imagenes/eliminar_juego.png" alt="Icono de Eliminar" id="icono-eliminar"> <!-- Icono de eliminar -->
                    <span>Eliminar</span> <!-- Texto del botón -->
                </a>
            <?php } ?>
        <?php } ?>
    </div>

    <?php if(isset($_SESSION['id_usuario']) && (!isset($_SESSION['modo_admin']) || (isset($_SESSION['modo_admin']) && !$_SESSION['modo_admin']))) { ?> <!-- Si el usuario está logueado -->
        <div class="comentarios-usuario"> <!-- Sección de comentario del usuario -->
            <h2>Tu comentario</h2> <!-- Título de la sección -->
            <?php if (isset($_SESSION['error_general_comentario'])) { ?> <!-- Si hay error general -->
                <div class="mensaje-error"> <?php echo htmlspecialchars($_SESSION['error_general_comentario']); ?> </div> <!-- Muestro el error -->
                <?php unset($_SESSION['error_general_comentario']); ?> <!-- Limpio el error de la sesión -->
            <?php } ?> <!-- Fin del condicional -->
            <?php if (isset($_SESSION['mensaje_exito_comentario'])) { ?> <!-- Si hay mensaje de éxito -->
                <div class="mensaje-exito"> <?php echo htmlspecialchars($_SESSION['mensaje_exito_comentario']); ?> </div> <!-- Muestro el mensaje de éxito -->
                <?php unset($_SESSION['mensaje_exito_comentario']); ?> <!-- Limpio el mensaje de la sesión -->
            <?php } ?> <!-- Fin del condicional -->
            <form action="../acciones/comentarios_valoraciones.php" method="post" id="form-comentario" class="form-comentario"> <!-- Formulario para crear/editar comentario -->
                <input type="hidden" name="id_juego" value="<?php echo $juego['id']; ?>"> <!-- ID del juego -->
                <textarea name="comentario" id="textarea-comentario" rows="5" cols="60" placeholder="Escribe tu comentario aquí..." required><?php echo obtenerComentarioUsuario($conexion, $_SESSION['id_usuario'], $juego['id']); ?></textarea> <!-- Área de texto para el comentario, con el texto actual si existe -->
                <?php if (isset($_SESSION['id_usuario']) && obtenerComentarioUsuario($conexion, $_SESSION['id_usuario'], $juego['id']) != null) { ?> <!-- Si el comentario ya existe -->
                    <div class="botones-comentario"> <!-- Contenedor para los botones -->
                        <input type="hidden" name="accion" value="editar_comentario"> <!-- Acción a realizar -->
                        <button type="submit" class="boton-modificar-comentario"> <!-- Botón para modificar -->
                            <img src="../recursos/imagenes/editar_comentario.png" alt="Icono de Guardar" id="icono-modificar-comentario"> <!-- Icono de guardar -->
                            <span>Modificar comentario</span> <!-- Texto del botón -->
                        </button>
                        <button type="button" class="boton-eliminar-comentario" onclick="eliminarComentario()"> <!-- Botón para eliminar -->
                            <img src="../recursos/imagenes/eliminar_comentario.png" alt="Icono de Eliminar" id="icono-eliminar-comentario"> <!-- Icono de eliminar -->
                            <span>Eliminar comentario</span> <!-- Texto del botón -->
                        </button>
                    </div>
                <?php } else { ?> <!-- Si el comentario no existe -->
                    <input type="hidden" name="accion" value="crear_comentario"> <!-- Acción a realizar -->
                    <button type="submit" class="boton-publicar-comentario"> <!-- Botón para publicar -->
                        <img src="../recursos/imagenes/guardar_comentario.png" alt="Icono de Guardar" id="icono-publicar-comentario"> <!-- Icono de guardar -->
                        <span>Publicar comentario</span> <!-- Texto del botón -->
                    </button>
                <?php } ?>
            </form>
        </div>
        <div class="valoraciones-usuario"> <!-- Sección de valoración del usuario -->
            <h2>Tu valoración</h2> <!-- Título de la sección -->
            <?php if (isset($_SESSION['error_general_valoracion'])) { ?> <!-- Si hay error general -->
                <div class="mensaje-error"> <?php echo htmlspecialchars($_SESSION['error_general_valoracion']); ?> </div> <!-- Muestro el error -->
                <?php unset($_SESSION['error_general_valoracion']); ?> <!-- Limpio el error de la sesión -->
            <?php } ?> <!-- Fin del condicional -->
            <?php if (isset($_SESSION['mensaje_exito_valoracion'])) { ?> <!-- Si hay mensaje de éxito -->
                <div class="mensaje-exito"> <?php echo htmlspecialchars($_SESSION['mensaje_exito_valoracion']); ?> </div> <!-- Muestro el mensaje de éxito -->
                <?php unset($_SESSION['mensaje_exito_valoracion']); ?> <!-- Limpio el mensaje de la sesión -->
            <?php } ?> <!-- Fin del condicional -->
            <form action="../acciones/comentarios_valoraciones.php" method="post" id="form-valoracion" class="form-valoracion"> <!-- Formulario para crear/editar valoración -->
                <input type="hidden" name="id_juego" value="<?php echo $juego['id']; ?>"> <!-- ID del juego -->
                <div class="valoraciones-estrellas" aria-label="Selecciona tu valoración"> <!-- Contenedor de estrellas para la valoración -->
                    <?php for($i=1; $i<=5; $i++) { ?> <!-- Recorro las 5 estrellas -->
                        <img src="../recursos/imagenes/<?php echo ($i <= $valoracion_actual ? 'valorado.png' : 'sin_valorar.png'); ?>" alt="Estrella <?php echo $i; ?>" class="estrella-valoracion" data-valor="<?php echo $i; ?>"> <!-- Imagen de la estrella, llena o vacía según la valoración actual -->
                    <?php } ?>
                </div>
                <input type="hidden" name="valoracion" id="input-valoracion" value="<?php echo obtenerValoracionUsuario($conexion, $_SESSION['id_usuario'], $juego['id']); ?>"> <!-- Input oculto para almacenar el valor numérico de la valoración -->
                <?php if(isset($_SESSION['id_usuario']) && obtenerValoracionUsuario($conexion, $_SESSION['id_usuario'], $juego['id']) != null) { ?> <!-- Si la valoración ya existe -->
                    <div class="botones-valoracion"> <!-- Contenedor para los botones -->
                        <input type="hidden" name="accion" value="editar_valoracion"> <!-- Acción a realizar -->
                        <button type="submit" class="boton-modificar-valoracion"> <!-- Botón para modificar -->
                            <img src="../recursos/imagenes/editar_valoracion.png" alt="Icono de Guardar" id="icono-modificar-valoracion"> <!-- Icono de guardar -->
                            <span>Modificar valoración</span> <!-- Texto del botón -->
                        </button>
                        <button type="button" class="boton-eliminar-valoracion" onclick="eliminarValoracion()"> <!-- Botón para eliminar -->
                            <img src="../recursos/imagenes/eliminar_valoracion.png" alt="Icono de Eliminar" id="icono-eliminar-valoracion"> <!-- Icono de eliminar -->
                            <span>Eliminar valoración</span> <!-- Texto del botón -->
                        </button>
                    </div>
                <?php } else { ?> <!-- Si la valoración no existe -->
                    <input type="hidden" name="accion" value="crear_valoracion"> <!-- Acción a realizar -->
                    <button type="submit" class="boton-modificar-valoracion"> <!-- Botón para publicar -->
                        <img src="../recursos/imagenes/guardar_valoracion.png" alt="Icono de Guardar" id="icono-publicar-valoracion"> <!-- Icono de guardar -->
                        <span>Publicar valoración</span> <!-- Texto del botón -->
                    </button>
                <?php } ?>
            </form>
        </div>
    <?php } ?>

    <div class="valoraciones-globales"> <!-- Media global -->
        <h2>Valoración media</h2> <!-- Título de la sección -->
        <?php if($total_valoraciones === 0) { ?> <!-- Si no hay valoraciones -->
            <p>No hay valoraciones todavía.</p> <!-- Mensaje indicando que no hay valoraciones -->
        <?php } else { ?> <!-- Si hay valoraciones -->
            <div class="valoraciones-media"> <!-- Contenedor para la media de valoraciones -->
                <div class="valoraciones-media-estrellas"> <!-- Contenedor interno -->
                    <?php
                        /* Pintar estrellas según media (redondeo hacia abajo para llenas) */
                        $media_redondeada = floor($media_valoraciones); /* Parte entera de la media (ej: 4.7 → 4) */
                        
                        /* Recorro las 5 estrellas para pintarlas según la media */
                        for($i=1; $i<=5; $i++) {
                            /* Compruebo si esta estrella debe mostrarse llena (amarilla) o vacía */
                            if($i <= $media_redondeada) { /* Esta estrella SÍ debe estar llena porque su posición es menor o igual a la media */
                                $imagen_estrella = 'valorado.png'; /* Estrella amarilla (llena) */
                            } else { /* Esta estrella NO debe estar llena porque su posición es mayor que la media */
                                $imagen_estrella = 'sin_valorar.png'; /* Estrella vacía (sin amarillo) */
                            }
                            
                            /* Muestro la imagen de la estrella con la ruta correcta */
                            echo '<img src="../recursos/imagenes/' . $imagen_estrella . '" alt="Estrella media ' . $i . '" class="estrella-media">';
                        }
                    ?>
                    <!-- Texto que muestra la media y el total de valoraciones -->
                    <span class="valoraciones-media-texto">Media: <?php echo number_format($media_valoraciones, 2, ',', '.'); ?> (<?php echo $total_valoraciones; ?> valoraciones)</span>
                </div>
                <div> <!-- Contenedor interno -->
                    <?php if ($es_admin && isset($_SESSION['modo_admin']) && $_SESSION['modo_admin'] === true) { ?> <!-- Si es administrador y está en modo admin -->
                        <button type="button" class="boton-eliminar-valoracion" onclick="eliminarTodasValoraciones(<?php echo $juego['id']; ?>)"> <!-- Botón para eliminar todas las valoraciones -->
                            <img src="../recursos/imagenes/eliminar_valoracion.png" alt="Icono eliminar valoraciones" id="icono-eliminar-valoracion"> <!-- Icono de eliminar valoraciones -->
                            <span>Eliminar todas las valoraciones</span> <!-- Texto del botón -->
                        </button>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>

    <div class="comentarios-globales"> <!-- Listado global de comentarios -->
        <h2>Comentarios</h2> <!-- Título de la sección -->
        <?php if (empty($comentarios_juego)) { ?> <!-- Si no hay comentarios -->
            <p>No hay comentarios todavía.</p> <!-- Mensaje indicando que no hay comentarios -->
        <?php } else { ?> <!-- Si hay comentarios -->
            <?php foreach ($comentarios_juego as $comentario) { ?> <!-- Recorro todos los comentarios obtenidos -->
                <div class="comentario-item"> <!-- Contenedor para cada comentario -->
                    <p><strong><?php echo htmlspecialchars($comentario['acronimo']); ?></strong> - <?php echo htmlspecialchars(($comentario['actualizado_en'] ?? $comentario['creado_en'])); ?></p> <!-- Muestro el acrónimo del usuario y la fecha de creación o actualización -->
                    <p><?php echo htmlspecialchars($comentario['comentario']); ?></p> <!-- Muestro el texto del comentario -->
                    <?php if ($es_admin && isset($_SESSION['modo_admin']) && $_SESSION['modo_admin'] === true) { ?> <!-- Si es administrador y está en modo admin -->
                        <button type="button" class="boton-eliminar-comentario" onclick="eliminarComentarioAdmin(<?php echo (int)$comentario['id']; ?>)"> <!-- Botón para eliminar -->
                            <img src="../recursos/imagenes/eliminar_comentario.png" alt="Icono de Eliminar" id="icono-eliminar-comentario"> <!-- Icono de eliminar -->
                            <span>Eliminar comentario</span> <!-- Texto del botón -->
                        </button>
                    <?php } ?>
                </div>
                <hr> <!-- Línea separadora entre comentarios -->
            <?php } ?>
            <?php if ($total_comentarios > $limite_mostrar) { /* Si hay más comentarios de los que se muestran por defecto */ ?>
                <?php if ($mostrar_mas) { /* Si se están mostrando todos */ ?>
                    <p><a href="?id=<?php echo $juego['id']; ?>">Ver menos comentarios</a></p> <!-- Enlace para ver menos comentarios -->
                <?php } else { /* Si solo se muestran los primeros */ ?>
                    <p><a href="?id=<?php echo $juego['id']; ?>&ver_comentarios=todos">Ver más comentarios (<?php echo $total_comentarios; ?>)</a></p> <!-- Enlace para ver todos los comentarios -->
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </div>

</main>

<br> <!-- Espacio antes del pie de página -->

<script>
    // Función para mostrar mensaje de opción no disponible
    function opcionNoDisponible() {        
        // Mostrar modal con mensaje de opción no disponible
        const mensaje = `<h1>¡Opción no disponible!</h1>
                            <p>Esta función no está habilitada en esta versión de CLC Games.</p>
                            <p>Podría incorporarse en futuras ampliaciones del proyecto.</p>
                        `; /* Mensaje HTML */
        
        modal('modal1', mensaje, false); /* Llamo a la función modal para mostrar el mensaje */
    }
</script>

<script src="../recursos/js/carrito.js" defer></script> <!-- Script para funcionalidad de carrito, pedidos, reservas y devoluciones -->
<script src="../recursos/js/favoritos.js" defer></script> <!-- Script para funcionalidad de favoritos -->
<script src="../recursos/js/comentarios_valoraciones.js" defer></script> <!-- Script para funcionalidades de comentarios y valoraciones -->

<!-- Pie de página -->
<?php include __DIR__ . '/../vistas/comunes/pie.php'; ?> <!-- Incluyo el pie de página común -->
