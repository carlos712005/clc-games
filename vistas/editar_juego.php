<!-- Encabezado -->
<?php include __DIR__ . '/../vistas/comunes/encabezado.php'; ?> <!-- Incluyo el encabezado con menú y estilos -->

<?php

    // Verificar sesión y permisos de administrador
    if(!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
        echo '<script>window.location.href = "../publico/index.php";</script>'; /* Redirijo con JavaScript si no es admin */
        exit; /* Termino la ejecución del script */
    }

    // Verificar si se ha proporcionado un ID de juego válido
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { /* Verifico que llegue un ID válido por GET */
        header('Location: panel_administrador.php'); /* Si no hay ID válido, redirijo al panel */
        exit; /* Termino la ejecución */
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

        // Obtener todos los filtros disponibles
        $consulta = $conexion->query("SELECT id, id_fijo, nombre, tipo_filtro, clave FROM filtros WHERE id > 0 ORDER BY tipo_filtro, orden"); /* Obtengo todos los filtros ordenados */
        $filtros = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Guardo todos los filtros en un array */

        // Obtener los filtros actuales del juego
        $consulta = $conexion->prepare("SELECT id_filtro FROM juegos_filtros WHERE id_juego = :id_juego"); /* Preparo consulta para obtener los filtros del juego */
        $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el ID del juego */
        $consulta->execute(); /* Ejecuto la consulta */
        $filtros_actuales = $consulta->fetchAll(PDO::FETCH_COLUMN); /* Obtengo solo los IDs de los filtros en un array simple */
        
        // Obtener el ID del filtro de tipo a partir de la clave del tipo
        $consulta = $conexion->prepare("SELECT id_fijo FROM filtros WHERE clave = :tipo"); /* Preparo consulta para obtener el ID del tipo de juego */
        $consulta->bindParam(':tipo', $juego['tipo'], PDO::PARAM_STR); /* Vinculo la clave del tipo */
        $consulta->execute(); /* Ejecuto la consulta */
        $id_tipo_actual = (int)$consulta->fetchColumn(); /* Obtengo solo el ID del tipo como entero */

        // Organizar filtros por tipo para facilitar su uso en los selects
        $filtros_por_tipo = []; /* Inicializo array para organizar filtros por tipo */
        foreach ($filtros as $filtro) { /* Recorro todos los filtros */
            $filtros_por_tipo[$filtro['tipo_filtro']][] = $filtro; /* Organizo por tipo de filtro */
        }

    } catch (PDOException $e) { /* Si hay error en cualquier consulta */
        echo "Error al obtener los datos: " . $e->getMessage(); /* Muestro el mensaje de error */
        exit; /* Termino la ejecución */
    }

?>

<link rel="stylesheet" href="../recursos/css/estilos_editar_datos.css" type="text/css"> <!-- Estilos específicos reutilizados de editar_datos -->

