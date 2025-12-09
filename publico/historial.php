    <!-- Encabezado -->
    <?php include __DIR__ . '/../vistas/comunes/encabezado.php'; ?> <!-- Incluyo el encabezado con menú y estilos -->

    <?php
    // Verificar sesión y redirigir con JavaScript si es necesario
    if(!isset($_SESSION['id_usuario'])) {
        echo '<script>window.location.href = "index.php";</script>'; /* Redirijo con JavaScript si no está logueado */
        exit; /* Termino la ejecución del script */
    }

    $id_usuario = null; /* Inicializo la variable para el ID del usuario */
    $datos_usuario = null; /* Inicializo la variable para los datos del usuario */

    // Verificar si se ha proporcionado un ID de usuario válido
    if (isset($_GET['id']) && is_numeric($_GET['id'])) { /* Verifico que llegue un ID válido por GET */
        $id_usuario = (int)$_GET['id']; /* Convierto el ID a entero para seguridad */
        
        // Verificar que el usuario actual sea admin o esté viendo su propio historial
        if($_SESSION['id_rol'] != 1 && $_SESSION['id_usuario'] != $id_usuario) {
            echo '<script>modal("modal1", "<h1>No tienes permisos para ver este historial.</h1>", false); window.location.href = "index.php";</script>'; /* Muestro el mensaje de error usando el modal y redirijo con JavaScript */
            exit; /* Termino la ejecución del script */
        }

        // Obtener datos del usuario para mostrar su nombre
        try { /* Inicio bloque try para capturar errores */
            $consulta_usuario = $conexion->prepare("SELECT nombre, apellidos FROM usuarios WHERE id = :id_usuario"); /* Preparo consulta para obtener datos del usuario */
            $consulta_usuario->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta_usuario->execute(); /* Ejecuto la consulta */
            $datos_usuario = $consulta_usuario->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del usuario */
            
            // Verificar que el usuario exista
            if(!$datos_usuario) {
                echo '<script>modal("modal1", "<h1>Usuario no encontrado.</h1>", false); window.location.href = "index.php";</script>'; /* Muestro el mensaje de error usando el modal y redirijo con JavaScript */
                exit; /* Termino la ejecución del script */
            }
        } catch (PDOException $e) { /* Si hay error al obtener los datos del usuario */
            echo '<script>modal("modal1", "<h1>Error al obtener datos del usuario.</h1>", false); window.location.href = "index.php";</script>'; /* Muestro el mensaje de error usando el modal y redirijo con JavaScript */
            exit; /* Termino la ejecución del script */
        }
    } else { /* Si no hay ID en la URL, usar el propio usuario logueado */
        $id_usuario = $_SESSION['id_usuario']; /* Asigno el ID del usuario logueado */
    }

    try { /* Inicio bloque try para capturar errores */
        // Verificar si hay una búsqueda activa
        if(isset($_SESSION['datos_busqueda']) && isset($_SESSION['datos_busqueda']['historiales_encontrados'])) {
            $ids_historial = $_SESSION['datos_busqueda']['historiales_encontrados']; /* Obtengo los IDs de acciones del historial encontradas */
            
            // Preparar una consulta con los IDs de acciones del historial encontradas
            $cantidad = count($ids_historial); /* Cantidad de acciones del historial encontradas */
            $signos = array_fill(0, $cantidad, '?'); /* Creo un array de forma ['?', '?', '?', ...] */
            $cadena = implode(',', $signos); /* Uno con comas: '?,?,?' */
            $consulta = $conexion->prepare("
                SELECT h.id AS id_historial, h.id_usuario, h.tipo, h.estado, h.total, h.metodo_pago, h.comentario, h.creado_en, h.actualizado_en,
                        hc.id AS id_detalle, hc.id_juego, hc.precio, hc.estado AS estado_detalle, hc.comentario AS comentario_detalle
                FROM historial h
                LEFT JOIN historial_compras hc ON hc.id_historial = h.id
                WHERE h.id IN ($cadena) AND h.id_usuario = ?
                ORDER BY h.actualizado_en DESC, h.creado_en DESC, hc.id ASC
            "); /* Preparo consulta para obtener el historial de compras del usuario, ordenadas por fecha de actualización y creación */
            // Vincular los IDs de historial primero, luego el id_usuario al final
            foreach($ids_historial as $indice => $id) { /* Recorro los IDs de acciones del historial */
                $consulta->bindValue($indice + 1, $id, PDO::PARAM_INT); /* Vinculo cada ID de historial (empezando en posición 1) */
            }
            $consulta->bindValue($cantidad + 1, $id_usuario, PDO::PARAM_INT); /* Vinculo el ID del usuario al final */
        } else { /* No hay búsqueda activa */
            // Obtener todo el historial del usuario
            $consulta = $conexion->prepare("
                SELECT h.id AS id_historial, h.id_usuario, h.tipo, h.estado, h.total, h.metodo_pago, h.comentario, h.creado_en, h.actualizado_en,
                        hc.id AS id_detalle, hc.id_juego, hc.precio, hc.estado AS estado_detalle, hc.comentario AS comentario_detalle
                FROM historial h
                LEFT JOIN historial_compras hc ON hc.id_historial = h.id
                WHERE h.id_usuario = :id_usuario
                ORDER BY h.actualizado_en DESC, h.creado_en DESC, hc.id ASC
                "); /* Preparo consulta para obtener el historial de compras del usuario */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID del usuario */
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
                    SELECT id, nombre, portada, tipo, precio, resumen
                    FROM juegos
                    WHERE id = :id_juego
                "); /* Preparo la consulta para obtener los datos del juego */
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
                        'portada' => $juego['portada'], /* Portada del juego */
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
                    'actualizado_en' => $fila['actualizado_en'] /* Fecha de última actualización del historial */
                ]; /* Creo una nueva entrada en el array */
            }
        }
        
    } catch (PDOException $e) { /* Si hay error al obtener el historial */
        echo "Error: " . $e->getMessage(); /* Muestro mensaje */
    }
    ?>

    <!-- Estilos específicos para historial -->
    <link rel="stylesheet" href="../recursos/css/estilos_historial.css" type="text/css">

    <main> <!-- Contenedor principal -->

        <?php if($id_usuario !== null && $id_usuario !== $_SESSION['id_usuario']) { ?> <!-- Si es otro usuario -->
            <h1>Historial de Compras: <?php echo htmlspecialchars($datos_usuario['nombre'] . ' ' . $datos_usuario['apellidos']); ?></h1> <!-- Título si se ve historial de otro usuario -->
        <?php } else { ?> <!-- Si es el propio usuario -->
            <h1 data-translate="historial_compras">Historial de Compras</h1> <!-- Título si se ve el propio historial -->
        <?php } ?>

        <hr> <!-- Línea horizontal decorativa -->

        <!-- Aquí se cargarán los juegos dinámicamente -->
        <div id="contenedor-historial" class="contenedor-historial"> <!-- Contenedor de las tarjetas de historial -->
            <?php if (!empty($historialAgrupado)) { /* Si hay registros en el historial */
                 foreach ($historialAgrupado as $h) { /* Recorro cada entrada del historial agrupado */ ?>
                    <div class="historial-tarjeta"> <!-- Tarjeta individual de historial -->
                        <?php 
                        /* Formateo el ID del historial de forma legible y profesional como: AAAA-00001
                         * - date('Y', strtotime($h['creado_en'])): Extrae el año de la fecha de creación (ej: 2025)
                         * - str_pad($h['id_historial'], 5, '0', STR_PAD_LEFT): Rellena el ID con ceros a la izquierda hasta tener 5 dígitos (ej: 3 → 00003)
                         * - Resultado final: "2025-00003" para el historial con id=3 creado en 2025
                         */
                        $id_historial_formateado = date('Y', strtotime($h['creado_en'])) . '-' . str_pad($h['id_historial'], 5, '0', STR_PAD_LEFT);
                        ?>
                        <div class="historial-id"> <!-- Contenedor del ID del historial -->
                            Nº <?php echo $id_historial_formateado; ?> <!-- Muestro el ID del historial formateado -->
                        </div>
                        <br> <!-- Salto de línea -->
                        <div class="historial-tipo"> <!-- Contenedor del tipo de historial -->
                            <strong>Tipo:</strong> <?php echo htmlspecialchars($h['tipo']); ?> <!-- Muestro el tipo de historial -->
                        </div>
                        <div class="historial-estado"> <!-- Contenedor del estado del historial -->
                            <strong>Estado:</strong> <?php echo htmlspecialchars($h['estado']); ?> <!-- Muestro el estado del historial -->
                        </div>
                        <div class="historial-fecha"> <!-- Contenedor de la fecha del historial -->
                            <strong>Fecha de creación:</strong> <?php echo date('d/m/Y H:i', strtotime($h['creado_en'])); ?> <!-- Muestro la fecha formateada del historial -->
                        </div>
                        <div class="historial-fecha"> <!-- Contenedor de la fecha de actualización del historial -->
                            <strong>Última actualización:</strong> <?php echo date('d/m/Y H:i', strtotime($h['actualizado_en'])); ?> <!-- Muestro la fecha formateada de última actualización del historial -->
                        </div>
                        <?php if($h['metodo_pago'] !== null) { ?> <!-- Si hay método de pago -->
                            <div class="historial-metodo-pago"> <!-- Contenedor del método de pago del historial -->
                                <strong>Método de Pago:</strong> <?php echo mb_strtoupper(htmlspecialchars($h['metodo_pago']), 'UTF-8'); ?> <!-- Muestro el método de pago del historial -->
                            </div>
                        <?php } ?>
                        <?php if($h['comentario'] !== null && trim($h['comentario']) !== '') { ?> <!-- Si hay comentario -->
                            <div class="historial-comentario"> <!-- Contenedor del comentario del historial -->
                                <strong>Comentario:</strong> <?php echo nl2br(htmlspecialchars($h['comentario'])); ?> <!-- Muestro el comentario del historial con saltos de línea -->
                            </div>
                        <?php } ?>
                        <div class="historial-total"> <!-- Contenedor del total del historial -->
                            <strong>Total:</strong> <?php echo ($h['total'] == '0.00') ? 'Gratis' : number_format($h['total'], 2, ',', '.') . ' €'; ?> <!-- Muestro el total del historial formateado -->
                        </div>
                        <hr> <!-- Línea horizontal decorativa -->
                        <div class="historial-acciones"> <!-- Contenedor de las acciones del historial -->
                            <a class="historial-boton-detalles" onclick="mostrarDetallesHistorial(<?php echo $h['id_historial']; ?>, '<?php echo $id_historial_formateado; ?>')"> <!-- Botón para ver detalles del historial -->
                                <img src="../recursos/imagenes/detalles.png" alt="Ver detalles" id="icono-detalles"> <!-- Icono de detalles -->
                                <span>Ver detalles</span> <!-- Texto del botón -->
                            </a>
                        </div>
                    </div>
                <?php }
            } else { /* Si no hay historial */ ?>
                <div class="sin-historial"> <!-- Contenedor para el mensaje -->
                    <h2 data-translate="no_hay_historial">No hay registros en el historial.</h2> <!-- Mensaje informativo -->
                </div>
            <?php } ?>
        </div> <!-- Fin del contenedor de juegos -->

    </main> <!-- Fin del contenedor principal -->

    <script>
        window.historial = <?php echo json_encode($historial); ?>; /* Paso el array de historial a JavaScript */
    </script>

    <!-- Script para historial -->
    <script src="../recursos/js/historial.js" defer></script> <!-- Script específico para historial -->

    <!-- Pie de página -->
    <?php include __DIR__ . '/../vistas/comunes/pie.php'; ?> <!-- Incluyo el pie de página común -->