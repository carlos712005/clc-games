<?php

    require_once('../config/conexion.php'); /* Incluyo la conexión a la base de datos */
    session_start(); /* Inicio la sesión para acceder a filtros y datos del usuario */


    // Verificar que el usuario sea administrador
    if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] !== 1) {
        header('Content-Type: application/json'); /* Indico que la respuesta es JSON */
        echo json_encode(['error' => 'Acceso denegado']); /* Retorno error de acceso */
        exit; /* Termino la ejecución */
    }

    try { /* Inicio bloque try para capturar errores */
        // Obtener los filtros de la sesión
        $filtros = isset($_SESSION['filtros_pedidos']) ? $_SESSION['filtros_pedidos'] : [];
        
        // Verificar si hay una búsqueda activa
        if(isset($_SESSION['datos_busqueda']) && isset($_SESSION['datos_busqueda']['historiales_encontrados'])) {
            $ids_historial = $_SESSION['datos_busqueda']['historiales_encontrados']; /* Obtengo los IDs de los historiales encontrados */
            
            // Preparar una consulta con los IDs de los historiales encontrados
            $cantidad = count($ids_historial); /* Cantidad de historiales encontrados */
            $signos = array_fill(0, $cantidad, '?'); /* Creo un array de forma ['?', '?', '?', ...] */
            $cadena = implode(',', $signos); /* Uno con comas: '?,?,?' */
            $consulta = $conexion->prepare("
                SELECT 
                    h.id AS id_historial,
                    h.id_usuario,
                    h.tipo,
                    h.estado,
                    h.total,
                    h.metodo_pago,
                    h.comentario,
                    h.creado_en,
                    h.actualizado_en,
                    hc.id AS id_detalle,
                    hc.id_juego,
                    hc.precio,
                    hc.estado AS estado_detalle,
                    hc.comentario AS comentario_detalle,
                    u.nombre AS usuario_nombre,
                    u.apellidos AS usuario_apellidos,
                    u.acronimo AS usuario_acronimo
                FROM historial h
                LEFT JOIN historial_compras hc ON hc.id_historial = h.id
                LEFT JOIN usuarios u ON u.id = h.id_usuario
                WHERE h.id IN ($cadena)
                ORDER BY h.actualizado_en DESC, h.creado_en DESC, hc.id ASC
            "); /* Preparo consulta para obtener el historial de compras general, ordenadas por fecha de actualización y de creación, incluyendo datos de usuario */
            // Vincular los IDs de los historiales
            foreach($ids_historial as $indice => $id) { /* Recorro los IDs de los historiales */
                $consulta->bindValue($indice + 1, $id, PDO::PARAM_INT); /* Vinculo cada ID de historial (empezando en posición 1) */
            }
        } else { /* No hay búsqueda activa */
            // Construir la consulta base
            $consulta = $conexion->prepare("
                SELECT 
                    h.id AS id_historial,
                    h.id_usuario,
                    h.tipo,
                    h.estado,
                    h.total,
                    h.metodo_pago,
                    h.comentario,
                    h.creado_en,
                    h.actualizado_en,
                    hc.id AS id_detalle,
                    hc.id_juego,
                    hc.precio,
                    hc.estado AS estado_detalle,
                    u.acronimo AS usuario_acronimo,
                    u.nombre AS usuario_nombre,
                    u.apellidos AS usuario_apellidos
                FROM historial h
                INNER JOIN historial_compras hc ON hc.id_historial = h.id
                LEFT JOIN usuarios u ON h.id_usuario = u.id
                ORDER BY h.actualizado_en DESC, h.creado_en DESC, hc.id ASC
            "); /* Preparo consulta para obtener el historial de compras general, incluyendo datos de usuario */
        }
        $consulta->execute(); /* Ejecuto la consulta */

        $historial = $consulta->fetchAll(PDO::FETCH_ASSOC); // Guardo un array de filas (cada fila = un detalle)

        // El & en el foreach me permite modificar el array original
        foreach ($historial as &$registro) { // referencia para que persistan los cambios
            $id_juego_comprado = $registro['id_juego']; // obtengo el id del juego comprado

            if (!$id_juego_comprado) { /* Si no hay id de juego comprado */
                $registro['juego'] = null; /* Asigno null al juego */
            } else { /* Si hay id de juego comprado */
                // Obtengo el juego
                $consulta_juego = $conexion->prepare("
                    SELECT id, nombre, tipo, precio, resumen
                    FROM juegos
                    WHERE id = :id_juego
                "); /* Preparo la consulta para obtener los datos del juego (sin portada) */
                $consulta_juego->bindParam(':id_juego', $id_juego_comprado, PDO::PARAM_INT); /* Vinculo el ID del juego */
                $consulta_juego->execute(); /* Ejecuto la consulta */
                $juego = $consulta_juego->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del juego */

                if ($juego) { /* Si se encontró el juego */
                    $consulta_tipo = $conexion->prepare("
                        SELECT nombre
                        FROM filtros
                        WHERE clave = :clave
                    "); /* Preparo la consulta para obtener el nombre del tipo */
                    $consulta_tipo->bindParam(':clave', $juego['tipo'], PDO::PARAM_STR); /* Vinculo la clave del tipo */
                    $consulta_tipo->execute(); /* Ejecuto la consulta */
                    $tipo_resultado = $consulta_tipo->fetch(PDO::FETCH_ASSOC); /* Obtengo el nombre del tipo */
                    $nombre_tipo = $tipo_resultado ? $tipo_resultado['nombre'] : $juego['tipo']; /* Si no se encuentra, uso la clave original */

                    // Añadir el objeto juego a la fila
                    $registro['juego'] = [
                        'id'      => (int) $juego['id'], /* Aseguro que el ID sea un entero */
                        'nombre'  => $juego['nombre'], /* Nombre del juego */
                        'tipo'    => $nombre_tipo, /* Tipo del juego */
                        'precio'  => $juego['precio'], /* Precio del juego */
                        'resumen' => $juego['resumen'], /* Resumen del juego */
                    ];
                } else { /* Si no se encontró el juego */
                    $registro['juego'] = null; /* Asigno null al juego */
                }
            }
        }
        unset($registro); /* Quito la referencia, para evitar problemas de memoria */
        
        // Agrupar por id_historial para evitar tarjetas duplicadas
        $historialAgrupado = []; /* Array para almacenar el historial agrupado */
        foreach ($historial as $fila) { /* Recorro cada fila del historial */
            $id = $fila['id_historial']; /* Obtengo el ID del historial */
            if (!isset($historialAgrupado[$id])) { /* Si no existe este id_historial en el array */
                $historialAgrupado[$id] = [
                    'id_historial' => $fila['id_historial'], /* ID del historial */
                    'tipo' => $fila['tipo'], /* Tipo de historial */
                    'estado' => $fila['estado'], /* Estado del historial */
                    'total' => $fila['total'], /* Total del historial */
                    'metodo_pago' => $fila['metodo_pago'], /* Método de pago del historial */
                    'comentario' => $fila['comentario'], /* Comentario del historial */
                    'creado_en' => $fila['creado_en'], /* Fecha de creación del historial */
                    'actualizado_en' => $fila['actualizado_en'], /* Fecha de última actualización del historial */
                    'id_usuario' => $fila['id_usuario'], /* ID del usuario */
                    'usuario_nombre' => $fila['usuario_nombre'] ?? null, /* Nombre del usuario */
                    'usuario_apellidos' => $fila['usuario_apellidos'] ?? null, /* Apellidos del usuario */
                    'usuario_acronimo' => $fila['usuario_acronimo'] ?? null, /* Acrónimo del usuario */
                    'detalles' => [] /* Array de detalles/juegos del pedido */
                ]; /* Creo una nueva entrada en el array */
            }

            // Añadir el detalle (si existe) al pedido agrupado
            if (!empty($fila['id_detalle'])) { /* Si existe un detalle */
                $detalle = [
                    'id_detalle' => $fila['id_detalle'], /* ID del detalle */
                    'id_juego' => $fila['id_juego'] ?? null, /* ID del juego */
                    'precio' => $fila['precio'] ?? null, /* Precio del detalle */
                    'estado_detalle' => $fila['estado_detalle'] ?? null, /* Estado del detalle */
                    'comentario_detalle' => $fila['comentario_detalle'] ?? null, /* Comentario del detalle */
                    'juego' => null /* Juego asociado al detalle */
                ]; /* Creo el array del detalle */

                if (!empty($fila['juego']) && is_array($fila['juego'])) { /* Si existe el juego */
                    $detalle['juego'] = [
                        'id' => $fila['juego']['id'] ?? null, /* ID del juego */
                        'nombre' => $fila['juego']['nombre'] ?? null, /* Nombre del juego */
                        'tipo' => $fila['juego']['tipo'] ?? null, /* Tipo del juego */
                        'precio' => $fila['juego']['precio'] ?? null, /* Precio del juego */
                        'resumen' => $fila['juego']['resumen'] ?? null /* Resumen del juego */
                    ]; /* Asigno el juego al detalle */
                }

                $historialAgrupado[$id]['detalles'][] = $detalle; /* Añado el detalle al array de detalles del pedido */
            }
        }
        
        // Array para almacenar descripción de filtros activos y búsqueda
        $filtrosTexto = []; /* Array para almacenar texto descriptivo de filtros */
        $busqueda = isset($_SESSION['texto_busqueda']) ? trim($_SESSION['texto_busqueda']) : ''; /* Obtengo el texto de búsqueda de la sesión */
        
        // Construir texto descriptivo de filtros (sin modificar consulta ya ejecutada)
        if (!empty($filtros)) { /* Si hay filtros aplicados */
            if (isset($filtros['tipo']) && $filtros['tipo'] !== 'null') { /* Si hay un tipo específico seleccionado */
                $filtrosTexto[] = "Tipo: " . $filtros['tipo']; /* Añado el tipo al texto de filtros */
            }
            if (isset($filtros['estado']) && $filtros['estado'] !== 'null') { /* Si hay un estado específico seleccionado */
                $filtrosTexto[] = "Estado: " . $filtros['estado']; /* Añado el estado al texto de filtros */
            }
            if (isset($filtros['estado_detalle']) && $filtros['estado_detalle'] !== 'null') { /* Si hay un estado detalle específico seleccionado */
                $filtrosTexto[] = "Estado detalle: " . $filtros['estado_detalle']; /* Añado el estado detalle al texto de filtros */
            }
            if (isset($filtros['acronimo']) && $filtros['acronimo'] !== 'null') { /* Si hay un acrónimo de usuario específico seleccionado */
                $filtrosTexto[] = "Acrónimo usuario: " . $filtros['acronimo']; /* Añado el acrónimo al texto de filtros */
            }
            if (isset($filtros['nombre']) && $filtros['nombre'] !== 'null') { /* Si hay un nombre de usuario específico seleccionado */
                $filtrosTexto[] = "Nombre usuario: " . $filtros['nombre']; /* Añado el nombre al texto de filtros */
            }
            if (isset($filtros['apellidos']) && $filtros['apellidos'] !== 'null') { /* Si hay unos apellidos de usuario específicos seleccionados */
                $filtrosTexto[] = "Apellidos usuario: " . $filtros['apellidos']; /* Añado los apellidos al texto de filtros */
            }
            if (isset($filtros['metodo_pago']) && $filtros['metodo_pago'] !== 'null') { /* Si hay un método de pago específico seleccionado */
                $filtrosTexto[] = "Método de pago: " . $filtros['metodo_pago']; /* Añado el método de pago al texto de filtros */
            }
            if (isset($filtros['total_min']) && $filtros['total_min'] !== null) { /* Si hay un total mínimo específico seleccionado */
                $filtrosTexto[] = "Total mínimo: " . number_format($filtros['total_min'], 2, ',', '.') . " €"; /* Añado el total mínimo al texto de filtros */
            }
            if (isset($filtros['total_max']) && $filtros['total_max'] !== null) { /* Si hay un total máximo específico seleccionado */
                $filtrosTexto[] = "Total máximo: " . number_format($filtros['total_max'], 2, ',', '.') . " €"; /* Añado el total máximo al texto de filtros */
            }
            if (isset($filtros['creado_desde']) && $filtros['creado_desde'] !== null) { /* Si hay una fecha de creación desde específica seleccionada */
                $filtrosTexto[] = "Creado desde: " . date('d/m/Y', strtotime($filtros['creado_desde'])); /* Añado la fecha de creación desde al texto de filtros */
            }
            if (isset($filtros['creado_hasta']) && $filtros['creado_hasta'] !== null) { /* Si hay una fecha de creación hasta específica seleccionada */
                $filtrosTexto[] = "Creado hasta: " . date('d/m/Y', strtotime($filtros['creado_hasta'])); /* Añado la fecha de creación hasta al texto de filtros */
            }
            if (isset($filtros['actualizado_desde']) && $filtros['actualizado_desde'] !== null) { /* Si hay una fecha de actualización desde específica seleccionada */
                $filtrosTexto[] = "Actualizado desde: " . date('d/m/Y', strtotime($filtros['actualizado_desde'])); /* Añado la fecha de actualización desde al texto de filtros */
            }
            if (isset($filtros['actualizado_hasta']) && $filtros['actualizado_hasta'] !== null) { /* Si hay una fecha de actualización hasta específica seleccionada */
                $filtrosTexto[] = "Actualizado hasta: " . date('d/m/Y', strtotime($filtros['actualizado_hasta'])); /* Añado la fecha de actualización hasta al texto de filtros */
            }
        }
        
        // Aplicar filtros sobre historialAgrupado
        $pedidosFiltrados = []; /* Array para almacenar pedidos que pasan los filtros */
        
        foreach ($historialAgrupado as $h) { /* Recorro cada pedido del historial agrupado */
            $mostrar_pedido = false; /* Bandera que controla si incluir este pedido */
            
            // Aplicar filtros si existen
            if(isset($_SESSION['filtros_pedidos'])) { /* Verifico si se han aplicado filtros */
                // Si hay filtros elegidos, verificar coincidencias
                $filtros_elegidos = $_SESSION['filtros_pedidos']; /* Obtengo los filtros que eligió el usuario */
                
                // Verifico si hay algún filtro específico seleccionado (no null)
                $hay_filtros_activos = ($filtros_elegidos['tipo'] !== 'null') || /* Verifico si eligió un tipo específico */
                                        ($filtros_elegidos['estado'] !== 'null') || /* Verifico si eligió un estado específico */
                                        ($filtros_elegidos['estado_detalle'] !== 'null') || /* Verifico si eligió un estado de detalle específico */
                                        ($filtros_elegidos['acronimo'] !== 'null') || /* Verifico si eligió un acrónimo específico */
                                        ($filtros_elegidos['nombre'] !== 'null') || /* Verifico si eligió un nombre específico */
                                        ($filtros_elegidos['apellidos'] !== 'null') || /* Verifico si eligió unos apellidos específicos */
                                        ($filtros_elegidos['metodo_pago'] !== 'null') || /* Verifico si eligió un método de pago específico */
                                        ($filtros_elegidos['total_min'] !== null) || /* Verifico si puso un total mínimo */
                                        ($filtros_elegidos['total_max'] !== null) || /* Verifico si puso un total máximo */
                                        ($filtros_elegidos['creado_desde'] !== null) || /* Verifico si puso fecha de creación desde */
                                        ($filtros_elegidos['creado_hasta'] !== null) || /* Verifico si puso fecha de creación hasta */
                                        ($filtros_elegidos['actualizado_desde'] !== null) || /* Verifico si puso fecha de actualización desde */
                                        ($filtros_elegidos['actualizado_hasta'] !== null); /* Verifico si puso fecha de actualización hasta */
                
                if ($hay_filtros_activos) { /* Si el usuario aplicó algún filtro específico */
                    // Verificar si el pedido cumple CON TODOS los filtros elegidos
                    $mostrar_pedido = true; /* Asumo que el pedido cumple hasta que demuestre lo contrario */
                    
                    // Verificar cada filtro individualmente - TODOS deben cumplirse
                    if ($filtros_elegidos['tipo'] !== 'null' && $filtros_elegidos['tipo'] != $h['tipo']) { /* Si eligió un tipo y no coincide */
                        $mostrar_pedido = false; /* Marco que no debe incluirse */
                    }
                    if ($filtros_elegidos['estado'] !== 'null' && $filtros_elegidos['estado'] != $h['estado']) { /* Si eligió un estado y no coincide */
                        $mostrar_pedido = false; /* Marco que no debe incluirse */
                    }
                    // Para estado_detalle: verificar si algún detalle coincide
                    if ($filtros_elegidos['estado_detalle'] !== 'null') { /* Si eligió un estado de detalle específico */
                        $coincide_detalle = false; /* Bandera para verificar si algún detalle coincide */
                        foreach ($h['detalles'] as $detalle) { /* Recorro cada detalle del pedido */
                            if (isset($detalle['estado_detalle']) && $detalle['estado_detalle'] == $filtros_elegidos['estado_detalle']) { /* Si el estado de detalle coincide */
                                $coincide_detalle = true; /* Marco que al menos un detalle coincide */
                                break; /* Salgo del ciclo ya que encontré una coincidencia */
                            }
                        }
                        if (!$coincide_detalle) { /* Si ningún detalle coincide */
                            $mostrar_pedido = false; /* Marco que no debe incluirse */
                        }
                    }
                    if ($filtros_elegidos['acronimo'] !== 'null' && $filtros_elegidos['acronimo'] != $h['usuario_acronimo']) { /* Si eligió un acrónimo y no coincide */
                        $mostrar_pedido = false; /* Marco que no debe incluirse */
                    }
                    if ($filtros_elegidos['nombre'] !== 'null' && $filtros_elegidos['nombre'] != $h['usuario_nombre']) { /* Si eligió un nombre y no coincide */
                        $mostrar_pedido = false; /* Marco que no debe incluirse */
                    }
                    if ($filtros_elegidos['apellidos'] !== 'null' && $filtros_elegidos['apellidos'] != $h['usuario_apellidos']) { /* Si eligió apellidos y no coincide */
                        $mostrar_pedido = false; /* Marco que no debe incluirse */
                    }
                    if ($filtros_elegidos['metodo_pago'] !== 'null' && $filtros_elegidos['metodo_pago'] != $h['metodo_pago']) { /* Si eligió un método de pago y no coincide */
                        $mostrar_pedido = false; /* Marco que no debe incluirse */
                    }
                    // Filtros de total
                    if ($filtros_elegidos['total_min'] !== null && $h['total'] < $filtros_elegidos['total_min']) { /* Si el total es menor al mínimo del filtro */
                        $mostrar_pedido = false; /* Marco que no debe incluirse */
                    }
                    if ($filtros_elegidos['total_max'] !== null && $h['total'] > $filtros_elegidos['total_max']) { /* Si el total es mayor al máximo del filtro */
                        $mostrar_pedido = false; /* Marco que no debe incluirse */
                    }
                    // Filtros de fecha de creación
                    if ($filtros_elegidos['creado_desde'] !== null && $h['creado_en'] < $filtros_elegidos['creado_desde']) { /* Si la fecha de creación es anterior al filtro */
                        $mostrar_pedido = false; /* Marco que no debe incluirse */
                    }
                    if ($filtros_elegidos['creado_hasta'] !== null && $h['creado_en'] > $filtros_elegidos['creado_hasta'] . ' 23:59:59') { /* Si la fecha de creación es posterior al filtro */
                        $mostrar_pedido = false; /* Marco que no debe incluirse */
                    }
                    // Filtros de fecha de actualización
                    if ($filtros_elegidos['actualizado_desde'] !== null && $h['actualizado_en'] < $filtros_elegidos['actualizado_desde']) { /* Si la fecha de actualización es anterior al filtro */
                        $mostrar_pedido = false; /* Marco que no debe incluirse */
                    }
                    if ($filtros_elegidos['actualizado_hasta'] !== null && $h['actualizado_en'] > $filtros_elegidos['actualizado_hasta'] . ' 23:59:59') { /* Si la fecha de actualización es posterior al filtro */
                        $mostrar_pedido = false; /* Marco que no debe incluirse */
                    }
                } else { /* Si todos los filtros están en "null" */
                    $mostrar_pedido = true; /* Incluyo todos los pedidos porque no hay filtros específicos */
                }
            } else { /* Si no hay filtros elegidos (no hay filtros en la sesión) */
                $mostrar_pedido = true; /* Incluyo todos los pedidos porque no se han aplicado filtros */
            }
            
            // Si el pedido cumple los filtros, añadirlo al array
            if ($mostrar_pedido) {
                $pedidosFiltrados[] = $h; /* Añado el pedido al array de filtrados */
            }
        }
        
        // Preparar los datos para enviar al JavaScript
        $pedidosFormateados = []; /* Array para almacenar los pedidos formateados */
        foreach ($pedidosFiltrados as $pedido) { /* Recorro cada pedido filtrado */
            // Formatear ID del pedido
            $id_formateado = date('Y', strtotime($pedido['creado_en'])) . '-' . str_pad($pedido['id_historial'], 5, '0', STR_PAD_LEFT); /* Formato: AAAA-00001 */
            
            // Cliente
            $nombreCompleto = trim(($pedido['usuario_nombre'] ?? '') . ' ' . ($pedido['usuario_apellidos'] ?? '')); /* Nombre completo del usuario */
            $acronimoUsuario = $pedido['usuario_acronimo'] ?? ''; /* Acrónimo del usuario */
            $cliente = $nombreCompleto !== '' ? $nombreCompleto : $acronimoUsuario; /* Uso nombre completo si existe, sino acrónimo */
            
            // Método de pago
            $metodo_pago = $pedido['metodo_pago'] ? strtoupper($pedido['metodo_pago']) : 'N/A'; /* Método de pago en mayúsculas o N/A si no existe */
            
            // Total
            $total = $pedido['total'] == '0.00' ? 'Gratis' : number_format($pedido['total'], 2, ',', '.') . ' €'; /* Formateo del total o 'Gratis' si es 0 */
            
            // Crear item del pedido
            $item = [
                'id' => $id_formateado, /* ID formateado del pedido */
                'cliente' => $cliente, /* Cliente del pedido */
                'tipo' => $pedido['tipo'], /* Tipo de pedido */
                'estado' => $pedido['estado'], /* Estado del pedido */
                'metodo_pago' => $metodo_pago, /* Método de pago */
                'total' => $total, /* Total del pedido */
                'creado_en' => date('d/m/Y H:i', strtotime($pedido['creado_en'])), /* Fecha de creación formateada */
                'actualizado_en' => date('d/m/Y H:i', strtotime($pedido['actualizado_en'])), /* Fecha de actualización formateada */
                'detalles' => [] /* Array para los juegos del pedido */
            ];
            
            // Recorrer detalles de juegos
            foreach ($pedido['detalles'] as $d) {
                $item['detalles'][] = [
                    'id_juego' => $d['id_juego'] ?? null, /* ID del juego */
                    'nombre' => $d['juego']['nombre'] ?? 'Desconocido', /* Nombre del juego */
                    'tipo' => $d['juego']['tipo'] ?? 'N/A', /* Tipo de juego */
                    'estado_detalle' => $d['estado_detalle'] ?? 'N/A', /* Estado del detalle */
                    'precio' => isset($d['precio']) ? number_format((float)$d['precio'], 2, ',', '.') . ' €' : 'N/A' /* Precio formateado */
                ];
            }
            
            $pedidosFormateados[] = $item; /* Añado el pedido formateado al array final */
        }
        
        // Devolver JSON con los datos
        header('Content-Type: application/json'); /* Indico que la respuesta es JSON */
        echo json_encode([
            'pedidos' => $pedidosFormateados, /* Array de pedidos formateados */
            'busqueda' => $busqueda, /* Término de búsqueda utilizado */
            'filtros' => $filtrosTexto, /* Filtros aplicados en texto */
            'total' => count($pedidosFormateados), /* Total de pedidos filtrados */
            'fecha_generacion' => date('d/m/Y H:i') /* Fecha y hora de generación */
        ]);
        
    } catch (PDOException $e) { /* Si hay error al obtener el historial */
        header('Content-Type: application/json'); /* Indico que la respuesta es JSON */
        echo json_encode(['error' => 'Error al obtener datos: ' . $e->getMessage()]); /* En caso de error, muestro mensaje */
    }

?>
