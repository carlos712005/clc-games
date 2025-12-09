    <!-- Encabezado -->
    <?php include __DIR__ . '/../vistas/comunes/encabezado.php'; ?> <!-- Incluyo el encabezado con menú y estilos -->

    <?php
    // Verificar sesión y permisos de administrador
    if(!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
        echo '<script>window.location.href = "../publico/index.php";</script>'; /* Redirijo con JavaScript si no es admin */
        exit; /* Termino la ejecución del script */
    }

    // Verificar si se ha proporcionado un ID de usuario válido
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { /* Verifico que llegue un ID válido por GET */
        header('Location: ../vistas/panel_administrador.php'); /* Si no hay ID válido, redirijo al panel */
        exit; /* Termino la ejecución */
    }

    $id_usuario = (int)$_GET['id']; /* Convierto el ID a entero para seguridad */
    $_SESSION['id_usuario_buscado'] = $id_usuario; /* Guardo el ID del usuario buscado */

    try { /* Inicio bloque try para capturar errores de base de datos */
        // Obtener los datos del usuario
        $consulta = $conexion->prepare("SELECT * FROM usuarios WHERE id = :id_usuario"); /* Preparo consulta para obtener todos los datos del usuario */
        $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID del usuario */
        $consulta->execute(); /* Ejecuto la consulta */
        $usuario = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del usuario como array asociativo */

        if (!$usuario) { /* Si no se encontró el usuario */
            echo '<script>window.location.href = "../vistas/panel_administrador.php";</script>'; /* Redirijo al panel */
            exit; /* Termino la ejecución */
        }

        // Obtener el nombre del rol del usuario
        $consulta = $conexion->prepare("SELECT nombre FROM roles WHERE id = :id_rol"); /* Preparo consulta para obtener nombre del rol */
        $consulta->bindParam(':id_rol', $usuario['id_rol'], PDO::PARAM_INT); /* Vinculo el ID del rol */
        $consulta->execute(); /* Ejecuto la consulta */
        $nombre_rol = $consulta->fetchColumn(); /* Obtengo solo el nombre del rol */

        // Obtener las preferencias del usuario
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
        $preferencias_usuario = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todas las preferencias */

        // Organizar preferencias por tipo
        $preferencias_por_tipo = []; /* Inicializo array para organizar preferencias por tipo */
        foreach ($preferencias_usuario as $preferencia) { /* Recorro cada preferencia */
            $preferencias_por_tipo[$preferencia['tipo_filtro']] = $preferencia['nombre']; /* Organizo por tipo de filtro */
        }

        // Contar juegos en biblioteca del usuario
        $consulta = $conexion->prepare("SELECT COUNT(*) FROM biblioteca WHERE id_usuario = :id_usuario"); /* Preparo consulta para contar juegos */
        $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID del usuario */
        $consulta->execute(); /* Ejecuto la consulta */
        $total_biblioteca = $consulta->fetchColumn(); /* Obtengo el total de juegos en biblioteca */

        // Contar juegos en carrito del usuario
        $consulta = $conexion->prepare("SELECT COUNT(*) FROM carrito WHERE id_usuario = :id_usuario"); /* Preparo consulta para contar juegos en carrito */
        $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID del usuario */
        $consulta->execute(); /* Ejecuto la consulta */
        $total_carrito = $consulta->fetchColumn(); /* Obtengo el total de juegos en carrito */

        // Contar juegos en favoritos del usuario
        $consulta = $conexion->prepare("SELECT COUNT(*) FROM favoritos WHERE id_usuario = :id_usuario"); /* Preparo consulta para contar juegos en favoritos */
        $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID del usuario */
        $consulta->execute(); /* Ejecuto la consulta */
        $total_favoritos = $consulta->fetchColumn(); /* Obtengo el total de juegos en favoritos */

        // Contar registros en historial del usuario
        $consulta = $conexion->prepare("SELECT COUNT(*) FROM historial WHERE id_usuario = :id_usuario"); /* Preparo consulta para contar registros en historial */
        $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID del usuario */
        $consulta->execute(); /* Ejecuto la consulta */
        $total_historial = $consulta->fetchColumn(); /* Obtengo el total de registros en historial */

        // Obtener todos los filtros disponibles
        $consulta = $conexion->query("SELECT id, id_fijo, nombre, tipo_filtro, clave FROM filtros WHERE id > 0"); /* Obtengo todos los filtros de la base de datos */
        $filtros = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Guardo todos los filtros en un array */

    } catch (PDOException $e) { /* Si hay error en cualquier consulta */
        echo "Error al obtener los datos: " . $e->getMessage(); /* Muestro el mensaje de error */
        exit; /* Termino la ejecución */
    }

    // Determinar la URL de regreso (referer)
    if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'detalles_usuario.php') === false) { /* Si hay una página anterior y no es otra página de detalles */
        $_SESSION['referer'] = $_SERVER['HTTP_REFERER']; /* Guardo la página de origen en sesión para poder volver */
    }

    // Si no hay referer, ir al panel de administrador
    $rutaRegreso = $_SESSION['referer'] ?? '../vistas/panel_administrador.php'; /* Si no hay página anterior, uso el panel por defecto */
    ?>

    <!-- Estilos específicos para detalles de usuario -->
    <link rel="stylesheet" href="../recursos/css/estilos_detalles_usuario.css" type="text/css">

    <main> <!-- Contenedor principal de la página -->
        <h1><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></h1> <!-- Título con nombre completo del usuario -->
        <h2 class="acronimo-usuario">@<?php echo htmlspecialchars($usuario['acronimo']); ?></h2> <!-- Acrónimo del usuario -->

        <div class="detalles-contenido"> <!-- Contenedor principal del contenido -->
            
            <!-- Información general del usuario -->
            <div class="info-general"> <!-- Contenedor para la información básica del usuario -->
                <h3>Información Personal</h3> <!-- Subtítulo -->
                <p><strong>ID:</strong> <?php echo htmlspecialchars($usuario['id']); ?></p> <!-- ID del usuario -->
                <p><strong>Nombre completo:</strong> <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></p> <!-- Nombre completo -->
                <p><strong>Nombre de usuario:</strong> <?php echo htmlspecialchars($usuario['acronimo']); ?></p> <!-- Acrónimo -->
                <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p> <!-- Email del usuario -->
                <p><strong>DNI:</strong> <?php echo htmlspecialchars($usuario['dni']); ?></p> <!-- DNI del usuario -->
                <p><strong>Rol:</strong> <?php echo htmlspecialchars($nombre_rol); ?></p> <!-- Rol del usuario -->
                <p><strong>Fecha de creación:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($usuario['creado_en']))); ?></p> <!-- Fecha de creación formateada, usando date (me permite formatear la fecha) y strtotime (me permite convertir la fecha a timestamp) -->
                <p><strong>Última actualización:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($usuario['actualizado_en']))); ?></p> <!-- Fecha de última actualización formateada, usando date (me permite formatear la fecha) y strtotime (me permite convertir la fecha a timestamp) -->
                <p><strong>Último acceso:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($usuario['ultimo_acceso']))); ?></p> <!-- Fecha de último acceso formateada, usando date (me permite formatear la fecha) y strtotime (me permite convertir la fecha a timestamp) -->
            </div>

            <!-- Estadísticas del usuario -->
            <div class="estadisticas-usuario"> <!-- Contenedor para las estadísticas -->
                <h3>Estadísticas</h3> <!-- Subtítulo -->
                <p><strong>Juegos en biblioteca:</strong> <?php echo $total_biblioteca; ?></p> <!-- Total de juegos en biblioteca -->
                <p><strong>Juegos en carrito:</strong> <?php echo $total_carrito; ?></p> <!-- Total de juegos en carrito -->
                <p><strong>Juegos en favoritos:</strong> <?php echo $total_favoritos; ?></p> <!-- Total de juegos en favoritos -->
                <p><strong>Registros en historial:</strong> <?php echo $total_historial; ?></p> <!-- Total de registros en historial -->
            </div>

        </div>

        <hr> <!-- Línea separadora -->
        <br> <!-- Espacio adicional -->

        <!-- Preferencias del usuario -->
        <div class="preferencias-usuario"> <!-- Contenedor para las preferencias -->
            <h2>Preferencias de Juegos</h2> <!-- Título de la sección -->
            <div class="lista-preferencias"> <!-- Lista de preferencias -->
                <p><strong>Género preferido:</strong> <?php /* Subtítulo para el género preferido */
                    echo isset($preferencias_por_tipo['generos']) ? htmlspecialchars($preferencias_por_tipo['generos']) : 'Todos los géneros'; /* Mostrar género preferido o mensaje por defecto */
                ?></p> <!-- Género preferido -->

                <p><strong>Categoría preferida:</strong> <?php /* Subtítulo para la categoría preferida */
                    echo isset($preferencias_por_tipo['categorias']) ? htmlspecialchars($preferencias_por_tipo['categorias']) : 'Todas las categorías';  /* Mostrar categoría preferida o mensaje por defecto */
                ?></p> <!-- Categoría preferida -->

                <p><strong>Modo de juego preferido:</strong> <?php /* Subtítulo para el modo de juego preferido */
                    echo isset($preferencias_por_tipo['modos']) ? htmlspecialchars($preferencias_por_tipo['modos']) : 'Todos los modos';   /* Mostrar modo preferido o mensaje por defecto */
                ?></p> <!-- Modo preferido -->
                
                <p><strong>Clasificación PEGI preferida:</strong> <?php /* Subtítulo para la clasificación PEGI preferida */
                    echo isset($preferencias_por_tipo['clasificacionPEGI']) ? htmlspecialchars($preferencias_por_tipo['clasificacionPEGI']) : 'Todas las clasificaciones'; /* Mostrar clasificación PEGI preferida o mensaje por defecto */
                ?></p> <!-- Clasificación PEGI preferida -->
            </div>
        </div>

        <hr> <!-- Línea separadora -->

        <!-- Opciones de administración -->
        <div class="opciones"> <!-- Contenedor para las opciones de acción -->
            <h2>Opciones de Administración:</h2> <!-- Título de la sección de opciones -->

            <a href="../publico/biblioteca.php?id=<?php echo $usuario['id']; ?>" class="boton-ver-biblioteca"> <!-- Enlace para ver biblioteca del usuario -->
                <img src="../recursos/imagenes/biblioteca.png" alt="Icono de Biblioteca" id="icono-biblioteca"> <!-- Icono de biblioteca -->
                <span>Ver biblioteca</span> <!-- Texto del botón -->
            </a>

            <a href="../publico/favoritos.php?id=<?php echo $usuario['id']; ?>" class="boton-ver-favoritos"> <!-- Enlace para ver favoritos del usuario -->
                <img src="../recursos/imagenes/favoritos_circulo.png" alt="Icono de Favoritos" id="icono-favoritos"> <!-- Icono de favoritos -->
                <span>Ver favoritos</span> <!-- Texto del botón -->
            </a>

            <a href="../publico/historial.php?id=<?php echo $usuario['id']; ?>" class="boton-ver-historial"> <!-- Enlace para ver historial del usuario -->
                <img src="../recursos/imagenes/historial.png" alt="Icono de Historial" id="icono-historial"> <!-- Icono de historial -->
                <span>Ver historial</span> <!-- Texto del botón -->
            </a>

            <a href="../publico/editar_datos.php?id=<?php echo $usuario['id']; ?>" class="boton-editar-usuario"> <!-- Enlace para editar usuario -->
                <img src="../recursos/imagenes/editar.png" alt="Icono de Editar" id="icono-editar"> <!-- Icono de editar -->
                <span>Editar datos</span> <!-- Texto del botón -->
            </a>

            <a href="#" onclick="eliminarUsuario(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['acronimo'], ENT_QUOTES); ?>')" class="boton-eliminar-usuario"> <!-- Enlace para eliminar usuario -->
                <img src="../recursos/imagenes/eliminar_usuario.png" alt="Icono de Eliminar" id="icono-eliminar"> <!-- Icono de eliminar -->
                <span>Eliminar usuario</span> <!-- Texto del botón -->
            </a>
        </div>

    </main>

    <br> <!-- Espacio antes del pie de página -->

    <script src="../recursos/js/administrador.js" defer></script> <!-- Cargo el JavaScript del administrador con defer -->

    <!-- Pie de página -->
    <?php include __DIR__ . '/../vistas/comunes/pie.php'; ?> <!-- Incluyo el pie de página común -->
