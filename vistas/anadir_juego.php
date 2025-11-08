<?php

    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */
    session_start(); /* Inicio la sesión para acceder a las variables de usuario */

    // Verificar sesión y permisos de administrador
    if(!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
        echo '<script>window.location.href = "../publico/index.php";</script>'; /* Redirijo con JavaScript si no es admin */
        exit; /* Termino la ejecución del script */
    }

    try { /* Inicio bloque try para capturar errores de base de datos */
        // Obtener todos los filtros disponibles
        $consulta = $conexion->query("SELECT id, id_fijo, nombre, tipo_filtro, clave FROM filtros WHERE id > 0 ORDER BY tipo_filtro, orden"); /* Obtengo todos los filtros ordenados */
        $filtros = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Guardo todos los filtros en un array */

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

<!DOCTYPE html>
<html lang="es"> <!-- Documento HTML en español -->

<head>
    <meta charset="UTF-8"> <!-- Codificación de caracteres UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Viewport responsive -->
    <title>CLC Games</title> <!-- Título de la página -->
    <link rel="icon" type="image/x-icon" href="../recursos/imagenes/favicon.ico"> <!-- Favicon del sitio -->
    <link rel="stylesheet" href="../recursos/css/estilos_anadir_juego.css" type="text/css"> <!-- Estilos específicos de la página de añadir juego -->
</head>

<body> <!-- Cuerpo del documento -->
    <main> <!-- Contenedor principal de la página de añadir juego -->
        <section id="anadir-juego"> <!-- Sección principal con el formulario -->
            <h1>Añadir Nuevo Juego</h1> <!-- Título principal -->

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

            <!-- Formulario único con todos los campos -->
            <form action="../acciones/procesar_anadir_juego.php" method="post" enctype="multipart/form-data" id="formulario-anadir-juego" class="formulario-anadir"> <!-- Formulario completo para añadir juego -->
                
                <!-- Sección: Información Básica -->
                <div class="seccion-formulario-anadir"> <!-- Agrupación de campos relacionados -->
                    <h2>Información Básica</h2> <!-- Título de la sección -->
                    
                    <label for="nombre">Nombre del juego:</label> <!-- Etiqueta para el nombre del juego -->
                    <input type="text" id="nombre" name="nombre" placeholder="Introduce el nombre del juego" 
                        minlength="2" maxlength="160" 
                        title="Mínimo 2 caracteres, máximo 160" 
                        required> <!-- Campo de texto para el nombre del juego -->

                    <label for="slug">Slug (URL amigable):</label> <!-- Etiqueta para el slug del juego -->
                    <input type="text" id="slug" name="slug" placeholder="zelda-breath-wild" 
                        minlength="2" maxlength="160" pattern="[a-z0-9-]+" 
                        title="Solo letras minúsculas, números y guiones, entre 2 y 160 caracteres" 
                        required> <!-- Campo de texto para el slug del juego -->

                    <label for="precio">Precio (€):</label> <!-- Etiqueta para el precio del juego -->
                    <input type="number" id="precio" name="precio" placeholder="0,00" 
                        min="0" max="100" step="0.01"
                        title="Precio entre 0 y 100 euros" 
                        value=""
                        required> <!-- Campo de número para el precio del juego -->

                    <label for="tipo">Tipo de juego:</label> <!-- Etiqueta para el tipo de juego -->
                    <select id="tipo" name="tipo" required> <!-- Select para elegir el tipo de juego desde los filtros -->
                        <option value="">-- Selecciona un tipo --</option> <!-- Opción por defecto vacía -->
                        <?php if(isset($filtros_por_tipo['tipos'])) { /* Si existen filtros de tipo */
                            foreach ($filtros_por_tipo['tipos'] as $filtro) { /* Recorro los filtros de tipo */ ?>
                            <option value="<?php echo $filtro['clave']; ?>"> <!-- Opción del select con el valor del filtro -->
                                <?php echo htmlspecialchars($filtro['nombre']); ?> <!-- Nombre escapado del filtro como opción -->
                            </option>
                        <?php } } ?>
                    </select>
                </div>

                <!-- Sección: Desarrollo y Publicación -->
                <div class="seccion-formulario-anadir"> <!-- Agrupación de campos relacionados -->
                    <h2>Desarrollo y Publicación</h2> <!-- Título de la sección -->
                    
                    <label for="desarrollador">Desarrollador:</label> <!-- Etiqueta para el desarrollador -->
                    <input type="text" id="desarrollador" name="desarrollador" placeholder="Nombre del desarrollador" 
                        maxlength="120" required> <!-- Campo de texto para el desarrollador -->

                    <label for="distribuidor">Distribuidor:</label> <!-- Etiqueta para el distribuidor -->
                    <input type="text" id="distribuidor" name="distribuidor" placeholder="Nombre del distribuidor" 
                        maxlength="120" required> <!-- Campo de texto para el distribuidor -->

                    <label for="fecha_lanzamiento">Fecha de lanzamiento:</label> <!-- Etiqueta para la fecha de lanzamiento -->
                    <input type="date" id="fecha_lanzamiento" name="fecha_lanzamiento" required> <!-- Campo de fecha para la fecha de lanzamiento -->
                </div>

                <!-- Sección: Descripciones -->
                <div class="seccion-formulario-anadir"> <!-- Agrupación de campos relacionados -->
                    <h2>Descripciones</h2> <!-- Título de la sección -->
                    
                    <label for="resumen">Resumen (breve para listados):</label> <!-- Etiqueta para el resumen -->
                    <textarea id="resumen" name="resumen" placeholder="Descripción breve del juego" 
                        maxlength="255" rows="3" 
                        required></textarea> <!-- Campo de texto para el resumen del juego -->

                    <label for="descripcion">Descripción completa:</label> <!-- Etiqueta para la descripción completa -->
                    <textarea id="descripcion" name="descripcion" placeholder="Descripción detallada del juego" 
                        rows="6" required></textarea> <!-- Campo de texto para la descripción completa del juego -->

                    <label for="requisitos">Requisitos del sistema:</label> <!-- Etiqueta para los requisitos del sistema -->
                    <textarea id="requisitos" name="requisitos" placeholder="Requisitos técnicos necesarios" 
                        rows="4" required></textarea> <!-- Campo de texto para los requisitos del sistema -->
                </div>

                <!-- Sección: Portada -->
                <div class="seccion-formulario-anadir"> <!-- Agrupación de campos relacionados -->
                    <h2>Portada</h2> <!-- Título de la sección -->
                    
                    <label for="portada">Seleccionar imagen de portada:</label> <!-- Etiqueta para la portada -->
                    <input type="file" id="portada" name="portada" 
                        accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                        title="Selecciona una imagen (JPG, PNG, GIF, WEBP)" 
                        required> <!-- Campo de archivo para la portada del juego -->
                    
                    <p class="info-nota-anadir"> <!-- Nota informativa -->
                        Formatos permitidos: JPG, PNG, GIF, WEBP<br>
                        La imagen se subirá a: recursos/imagenes/portadas/
                    </p>
                </div>

                <!-- Sección: Categorización -->
                <div class="seccion-formulario-anadir"> <!-- Agrupación de campos relacionados -->
                    <h2>Categorización</h2> <!-- Título de la sección -->
                    
                    <label for="generos">Géneros (mantén Ctrl/Cmd para seleccionar varios):</label> <!-- Etiqueta para los géneros -->
                    <select id="generos" name="generos[]" multiple size="6" required> <!-- Select para elegir varios géneros -->
                        <?php if(isset($filtros_por_tipo['generos'])) { /* Si existen filtros de géneros */
                            foreach ($filtros_por_tipo['generos'] as $filtro) { /* Iterar sobre los filtros de géneros */ ?>
                            <option value="<?php echo $filtro['id_fijo']; ?>"> <!-- Opción del select con el valor del filtro -->
                                <?php echo htmlspecialchars($filtro['nombre']); ?> <!-- Nombre escapado del filtro como opción -->
                            </option>
                        <?php } } ?>
                    </select>

                    <label for="categorias">Categorías (mantén Ctrl/Cmd para seleccionar varios):</label> <!-- Etiqueta para las categorías -->
                    <select id="categorias" name="categorias[]" multiple size="4" required> <!-- Select para elegir varias categorías -->
                        <?php if(isset($filtros_por_tipo['categorias'])) { /* Si existen filtros de categorías */
                            foreach ($filtros_por_tipo['categorias'] as $filtro) { /* Iterar sobre los filtros de categorías */ ?>
                            <option value="<?php echo $filtro['id_fijo']; ?>"> <!-- Opción del select con el valor del filtro -->
                                <?php echo htmlspecialchars($filtro['nombre']); ?> <!-- Nombre escapado del filtro como opción -->
                            </option>
                        <?php } } ?>
                    </select>

                    <label for="modos">Modos de juego (mantén Ctrl/Cmd para seleccionar varios):</label> <!-- Etiqueta para los modos de juego -->
                    <select id="modos" name="modos[]" multiple size="4" required> <!-- Select para elegir varios modos de juego -->
                        <?php if(isset($filtros_por_tipo['modos'])) { /* Si existen filtros de modos de juego */
                            foreach ($filtros_por_tipo['modos'] as $filtro) { /* Iterar sobre los filtros de modos de juego */ ?>
                            <option value="<?php echo $filtro['id_fijo']; ?>"> <!-- Opción del select con el valor del filtro -->
                                <?php echo htmlspecialchars($filtro['nombre']); ?> <!-- Nombre escapado del filtro como opción -->
                            </option>
                        <?php } } ?>
                    </select>

                    <label for="clasificaciones_pegi">Clasificaciones PEGI (mantén Ctrl/Cmd para seleccionar varios):</label> <!-- Etiqueta para las clasificaciones PEGI -->
                    <select id="clasificaciones_pegi" name="clasificaciones_pegi[]" multiple size="5" required> <!-- Select para elegir varias clasificaciones PEGI -->
                        <?php if(isset($filtros_por_tipo['clasificacionPEGI'])) { /* Si existen filtros de clasificaciones PEGI */
                            foreach ($filtros_por_tipo['clasificacionPEGI'] as $filtro) { /* Iterar sobre los filtros de clasificaciones PEGI */ ?>
                            <option value="<?php echo $filtro['id_fijo']; ?>"> <!-- Opción del select con el valor del filtro -->
                                <?php echo htmlspecialchars($filtro['nombre']); ?> <!-- Nombre escapado del filtro como opción -->
                            </option>
                        <?php } } ?>
                    </select>

                    <p class="info-nota-anadir"> <!-- Nota informativa -->
                        Mantén presionada la tecla Ctrl (Windows/Linux) o Cmd (Mac) para seleccionar múltiples opciones.
                    </p>
                </div>

                <!-- Botón para enviar el formulario -->
                <button type="submit" class="boton-anadir-juego">Añadir Juego</button> <!-- Botón de envío -->
            </form>

            <a href="panel_administrador.php" id="volver-panel-admin" class="enlace-volver-panel">Volver al panel</a> <!-- Enlace de vuelta al panel -->

        </section> <!-- Fin de la sección principal -->
    </main> <!-- Fin del contenedor principal -->
</body>
</html>

