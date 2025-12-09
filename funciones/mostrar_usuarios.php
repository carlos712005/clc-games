<?php

    // Función para mostrar los usuarios en el panel de administrador
    function mostrarUsuarios($usuarios, $conexion) { /* Función principal que recibe los usuarios y la conexión a BD */
        
        // Variable para determinar si mostrar el usuario
        $mostrar_usuario = false; /* Bandera que controla si debo mostrar cada usuario individual */
        // Variable para saber si hay al menos un usuario que cumple los filtros
        $hay_usuarios_coincidentes = false; /* Bandera para saber si encontré al menos un usuario */
        
        if (empty($usuarios)) { /* Si no hay usuarios en la base de datos */
            ?> <!-- Inicio HTML para mensaje de "sin usuarios" -->
            <div class="sin-usuarios"> <!-- Contenedor para el mensaje -->
                <h2>No hay usuarios registrados en el sistema.</h2> <!-- Mensaje informativo -->
            </div>
            <?php /* Vuelvo a PHP */
            return; /* Termino la ejecución de la función */
        }

        foreach ($usuarios as $usuario) { /* Recorro todos los usuarios que me llegaron */
            try { /* Inicio bloque try para capturar errores de base de datos */
                
                // Obtener el nombre del rol del usuario
                $consulta = $conexion->prepare("SELECT nombre FROM roles WHERE id = :id_rol"); /* Preparo consulta para obtener nombre del rol */
                $consulta->bindParam(':id_rol', $usuario['id_rol'], PDO::PARAM_INT); /* Vinculo el ID del rol */
                $consulta->execute(); /* Ejecuto la consulta */
                $nombre_rol = $consulta->fetchColumn(); /* Obtengo solo el nombre del rol */
                
                // Aplicar filtros si existen
                if(isset($_SESSION['filtros_usuarios'])) { /* Verifico si el usuario ha aplicado filtros */
                    // Si hay filtros elegidos, verificar coincidencias
                    $filtros_elegidos = $_SESSION['filtros_usuarios']; /* Obtengo los filtros que eligió el usuario */
                    
                    // Verifico si hay algún filtro específico seleccionado (no null)
                    $hay_filtros_activos = ($filtros_elegidos['rol'] !== 'null') || /* Verifico si eligió un rol específico */
                                            ($filtros_elegidos['acronimo'] !== 'null') || /* Verifico si eligió un acrónimo específico */
                                            ($filtros_elegidos['email'] !== 'null') || /* Verifico si eligió un correo específico */
                                            ($filtros_elegidos['dni'] !== 'null') || /* Verifico si eligió un DNI específico */
                                            ($filtros_elegidos['nombre'] !== 'null') || /* Verifico si eligió un nombre específico */
                                            ($filtros_elegidos['apellidos'] !== 'null') || /* Verifico si eligió unos apellidos específicos */
                                            ($filtros_elegidos['fecha_creacion_desde'] !== null) || /* Verifico si puso fecha de creación desde */
                                            ($filtros_elegidos['fecha_creacion_hasta'] !== null) || /* Verifico si puso fecha de creación hasta */
                                            ($filtros_elegidos['fecha_actualizacion_desde'] !== null) || /* Verifico si puso fecha de última actualización desde */
                                            ($filtros_elegidos['fecha_actualizacion_hasta'] !== null) || /* Verifico si puso fecha de última actualización hasta */
                                            ($filtros_elegidos['fecha_acceso_desde'] !== null) || /* Verifico si puso fecha de acceso desde */
                                            ($filtros_elegidos['fecha_acceso_hasta'] !== null); /* Verifico si puso fecha de acceso hasta */
                    
                    if ($hay_filtros_activos) { /* Si el usuario aplicó algún filtro específico */
                        // Verificar si el usuario cumple CON TODOS los filtros elegidos
                        $mostrar_usuario = true; /* Asumo que el usuario cumple hasta que demuestre lo contrario */
                        
                        // Verificar cada filtro individualmente - TODOS deben cumplirse
                        if ($filtros_elegidos['rol'] !== 'null' && $filtros_elegidos['rol'] != $usuario['id_rol']) { /* Si eligió un rol y no coincide */
                            $mostrar_usuario = false; /* Marco que no debe mostrarse */
                        }
                        if ($filtros_elegidos['acronimo'] !== 'null' && $filtros_elegidos['acronimo'] != $usuario['acronimo']) { /* Si eligió un acrónimo y no coincide */
                            $mostrar_usuario = false; /* Marco que no debe mostrarse */
                        }
                        if ($filtros_elegidos['email'] !== 'null' && $filtros_elegidos['email'] != $usuario['email']) { /* Si eligió un correo y no coincide */
                            $mostrar_usuario = false; /* Marco que no debe mostrarse */
                        }
                        if ($filtros_elegidos['dni'] !== 'null' && $filtros_elegidos['dni'] != $usuario['dni']) { /* Si eligió un DNI y no coincide */
                            $mostrar_usuario = false; /* Marco que no debe mostrarse */
                        }
                        if ($filtros_elegidos['nombre'] !== 'null' && $filtros_elegidos['nombre'] != $usuario['nombre']) { /* Si eligió un nombre y no coincide */
                            $mostrar_usuario = false; /* Marco que no debe mostrarse */
                        }
                        if ($filtros_elegidos['apellidos'] !== 'null' && $filtros_elegidos['apellidos'] != $usuario['apellidos']) { /* Si eligió apellidos y no coincide */
                            $mostrar_usuario = false; /* Marco que no debe mostrarse */
                        }
                        // Filtros de fecha de creación
                        if ($filtros_elegidos['fecha_creacion_desde'] !== null && $usuario['creado_en'] < $filtros_elegidos['fecha_creacion_desde']) { /* Si la fecha de creación es anterior al filtro */
                            $mostrar_usuario = false; /* Marco que no debe mostrarse */
                        }
                        if ($filtros_elegidos['fecha_creacion_hasta'] !== null && $usuario['creado_en'] > $filtros_elegidos['fecha_creacion_hasta'] . ' 23:59:59') { /* Si la fecha de creación es posterior al filtro */
                            $mostrar_usuario = false; /* Marco que no debe mostrarse */
                        }
                        // Filtros de fecha de última actualización
                        if ($filtros_elegidos['fecha_actualizacion_desde'] !== null && $usuario['actualizado_en'] < $filtros_elegidos['fecha_actualizacion_desde']) { /* Si la fecha de última actualización es anterior al filtro */
                            $mostrar_usuario = false; /* Marco que no debe mostrarse */
                        }
                        if ($filtros_elegidos['fecha_actualizacion_hasta'] !== null && $usuario['actualizado_en'] > $filtros_elegidos['fecha_actualizacion_hasta'] . ' 23:59:59') { /* Si la fecha de última actualización es posterior al filtro */
                            $mostrar_usuario = false; /* Marco que no debe mostrarse */
                        }
                        // Filtros de fecha de último acceso
                        if ($filtros_elegidos['fecha_acceso_desde'] !== null && $usuario['ultimo_acceso'] < $filtros_elegidos['fecha_acceso_desde']) { /* Si la fecha de último acceso es anterior al filtro */
                            $mostrar_usuario = false; /* Marco que no debe mostrarse */
                        }
                        if ($filtros_elegidos['fecha_acceso_hasta'] !== null && $usuario['ultimo_acceso'] > $filtros_elegidos['fecha_acceso_hasta'] . ' 23:59:59') { /* Si la fecha de último acceso es posterior al filtro */
                            $mostrar_usuario = false; /* Marco que no debe mostrarse */
                        }
                    } else { /* Si todos los filtros están en "null", mostrar todos los usuarios */
                        $mostrar_usuario = true; /* Muestro todos los usuarios porque no hay filtros específicos */
                    }
                } else { /* Si no hay filtros elegidos (no hay filtros en la sesión), mostrar todos los usuarios */
                    $mostrar_usuario = true; /* Muestro todos los usuarios porque no se han aplicado filtros */
                }
                
                // Mostrar el usuario si cumple las condiciones
                if ($mostrar_usuario) { /* Si el usuario cumple los filtros */
                
                    // Marcar que hay al menos un usuario que cumple los filtros
                    $hay_usuarios_coincidentes = true; /* Marco que encontré al menos un usuario válido */
                    
                    // Contar juegos en biblioteca del usuario
                    $consulta = $conexion->prepare("SELECT COUNT(*) FROM biblioteca WHERE id_usuario = :id_usuario"); /* Preparo consulta para contar juegos */
                    $consulta->bindParam(':id_usuario', $usuario['id'], PDO::PARAM_INT); /* Vinculo el ID del usuario */
                    $consulta->execute(); /* Ejecuto la consulta */
                    $total_juegos = $consulta->fetchColumn(); /* Obtengo el total de juegos en biblioteca */
                    
                    // Contar juegos en carrito del usuario
                    $consulta = $conexion->prepare("SELECT COUNT(*) FROM carrito WHERE id_usuario = :id_usuario"); /* Preparo consulta para contar juegos en carrito */
                    $consulta->bindParam(':id_usuario', $usuario['id'], PDO::PARAM_INT); /* Vinculo el ID del usuario */
                    $consulta->execute(); /* Ejecuto la consulta */
                    $total_carrito = $consulta->fetchColumn(); /* Obtengo el total de juegos en carrito */

                    // Contar juegos en favoritos del usuario
                    $consulta = $conexion->prepare("SELECT COUNT(*) FROM favoritos WHERE id_usuario = :id_usuario"); /* Preparo consulta para contar juegos en favoritos */
                    $consulta->bindParam(':id_usuario', $usuario['id'], PDO::PARAM_INT); /* Vinculo el ID del usuario */
                    $consulta->execute(); /* Ejecuto la consulta */
                    $total_favoritos = $consulta->fetchColumn(); /* Obtengo el total de juegos en favoritos */
                    ?> <!-- Inicio de HTML para mostrar el usuario -->
                    <div class="usuario"> <!-- Contenedor principal de cada usuario -->
                        <h2><?php echo htmlspecialchars($usuario['nombre']); ?> (<?php echo htmlspecialchars($usuario['acronimo']); ?>)</h2> <!-- Nombre y acrónimo del usuario, escapados para seguridad -->
                        
                        <hr/> <!-- Línea separadora -->
                        <div class="info-usuario"> <!-- Contenedor para información del usuario -->
                            <p><strong>ID:</strong> <?php echo htmlspecialchars($usuario['id']); ?></p> <!-- ID del usuario -->
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p> <!-- Email del usuario -->
                            <p><strong>Rol:</strong> <?php echo htmlspecialchars($nombre_rol); ?></p> <!-- Rol del usuario -->
                            <p><strong>Fecha de creación:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($usuario['creado_en']))); ?></p> <!-- Fecha formateada -->
                            <p><strong>Última actualización:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($usuario['actualizado_en']))); ?></p> <!-- Fecha formateada -->
                            <p><strong>Último acceso:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($usuario['ultimo_acceso']))); ?></p> <!-- Fecha formateada -->
                        </div> <!-- Cierro contenedor de información -->
                        
                        <div class="estadisticas-usuario"> <!-- Contenedor para estadísticas del usuario -->
                            <p><strong>Juegos en biblioteca:</strong> <?php echo $total_juegos; ?></p> <!-- Total de juegos -->
                            <p><strong>Juegos en carrito:</strong> <?php echo $total_carrito; ?></p> <!-- Total de juegos en carrito -->
                            <p><strong>Juegos en favoritos:</strong> <?php echo $total_favoritos; ?></p> <!-- Total de juegos en favoritos -->
                        </div> <!-- Cierro contenedor de estadísticas -->
                        
                        <hr/> <!-- Línea separadora -->

                        <!-- Contenedor para botones de acciones del usuario -->
                        <div class="botones-usuario"> <!-- Contenedor para los botones de acción -->
                            <a href="../vistas/detalles_usuario.php?id=<?php echo $usuario['id']; ?>" class="boton-detalles-usuario"> <!-- Enlace a detalles del juego -->
                                <img src="../recursos/imagenes/detalles.png" alt="Icono de Detalle" class="icono-detalles-usuario"> <!-- Icono de detalles -->  
                                <span>Ver detalles</span> <!-- Texto del botón -->
                            </a>
                        </div> <!-- Cierro contenedor de botones -->
                        <hr/> <!-- Línea separadora -->
                        <div class="botones-usuario"> <!-- Contenedor para los botones de acción -->
                            <a href="../publico/editar_datos.php?id=<?php echo $usuario['id']; ?>" class="boton-editar-usuario"> <!-- Enlace para editar usuario -->
                                <img src="../recursos/imagenes/editar.png" alt="Icono de Editar" class="icono-editar"> <!-- Icono de editar -->  
                                <span>Editar</span> <!-- Texto del botón -->
                            </a>
                            <a href="#" onclick="eliminarUsuario(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['acronimo'], ENT_QUOTES); ?>')" class="boton-eliminar-usuario"> <!-- Enlace para eliminar usuario -->
                                <img src="../recursos/imagenes/eliminar_usuario.png" alt="Icono de Eliminar" class="icono-eliminar"> <!-- Icono de eliminar -->
                                <span>Eliminar</span> <!-- Texto del botón -->
                            </a>
                        </div> <!-- Cierro contenedor de botones -->
                    </div> <!-- Cierro contenedor del usuario -->
                    <?php /* Vuelvo a PHP */
                }

            } catch (PDOException $e) { /* Si hay error en las consultas */
                $_SESSION['mensaje_error'] = 'Error al conectar con la base de datos: ' . $e->getMessage(); /* Guardo el mensaje de error */
                header('Location: panel_administrador.php'); /* Redirijo al panel de administrador */
                exit; /* Termino la ejecución */
            }
        } /* Fin del foreach que recorre todos los usuarios */
        
        if (!$hay_usuarios_coincidentes) { /* Si no encontré ningún usuario que cumpla los filtros */
            ?> <!-- Inicio HTML para mensaje de "sin usuarios" -->
            <div class="sin-usuarios"> <!-- Contenedor para el mensaje -->
                <h2>No hay usuarios que coincidan con los filtros seleccionados.</h2> <!-- Mensaje informativo -->
            </div>
            <?php /* Vuelvo a PHP */
        }
    } /* Fin de la función mostrarUsuarios */
    
?>
