    <!-- Encabezado -->
    <?php include __DIR__ . '/../vistas/comunes/encabezado.php'; ?> <!-- Incluyo el encabezado con menú y estilos -->

    <?php
    // Verificar sesión y redirigir con JavaScript si es necesario
    if(!isset($_SESSION['id_usuario'])) {
        echo '<script>window.location.href = "index.php";</script>'; /* Redirijo con JavaScript si no está logueado */
        exit; /* Termino la ejecución del script */
    }
    ?>

    <link rel="stylesheet" href="../recursos/css/estilos_editar_datos.css" type="text/css"> <!-- Estilos específicos para la página de editar datos -->

    <?php
    $id_usuario = null; /* Inicializo la variable para el ID del usuario */

    // Verificar si se ha proporcionado un ID de usuario válido
    if (isset($_GET['id']) && is_numeric($_GET['id'])) { /* Verifico que llegue un ID válido por GET */
        $id_usuario = (int)$_GET['id']; /* Convierto el ID a entero para seguridad */
        
        // Verificar que el usuario actual sea admin o esté editando su propio perfil
        if($_SESSION['id_rol'] != 1 && $_SESSION['id_usuario'] != $id_usuario) {
            echo '<script>modal("modal1", "<h1>No tienes permisos para editar este usuario.</h1>", false); window.location.href = "index.php";</script>'; /* Muestro el mensaje de error usando el modal y redirijo con JavaScript */
            exit; /* Termino la ejecución del script */
        }
    }

    // Obtener categorías para el formulario de registro
    try { /* Inicio bloque try para capturar errores al obtener filtros */
        if ($id_usuario !== null) { /* Si se está editando otro usuario */
            $consulta = $conexion->prepare("SELECT * FROM usuarios WHERE id = :id_usuario"); /* Preparo consulta para obtener datos del usuario */
            $consulta->bindParam(':id_usuario', $id_usuario); /* Vinculo el ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */

            $datos_usuario = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del usuario */
            
            // Verificar que el usuario exista
            if(!$datos_usuario) {
                echo '<script>modal("modal1", "<h1>Usuario no encontrado.</h1>", false); window.location.href = "index.php";</script>'; /* Muestro el mensaje de error usando el modal y redirijo con JavaScript */
                exit; /* Termino la ejecución del script */
            }
        
            $consulta = $conexion->prepare("
                SELECT 
                    f.id_fijo,
                    f.nombre,
                    f.tipo_filtro,
                    f.clave,
                    f.orden
                FROM preferencias_usuario pu
                INNER JOIN filtros f ON pu.id_filtro = f.id_fijo
                WHERE pu.id_usuario = :id_usuario
            "); /* Preparo consulta para obtener las preferencias del usuario con JOIN a filtros */
            
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */
            $preferencias_usuario = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo las preferencias del usuario */
        }

        $consulta = $conexion->query("SELECT id, id_fijo, nombre, tipo_filtro, clave FROM filtros WHERE id > 0"); /* Obtengo todos los filtros de la base de datos */
        $filtros = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Guardo todos los filtros en un array */
        
        $preferencias_por_tipo = []; /* Inicializo array para organizar preferencias por tipo */
        
        if($id_usuario !== null) { /* Si se está editando otro usuario */
            foreach ($preferencias_usuario as $preferencia) { /* Recorro cada preferencia */
                $preferencias_por_tipo[$preferencia['tipo_filtro']] = $preferencia['id_fijo']; /* Organizo por tipo de filtro */
            }
        } else { /* Si se está editando el propio usuario */
            if (isset($_SESSION['preferencias_usuario']) && is_array($_SESSION['preferencias_usuario'])) { /* Si hay preferencias del usuario */
                foreach ($_SESSION['preferencias_usuario'] as $preferencia) { /* Recorro cada preferencia */
                    $preferencias_por_tipo[$preferencia['tipo_filtro']] = $preferencia['id_fijo']; /* Organizo por tipo de filtro */
                }
            }
        }
    } catch (PDOException $e) { /* Si hay error al obtener los filtros */
        echo "Error al obtener las categorías: " . $e->getMessage(); /* Muestro el error */
        exit; /* Termino la ejecución */
    }

    // Determinar la URL de regreso (referer)
    if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'editar_datos.php') === false) { /* Si hay una página anterior y no es otra página de editar datos */
        $_SESSION['referer'] = $_SERVER['HTTP_REFERER']; /* Guardo la página de origen en sesión para poder volver */
    }

    if(isset($_SESSION['modo_admin'])) { /* Si está en modo admin */
        $rutaRegreso = $_SESSION['referer'] ?? '../vistas/panel_administrador.php'; /* Si no hay página anterior, uso el panel de admin */
    } else { /* Si no está en modo admin */
        $rutaRegreso = $_SESSION['referer'] ?? 'index.php'; /* Si no hay página anterior, uso el index por defecto */
    }
    
    ?>
    <main> <!-- Contenedor principal de la página -->
        <section id="editar-datos"> <!-- Sección principal con las tarjetas de edición -->
            <?php if($id_usuario !== null) { ?> <!-- Si se está editando otro usuario -->
                <h1>Editar Usuario: <?php echo htmlspecialchars($datos_usuario['nombre']); ?></h1> <!-- Título si se edita otro usuario -->
            <?php } else { ?> <!-- Si se está editando el propio usuario -->
                <h1>Mi Perfil</h1> <!-- Título principal de la página -->
            <?php } ?>

            <!-- Mostrar mensajes de error si existen -->
            <?php if (isset($_SESSION['error_acronimo_existente'])) { ?> <!-- Si hay error de usuario existente -->
                <div class="mensaje-error"> <?php echo htmlspecialchars($_SESSION['error_acronimo_existente']); ?> </div> <!-- Muestro el error -->
                <?php unset($_SESSION['error_acronimo_existente']); ?> <!-- Limpio el error de la sesión -->
            <?php } ?> <!-- Fin del condicional -->

            <?php if (isset($_SESSION['error_dni_existente'])) { ?> <!-- Si hay error de DNI existente -->
                <div class="mensaje-error"> <?php echo htmlspecialchars($_SESSION['error_dni_existente']); ?> </div> <!-- Muestro el error -->
                <?php unset($_SESSION['error_dni_existente']); ?> <!-- Limpio el error de la sesión -->
            <?php } ?> <!-- Fin del condicional -->

            <?php if (isset($_SESSION['error_email_existente'])) { ?> <!-- Si hay error de email existente -->
                <div class="mensaje-error"> <?php echo htmlspecialchars($_SESSION['error_email_existente']); ?> </div> <!-- Muestro el error -->
                <?php unset($_SESSION['error_email_existente']); ?> <!-- Limpio el error de la sesión -->
            <?php } ?> <!-- Fin del condicional -->

            <?php if (isset($_SESSION['error_contrasena_no_coincide'])) { ?> <!-- Si hay error de contraseñas que no coinciden -->
                <div class="mensaje-error"> <?php echo htmlspecialchars($_SESSION['error_contrasena_no_coincide']); ?> </div> <!-- Muestro el error -->
                <?php unset($_SESSION['error_contrasena_no_coincide']); ?> <!-- Limpio el error de la sesión -->
            <?php } ?> <!-- Fin del condicional -->

            <?php if (isset($_SESSION['error_general'])) { ?> <!-- Si hay error general -->
                <div class="mensaje-error"> <?php echo htmlspecialchars($_SESSION['error_general']); ?> </div> <!-- Muestro el error -->
                <?php unset($_SESSION['error_general']); ?> <!-- Limpio el error de la sesión -->
            <?php } ?> <!-- Fin del condicional -->

            <?php if (isset($_SESSION['mensaje_exito'])) { ?> <!-- Si hay mensaje de éxito -->
                <div class="mensaje-exito"> <?php echo htmlspecialchars($_SESSION['mensaje_exito']); ?> </div> <!-- Muestro el mensaje de éxito -->
                <?php unset($_SESSION['mensaje_exito']); ?> <!-- Limpio el mensaje de la sesión -->
            <?php } ?> <!-- Fin del condicional -->
            
            <div class="contenedor-tarjetas"> <!-- Contenedor que organiza las tarjetas en rejilla -->

                <div class="tarjeta"> <!-- Tarjeta para la información personal -->
                    <h2 class="titulo-tarjeta">Información Personal</h2> <!-- Título de la tarjeta -->
                    <div class="contenido-tarjeta"> <!-- Contenido de la tarjeta -->
                        <p class="nombre-completo"> <!-- Párrafo con el nombre completo del usuario -->
                            <?php echo $id_usuario !== null ? htmlspecialchars($datos_usuario['nombre'] . ' ' . $datos_usuario['apellidos']) : htmlspecialchars($_SESSION['nombre_usuario'] . ' ' . $_SESSION['apellidos_usuario']); ?> <!-- Nombre y apellidos del usuario -->
                        </p>
                        <p class="informacion-adicional"> <!-- Información adicional -->
                            <strong>Usuario:</strong> <?php echo $id_usuario !== null ? htmlspecialchars($datos_usuario['acronimo']) : htmlspecialchars($_SESSION['acronimo_usuario']); ?><br> <!-- Nombre de usuario -->
                            <strong>Email:</strong> <?php echo $id_usuario !== null ? htmlspecialchars($datos_usuario['email']) : htmlspecialchars($_SESSION['email_usuario']); ?><br> <!-- Email del usuario -->
                            <strong>DNI:</strong> <?php echo $id_usuario !== null ? htmlspecialchars($datos_usuario['dni']) : htmlspecialchars($_SESSION['dni_usuario']); ?> <!-- DNI del usuario -->
                        </p>
                    </div>
                    <button class="boton-tarjeta" onclick="abrirModalInfoPersonal()">Editar</button> <!-- Botón para abrir el modal de edición -->
                </div>

                <div class="tarjeta"> <!-- Tarjeta para las preferencias de juegos -->
                    <h2 class="titulo-tarjeta">Preferencias</h2> <!-- Título de la tarjeta -->
                    <div class="contenido-tarjeta"> <!-- Contenido de la tarjeta -->
                        <p class="lista-preferencias"> <!-- Lista de preferencias del usuario -->
                            <!--Género preferido-->
                            <strong>Género:</strong> <?php 
                                if($id_usuario !== null) { /* Si se está editando otro usuario */
                                    if (isset($preferencias_por_tipo['generos'])) { /* Si hay preferencia de género */
                                        foreach ($filtros as $filtro) { /* Recorro los filtros */
                                            if ($filtro['tipo_filtro'] === 'generos' && $filtro['id_fijo'] == $preferencias_por_tipo['generos']) { /* Si coincide el filtro */
                                                echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre del género */
                                                break;
                                            }
                                        }
                                    } else { /* Si no hay preferencia de género */
                                        echo "Todos los géneros"; /* Muestro "Todos los géneros" */
                                    }
                                } else { /* Si se está editando el propio usuario */
                                    if (isset($preferencias_por_tipo['generos'])) { /* Si hay preferencia de género */
                                        foreach ($filtros as $filtro) { /* Recorro los filtros */
                                            if ($filtro['tipo_filtro'] === 'generos' && $filtro['id_fijo'] == $preferencias_por_tipo['generos']) { /* Si coincide el filtro */
                                                echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre del género */
                                                break;
                                            }
                                        }
                                    } else { /* Si no hay preferencia de género */
                                        echo "Todos los géneros"; /* Muestro "Todos los géneros" */
                                    }
                                }
                            ?><br> <!-- Categoría preferida -->
                            <strong>Categoría:</strong> <?php 
                                if($id_usuario !== null) { /* Si se está editando otro usuario */
                                    if (isset($preferencias_por_tipo['categorias'])) { /* Si hay preferencia de categoría */
                                        foreach ($filtros as $filtro) { /* Recorro los filtros */
                                            if ($filtro['tipo_filtro'] === 'categorias' && $filtro['id_fijo'] == $preferencias_por_tipo['categorias']) { /* Si coincide el filtro */
                                                echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre de la categoría */
                                                break;
                                            }
                                        }
                                    } else { /* Si no hay preferencia de categoría */
                                        echo "Todas las categorías"; /* Muestro "Todas las categorías" */
                                    }
                                } else { /* Si se está editando el propio usuario */
                                    if (isset($preferencias_por_tipo['categorias'])) { /* Si hay preferencia de categoría */
                                        foreach ($filtros as $filtro) { /* Recorro los filtros */
                                            if ($filtro['tipo_filtro'] === 'categorias' && $filtro['id_fijo'] == $preferencias_por_tipo['categorias']) { /* Si coincide el filtro */
                                                echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre de la categoría */
                                                break;
                                            }
                                        }
                                    } else { /* Si no hay preferencia de categoría */
                                        echo "Todas las categorías"; /* Muestro "Todas las categorías" */
                                    }
                                }
                            ?><br> <!-- Modo preferido -->
                            <strong>Modo:</strong> <?php 
                                if($id_usuario !== null) { /* Si se está editando otro usuario */
                                    if (isset($preferencias_por_tipo['modos'])) { /* Si hay preferencia de modo */
                                        foreach ($filtros as $filtro) { /* Recorro los filtros */
                                            if ($filtro['tipo_filtro'] === 'modos' && $filtro['id_fijo'] == $preferencias_por_tipo['modos']) { /* Si coincide el filtro */
                                                echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre del modo */
                                                break;
                                            }
                                        }
                                    } else { /* Si no hay preferencia de modo */
                                        echo "Todos los modos"; /* Muestro "Todos los modos" */
                                    }
                                } else { /* Si se está editando el propio usuario */
                                    if (isset($preferencias_por_tipo['modos'])) { /* Si hay preferencia de modo */
                                        foreach ($filtros as $filtro) { /* Recorro los filtros */
                                            if ($filtro['tipo_filtro'] === 'modos' && $filtro['id_fijo'] == $preferencias_por_tipo['modos']) { /* Si coincide el filtro */
                                                echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre del modo */
                                                break;
                                            }
                                        }
                                    } else { /* Si no hay preferencia de modo */
                                        echo "Todos los modos"; /* Muestro "Todos los modos" */
                                    }
                                }
                            ?><br> <!-- Calificación PEGI preferida -->
                            <strong>Calificación PEGI:</strong> <?php 
                                if($id_usuario !== null) { /* Si se está editando otro usuario */
                                    if (isset($preferencias_por_tipo['clasificacionPEGI'])) { /* Si hay preferencia de clasificación PEGI */
                                        foreach ($filtros as $filtro) { /* Recorro los filtros */
                                            if ($filtro['tipo_filtro'] === 'clasificacionPEGI' && $filtro['id_fijo'] == $preferencias_por_tipo['clasificacionPEGI']) { /* Si coincide el filtro */
                                                echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre de la clasificación PEGI */
                                                break;
                                            }
                                        }
                                    } else { /* Si no hay preferencia de clasificación PEGI */
                                        echo "Todas las clasificaciones"; /* Muestro "Todas las clasificaciones" */
                                    }
                                } else { /* Si se está editando el propio usuario */
                                    if (isset($preferencias_por_tipo['clasificacionPEGI'])) { /* Si hay preferencia de clasificación PEGI */
                                        foreach ($filtros as $filtro) { /* Recorro los filtros */
                                            if ($filtro['tipo_filtro'] === 'clasificacionPEGI' && $filtro['id_fijo'] == $preferencias_por_tipo['clasificacionPEGI']) { /* Si coincide el filtro */
                                                echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre de la clasificación PEGI */
                                                break;
                                            }
                                        }
                                    } else { /* Si no hay preferencia de clasificación PEGI */
                                        echo "Todas las clasificaciones"; /* Muestro "Todas las clasificaciones" */
                                    }
                                }
                            ?>
                        </p>
                    </div>
                    <button class="boton-tarjeta" onclick="abrirModalPreferencias()">Modificar</button> <!-- Botón para abrir el modal de preferencias -->
                </div>
                
                <div class="tarjeta"> <!-- Tarjeta para cambiar la contraseña -->
                    <h2 class="titulo-tarjeta">Contraseña</h2> <!-- Título de la tarjeta -->
                    <div class="contenido-tarjeta"> <!-- Contenido de la tarjeta -->
                        <p class="password-oculta">•••••••••</p> <!-- Contraseña oculta con asteriscos -->
                        <p class="descripcion-password">Tu contraseña está protegida</p> <!-- Descripción informativa -->
                    </div>
                    <button class="boton-tarjeta" onclick="abrirModalContrasena()">Cambiar Contraseña</button> <!-- Botón para abrir el modal de contraseña -->
                </div>

            </div> <!-- Fin del contenedor de tarjetas -->

            <a href="<?php echo $rutaRegreso; ?>" <?php if(isset($_SESSION['modo_admin']) && $_SESSION['modo_admin']) echo 'onclick="mostrarEdicionUsuarios()"'; ?> id="volver-inicio">Volver atrás</a> <!-- Enlace de vuelta al index -->

        </section> <!-- Fin de la sección principal -->
    </main> <!-- Fin del contenedor principal -->

    <!-- JavaScript para los modales -->
    <script>
        // Función para abrir el modal de información personal
        function abrirModalInfoPersonal() {
            const contenido = `
                <h2>Editar Información Personal</h2>
                <form action="../acciones/procesar_editar_datos.php" method="post" id="form-info-personal">
                    <input type="hidden" name="tipo_edicion" value="info_personal">
                    <?php if($id_usuario !== null) { /* Si se esta editando a otro usuario */ ?>
                        <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                    <?php } ?>
                    
                    <label for="nombre">Nombre real:</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Introduce tu nombre real" 
                        minlength="2" maxlength="50" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñÜü\\s]+" 
                        title="Solo se permiten letras y espacios, mínimo 2 caracteres" 
                        value="<?php echo $id_usuario !== null ? htmlspecialchars($datos_usuario['nombre']) : htmlspecialchars($_SESSION['nombre_usuario']); ?>"
                        required>

                    <label for="acronimo">Nombre de usuario:</label>
                    <input type="text" id="acronimo" name="acronimo" placeholder="Introduce tu nombre de usuario" 
                        minlength="3" maxlength="20" pattern="[A-Za-z0-9_]+" 
                        title="Solo letras, números y guiones bajos, entre 3 y 20 caracteres" 
                        value="<?php echo $id_usuario !== null ? htmlspecialchars($datos_usuario['acronimo']) : htmlspecialchars($_SESSION['acronimo_usuario']); ?>"
                        required>

                    <label for="apellidos">Apellidos:</label>
                    <input type="text" id="apellidos" name="apellidos" placeholder="Introduce tus apellidos" 
                        minlength="2" maxlength="100" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñÜü\\s]+" 
                        title="Solo se permiten letras y espacios, mínimo 2 caracteres" 
                        value="<?php echo $id_usuario !== null ? htmlspecialchars($datos_usuario['apellidos']) : htmlspecialchars($_SESSION['apellidos_usuario']); ?>"
                        required>

                    <label for="dni">DNI:</label>
                    <input type="text" id="dni" name="dni" pattern="[0-9]{8}[A-Za-z]{1}" 
                        maxlength="9" minlength="9"
                        title="Formato: 12345678A (8 números seguidos de 1 letra)" 
                        placeholder="12345678A" 
                        value="<?php echo $id_usuario !== null ? htmlspecialchars($datos_usuario['dni']) : htmlspecialchars($_SESSION['dni_usuario']); ?>"
                        required>

                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" placeholder="Introduce tu correo electrónico" 
                        maxlength="255" 
                        title="Introduce un correo electrónico válido" 
                        value="<?php echo $id_usuario !== null ? htmlspecialchars($datos_usuario['email']) : htmlspecialchars($_SESSION['email_usuario']); ?>"
                        required>
                </form>
            `; /* Contenido HTML del modal */
            modal('modal-info-personal', contenido, true); /* Llamo a la función modal para mostrar el modal */
            
            // Agregar evento al botón aceptar para disparar submit con validaciones
            document.getElementById('aceptar-modal-info-personal').onclick = function() {
                const form = document.getElementById('form-info-personal');
                form.requestSubmit(); /* Dispara el evento submit con validaciones nativas */
            };
        }

        // Función para abrir el modal de preferencias
        function abrirModalPreferencias() {
            const contenido = `
                <h2>Modificar Preferencias</h2>
                <form action="../acciones/procesar_editar_datos.php" method="post" id="form-preferencias">
                    <input type="hidden" name="tipo_edicion" value="preferencias">
                    <?php if($id_usuario !== null) { /* Si se está editando a otro usuario */ ?>
                        <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                    <?php } ?>
                    
                    <label for="id_preferencia_genero">Géneros:</label>
                    <select id="id_preferencia_genero" name="id_preferencia_genero" required>
                        <option value="0"<?php echo (!isset($preferencias_por_tipo['generos']) || $preferencias_por_tipo['generos'] == '0') ? ' selected' : ''; ?>>Todos los géneros</option>
                        <?php foreach ($filtros as $filtro) { /* Recorro los filtros */
                            if($filtro['tipo_filtro'] === 'generos'){ /* Si es del tipo géneros */
                        ?>
                            <option value="<?php echo $filtro['id_fijo']; ?>"<?php echo (isset($preferencias_por_tipo['generos']) && $preferencias_por_tipo['generos'] == $filtro['id_fijo']) ? ' selected' : ''; /* Si es el género seleccionado lo selecciono */?>>
                                <?php echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre del género */ ?>
                            </option>
                        <?php } }?>
                    </select>

                    <label for="id_preferencia_categoria">Categorías:</label>
                    <select id="id_preferencia_categoria" name="id_preferencia_categoria" required>
                        <option value="0"<?php echo (!isset($preferencias_por_tipo['categorias']) || $preferencias_por_tipo['categorias'] == '0') ? ' selected' : ''; ?>>Todas las categorías</option>
                        <?php foreach ($filtros as $filtro) { /* Recorro los filtros */
                            if($filtro['tipo_filtro'] === 'categorias'){ /* Si es del tipo categorías */
                        ?>
                            <option value="<?php echo $filtro['id_fijo']; ?>"<?php echo (isset($preferencias_por_tipo['categorias']) && $preferencias_por_tipo['categorias'] == $filtro['id_fijo']) ? ' selected' : ''; /* Si es la categoría seleccionada lo selecciono */?>>
                                <?php echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre de la categoría */ ?>
                            </option>
                        <?php } }?>
                    </select>

                    <label for="id_preferencia_modo">Modos de juego:</label>
                    <select id="id_preferencia_modo" name="id_preferencia_modo" required>
                        <option value="0"<?php echo (!isset($preferencias_por_tipo['modos']) || $preferencias_por_tipo['modos'] == '0') ? ' selected' : ''; ?>>Todos los modos</option>
                        <?php foreach ($filtros as $filtro) { /* Recorro los filtros */
                            if($filtro['tipo_filtro'] === 'modos'){ /* Si es del tipo modos */
                        ?>
                            <option value="<?php echo $filtro['id_fijo']; ?>"<?php echo (isset($preferencias_por_tipo['modos']) && $preferencias_por_tipo['modos'] == $filtro['id_fijo']) ? ' selected' : ''; /* Si es el modo seleccionado lo selecciono */?>>
                                <?php echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre del modo */ ?>
                            </option>
                        <?php } }?>
                    </select>

                    <label for="id_preferencia_pegi">Clasificaciones PEGI:</label>
                    <select id="id_preferencia_pegi" name="id_preferencia_pegi" required>
                        <option value="0"<?php echo (!isset($preferencias_por_tipo['clasificacionPEGI']) || $preferencias_por_tipo['clasificacionPEGI'] == '0') ? ' selected' : ''; ?>>Todas las clasificaciones PEGI</option>
                        <?php foreach ($filtros as $filtro) { /* Recorro los filtros */
                            if($filtro['tipo_filtro'] === 'clasificacionPEGI'){ /* Si es del tipo clasificación PEGI */
                        ?>
                            <option value="<?php echo $filtro['id_fijo']; ?>"<?php echo (isset($preferencias_por_tipo['clasificacionPEGI']) && $preferencias_por_tipo['clasificacionPEGI'] == $filtro['id_fijo']) ? ' selected' : ''; /* Si es la clasificación PEGI seleccionada lo selecciono */?>>
                                <?php echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre de la clasificación PEGI */ ?>
                            </option>
                        <?php } }?>
                    </select>
                </form>
            `; /* Contenido HTML del modal */
            modal('modal-preferencias', contenido, true); /* Llamo a la función modal para mostrar el modal */
            
            // Agregar evento al botón aceptar para disparar submit con validaciones
            document.getElementById('aceptar-modal-preferencias').onclick = function() {
                const form = document.getElementById('form-preferencias');
                form.requestSubmit(); /* Dispara el evento submit con validaciones nativas */
            };
        }

        // Función para abrir el modal de contraseña
        function abrirModalContrasena() {
            const contenido = `
                <h2>Cambiar Contraseña</h2>
                <form action="../acciones/procesar_editar_datos.php" method="post" id="form-contrasena">
                    <input type="hidden" name="tipo_edicion" value="contrasena">
                    <?php if($id_usuario !== null) { /* Si se está editando a otro usuario */ ?>
                        <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                    <?php } ?>
                    
                    <label for="contrasena">Nueva Contraseña:</label>
                    <div class="contenedor-contrasena">
                        <input type="password" id="contrasena" name="contrasena" placeholder="Introduce tu nueva contraseña" 
                            minlength="8" maxlength="255" 
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[@$!%*?&])[A-Za-z\\d@$!%*?&]{8,}"
                            title="Mínimo 8 caracteres, debe contener al menos: 1 minúscula, 1 mayúscula, 1 número y 1 carácter especial (@$!%*?&)"
                            required>
                        <button type="button" id="boton-contrasena" class="mostrar-ocultar-contrasena" onclick="mostrarOcultarContrasena('contrasena')" tabindex="-1" title="Mostrar contraseña"></button>
                    </div>

                    <label for="contrasena-confirmar">Confirmar Nueva Contraseña:</label>
                    <div class="contenedor-contrasena">
                        <input type="password" id="contrasena-confirmar" name="contrasena-confirmar" placeholder="Confirma tu nueva contraseña" 
                            minlength="8" maxlength="255" 
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[@$!%*?&])[A-Za-z\\d@$!%*?&]{8,}"
                            title="Mínimo 8 caracteres, debe contener al menos: 1 minúscula, 1 mayúscula, 1 número y 1 carácter especial (@$!%*?&)"
                            required>
                        <button type="button" id="boton-contrasena-confirmar" class="mostrar-ocultar-contrasena" onclick="mostrarOcultarContrasena('contrasena-confirmar')" tabindex="-1" title="Mostrar contraseña"></button>
                    </div>
                </form>
            `; /* Contenido HTML del modal */
            modal('modal-contrasena', contenido, true); /* Llamo a la función modal para mostrar el modal */
            
            // Agregar evento al botón aceptar para disparar submit con validaciones
            document.getElementById('aceptar-modal-contrasena').onclick = function() {
                const form = document.getElementById('form-contrasena');
                form.requestSubmit(); /* Dispara el evento submit con validaciones nativas */
            };
        }
    </script>
    
    <!-- JavaScript para funcionalidad de mostrar/ocultar contraseña -->
    <script src="../recursos/js/mostrar_ocultar_contrasena.js"></script> <!-- Incluyo el script para mostrar/ocultar contraseñas -->
    
    <!-- Pie de página -->
    <?php include __DIR__ . '/../vistas/comunes/pie.php'; ?> <!-- Incluyo el pie de página común -->