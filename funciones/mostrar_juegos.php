<?php

    // Función para verificar si el último historial coincide con el tipo y estado dados
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

    // Función para mostrar los juegos con filtros aplicados
    function mostrarJuegos($juegos, $conexion, $es_admin_panel = false, $id_usuario = null) { /* Función principal que recibe los juegos, la conexión a BD, opcionalmente si es panel admin y opcionalmente un id_usuario específico */
        // Variable para determinar si mostrar el juego
        $mostrar_juego = false; /* Bandera que controla si debo mostrar cada juego individual */
        // Variable para saber si hay al menos un juego que cumple los filtros
        $hay_juegos_coincidentes = false; /* Bandera para saber si encontré al menos un juego */
        
        // Determinar qué id_usuario usar (el pasado como parámetro o el de la sesión)
        $id_usuario_actual = $id_usuario !== null ? $id_usuario : (isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null);
        
        if (empty($juegos)) { /* Si no hay juegos */ ?>
            <div class="sin-juegos"> <!-- Contenedor para el mensaje -->
                <h2 data-translate="no_hay_juegos">No hay juegos disponibles.</h2> <!-- Mensaje informativo -->
            </div>
            <?php return; /* Termino la función si no hay historial */
        }

        foreach ($juegos as $juego) { /* Recorro todos los juegos que me llegaron */
            try { /* Inicio bloque try para capturar errores de base de datos */
                
                $existe_en_carrito = false; /* Inicializo la variable que indica si el juego está en el carrito */
                $existe_en_biblioteca = false; /* Inicializo la variable que indica si el juego está en la biblioteca */
                $reserva_solicitada = false; /* Inicializo la variable que indica si el juego tiene una reserva solicitada */
                $reserva_aprobada = false; /* Inicializo la variable que indica si el juego tiene una reserva aprobada */
                $reserva_rechazada = false; /* Inicializo la variable que indica si el juego tiene una reserva rechazada */

                if($id_usuario_actual !== null) { /* Si hay un usuario especificado (logueado o pasado como parámetro) */
                    // Verificar si el juego está en el carrito para el usuario especificado
                    $consulta = $conexion->prepare("SELECT id_juego FROM carrito WHERE id_juego = :id_juego AND id_usuario = :id_usuario"); /* Preparo consulta */
                    $consulta->bindParam(':id_juego', $juego['id']); /* Vinculo el ID del juego */
                    $consulta->bindParam(':id_usuario', $id_usuario_actual); /* Vinculo el ID del usuario */
                    $consulta->execute(); /* Ejecuto la consulta */
                    $juego_en_carrito = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del juego como array asociativo */

                    if($juego_en_carrito) $existe_en_carrito = true; /* Marco que el juego está en el carrito */
                    
                    // Verificar si el juego está en la biblioteca para el usuario especificado
                    $consulta = $conexion->prepare("SELECT id_juego FROM biblioteca WHERE id_juego = :id_juego AND id_usuario = :id_usuario"); /* Preparo consulta */
                    $consulta->bindParam(':id_juego', $juego['id']); /* Vinculo el ID del juego */
                    $consulta->bindParam(':id_usuario', $id_usuario_actual); /* Vinculo el ID del usuario */
                    $consulta->execute(); /* Ejecuto la consulta */
                    $juego_en_biblioteca = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del juego como array asociativo */

                    if($juego_en_biblioteca) $existe_en_biblioteca = true; /* Marco que el juego está en la biblioteca */
                
                    // Obtengo el ID del último registro en historial para este usuario+juego (cualquier tipo/estado)
                    $consulta = $conexion->prepare("
                        SELECT h.id
                        FROM historial h
                        INNER JOIN historial_compras hc ON hc.id_historial = h.id
                        WHERE h.id_usuario = :id_usuario AND hc.id_juego = :id_juego
                        ORDER BY h.creado_en DESC
                        LIMIT 1
                    "); /* Último historial asociado al juego para el usuario */
                    $consulta->bindParam(':id_usuario', $id_usuario_actual, PDO::PARAM_INT); /* Vinculo el ID del usuario */
                    $consulta->bindParam(':id_juego', $juego['id'], PDO::PARAM_INT); /* Vinculo el ID del juego */
                    $consulta->execute(); /* Ejecuto la consulta */
                    $ultimo_historial_id = $consulta->fetchColumn(); /* ID del último historial (o false si no hay) */

                    if($ultimo_historial_id) { /* Si existe algún historial previo del juego */
                        $reserva_en_solicitud = verificarExistencia($conexion, $ultimo_historial_id, 'RESERVA', 'PENDIENTE'); /* Verifico si el último historial es una reserva pendiente */
                        $reserva_esta_aprobada = verificarExistencia($conexion, $ultimo_historial_id, 'RESERVA', 'APROBADA'); /* Verifico si el último historial es una reserva aprobada */
                        $reserva_esta_rechazada = verificarExistencia($conexion, $ultimo_historial_id, 'RESERVA', 'RECHAZADA'); /* Verifico si el último historial es una reserva rechazada */
                        if($reserva_en_solicitud) $reserva_solicitada = true; /* Si el último registro es reserva pendiente, marco la bandera */
                        if($reserva_esta_aprobada) $reserva_aprobada = true; /* Si el último registro es reserva aprobada, marco la bandera */
                        if($reserva_esta_rechazada) $reserva_rechazada = true; /* Si el último registro es reserva rechazada, marco la bandera */
                    }
                }
                
                // Obtener los filtros asociados al juego actual
                $consulta = $conexion->prepare("SELECT id_juego, id_filtro FROM juegos_filtros WHERE id_juego = :id_juego"); /* Preparo consulta para obtener filtros del juego */
                $consulta->bindParam(':id_juego', $juego['id'], PDO::PARAM_INT); /* Vinculo el ID del juego actual */
                $consulta->execute(); /* Ejecuto la consulta */
                $filtros_juego = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los filtros del juego */
                
                // Obtener el ID del filtro de tipo a partir de la clave del tipo
                $consulta = $conexion->prepare("SELECT id_fijo FROM filtros WHERE clave = :tipo"); /* Preparo consulta para obtener ID del tipo */
                $consulta->bindParam(':tipo', $juego['tipo'], PDO::PARAM_STR); /* Vinculo la clave del tipo del juego */
                $consulta->execute(); /* Ejecuto la consulta */
                $id_tipo = (int)$consulta->fetchColumn(); // Devuelve solo el id_fijo convertido a entero /* Obtengo solo el ID del tipo como entero */

                // Obtener el nombre del filtro de tipo a partir de la clave del tipo
                $consulta = $conexion->prepare("SELECT nombre FROM filtros WHERE id_fijo = :id_tipo"); /* Preparo consulta para obtener nombre del tipo */
                $consulta->bindParam(':id_tipo', $id_tipo, PDO::PARAM_STR); /* Vinculo el ID del tipo que acabo de obtener */
                $consulta->execute(); /* Ejecuto la consulta */
                $nombre_tipo = $consulta->fetchColumn(); /* Obtengo solo el nombre del tipo */
                
                if(isset($_SESSION['filtros_elegidos'])) { /* Verifico si el usuario ha aplicado filtros */
                    // Si hay filtros elegidos, verificar coincidencias
                    $filtros_elegidos = $_SESSION['filtros_elegidos']; /* Obtengo los filtros que eligió el usuario */

                    // Creo un array con los IDs de filtros del juego, a partir de la  
                    // columna id_filtro de juegos_filtros, para comparar más fácil
                    $ids_filtros_juego = array_column($filtros_juego, 'id_filtro'); /* Extraigo solo los IDs de filtros para comparar más fácil */
                    
                    // Verifico si hay algún filtro específico seleccionado (no null)
                    $hay_filtros_activos = ($filtros_elegidos['tipo'] !== 0) || /* Verifico si eligió un tipo específico */
                                            ($filtros_elegidos['genero'] !== 0) || /* Verifico si eligió un género específico */
                                            ($filtros_elegidos['categoria'] !== 0) || /* Verifico si eligió una categoría específica */
                                            ($filtros_elegidos['modo'] !== 0) || /* Verifico si eligió un modo específico */
                                            ($filtros_elegidos['pegi'] !== 0) || /* Verifico si eligió una clasificación PEGI específica */
                                            ($filtros_elegidos['precio_min'] !== 0) || /* Verifico si puso un precio mínimo */
                                            ($filtros_elegidos['precio_max'] !== 100); /* Verifico si cambió el precio máximo del valor por defecto */
                    
                    if ($hay_filtros_activos) { /* Si el usuario aplicó algún filtro específico */
                        // Solo filtrar si hay filtros específicos seleccionados
                        // Verificar si el juego cumple CON TODOS los filtros elegidos
                        $mostrar_juego = true; /* Asumo que el juego cumple hasta que demuestre lo contrario */
                        
                        // Verificar cada filtro individualmente - TODOS deben cumplirse
                        if ($filtros_elegidos['tipo'] !== 0 && $filtros_elegidos['tipo'] != $id_tipo) { /* Si eligió un tipo y no coincide*/
                            $mostrar_juego = false; // No cumple el filtro de tipo /* Marco que no debe mostrarse */
                        }
                        if ($filtros_elegidos['genero'] !== 0 && !in_array($filtros_elegidos['genero'], $ids_filtros_juego)) { /* Si eligió un género y el juego no lo tiene */
                            $mostrar_juego = false; // No cumple el filtro de género /* Marco que no debe mostrarse */
                        }
                        if ($filtros_elegidos['categoria'] !== 0 && !in_array($filtros_elegidos['categoria'], $ids_filtros_juego)) { /* Si eligió una categoría y el juego no la tiene */
                            $mostrar_juego = false; // No cumple el filtro de categoría /* Marco que no debe mostrarse */
                        }
                        if ($filtros_elegidos['modo'] !== 0 && !in_array($filtros_elegidos['modo'], $ids_filtros_juego)) { /* Si eligió un modo y el juego no lo tiene */
                            $mostrar_juego = false; // No cumple el filtro de modo /* Marco que no debe mostrarse */
                        }
                        if ($filtros_elegidos['pegi'] !== 0 && !in_array($filtros_elegidos['pegi'], $ids_filtros_juego)) { /* Si eligió una clasificación PEGI y el juego no la tiene */
                            $mostrar_juego = false; // No cumple el filtro de PEGI /* Marco que no debe mostrarse */
                        }
                        if ($filtros_elegidos['precio_min'] !== 0 && $juego['precio'] < $filtros_elegidos['precio_min']) { /* Si el precio del juego es menor al mínimo elegido */
                            $mostrar_juego = false; // No cumple el precio mínimo /* Marco que no debe mostrarse */
                        }
                        if ($filtros_elegidos['precio_max'] !== 100 && $juego['precio'] > $filtros_elegidos['precio_max']) { /* Si el precio del juego es mayor al máximo elegido */
                            $mostrar_juego = false; // No cumple el precio máximo /* Marco que no debe mostrarse */
                        }
                    } else { /* Si todos los filtros están en valor por defecto */
                        // Si todos los filtros están en "null" (todos seleccionados (el valor que se manda es null pero en el array se guardan como 0)), mostrar todos los juegos
                        $mostrar_juego = true; /* Muestro todos los juegos porque no hay filtros específicos */
                    }
                } else { /* Si no hay filtros en la sesión */
                    // Si no hay filtros elegidos, mostrar todos los juegos
                    $mostrar_juego = true; /* Muestro todos los juegos porque no se han aplicado filtros */
                }
                
                // Mostrar el juego si cumple las condiciones
                if ($mostrar_juego) { /* Si el juego pasó todas las validaciones */
                    // Marcar que hay al menos un juego que cumple los filtros
                    $hay_juegos_coincidentes = true; /* Marco que encontré al menos un juego válido */

                    try { /* Inicio otro bloque try para obtener datos detallados del juego */
                                                        
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
                        "); /* Preparo consulta compleja para obtener todos los datos de los filtros del juego */

                        $consulta->bindParam(':id_juego', $juego['id'], PDO::PARAM_INT); /* Vinculo el ID del juego actual */
                        $consulta->execute(); /* Ejecuto la consulta */
                        $datos_filtros_juego = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los datos completos de los filtros */

                    } catch (PDOException $e) { /* Si hay error en la consulta de filtros detallados */
                        $_SESSION['mensaje_error'] = 'Error al conectar con la base de datos: ' . $e->getMessage(); /* Guardo el mensaje de error en sesión */
                        header('Location: index.php'); /* Redirijo al index */
                        exit; /* Termino la ejecución */
                    }
                    
                    ?> <!-- Inicio de HTML para mostrar el juego -->
                    <div class="juego"> <!-- Contenedor principal de cada juego -->
                        <?php 
                        $es_proximamente = false; /* Inicializo la variable que indica si el juego es próximo a lanzarse */
                        // Verificar si el juego tiene fecha de lanzamiento futura
                        if(isset($juego['fecha_lanzamiento']) && strtotime($juego['fecha_lanzamiento']) > time()) {
                            $es_proximamente = true; /* Marco que el juego es próximo a lanzarse */
                        }
                        ?>
                        <h2><?php echo htmlspecialchars($juego['nombre']); ?></h2> <!-- Título del juego, escapado para seguridad -->
                        <?php if($es_proximamente) { ?> <!-- Si el juego está próximo a lanzarse -->
                            <div class="etiqueta-proximamente"> <!-- Contenedor para la etiqueta de próximo lanzamiento -->
                                <p><strong>Próximamente - Lanzamiento: <?php echo date('d/m/Y', strtotime($juego['fecha_lanzamiento'])); ?></strong></p> <!-- Muestro la fecha de lanzamiento formateada -->
                            </div>
                        <?php } ?> <!-- Fin del condicional de juego próximamente -->
                        <img src="../<?php echo htmlspecialchars($juego['portada']); ?>" alt="<?php echo htmlspecialchars($juego['nombre']); ?>" id="imagen-juego"> <!-- Imagen del juego con ruta relativa -->
                        <div class="info-juego"> <!-- Grupo de etiquetas informativas para aislar estilos -->
                            <p>Tipo de juego: <?php echo htmlspecialchars($nombre_tipo); ?></p> <!-- Muestro el tipo de juego que obtuve antes -->
                            <p>Generos: <?php /* Inicio sección para mostrar géneros */
                                            if(!empty($datos_filtros_juego)) { /* Si el juego tiene filtros asociados */
                                                $generos = []; /* Array para almacenar los géneros */
                                                foreach($datos_filtros_juego as $filtro) { /* Recorro todos los filtros del juego */
                                                    if($filtro['tipo_filtro'] === 'generos') { /* Si el filtro es un género */
                                                        $generos[] = htmlspecialchars($filtro['nombre']); /* Añado el género al array, escapado */
                                                    } 
                                                }
                                                if(!empty($generos)) { /* Si encontré géneros */
                                                    echo implode(', ', $generos) . '.'; /* Los muestro separados por comas */
                                                }
                                            } ?></p> <!-- Cierro la sección de géneros -->
                            <p>Categorías: <?php /* Inicio sección para mostrar categorías */
                                            if(!empty($datos_filtros_juego)) { /* Si el juego tiene filtros asociados */
                                                $categorias = []; /* Array para almacenar las categorías */
                                                foreach($datos_filtros_juego as $filtro) { /* Recorro todos los filtros del juego */
                                                    if($filtro['tipo_filtro'] === 'categorias') { /* Si el filtro es una categoría */
                                                        $categorias[] = htmlspecialchars($filtro['nombre']); /* Añado la categoría al array, escapado */
                                                    } 
                                                }
                                                if(!empty($categorias)) { /* Si encontré categorías */
                                                    echo implode(', ', $categorias) . '.'; /* Las muestro separadas por comas */
                                                }
                                            } ?></p> <!-- Cierro la sección de categorías -->
                            <p>Modos: <?php /* Inicio sección para mostrar modos de juego */
                                            if(!empty($datos_filtros_juego)) { /* Si el juego tiene filtros asociados */
                                                $modos = []; /* Array para almacenar los modos */
                                                foreach($datos_filtros_juego as $filtro) { /* Recorro todos los filtros del juego */
                                                    if($filtro['tipo_filtro'] === 'modos') { /* Si el filtro es un modo */
                                                        $modos[] = htmlspecialchars($filtro['nombre']); /* Añado el modo al array, escapado */
                                                    } 
                                                }
                                                if(!empty($modos)) { /* Si encontré modos */
                                                    echo implode(', ', $modos) . '.'; /* Los muestro separados por comas */
                                                }
                                            } ?></p> <!-- Cierro la sección de modos -->
                            <p>Clasificaciones PEGI: <?php /* Inicio sección para mostrar clasificaciones PEGI */
                                            if(!empty($datos_filtros_juego)) { /* Si el juego tiene filtros asociados */
                                                $clasificaciones = []; /* Array para almacenar las clasificaciones */
                                                foreach($datos_filtros_juego as $filtro) { /* Recorro todos los filtros del juego */
                                                    if($filtro['tipo_filtro'] === 'clasificacionPEGI') { /* Si el filtro es una clasificación PEGI */
                                                        $clasificaciones[] = htmlspecialchars($filtro['nombre']); /* Añado la clasificación al array, escapado */
                                                    } 
                                                }
                                                if(!empty($clasificaciones)) { /* Si encontré clasificaciones */
                                                    echo implode(', ', $clasificaciones) . '.'; /* Las muestro separadas por comas */
                                                }
                                            } ?></p> <!-- Cierro la sección de clasificaciones PEGI -->
                        </div> <!-- Fin grupo info-juego -->
                        <p id="descripcion-juego"><?php echo htmlspecialchars($juego['resumen']); ?></p> <!-- Descripción del juego escapada -->
                        <p id="precio-juego">Precio: <?php if($juego['precio'] !== '0.00') echo str_replace('.', ',', htmlspecialchars($juego['precio'])) . " €"; else echo "Gratis"; ?></p> <!-- Precio del juego (mostrado con una coma en vez de un punto), si es 0 muestro "Gratis" -->
                        
                        <?php if(isset($juego['activo']) && $juego['activo'] == 0) { ?> <!-- Si el juego está inactivo -->
                            <p class="juego-descatalogado">Este juego está descatalogado</p> <!-- Mensaje de descatalogado -->
                        <?php } ?> <!-- Fin del condicional de juego inactivo -->
                        
                        <hr/> <!-- Línea separadora -->

                        <!-- Contenedor para botones de carrito y detalles del juego -->
                        <div class="botones-carrito-detalles"> <!-- Contenedor para los botones de acción -->
                            <a href="<?php if(isset($_SESSION['id_usuario']) && isset($_SESSION['id_rol']) && $_SESSION['id_rol'] === 1) echo '../publico/'; ?>detalles_juego.php?id=<?php echo $juego['id']; ?>" class="boton-detalles"> <!-- Enlace a detalles del juego -->
                                <img src="../recursos/imagenes/detalles.png" alt="Icono de Detalle" id="icono-detalles"> <!-- Icono de detalles -->  
                                <span>Ver detalles</span> <!-- Texto del botón -->
                            </a>
                            <?php if(!$existe_en_biblioteca && !$es_admin_panel && (isset($_SESSION['modo_admin']) && !$_SESSION['modo_admin'])) { /* Si el juego no está en la biblioteca y no estoy en el panel de administrador y no estoy en modo admin */
                                if(!isset($es_proximamente) || !$es_proximamente) { ?> <!-- Si el juego no es "Próximamente" -->    
                                    <?php if($existe_en_carrito) { ?> <!-- Si el juego ya está en el carrito -->
                                        <a href="#" onclick="eliminarDelCarrito(<?php echo $juego['id']; ?>, '<?php echo $juego['nombre']; ?>', this)" id="tarjeta-eliminar<?php echo $juego['id']; ?>" class="boton-carrito"> <!-- Enlace para quitar del carrito -->
                                            <img src="../recursos/imagenes/en_carrito2.png" alt="Icono de Carrito" id="icono-carrito"> <!-- Icono del carrito -->
                                            <span>Quitar del carrito</span> <!-- Texto del botón -->
                                        </a>
                                    <?php } else { ?> <!-- Si el juego no está en el carrito -->
                                        <a <?php if (isset($_SESSION['id_usuario'])) { echo 'href="#" onclick="mandar(\'agregar\', ' . $juego['id'] . ', \'modal1\', \'<h1>Juego añadido al carrito</h1>\', false , null, this)"'; } else { echo 'href="../sesiones/formulario_autenticacion.php"'; } ?> id="tarjeta-anadir<?php echo $juego['id']; ?>" class="boton-carrito" title="Añadir al carrito"> <!-- Enlace para añadir al carrito -->
                                            <img src="../recursos/imagenes/carrito2.png" alt="Icono de Carrito" id="icono-carrito"> <!-- Icono del carrito -->
                                            <span>Añadir al carrito</span> <!-- Texto del botón -->
                                        </a>
                                    <?php }
                                } else { /* Si el juego es "Próximamente" */
                                    $reserva = []; /* Inicializo el array para guardar la reserva */
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
                                        <a href="#" onclick="cancelarSolicitud('cancelar_solicitud_reserva', <?php echo $juego['id']; ?>, '<?php echo $juego['nombre']; ?>', <?php echo $juego['precio']; ?>, this)" id="reserva-cancelar<?php echo $juego['id']; ?>" class="boton-cancelar-solicitud-reserva"> <!-- Enlace de cancelar solicitud -->
                                            <img src="../recursos/imagenes/cancelar_solicitud.png" alt="Icono de Cancelar Solicitud" id="icono-cancelar-solicitud"> <!-- Icono de Cancelar Solicitud -->
                                            <span>Cancelar solicitud</span> <!-- Texto del botón -->
                                        </a>
                                    <?php }
                                }
                            } else { ?> <!-- Si el juego esta en la biblioteca -->
                                <a href="#" onclick="opcionNoDisponible()" class="boton-jugar"> <!-- Enlace para jugar -->
                                    <img src="../recursos/imagenes/jugar.png" alt="Icono de Jugar" id="icono-jugar"> <!-- Icono de jugar -->
                                    <span>Jugar</span> <!-- Texto del botón -->
                                </a>
                            <?php } ?>
                        </div> <!-- Cierro contenedor de botones -->
                        <?php if($es_admin_panel) { ?> <!-- Si estoy en el panel de administrador -->
                            <hr/> <!-- Línea separadora -->  
                            <div class="botones-carrito-detalles"> <!-- Contenedor para los botones de acción -->
                                <a href="editar_juego.php?id=<?php echo $juego['id']; ?>" class="boton-editar-juego"> <!-- Enlace a editar del juego -->
                                    <img src="../recursos/imagenes/editar_juego.png" alt="Icono de Editar" id="icono-editar"> <!-- Icono de editar -->
                                    <span>Editar</span> <!-- Texto del botón -->
                                </a>
                                <?php if(isset($juego['activo']) && $juego['activo'] == 1) { ?> <!-- Si el juego está activo -->
                                    <a href="#" onclick="eliminarJuego(<?php echo (int)$juego['id']; ?>, '<?php echo htmlspecialchars($juego['nombre'], ENT_QUOTES); ?>')" class="boton-eliminar"> <!-- Enlace para eliminar juego -->
                                        <img src="../recursos/imagenes/eliminar_juego.png" alt="Icono de Eliminar" id="icono-eliminar"> <!-- Icono de eliminar -->
                                        <span>Eliminar</span> <!-- Texto del botón -->
                                    </a>
                                <?php } else { ?> <!-- Si el juego está inactivo -->
                                    <a href="#" onclick="reactivarJuego(<?php echo (int)$juego['id']; ?>, '<?php echo htmlspecialchars($juego['nombre'], ENT_QUOTES); ?>')" class="boton-reactivar"> <!-- Enlace para reactivar juego -->
                                        <img src="../recursos/imagenes/reactivar.png" alt="Icono de Reactivar" id="icono-reactivar"> <!-- Icono de reactivar -->
                                        <span>Reactivar</span> <!-- Texto del botón -->
                                    </a>
                                <?php } ?>
                            </div>
                        <?php } ?> <!-- Fin de la verificación de estar en panel de administrador -->
                    </div> <!-- Cierro contenedor del juego -->
                    <?php /* Vuelvo a PHP */
                }

            } catch (PDOException $e) { /* Si hay error en las consultas principales */
                $_SESSION['mensaje_error'] = 'Error al conectar con la base de datos: ' . $e->getMessage(); /* Guardo el mensaje de error */
                header('Location: index.php'); /* Redirijo al index */
                exit; /* Termino la ejecución */
            }
        } /* Fin del foreach que recorre todos los juegos */
        
        if (!$hay_juegos_coincidentes) { /* Si no encontré ningún juego que cumpla los filtros */
            ?> <!-- Inicio HTML para mensaje de "sin juegos" -->
            <div class="sin-juegos"> <!-- Contenedor para el mensaje -->
                <h2 data-translate="no_hay_juegos">No hay juegos que coincidan con los filtros seleccionados.</h2> <!-- Mensaje informativo -->
            </div>
            <?php /* Vuelvo a PHP */
        }
    } /* Fin de la función mostrarJuegos */
    
?>

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
