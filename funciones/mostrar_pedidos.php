<?php

    // Función para mostrar los pedidos con filtros aplicados
    function mostrarPedidos($historialAgrupado, $conexion) {
        // Variable para determinar si mostrar el pedido
        $mostrar_pedido = false; /* Bandera que controla si debo mostrar cada pedido individual */
        // Variable para saber si hay al menos un pedido que cumple los filtros
        $hay_pedidos_coincidentes = false; /* Bandera para saber si encontré al menos un pedido */
        
        if (empty($historialAgrupado)) { /* Si hay registros en el historial */ ?>
            <div class="sin-historial"> <!-- Contenedor para el mensaje -->
                <h2 data-translate="no_hay_historial">No hay registros de pedidos.</h2> <!-- Mensaje informativo -->
            </div>
            <?php return; /* Termino la función si no hay historial */
        }

        foreach ($historialAgrupado as $h) { /* Recorro cada entrada del historial agrupado */
            
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
                        $mostrar_pedido = false; /* Marco que no debe mostrarse */
                    }
                    if ($filtros_elegidos['estado'] !== 'null' && $filtros_elegidos['estado'] != $h['estado']) { /* Si eligió un estado y no coincide */
                        $mostrar_pedido = false; /* Marco que no debe mostrarse */
                    }
                    if ($filtros_elegidos['estado_detalle'] !== 'null' && $filtros_elegidos['estado_detalle'] != $h['estado_detalle']) { /* Si eligió un estado de detalle y no coincide */
                        $mostrar_pedido = false; /* Marco que no debe mostrarse */
                    }
                    if ($filtros_elegidos['acronimo'] !== 'null' && $filtros_elegidos['acronimo'] != $h['usuario_acronimo']) { /* Si eligió un acrónimo y no coincide */
                        $mostrar_pedido = false; /* Marco que no debe mostrarse */
                    }
                    if ($filtros_elegidos['nombre'] !== 'null' && $filtros_elegidos['nombre'] != $h['usuario_nombre']) { /* Si eligió un nombre y no coincide */
                        $mostrar_pedido = false; /* Marco que no debe mostrarse */
                    }
                    if ($filtros_elegidos['apellidos'] !== 'null' && $filtros_elegidos['apellidos'] != $h['usuario_apellidos']) { /* Si eligió apellidos y no coincide */
                        $mostrar_pedido = false; /* Marco que no debe mostrarse */
                    }
                    if ($filtros_elegidos['metodo_pago'] !== 'null' && $filtros_elegidos['metodo_pago'] != $h['metodo_pago']) { /* Si eligió un método de pago y no coincide */
                        $mostrar_pedido = false; /* Marco que no debe mostrarse */
                    }
                    // Filtros de total
                    if ($filtros_elegidos['total_min'] !== null && $h['total'] < $filtros_elegidos['total_min']) { /* Si el total es menor al mínimo del filtro */
                        $mostrar_pedido = false; /* Marco que no debe mostrarse */
                    }
                    if ($filtros_elegidos['total_max'] !== null && $h['total'] > $filtros_elegidos['total_max']) { /* Si el total es mayor al máximo del filtro */
                        $mostrar_pedido = false; /* Marco que no debe mostrarse */
                    }
                    // Filtros de fecha de creación
                    if ($filtros_elegidos['creado_desde'] !== null && $h['creado_en'] < $filtros_elegidos['creado_desde']) { /* Si la fecha de creación es anterior al filtro */
                        $mostrar_pedido = false; /* Marco que no debe mostrarse */
                    }
                    if ($filtros_elegidos['creado_hasta'] !== null && $h['creado_en'] > $filtros_elegidos['creado_hasta'] . ' 23:59:59') { /* Si la fecha de creación es posterior al filtro */
                        $mostrar_pedido = false; /* Marco que no debe mostrarse */
                    }
                    // Filtros de fecha de actualización
                    if ($filtros_elegidos['actualizado_desde'] !== null && $h['actualizado_en'] < $filtros_elegidos['actualizado_desde']) { /* Si la fecha de actualización es anterior al filtro */
                        $mostrar_pedido = false; /* Marco que no debe mostrarse */
                    }
                    if ($filtros_elegidos['actualizado_hasta'] !== null && $h['actualizado_en'] > $filtros_elegidos['actualizado_hasta'] . ' 23:59:59') { /* Si la fecha de actualización es posterior al filtro */
                        $mostrar_pedido = false; /* Marco que no debe mostrarse */
                    }
                } else { /* Si todos los filtros están en "null", mostrar todos los pedidos */
                    $mostrar_pedido = true; /* Muestro todos los pedidos porque no hay filtros específicos */
                }
            } else { /* Si no hay filtros elegidos (no hay filtros en la sesión), mostrar todos los pedidos */
                $mostrar_pedido = true; /* Muestro todos los pedidos porque no se han aplicado filtros */
            }
            
            // Mostrar el pedido si cumple las condiciones
            if ($mostrar_pedido) { /* Si el pedido cumple los filtros */
            
                $hay_pedidos_coincidentes = true; /* Marco que encontré al menos un pedido válido */ ?>
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
                    <?php
                    $nombreCompleto = trim(((string)($h['usuario_nombre'] ?? '')) . ' ' . ((string)($h['usuario_apellidos'] ?? ''))); /* Construyo el nombre completo del usuario */
                    $acronimoUsuario = (string)($h['usuario_acronimo'] ?? ''); /* Obtengo el acrónimo del usuario */
                    if ($nombreCompleto !== '' || $acronimoUsuario !== '') { /* Si hay nombre completo o acrónimo */ ?>
                        <div class="historial-cliente"> <!-- Contenedor del cliente -->
                            <strong>Cliente:</strong> <!-- Etiqueta de cliente -->
                            <?php echo htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8'); ?> <!-- Muestro el nombre completo del usuario -->
                            <?php if ($acronimoUsuario !== '') { echo ' (' . htmlspecialchars($acronimoUsuario, ENT_QUOTES, 'UTF-8') . ')'; } ?> <!-- Muestro el acrónimo entre paréntesis si existe -->
                        </div>
                    <?php } ?>
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
                    <div class="historial-acciones administrador"> <!-- Contenedor de las acciones del historial -->
                        <div class="historial-botones"> <!-- Contenedor para los botones de acciones -->
                            <a class="historial-boton-detalles" onclick="mostrarDetallesHistorial(<?php echo $h['id_historial']; ?>, '<?php echo $id_historial_formateado; ?>')"> <!-- Botón para ver detalles del historial -->
                                <img src="../recursos/imagenes/detalles.png" alt="Ver detalles" id="icono-detalles"> <!-- Icono de detalles -->
                                <span>Ver detalles pedido</span> <!-- Texto del botón -->
                            </a>
                            <a href="../vistas/detalles_usuario.php?id=<?php echo (int)$h['id_usuario']; ?>" class="historial-boton-detalles"> <!-- Botón para ver detalles del usuario -->
                                <img src="../recursos/imagenes/detalles.png" alt="Ver detalles" id="icono-detalles"> <!-- Icono de detalles -->
                                <span>Ver detalles usuario</span> <!-- Texto del botón -->
                            </a>
                        </div>
                        <?php if($h['tipo'] === 'SOLICITUD_DEVOLUCION' && $h['estado'] === 'PENDIENTE_REVISION') { /* Si es una solicitud de devolución pendiente de revisión */ ?>
                            <hr> <!-- Línea horizontal decorativa -->
                            <div class="historial-botones"> <!-- Contenedor para los botones de devolución -->
                                <a class="historial-boton-confirmar" onclick="aprobarSolicitud('<?php echo $id_historial_formateado ?>', <?php echo $h['id_historial']; ?>, <?php echo $h['id_detalle']; ?>, 'SOLICITUD_DEVOLUCION', '<?php echo $h['usuario_acronimo']; ?>')"> <!-- Botón para aprobar la devolución -->
                                    <img src="../recursos/imagenes/aprobar_devolucion.png" alt="Aprobar devolución" id="icono-aprobar"> <!-- Icono de aprobar -->
                                    <span>Aprobar devolución</span> <!-- Texto del botón -->
                                </a>
                                <a class="historial-boton-rechazar" onclick="rechazarSolicitud('<?php echo $id_historial_formateado ?>', <?php echo $h['id_historial']; ?>, <?php echo $h['id_detalle']; ?>, 'SOLICITUD_DEVOLUCION', '<?php echo $h['usuario_acronimo']; ?>')"> <!-- Botón para rechazar la devolución -->
                                    <img src="../recursos/imagenes/rechazar_devolucion.png" alt="Rechazar devolución" id="icono-rechazar"> <!-- Icono de rechazar -->
                                    <span>Rechazar devolución</span> <!-- Texto del botón -->
                                </a>
                            </div>
                        <?php } ?>
                        <?php if($h['tipo'] === 'RESERVA' && $h['estado'] === 'PENDIENTE') { /* Si es una reserva pendiente */ ?>
                            <hr> <!-- Línea horizontal decorativa -->
                            <div class="historial-botones"> <!-- Contenedor para los botones de reserva -->
                                <a class="historial-boton-confirmar" onclick="aprobarSolicitud('<?php echo $id_historial_formateado ?>', <?php echo $h['id_historial']; ?>, <?php echo $h['id_detalle']; ?>, 'RESERVA', '<?php echo $h['usuario_acronimo']; ?>')"> <!-- Botón para confirmar la reserva -->
                                    <img src="../recursos/imagenes/aprobar_reserva.png" alt="Confirmar reserva" id="icono-confirmar"> <!-- Icono de confirmar -->
                                    <span>Aprobar reserva</span> <!-- Texto del botón -->
                                </a>
                                <a class="historial-boton-rechazar" onclick="rechazarSolicitud('<?php echo $id_historial_formateado ?>', <?php echo $h['id_historial']; ?>, <?php echo $h['id_detalle']; ?>, 'RESERVA', '<?php echo $h['usuario_acronimo']; ?>')"> <!-- Botón para rechazar la reserva -->
                                    <img src="../recursos/imagenes/rechazar_reserva.png" alt="Rechazar reserva" id="icono-rechazar"> <!-- Icono de rechazar -->
                                    <span>Rechazar reserva</span> <!-- Texto del botón -->
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php /* Vuelvo a PHP */
            }

        } /* Fin del foreach que recorre todos los pedidos */
        
        if (!$hay_pedidos_coincidentes) { /* Si no encontré ningún pedido que cumpla los filtros */ ?>
            <div class="sin-historial"> <!-- Contenedor para el mensaje -->
                <h2>No hay pedidos que coincidan con los filtros seleccionados.</h2> <!-- Mensaje informativo -->
            </div>
        <?php /* Vuelvo a PHP */
        }
    } 
    
?>

        