<main> <!-- Contenedor principal de la página de editar juego -->
    <section id="editar-datos"> <!-- Sección principal con las tarjetas de edición -->
        <h1>Editar Juego: <?php echo htmlspecialchars($juego['nombre']); ?></h1> <!-- Título principal con el nombre del juego -->

        <!-- Mostrar mensajes de error si existen -->
        <?php if (isset($_SESSION['error_slug_existente'])) { ?> <!-- Si hay error de slug existente -->
            <div class="mensaje-error"> <?php echo htmlspecialchars($_SESSION['error_slug_existente']); ?> </div> <!-- Muestro el error -->
            <?php unset($_SESSION['error_slug_existente']); ?> <!-- Limpio el error de la sesión -->
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

            <div class="tarjeta"> <!-- Tarjeta para la información básica del juego -->
                <h2 class="titulo-tarjeta">Información Básica</h2> <!-- Título de la tarjeta -->
                <div class="contenido-tarjeta"> <!-- Contenido de la tarjeta -->
                    <p class="nombre-completo"> <!-- Párrafo con el nombre del juego -->
                        <?php echo htmlspecialchars($juego['nombre']); ?> <!-- Nombre del juego -->
                    </p>
                    <p class="informacion-adicional"> <!-- Información adicional -->
                        <strong>Slug (URL Amigable):</strong> <?php echo htmlspecialchars($juego['slug']); ?><br> <!-- Slug del juego -->
                        <strong>Precio:</strong> <?php if($juego['precio'] !== '0.00') echo str_replace('.', ',', htmlspecialchars($juego['precio'])) . " €"; else echo "Gratis"; ?><br> <!-- Precio del juego -->
                        <strong>Tipo:</strong> <?php echo htmlspecialchars($juego['tipo'] === 'interno' ? 'Interno (jugable aquí)' : 'Externo (no jugable aquí)'); ?> <!-- Tipo del juego -->
                    </p>
                </div>
                <button class="boton-tarjeta" onclick="abrirModalInfoBasica()">Editar</button> <!-- Botón para abrir el modal de edición -->
            </div>

            <!-- Tarjeta de Detalles y Desarrollo -->
            <div class="tarjeta"> <!-- Tarjeta para detalles de desarrollo -->
                <h2 class="titulo-tarjeta">Desarrollo y Publicación</h2> <!-- Título de la tarjeta -->
                <div class="contenido-tarjeta"> <!-- Contenido de la tarjeta -->
                    <p class="informacion-adicional"> <!-- Información de desarrollo -->
                        <strong>Desarrollador:</strong> <?php echo htmlspecialchars($juego['desarrollador']); ?><br> <!-- Desarrollador -->
                        <strong>Distribuidor:</strong> <?php echo htmlspecialchars($juego['distribuidor']); ?><br> <!-- Distribuidor -->
                        <strong>Fecha de lanzamiento:</strong> <?php echo htmlspecialchars($juego['fecha_lanzamiento']); ?> <!-- Fecha de lanzamiento -->
                    </p>
                </div>
                <button class="boton-tarjeta" onclick="abrirModalDesarrollo()">Editar</button> <!-- Botón para abrir el modal de edición -->
            </div>

            <!-- Tarjeta de Contenido Textual -->
            <div class="tarjeta"> <!-- Tarjeta para el contenido textual del juego -->
                <h2 class="titulo-tarjeta">Descripciones</h2> <!-- Título de la tarjeta -->
                <div class="contenido-tarjeta"> <!-- Contenido de la tarjeta -->
                    <p class="informacion-adicional"> <!-- Información textual -->
                        <strong>Resumen:</strong> <?php echo htmlspecialchars(substr($juego['resumen'], 0, 80)) . '...'; ?><br> <!-- Resumen truncado -->
                        <strong>Descripción:</strong> <?php echo htmlspecialchars(substr($juego['descripcion'], 0, 80)) . '...'; ?><br> <!-- Descripción truncada -->
                        <strong>Requisitos:</strong> <?php echo htmlspecialchars(substr($juego['requisitos'], 0, 80)) . '...'; ?> <!-- Requisitos truncados -->
                    </p>
                </div>
                <button class="boton-tarjeta" onclick="abrirModalContenido()">Editar</button> <!-- Botón para abrir el modal de edición -->
            </div>

            <!-- Tarjeta de Imagen/Portada -->
            <div class="tarjeta"> <!-- Tarjeta para la imagen de portada -->
                <h2 class="titulo-tarjeta">Portada</h2> <!-- Título de la tarjeta -->
                <div class="contenido-tarjeta"> <!-- Contenido de la tarjeta -->
                    <img src="../<?php echo htmlspecialchars($juego['portada']); ?>" alt="Portada" class="imagen-portada-tarjeta"> <!-- Imagen de portada -->
                    <p class="informacion-adicional margen-superior"> <!-- Información de la ruta -->
                        <strong>Ruta:</strong> <?php echo htmlspecialchars($juego['portada']); ?> <!-- Ruta de la portada -->
                    </p>
                </div>
                <button class="boton-tarjeta" onclick="abrirModalPortada()">Cambiar</button> <!-- Botón para abrir el modal de edición -->
            </div>

            <!-- Tarjeta de Filtros -->
            <div class="tarjeta"> <!-- Tarjeta para los filtros del juego -->
                <h2 class="titulo-tarjeta">Categorización</h2> <!-- Título de la tarjeta -->
                <div class="contenido-tarjeta"> <!-- Contenido de la tarjeta -->
                    <p class="informacion-adicional"> <!-- Información de filtros -->
                        <strong>Géneros:</strong> <!-- Subtítulo para géneros -->
                        <?php 
                            $generos_actuales = []; /* Inicializo array para géneros actuales */
                            foreach ($filtros as $filtro) { /* Recorro todos los filtros */
                                if ($filtro['tipo_filtro'] === 'generos' && in_array($filtro['id_fijo'], $filtros_actuales)) { /* Si es género y está asignado al juego */
                                    $generos_actuales[] = htmlspecialchars($filtro['nombre']); /* Agrego el nombre al array */
                                }
                            }
                            echo !empty($generos_actuales) ? implode(', ', $generos_actuales) : 'Ninguno'; /* Muestro los géneros o "Ninguno" si no hay */
                        ?><br> <!-- Géneros actuales -->
                        <strong>Categorías:</strong> <!-- Subtítulo para categorías -->
                        <?php 
                            $categorias_actuales = []; /* Inicializo array para categorías actuales */
                            foreach ($filtros as $filtro) { /* Recorro todos los filtros */
                                if ($filtro['tipo_filtro'] === 'categorias' && in_array($filtro['id_fijo'], $filtros_actuales)) { /* Si es categoría y está asignado al juego */
                                    $categorias_actuales[] = htmlspecialchars($filtro['nombre']); /* Agrego el nombre al array */
                                }
                            }
                            echo !empty($categorias_actuales) ? implode(', ', $categorias_actuales) : 'Ninguna'; /* Muestro las categorías o "Ninguna" si no hay */
                        ?><br> <!-- Categorías actuales -->
                        <strong>Modos:</strong> <!-- Subtítulo para modos -->
                        <?php 
                            $modos_actuales = []; /* Inicializo array para modos actuales */
                            foreach ($filtros as $filtro) { /* Recorro todos los filtros */
                                if ($filtro['tipo_filtro'] === 'modos' && in_array($filtro['id_fijo'], $filtros_actuales)) { /* Si es modo y está asignado al juego */
                                    $modos_actuales[] = htmlspecialchars($filtro['nombre']); /* Agrego el nombre al array */
                                }
                            }
                            echo !empty($modos_actuales) ? implode(', ', $modos_actuales) : 'Ninguno'; /* Muestro los modos o "Ninguno" si no hay */
                        ?><br> <!-- Modos actuales -->
                        <strong>Calificaciones PEGI:</strong> <!-- Subtítulo para calificaciones PEGI -->
                        <?php 
                            $pegi_actuales = []; /* Inicializo array para PEGI actuales */
                            foreach ($filtros as $filtro) { /* Recorro todos los filtros */
                                if ($filtro['tipo_filtro'] === 'clasificacionPEGI' && in_array($filtro['id_fijo'], $filtros_actuales)) { /* Si es calificación PEGI y está asignado al juego */
                                    $pegi_actuales[] = htmlspecialchars($filtro['nombre']); /* Agrego el nombre al array */
                                }
                            }
                            echo !empty($pegi_actuales) ? implode(', ', $pegi_actuales) : 'Ninguna'; /* Muestro las calificaciones PEGI o "Ninguna" si no hay */
                        ?> <!-- Clasificaciones PEGI actuales -->
                    </p>
                </div>
                <button class="boton-tarjeta" onclick="abrirModalFiltros()">Modificar</button> <!-- Botón para abrir el modal de edición -->
            </div>

        </div> <!-- Fin del contenedor de tarjetas -->

        <a href="panel_administrador.php" id="volver-inicio">Volver atrás</a> <!-- Enlace de vuelta al panel -->

    </section> <!-- Fin de la sección principal -->
</main> <!-- Fin del contenedor principal -->

<!-- JavaScript para los modales -->
<script>
    // Función para abrir el modal de información básica
    function abrirModalInfoBasica() {
        var contenido = `
            <h2>Editar Información Básica</h2>
            <form action="../acciones/procesar_editar_juego.php" method="post" id="form-info-basica">
                <input type="hidden" name="id_juego" value="<?php echo $id_juego; ?>">
                <input type="hidden" name="tipo_edicion" value="info_basica">
                
                <label for="nombre">Nombre del juego:</label>
                <input type="text" id="nombre" name="nombre" placeholder="Introduce el nombre del juego" 
                    minlength="2" maxlength="160" 
                    title="Mínimo 2 caracteres, máximo 160" 
                    value="<?php echo htmlspecialchars($juego['nombre']); ?>"
                    required>

                <label for="slug">Slug (URL amigable):</label>
                <input type="text" id="slug" name="slug" placeholder="zelda-breath-wild" 
                    minlength="2" maxlength="160" pattern="[a-z0-9-]+" 
                    title="Solo letras minúsculas, números y guiones, entre 2 y 160 caracteres" 
                    value="<?php echo htmlspecialchars($juego['slug']); ?>"
                    required>

                <label for="precio">Precio (€):</label>
                <input type="number" id="precio" name="precio" placeholder="0.00" 
                    min="0" max="100" step="0.01"
                    title="Precio entre 0 y 100 euros" 
                    value="<?php echo htmlspecialchars($juego['precio']); ?>"
                    required>

                <label for="tipo">Tipo de juego:</label>
                <select id="tipo" name="tipo" required>
                    <?php if(isset($filtros_por_tipo['tipos'])) { /* Verifico que existan filtros de tipo */
                        foreach ($filtros_por_tipo['tipos'] as $filtro) { /* Recorro los filtros de tipo */ ?>
                        <option value="<?php echo $filtro['clave']; ?>"<?php echo ($juego['tipo'] === $filtro['clave']) ? ' selected' : ''; /* Si es el tipo actual, lo selecciono */ ?>>
                            <?php echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre del filtro */ ?>
                        </option>
                    <?php } } ?>
                </select>
            </form>
        `; /* Contenido HTML del modal */
        modal('modal-info-basica', contenido, true); /* Llamo a la función modal para mostrar el modal */
        
        // Agregar evento al botón aceptar
        document.getElementById('aceptar-modal-info-basica').onclick = function() {
            document.getElementById('form-info-basica').submit(); /* Envío el formulario al aceptar */
        };
    }

    // Función para abrir el modal de desarrollo
    function abrirModalDesarrollo() {
        var contenido = `
            <h2>Editar Desarrollo y Publicación</h2>
            <form action="../acciones/procesar_editar_juego.php" method="post" id="form-desarrollo">
                <input type="hidden" name="id_juego" value="<?php echo $id_juego; ?>">
                <input type="hidden" name="tipo_edicion" value="desarrollo">
                
                <label for="desarrollador">Desarrollador:</label>
                <input type="text" id="desarrollador" name="desarrollador" placeholder="Nombre del desarrollador" 
                    maxlength="120" 
                    value="<?php echo htmlspecialchars($juego['desarrollador']); ?>">

                <label for="distribuidor">Distribuidor:</label>
                <input type="text" id="distribuidor" name="distribuidor" placeholder="Nombre del distribuidor" 
                    maxlength="120" 
                    value="<?php echo htmlspecialchars($juego['distribuidor']); ?>">

                <label for="fecha_lanzamiento">Fecha de lanzamiento:</label>
                <input type="date" id="fecha_lanzamiento" name="fecha_lanzamiento"
                    value="<?php echo htmlspecialchars($juego['fecha_lanzamiento']); ?>">
            </form>
        `; /* Contenido HTML del modal */
        modal('modal-desarrollo', contenido, true); /* Llamo a la función modal para mostrar el modal */
        
        // Agregar evento al botón aceptar
        document.getElementById('aceptar-modal-desarrollo').onclick = function() {
            document.getElementById('form-desarrollo').submit(); /* Envío el formulario al aceptar */
        };
    }

    // Función para abrir el modal de contenido textual
    function abrirModalContenido() {
        var contenido = `
            <h2>Editar Descripciones</h2>
            <form action="../acciones/procesar_editar_juego.php" method="post" id="form-contenido">
                <input type="hidden" name="id_juego" value="<?php echo $id_juego; ?>">
                <input type="hidden" name="tipo_edicion" value="contenido">
                
                <label for="resumen">Resumen (breve para listados):</label>
                <textarea id="resumen" name="resumen" placeholder="Descripción breve del juego" 
                    maxlength="255" rows="3" 
                    required><?php echo htmlspecialchars($juego['resumen']); ?></textarea>

                <label for="descripcion">Descripción completa:</label>
                <textarea id="descripcion" name="descripcion" placeholder="Descripción detallada del juego" 
                    rows="6"><?php echo htmlspecialchars($juego['descripcion']); ?></textarea>

                <label for="requisitos">Requisitos del sistema:</label>
                <textarea id="requisitos" name="requisitos" placeholder="Requisitos técnicos necesarios" 
                    rows="4"><?php echo htmlspecialchars($juego['requisitos']); ?></textarea>
            </form>
        `; /* Contenido HTML del modal */
        modal('modal-contenido', contenido, true); /* Llamo a la función modal para mostrar el modal */
        
        // Agregar evento al botón aceptar
        document.getElementById('aceptar-modal-contenido').onclick = function() {
            document.getElementById('form-contenido').submit(); /* Envío el formulario al aceptar */
        };
    }

    // Función para abrir el modal de portada
    function abrirModalPortada() {
        var contenido = `
            <h2>Cambiar Portada</h2>
            <form action="../acciones/procesar_editar_juego.php" method="post" id="form-portada" enctype="multipart/form-data">
                <input type="hidden" name="id_juego" value="<?php echo $id_juego; ?>">
                <input type="hidden" name="tipo_edicion" value="portada">
                
                <label for="portada">Seleccionar imagen de portada:</label>
                <input type="file" id="portada" name="portada" 
                    accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                    title="Selecciona una imagen (JPG, PNG, GIF, WEBP)" 
                    required>
                
                <p class="info-nota">
                    <strong>Portada actual:</strong><br>
                    <img src="../<?php echo htmlspecialchars($juego['portada']); ?>" alt="Portada actual" class="imagen-portada-previa">
                </p>
                
                <p class="info-nota">
                    Formatos permitidos: JPG, PNG, GIF, WEBP<br>
                    La imagen se subirá a: recursos/imagenes/portadas/
                </p>
            </form>
        `; /* Contenido HTML del modal */
        modal('modal-portada', contenido, true); /* Llamo a la función modal para mostrar el modal */
        
        // Agregar evento al botón aceptar
        document.getElementById('aceptar-modal-portada').onclick = function() {
            document.getElementById('form-portada').submit(); /* Envío el formulario al aceptar */
        };
    }

    // Función para abrir el modal de filtros (selects múltiples)
    function abrirModalFiltros() {
        var contenido = `
            <h2>Modificar Categorización</h2>
            <form action="../acciones/procesar_editar_juego.php" method="post" id="form-filtros">
                <input type="hidden" name="id_juego" value="<?php echo $id_juego; ?>">
                <input type="hidden" name="tipo_edicion" value="filtros">
                
                <label for="generos">Géneros (mantén Ctrl/Cmd para seleccionar varios):</label>
                <select id="generos" name="generos[]" multiple size="6" required>
                    <?php if(isset($filtros_por_tipo['generos'])) { /* Verifico que existan filtros de géneros */
                        foreach ($filtros_por_tipo['generos'] as $filtro) { /* Recorro los filtros de géneros */ ?>
                        <option value="<?php echo $filtro['id_fijo']; ?>"<?php echo in_array($filtro['id_fijo'], $filtros_actuales) ? ' selected' : ''; /* Si es un filtro actual, lo selecciono */ ?>>
                            <?php echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre del filtro */ ?>
                        </option>
                    <?php } } ?>
                </select>

                <label for="categorias">Categorías (mantén Ctrl/Cmd para seleccionar varios):</label>
                <select id="categorias" name="categorias[]" multiple size="4" required>
                    <?php if(isset($filtros_por_tipo['categorias'])) { /* Verifico que existan filtros de categorías */
                        foreach ($filtros_por_tipo['categorias'] as $filtro) { /* Recorro los filtros de categorías */ ?>
                        <option value="<?php echo $filtro['id_fijo']; ?>"<?php echo in_array($filtro['id_fijo'], $filtros_actuales) ? ' selected' : ''; /* Si es un filtro actual, lo selecciono */ ?>>
                            <?php echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre del filtro */ ?>
                        </option>
                    <?php } } ?>
                </select>

                <label for="modos">Modos de juego (mantén Ctrl/Cmd para seleccionar varios):</label>
                <select id="modos" name="modos[]" multiple size="4" required>
                    <?php if(isset($filtros_por_tipo['modos'])) { /* Verifico que existan filtros de modos */
                        foreach ($filtros_por_tipo['modos'] as $filtro) { /* Recorro los filtros de modos */ ?>
                        <option value="<?php echo $filtro['id_fijo']; ?>"<?php echo in_array($filtro['id_fijo'], $filtros_actuales) ? ' selected' : ''; /* Si es un filtro actual, lo selecciono */ ?>>
                            <?php echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre del filtro */ ?>
                        </option>
                    <?php } } ?>
                </select>

                <label for="clasificaciones_pegi">Clasificaciones PEGI (mantén Ctrl/Cmd para seleccionar varios):</label>
                <select id="clasificaciones_pegi" name="clasificaciones_pegi[]" multiple size="5" required>
                    <?php if(isset($filtros_por_tipo['clasificacionPEGI'])) { /* Verifico que existan filtros de clasificación PEGI */
                        foreach ($filtros_por_tipo['clasificacionPEGI'] as $filtro) { /* Recorro los filtros de clasificación PEGI */ ?>
                        <option value="<?php echo $filtro['id_fijo']; ?>"<?php echo in_array($filtro['id_fijo'], $filtros_actuales) ? ' selected' : ''; /* Si es un filtro actual, lo selecciono */ ?>>
                            <?php echo htmlspecialchars($filtro['nombre']); /* Muestro el nombre del filtro */ ?>
                        </option>
                    <?php } } ?>
                </select>

                <p class="info-nota">
                    Mantén presionada la tecla Ctrl (Windows/Linux) o Cmd (Mac) para seleccionar múltiples opciones.
                </p>
            </form>
        `; /* Contenido HTML del modal */
        modal('modal-filtros', contenido, true); /* Llamo a la función modal para mostrar el modal */
        
        // Agregar evento al botón aceptar
        document.getElementById('aceptar-modal-filtros').onclick = function() {
            document.getElementById('form-filtros').submit(); /* Envío el formulario al aceptar */
        };
    }
</script>

<!-- Pie de página -->
<?php include __DIR__ . '/../vistas/comunes/pie.php'; ?> <!-- Incluyo el pie de página común -->
