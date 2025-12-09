<?php
    
    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */
    session_start(); /* Inicio la sesión para acceder a las variables de usuario */

    // Verificar que el usuario esté logueado y sea administrador
    if(!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
        echo '<div class="error"><h2>Acceso no autorizado</h2></div>'; /* Devuelvo error */
        exit; /* Termino la ejecución */
    }

    $_SESSION['modo_edicion'] = 'pedidos'; /* Indico que estamos en modo edición de pedidos */

    try { /* Inicio bloque try para capturar errores */
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
            // Verificar si existen datos de búsqueda para limpiar
            if(isset($_SESSION['texto_busqueda']) && isset($_SESSION['datos_busqueda'])) {
                unset($_SESSION['texto_busqueda']); /* Elimino el texto de búsqueda */
                unset($_SESSION['datos_busqueda']); /* Elimino los datos de búsqueda */
            }

            // Obtener todos los historiales de compras
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
                    'actualizado_en' => $fila['actualizado_en'], /* Fecha de última actualización del historial */
                    'id_detalle' => $fila['id_detalle'], /* ID del detalle */
                    'id_usuario' => $fila['id_usuario'], /* ID del usuario */
                    'usuario_nombre' => $fila['usuario_nombre'] ?? null, /* Nombre del usuario */
                    'usuario_apellidos' => $fila['usuario_apellidos'] ?? null, /* Apellidos del usuario */
                    'usuario_acronimo' => $fila['usuario_acronimo'] ?? null /* Acrónimo del usuario */
                ]; /* Creo una nueva entrada en el array */
            }
        }
        
    } catch (PDOException $e) { /* Si hay error al obtener el historial */
        echo "Error: " . $e->getMessage(); /* En caso de error, muestro mensaje */
    }

    // Incluir función de mostrar pedidos
    require_once __DIR__ . "/../funciones/mostrar_pedidos.php"; /* Incluyo la función */
        
    // Generar el HTML de los pedidos
    mostrarPedidos($historialAgrupado, $conexion); /* Llamo a la función que genera HTML */

    $_SESSION['historial_general'] = $historial; /* Almaceno el historial en la sesión para uso en JavaScript */ 
    
?>

<script>
    window.historial = <?php echo json_encode($historial); ?>; /* Actualizo la variable global historial con los nuevos datos */
</script>