<?php

    require_once __DIR__ . "/../../config/conexion.php"; /* Incluyo la conexión a la base de datos desde dos niveles arriba */
    require_once __DIR__ . "/../../funciones/funciones_notificaciones.php"; /* Incluyo las funciones de notificaciones */
    session_start(); /* Inicio la sesión para acceder a las variables de usuario */

    // Verificar si el usuario no está en modo administrador
    if(!isset($_SESSION['modo_admin'])) {
        $_SESSION['modo_admin'] = false; /* Indico que no estamos en modo administrador por defecto */
    }

    // Verificar si el usuario es administrador y está en el panel de administrador
    if(isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1 && basename($_SERVER['PHP_SELF']) === 'panel_administrador.php') {
        $_SESSION['modo_admin'] = true; /* Indico que estamos en modo administrador */
    }

    try { /* Inicio bloque try para capturar errores relacionados con la base de datos */
        // Obtener categorías para el formulario de registro
        $consulta = $conexion->prepare("SELECT id, id_fijo, nombre, tipo_filtro, clave FROM filtros WHERE id > 0"); /* Obtengo todos los filtros de la base de datos */
        $consulta->execute(); /* Ejecuto la consulta */
        $categorias = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Guardo todos los filtros en un array asociativo */

        // Obtener usuarios, roles y artículos en el carrito para uso general
        $consulta = $conexion->prepare("SELECT * FROM usuarios"); /* Preparo la consulta */
        $consulta->execute(); /* Ejecuto la consulta */
        $usuarios_bd = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Guardo todos los usuarios en un array asociativo */

        $consulta = $conexion->prepare("SELECT * FROM roles"); /* Preparo la consulta */
        $consulta->execute(); /* Ejecuto la consulta */
        $roles_bd = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Guardo todos los roles en un array asociativo */

        // Obtener todos los historiales de compras
        $consulta = $conexion->prepare("
            SELECT 
                h.total,
                hc.precio,
                hc.estado AS estado_detalle,
                hc.comentario AS comentario_detalle,
                u.nombre AS usuario_nombre,
                u.apellidos AS usuario_apellidos,
                u.acronimo AS usuario_acronimo
            FROM historial h
            LEFT JOIN historial_compras hc ON hc.id_historial = h.id
            LEFT JOIN usuarios u ON u.id = h.id_usuario
        "); /* Preparo consulta para obtener el historial de compras general, incluyendo datos de usuario */
        $consulta->execute(); /* Ejecuto la consulta */
        $historial_general = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Guardo todo el historial en un array asociativo */

        // Calcular el total mínimo y máximo del historial
        $totales = array_column($historial_general, 'total'); /* Extraigo solo los totales */
        $total_min_historial = !empty($totales) ? min($totales) : 0; /* Obtengo el mínimo o 0 si está vacío */
        $total_max_historial = !empty($totales) ? max($totales) : 100; /* Obtengo el máximo o 100 si está vacío */

        $consulta = $conexion->prepare("SELECT * FROM carrito WHERE id_usuario = :id_usuario"); /* Preparo la consulta */
        $consulta->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
        $consulta->execute(); /* Ejecuto la consulta */

        $articulos_carrito = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los artículos en el carrito */
        $_SESSION['cantidad_carrito'] = count($articulos_carrito); /* Guardo la cantidad de artículos en el carrito en una variable de sesión */

        $consulta = $conexion->prepare("SELECT * FROM notificaciones WHERE leido = 0 AND id_usuario = :id_usuario"); /* Preparo la consulta */
        $consulta->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
        $consulta->execute(); /* Ejecuto la consulta */

        $cantidad_notificaciones = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los artículos en el carrito */
        $_SESSION['notificaciones_sin_leer'] = contarNotificacionesNoLeidas($conexion, $_SESSION['id_usuario']); /* Guardo la cantidad de notificaciones no leídas en una variable de sesión */
    } catch (PDOException $e) { /* Si hay error al obtener los datos */
        echo "Error al obtener los datos: " . $e->getMessage(); /* Muestro el error */
        exit; /* Termino la ejecución */
    }
?>

<!DOCTYPE html>
<html lang="es"> <!-- Documento HTML en español -->

<head>
    <!-- Metadatos y configuración básica -->
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Configuración de codificación y viewport responsive -->
    <title>CLC Games</title> <!-- Título de la página -->

    <!-- Icono y estilos -->
    <link rel="icon" type="image/x-icon" href="../recursos/imagenes/favicon.ico"> <!-- Favicon del sitio -->
    <link rel="stylesheet" href="../recursos/css/estilos_encabezado.css" type="text/css"> <!-- Estilos específicos del encabezado -->
    <link rel="stylesheet" href="../recursos/css/estilos_pie.css" type="text/css"> <!-- Estilos del pie de página -->
    <link rel="stylesheet" href="../recursos/css/estilos_modal.css" type="text/css"> <!-- Estilos generales de los modales -->
    <link rel="stylesheet" href="../recursos/css/estilos_carrito.css" type="text/css"> <!-- Estilos específicos del carrito -->

    <!-- Pasar datos de PHP a JavaScript -->
    <script> // Inicio del script para pasar datos PHP a JavaScript
        // Crear variable global con las preferencias del usuario
        window.preferenciasUsuario = <?php echo json_encode(isset($_SESSION['preferencias_usuario']) ? $_SESSION['preferencias_usuario'] : []); ?>; /* Paso las preferencias del usuario o un array vacío a JavaScript */
        
        // Crear variable global con los filtros elegidos
        window.filtrosElegidos = <?php echo json_encode(isset($_SESSION['filtros_elegidos']) ? $_SESSION['filtros_elegidos'] : []); ?>; /* Paso los filtros elegidos o un array vacío a JavaScript */
        // Crear variable global con el modo de edición
        window.modoEdicion = <?php echo json_encode(isset($_SESSION['modo_edicion']) ? $_SESSION['modo_edicion'] : 'juegos'); ?>; /* Paso el modo de edición guardado */
    
        // Crear variable global con los filtros de usuarios
        window.filtrosUsuarios = <?php echo json_encode(isset($_SESSION['filtros_usuarios']) ? $_SESSION['filtros_usuarios'] : []); ?>; /* Paso los filtros de usuarios o un array vacío a JavaScript */
    
        // Crear variable global con la página actual
        window.paginaActual = "<?php echo basename($_SERVER['PHP_SELF']); ?>"; /* Paso el nombre de la página actual a JavaScript */
    
        // Crear variable global con el modo administrador
        window.modoAdmin = <?php echo json_encode(isset($_SESSION['modo_admin']) ? $_SESSION['modo_admin'] : false); ?>; /* Paso el modo administrador a JavaScript */
    
        // Crear variable global con el ID del usuario buscado
        window.idUsuarioBuscado = <?php echo json_encode(isset($_SESSION['id_usuario_buscado']) ? $_SESSION['id_usuario_buscado'] : null); ?>; /* Paso el ID del usuario buscado a JavaScript */
    
        // Crear variables globales con los rangos de totales del historial
        window.totalMinHistorial = <?php echo json_encode($total_min_historial); ?>; /* Paso el total mínimo del historial */
        window.totalMaxHistorial = <?php echo json_encode($total_max_historial); ?>; /* Paso el total máximo del historial */
    
        // Crear variable global con los filtros de pedidos
        window.filtrosPedidos = <?php echo json_encode(isset($_SESSION['filtros_pedidos']) ? $_SESSION['filtros_pedidos'] : []); ?>; /* Paso los filtros de pedidos o un array vacío a JavaScript */
    </script> <!-- Fin del script inline -->

    <!-- Script para el menú dinámico -->
    <script src="../recursos/js/menu_filtros.js" defer></script> <!-- Cargo el JavaScript del menú de filtros con defer -->

    <!-- Scripts para modales -->
    <script src="../recursos/js/modal.js" defer></script> <!-- Cargo el JavaScript de modales con defer -->

    <!-- Scripts para el carrito -->
    <script src="../recursos/js/carrito.js" defer></script> <!-- Cargo el JavaScript del carrito con defer -->

    <!-- Scripts para el administrador -->
    <script src="../recursos/js/administrador.js" defer></script> <!-- Cargo el JavaScript del administrador con defer -->
    
    <!-- Script para buscador textual -->
    <script src="../recursos/js/buscador.js" defer></script> <!-- Buscador integrado con filtros -->

    <!-- Librerías locales para generar PDFs desde el navegador -->
    <script src="../recursos/js/librerias/jspdf.umd.min.js"></script>
    <script src="../recursos/js/librerias/jspdf.plugin.autotable.min.js"></script>

</head>

<body> <!-- Inicio del cuerpo del documento -->

    <!-- Encabezado -->
    <header class="encabezado" role="banner"> <!-- Encabezado principal con rol de banner (para accesibilidad) -->

        <!-- Barra superior: Menú | Logo+Título | Buscador | Acciones -->
        <div class="barra-encabezado"> <!-- Contenedor de la barra superior del encabezado -->
            
            <div class="zona-izquierda"> <!-- Zona izquierda para el botón del menú o el boton de volver al panel (si estamos en modo admin)-->
                <?php if(!((basename($_SERVER['PHP_SELF']) === 'editar_datos.php' || basename($_SERVER['PHP_SELF']) === 'detalles_juego.php' 
                                || basename($_SERVER['PHP_SELF']) === 'editar_juego.php' || basename($_SERVER['PHP_SELF']) === 'historial.php'
                                || basename($_SERVER['PHP_SELF']) === 'detalles_usuario.php' || basename($_SERVER['PHP_SELF']) === 'favoritos.php'
                                || basename($_SERVER['PHP_SELF']) === 'biblioteca.php')
                            && isset($_SESSION['modo_admin']) && $_SESSION['modo_admin'] === true)) { ?> <!-- Si no estamos en una página de edición y no somos admin -->
                    <!-- Botón Menú (abre lateral) -->
                    <label for="abrir-menu" class="boton-menu" title="Abrir menú" id="menu-con-notificaciones" style="display: <?php echo (isset($_SESSION['notificaciones_sin_leer']) && $_SESSION['notificaciones_sin_leer'] > 0) ? 'inline-flex' : 'none'; ?>;"> <!-- Etiqueta que activa el checkbox del menú -->
                        <span> <!-- Contenedor del icono y contador de notificaciones -->
                            <img src="../recursos/imagenes/menu.png" alt="Menú" id="icono-menu"> <!-- Icono del menú hamburguesa -->
                            <span id="cantidad-notificaciones" class="cantidad-notificaciones" aria-label="Notificaciones"><?php echo $_SESSION['notificaciones_sin_leer']; ?></span> <!-- Contador de notificaciones -->
                        </span>
                        <span>Menú</span> <!-- Texto del botón de menú -->
                    </label>
                    <label for="abrir-menu" class="boton-menu" title="Abrir menú" id="menu-sin-notificaciones" style="display: <?php echo (!isset($_SESSION['notificaciones_sin_leer']) || $_SESSION['notificaciones_sin_leer'] == 0) ? 'inline-flex' : 'none'; ?>;"> <!-- Etiqueta que activa el checkbox del menú -->
                        <img src="../recursos/imagenes/menu.png" alt="Menú" id="icono-menu"> <!-- Icono del menú hamburguesa -->
                        <span>Menú</span> <!-- Texto del botón de menú -->
                    </label>
                <?php } else { ?> <!-- Si estamos en modo administrador en una página de edición -->
                    <a href="../acciones/limpiar_busqueda.php?ir_a=../vistas/panel_administrador.php" class="boton-volver-atras" title="Volver al panel"> <!-- Enlace para volver al panel -->
                        <img src="../recursos/imagenes/atras.png" alt="Volver" id="icono-volver"> <!-- Icono de volver al panel -->
                        <span>Volver al panel</span> <!-- Texto del botón volver al panel -->
                    </a>
                <?php } ?>
            </div> <!-- Fin de la zona izquierda -->

            <!-- Logo + Título -->
            <div class="zona-identidad"> <!-- Zona central para logo y título -->
            <a href="../publico/index.php"> <!-- Enlace al índice principal -->
                <img src="../recursos/imagenes/logo.png" alt="Logo CLC Games" id="logo-sitio"> <!-- Logo principal del sitio -->
            </a>
            <h1 class="titulo-sitio">CLC Games</h1> <!-- Título principal del sitio -->
            </div> <!-- Fin de la zona de identidad -->

            <!-- Buscador GRANDE -->
            <div class="zona-buscador"> <!-- Zona del buscador principal -->
            <form class="formulario-busqueda" role="search" aria-label="Buscar"> <!-- Formulario de búsqueda -->
                <input type="text" id="cuadro-busqueda" name="q" 
                    placeholder="<?php echo basename($_SERVER['PHP_SELF']) !== 'historial.php' ? 'Buscar juegos, categorías...' : 'Buscar pedidos, devoluciones...'; ?>" 
                    autocomplete="off" <?php echo isset($_SESSION['texto_busqueda']) ? 'value="' . htmlspecialchars($_SESSION['texto_busqueda']) . '"' : ''; ?>> <!-- Campo de texto para búsquedas -->
                <button type="submit" id="boton-buscar" class="boton-lupa" aria-label="Buscar"> <!-- Botón para enviar la búsqueda -->
                    <img src="../recursos/imagenes/lupa.png" alt="Buscar" id="icono-lupa"> <!-- Icono de lupa para el botón de búsqueda -->
                </button>
                <button type="button" id="boton-limpiar-busqueda" class="boton-lupa" aria-label="Limpiar búsqueda" onclick="eliminarBusqueda()"> <!-- Botón para limpiar la búsqueda -->
                    <img src="../recursos/imagenes/eliminar_busqueda.png" alt="Eliminar búsqueda" id="icono-lupa"> <!-- Icono de x para el botón de limpiar búsqueda -->
                </button>
            </form> <!-- Fin del formulario de búsqueda -->
            </div> <!-- Fin de la zona del buscador -->

            <!-- Login + Carrito o + Añadir usuario o juego (si es admin) -->
            <div class="zona-derecha"> <!-- Zona derecha para acciones de usuario -->
            <?php 
                if (isset($_SESSION['id_usuario'])) { /* Si hay un usuario logueado */
                    if(isset($_SESSION['id_rol']) && ($_SESSION['id_rol'] == 2 || $_SESSION['id_rol'] == 1)){ /* Si el usuario es un cliente normal (rol 2) */
            ?>
                        <a href="../publico/editar_datos.php" class="boton-editar" title="Editar mis datos"> <!-- Enlace para editar datos del usuario -->
                            <img src="../recursos/imagenes/editar.png" alt="Editar" id="icono-editar"> <!-- Icono de editar -->
                            <span><?php echo htmlspecialchars($_SESSION['acronimo_usuario']); ?></span> <!-- Nombre de usuario escapado -->
                        </a>
                        <a href="../sesiones/cerrar_sesion.php" class="boton-salir" title="Cerrar sesión"> <!-- Enlace para cerrar sesión -->
                            <img src="../recursos/imagenes/salir.png" alt="salir" id="icono-salir"> <!-- Icono de salir -->
                            <span>Salir</span> <!-- Texto del botón salir -->
                        </a>
            <?php 
                    } /* Fin del condicional de rol de cliente */
                } else { ?> <!-- Si no hay usuario logueado -->
                    <a href="../sesiones/formulario_autenticacion.php" class="boton-login" title="Iniciar sesión o registrarse"> <!-- Enlace al formulario de login -->
                        <img src="../recursos/imagenes/login.png" alt="Login" id="icono-login"> <!-- Icono de login -->
                        <span>Registrarse</span> <!-- Texto del botón de registro -->
                    </a>
            <?php }?> <!-- Fin del condicional de usuario logueado -->

            <?php if(basename($_SERVER['PHP_SELF']) !== 'historial.php' 
                        && basename($_SERVER['PHP_SELF']) !== 'mapa.php'
                        && basename($_SERVER['PHP_SELF']) !== 'panel_administrador.php' 
                        && basename($_SERVER['PHP_SELF']) !== 'editar_datos.php' 
                        && $_SESSION['modo_admin'] === false) { ?> <!-- Si no estamos en estas páginas específicas y no somos admin -->
                <a <?php echo isset($_SESSION['id_usuario']) ? 'href="#" onclick="mostrarCarrito();"' : 'href="../sesiones/formulario_autenticacion.php"'; ?> class="boton-carrito" title="Ver carrito"> <!-- Enlace al carrito o login -->
                    <span> <!-- Contenedor del icono y contador del carrito -->
                        <img src="../recursos/imagenes/carrito.png" alt="Carrito" id="icono-carrito"> <!-- Icono del carrito -->
                        <span id="cantidad-carrito" class="cantidad-carrito" aria-label="Artículos en el carrito"><?php echo $_SESSION['cantidad_carrito']; ?></span> <!-- Contador de artículos en el carrito -->
                    </span>
                    <span>Carrito</span> <!-- Texto del botón carrito -->
                </a>
            <?php } ?> <!-- Fin del condicional -->

            <?php if(basename($_SERVER['PHP_SELF']) === 'panel_administrador.php' && isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) { ?> <!-- Si estamos en la página de panel de administrador y el usuario es admin -->
                <a href="anadir_juego.php" id="boton-anadir-juego" title="Añadir juego"> <!-- Enlace para añadir juego -->
                    <img src="../recursos/imagenes/anadir_juego.png" alt="Añadir" id="icono-anadir"> <!-- Icono de añadir -->
                    <span>Añadir juego</span> <!-- Texto del botón añadir juego -->
                </a>
                <a href="../sesiones/registro.php" id="boton-anadir-usuario" title="Añadir usuario"> <!-- Enlace para añadir usuario -->
                    <img src="../recursos/imagenes/anadir_usuario.png" alt="Añadir" id="icono-anadir"> <!-- Icono de añadir -->
                    <span>Añadir usuario</span> <!-- Texto del botón añadir usuario -->
                </a>
                <a href="#" id="boton-descargar-pdf" title="Descargar PDF"> <!-- Enlace para descargar PDF -->
                    <img src="../recursos/imagenes/descargar_pdf.png" alt="Descargar PDF" id="icono-descargar-pdf"> <!-- Icono de descargar PDF -->
                    <span>Descargar PDF</span> <!-- Texto del botón descargar PDF -->
                </a>
            <?php }?> <!-- Fin del condicional de página de panel de administrador -->
            </div> <!-- Fin de la zona derecha -->

        </div> <!-- Fin de la barra del encabezado -->

        <!-- Control del menú lateral (simple con checkbox) -->
        <input type="checkbox" id="abrir-menu" class="control-menu" hidden> <!-- Checkbox oculto que controla la apertura del menú lateral -->

        <!-- Cortina para cerrar al hacer clic fuera -->
        <label for="abrir-menu" class="cortina" aria-hidden="true"></label> <!-- Cortina que cierra el menú al hacer clic fuera -->

        <!-- MENÚ LATERAL VERTICAL -->
        <nav class="menu-lateral" aria-label="Menú principal"> <!-- Navegación lateral principal -->
            <div class="cabecera-lateral"> <!-- Cabecera del menú lateral -->
                <span class="texto-categorias" id="titulo-menu">Todas las secciones</span> <!-- Título del menú lateral -->
                <label for="abrir-menu" id="boton-cerrar" title="Cerrar">×</label> <!-- Botón para cerrar el menú lateral -->
            </div>

            <!-- MENÚ PRINCIPAL -->
            <ul class="lista-menu" id="menu-principal"> <!-- Lista del menú principal -->
                <li> <!-- Elemento de lista para Notificaciones -->
                    <a href="<?php echo isset($_SESSION['id_usuario']) ? '../acciones/limpiar_busqueda.php?ir_a=../publico/notificaciones.php' : '../sesiones/formulario_autenticacion.php'; ?>" class="enlace-menu enlace-notificaciones" id="enlace-con-notificaciones" style="display: <?php echo (isset($_SESSION['notificaciones_sin_leer']) && $_SESSION['notificaciones_sin_leer'] > 0) ? 'flex' : 'none'; ?>;"> <!-- Enlace a notificaciones que limpia la búsqueda -->
                        <span> <!-- Contenedor del icono y contador de notificaciones -->
                            <img src="../recursos/imagenes/notificaciones.png" alt="Notificaciones" id="icono-notificaciones"> <!-- Icono de notificaciones -->
                            <span id="cantidad-notificaciones" class="cantidad-notificaciones" aria-label="Notificaciones"><?php echo $_SESSION['notificaciones_sin_leer']; ?></span> <!-- Contador de notificaciones -->
                        </span>   
                        <span>Notificaciones</span> <!-- Texto del enlace -->
                    </a>
                    <a href="<?php echo isset($_SESSION['id_usuario']) ? '../acciones/limpiar_busqueda.php?ir_a=../publico/notificaciones.php' : '../sesiones/formulario_autenticacion.php'; ?>" class="enlace-menu enlace-notificaciones" id="enlace-sin-notificaciones" style="display: <?php echo (!isset($_SESSION['notificaciones_sin_leer']) || $_SESSION['notificaciones_sin_leer'] == 0) ? 'flex' : 'none'; ?>;"> <!-- Enlace a notificaciones que limpia la búsqueda -->
                        <img src="../recursos/imagenes/notificaciones.png" alt="Notificaciones" id="icono-notificaciones"> <!-- Icono de notificaciones -->
                        <span>Notificaciones</span> <!-- Texto del enlace -->
                    </a>
                </li>
                <?php if(basename($_SERVER['PHP_SELF']) !== 'mapa.php' && basename($_SERVER['PHP_SELF']) !== 'detalles_juego.php' 
                            && basename($_SERVER['PHP_SELF']) !== 'editar_datos.php' && basename($_SERVER['PHP_SELF']) !== 'historial.php'
                            && basename($_SERVER['PHP_SELF']) !== 'notificaciones.php'){ ?> <!-- Si no estamos en ninguna de estas páginas específicas -->
                    <li> <!-- Elemento de lista para Filtros -->
                        <a onclick="mostrarMenuFiltros()" class="enlace-menu enlace-filtros" id="boton-filtros"> <!-- Enlace que abre el menú de filtros -->
                            <img src="../recursos/imagenes/filtros.png" alt="Filtros" id="icono-filtros"> <!-- Icono de filtros -->
                            <span>Filtros</span> <!-- Texto del enlace -->
                        </a>
                    </li>
                <?php } ?> <!-- Fin del condicional -->
                <?php if((basename($_SERVER['PHP_SELF']) === 'panel_administrador.php' || basename($_SERVER['PHP_SELF']) === 'notificaciones.php'
                            || basename($_SERVER['PHP_SELF']) === 'estadisticas.php')
                            && isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1 
                            && isset($_SESSION['modo_admin']) && $_SESSION['modo_admin'] === true) { /* Si estamos en el panel adminitrador o en notificaciones y el usuario es un administrador (rol 1) */ ?>
                    <li class="menu-con-submenu"> <!-- Elemento con submenú -->
                        <a onclick="submenuEdicion()" class="enlace-menu enlace-edicion" id="boton-modo-edicion"> <!-- Enlace que abre/cierra el submenú -->
                            <img src="../recursos/imagenes/edicion.png" alt="Edición" id="icono-edicion"> <!-- Icono de edición -->
                            <span>Modo de edición</span> <!-- Texto del enlace -->
                            <img src="../recursos/imagenes/flecha_abajo.png" alt="Desplegar" class="flecha-submenu" id="flecha-submenu-icono"> <!-- Flecha indicadora -->
                        </a>
                        <ul class="submenu-edicion" id="submenu-edicion" style="display: none;"> <!-- Submenú oculto inicialmente -->
                            <li> <!-- Opción de edición de juegos -->
                                <a href="#" class="enlace-submenu" data-modo="juegos"> <!-- Enlace para modo juegos -->
                                    <img src="../recursos/imagenes/edicion_juegos.png" alt="Juegos" class="icono-submenu"> <!-- Icono de juegos -->
                                    <span>Edición de juegos</span> <!-- Texto de la opción -->
                                </a>
                            </li>
                            <li> <!-- Opción de edición de usuarios -->
                                <a href="#" class="enlace-submenu" data-modo="usuarios"> <!-- Enlace para modo usuarios -->
                                    <img src="../recursos/imagenes/edicion_usuarios.png" alt="Usuarios" class="icono-submenu"> <!-- Icono de usuarios -->
                                    <span>Edición de usuarios</span> <!-- Texto de la opción -->
                                </a>
                            </li>
                            <li> <!-- Opción de edición de pedidos -->
                                <a href="#" class="enlace-submenu" data-modo="pedidos"> <!-- Enlace para modo pedidos -->
                                    <img src="../recursos/imagenes/edicion_pedidos.png" alt="Pedidos" class="icono-submenu"> <!-- Icono de pedidos -->
                                    <span>Edición de pedidos</span> <!-- Texto de la opción -->
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li> <!-- Elemento de lista para Estadísticas -->
                        <a href="../acciones/limpiar_busqueda.php?ir_a=../vistas/estadisticas.php" class="enlace-menu enlace-estadisticas"> <!-- Enlace a estadísticas que limpia la búsqueda -->
                            <img src="../recursos/imagenes/estadisticas.png" alt="Estadísticas" id="icono-estadisticas"> <!-- Icono de estadísticas -->
                            <span>Estadísticas</span> <!-- Texto del enlace -->
                        </a>
                    </li>
                <?php } else { /* Si el usuario no es un administrador (rol 1) en modo admin */ ?>
                    <li> <!-- Elemento de lista para Inicio -->
                        <a href="../acciones/limpiar_busqueda.php?ir_a=../publico/index.php" class="enlace-menu enlace-inicio"> <!-- Enlace al inicio que limpia la búsqueda -->
                            <img src="../recursos/imagenes/inicio.png" alt="Inicio" id="icono-inicio"> <!-- Icono de inicio -->
                            <span>Inicio</span> <!-- Texto del enlace -->
                        </a>
                    </li>
                    <li> <!-- Elemento de lista para Próximos lanzamientos -->
                        <a href="../acciones/limpiar_busqueda.php?ir_a=../publico/reservables.php" class="enlace-menu enlace-reservables"> <!-- Enlace a próximos lanzamientos que limpia la búsqueda -->
                            <img src="../recursos/imagenes/reservable.png" alt="Próximos lanzamientos" id="icono-reservable"> <!-- Icono de próximos lanzamientos -->
                            <span>Próximos lanzamientos</span> <!-- Texto del enlace -->
                        </a>
                    </li>
                    <li> <!-- Elemento de lista para Favoritos -->
                        <a href="<?php echo isset($_SESSION['id_usuario']) ? '../acciones/limpiar_busqueda.php?ir_a=../publico/favoritos.php' : '../sesiones/formulario_autenticacion.php'; ?>" class="enlace-menu enlace-favoritos"> <!-- Enlace a favoritos o login -->
                            <img src="../recursos/imagenes/favoritos_circulo.png" alt="Favoritos" id="icono-favoritos"> <!-- Icono de favoritos -->
                            <span>Favoritos</span> <!-- Texto del enlace -->
                        </a>
                    </li>
                    <li> <!-- Elemento de lista para Biblioteca -->
                        <a href="<?php echo isset($_SESSION['id_usuario']) ? '../acciones/limpiar_busqueda.php?ir_a=../publico/biblioteca.php' : '../sesiones/formulario_autenticacion.php'; ?>" class="enlace-menu enlace-biblioteca"> <!-- Enlace a biblioteca o login -->
                            <img src="../recursos/imagenes/biblioteca.png" alt="Biblioteca" id="icono-biblioteca"> <!-- Icono de biblioteca -->
                            <span>Biblioteca</span> <!-- Texto del enlace -->
                        </a>
                    </li>
                    <li> <!-- Elemento de lista para Historial -->
                        <a href="<?php echo isset($_SESSION['id_usuario']) ? '../acciones/limpiar_busqueda.php?ir_a=../publico/historial.php' : '../sesiones/formulario_autenticacion.php'; ?>" class="enlace-menu enlace-historial"> <!-- Enlace a historial o login -->
                            <img src="../recursos/imagenes/historial.png" alt="Historial" id="icono-historial"> <!-- Icono de historial -->
                            <span>Historial</span> <!-- Texto del enlace -->
                        </a>
                    </li>
                    <li> <!-- Elemento de lista para Mapa -->
                        <a href="../acciones/limpiar_busqueda.php?ir_a=../publico/mapa.php" class="enlace-menu enlace-mapa"> <!-- Enlace al mapa interactivo -->
                            <img src="../recursos/imagenes/mapa.png" alt="Mapa" id="icono-mapa"> <!-- Icono del mapa -->
                            <span>Mapa</span> <!-- Texto del enlace -->
                        </a>
                    </li>
                <?php } ?> <!-- Fin del condicional de página de panel de administrador -->
                <?php if(isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1){ /* Si el usuario es un administrador (rol 1) */ ?>
                    <li> <!-- Elemento de lista para Modo de Visión -->
                        <a href="../acciones/acciones_vision.php?modo=salir&administrando=<?php echo basename($_SERVER['PHP_SELF']) === 'panel_administrador.php' ? 1 : 0; ?>" class="enlace-menu enlace-modo-vision"> <!-- Enlace al modo de visión -->
                            <img src="../recursos/imagenes/vision.png" alt="Modo de Visión" id="icono-modo-vision"> <!-- Icono del modo de visión -->
                            <span>Cambiar modo de Visión</span> <!-- Texto del enlace -->
                        </a>
                    </li>
                <?php } ?> <!-- Fin del condicional de rol de administrador -->
            </ul> <!-- Fin de la lista del menú principal -->

            <!-- MENÚ DE FILTROS (oculto inicialmente) -->
            <ul class="lista-menu lista-filtros" id="menu-filtros" style="display: none;"> <!-- Lista del menú de filtros, oculta por defecto -->
                <form action="../acciones/procesar_filtros.php" method="post"> <!-- Formulario que procesa los filtros -->
                    <?php 
                        // Determinar la página actual para saber a dónde volver después de aplicar filtros
                        $pagina_actual = basename($_SERVER['PHP_SELF']); /* Obtengo el nombre del archivo actual */
                        $origen = 'index'; /* Por defecto asumo que es index */
                        
                        if ($pagina_actual === 'favoritos.php') { /* Si estamos en favoritos */
                            $origen = 'favoritos'; /* Marca origen como favoritos */
                        } elseif ($pagina_actual === 'index.php') { /* Si estamos en index */
                            $origen = 'index'; /* Marca origen como index */
                        } elseif ($pagina_actual === 'biblioteca.php') { /* Si estamos en biblioteca */
                            $origen = 'biblioteca'; /* Marca origen como biblioteca */
                        } elseif ($pagina_actual === 'panel_administrador.php') { /* Si estamos en el panel de administrador */
                            $origen = 'panel_administrador'; /* Marca origen como panel_administrador */
                        }
                        
                    ?>
                    <input type="hidden" name="pagina_origen" value="<?php echo $origen; ?>"> <!-- Campo oculto que indica desde qué página se aplicaron los filtros -->
                    
                    <div id="parte-filtros-usuarios"> <!-- Parte de filtros para usuarios -->
                        <!-- FILTROS DE USUARIOS -->
                        
                        <!-- SECCIÓN: DATOS PERSONALES -->
                        <li class="titulo-seccion-filtro"> <!-- Título de sección -->
                            <h3>Datos Personales</h3>
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de rol -->
                            <label for="filtro_rol">Rol:</label> <!-- Etiqueta para el select de rol -->
                            <select id="filtro_rol" name="filtro_rol"> <!-- Select de rol -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_usuarios']['rol']) || $_SESSION['filtros_usuarios']['rol'] == 'null') ? 'selected' : ''; ?>>Todos los roles</option> <!-- Opción por defecto -->
                                <?php foreach ($roles_bd as $rol) { /* Recorro todos los roles */ ?>
                                    <option value="<?php echo $rol['id_rol']; ?>" <?php echo (isset($_SESSION['filtros_usuarios']['rol']) && $_SESSION['filtros_usuarios']['rol'] == $rol['id_rol']) ? 'selected' : ''; ?>> <!-- Opción del rol con selección condicional -->
                                        <?php echo htmlspecialchars($rol['nombre']); ?> <!-- Nombre del rol escapado -->
                                    </option>
                                <?php } ?> <!-- Fin del foreach y condicional -->
                            </select>
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de búsqueda por nombre -->
                            <label for="filtro_acronimo">Acrónimo:</label> <!-- Etiqueta para el input de búsqueda -->
                            <select id="filtro_acronimo" name="filtro_acronimo"> <!-- Select de acrónimos -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_usuarios']['acronimo']) || $_SESSION['filtros_usuarios']['acronimo'] == 'null') ? 'selected' : ''; ?>>Todos los acrónimos</option> <!-- Opción por defecto -->
                                <?php foreach ($usuarios_bd as $usuario) { /* Recorro todos los usuarios */ ?>
                                    <option value="<?php echo $usuario['acronimo']; ?>" <?php echo (isset($_SESSION['filtros_usuarios']['acronimo']) && $_SESSION['filtros_usuarios']['acronimo'] == $usuario['acronimo']) ? 'selected' : ''; ?>> <!-- Opción del acrónimo con selección condicional -->
                                        <?php echo htmlspecialchars($usuario['acronimo']); ?> <!-- Acrónimo del usuario escapado -->
                                    </option>
                                <?php } ?> <!-- Fin del foreach y condicional -->
                            </select>
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de correo -->
                            <label for="filtro_correo">Correo:</label> <!-- Etiqueta para el input de correo -->
                            <select id="filtro_correo" name="filtro_correo"> <!-- Select de correos -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_usuarios']['email']) || $_SESSION['filtros_usuarios']['email'] == 'null') ? 'selected' : ''; ?>>Todos los correos</option> <!-- Opción por defecto -->
                                <?php foreach ($usuarios_bd as $usuario) { /* Recorro todos los usuarios */ ?>
                                    <option value="<?php echo $usuario['email']; ?>" <?php echo (isset($_SESSION['filtros_usuarios']['email']) && $_SESSION['filtros_usuarios']['email'] == $usuario['email']) ? 'selected' : ''; ?>> <!-- Opción del correo con selección condicional -->
                                        <?php echo htmlspecialchars($usuario['email']); ?> <!-- Correo del usuario escapado -->
                                    </option>
                                <?php } ?> <!-- Fin del foreach y condicional -->
                            </select>
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de dni -->
                            <label for="filtro_dni">DNI:</label> <!-- Etiqueta para el input de dni -->
                            <select id="filtro_dni" name="filtro_dni"> <!-- Select de dni -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_usuarios']['dni']) || $_SESSION['filtros_usuarios']['dni'] == 'null') ? 'selected' : ''; ?>>Todos los DNIs</option> <!-- Opción por defecto -->
                                <?php foreach ($usuarios_bd as $usuario) { /* Recorro todos los usuarios */ ?>
                                    <option value="<?php echo $usuario['dni']; ?>" <?php echo (isset($_SESSION['filtros_usuarios']['dni']) && $_SESSION['filtros_usuarios']['dni'] == $usuario['dni']) ? 'selected' : ''; ?>> <!-- Opción del dni con selección condicional -->
                                        <?php echo htmlspecialchars($usuario['dni']); ?> <!-- DNI escapado -->
                                    </option>
                                <?php } ?> <!-- Fin del foreach -->
                            </select>
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de nombre -->
                            <label for="filtro_nombre">Nombre:</label> <!-- Etiqueta para el select de nombre -->
                            <select id="filtro_nombre" name="filtro_nombre"> <!-- Select de nombre -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_usuarios']['nombre']) || $_SESSION['filtros_usuarios']['nombre'] == 'null') ? 'selected' : ''; ?>>Todos los nombres</option> <!-- Opción por defecto -->
                                <?php 
                                    // Obtener nombres únicos
                                    $nombres_unicos = array_unique(array_column($usuarios_bd, 'nombre')); /* Extraigo solo los nombres y elimino duplicados */
                                    sort($nombres_unicos); /* Ordeno alfabéticamente */
                                    foreach ($nombres_unicos as $nombre) { /* Recorro los nombres únicos */
                                ?>
                                    <option value="<?php echo htmlspecialchars($nombre); ?>" <?php echo (isset($_SESSION['filtros_usuarios']['nombre']) && $_SESSION['filtros_usuarios']['nombre'] == $nombre) ? 'selected' : ''; ?>> <!-- Opción del nombre con selección condicional -->
                                        <?php echo htmlspecialchars($nombre); ?> <!-- Nombre escapado -->
                                    </option>
                                <?php } ?> <!-- Fin del foreach -->
                            </select>
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de apellidos -->
                            <label for="filtro_apellidos">Apellidos:</label> <!-- Etiqueta para el select de apellidos -->
                            <select id="filtro_apellidos" name="filtro_apellidos"> <!-- Select de apellidos -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_usuarios']['apellidos']) || $_SESSION['filtros_usuarios']['apellidos'] == 'null') ? 'selected' : ''; ?>>Todos los apellidos</option> <!-- Opción por defecto -->
                                <?php 
                                    // Obtener apellidos únicos
                                    $apellidos_unicos = array_unique(array_column($usuarios_bd, 'apellidos')); /* Extraigo solo los apellidos y elimino duplicados */
                                    sort($apellidos_unicos); /* Ordeno alfabéticamente */
                                    foreach ($apellidos_unicos as $apellidos) { /* Recorro los apellidos únicos */
                                ?>
                                    <option value="<?php echo htmlspecialchars($apellidos); ?>" <?php echo (isset($_SESSION['filtros_usuarios']['apellidos']) && $_SESSION['filtros_usuarios']['apellidos'] == $apellidos) ? 'selected' : ''; ?>> <!-- Opción de apellidos con selección condicional -->
                                        <?php echo htmlspecialchars($apellidos); ?> <!-- Apellidos escapados -->
                                    </option>
                                <?php } ?> <!-- Fin del foreach -->
                            </select>
                        </li>
                        
                        <br>
                        <!-- SECCIÓN: FECHA DE CREACIÓN -->
                        <li class="titulo-seccion-filtro"> <!-- Título de sección -->
                            <h3>Fecha de Creación</h3>
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de fecha de creación desde -->
                            <label for="filtro_fecha_creacion_desde">Creado desde:</label> <!-- Etiqueta para el input de fecha -->
                            <input type="date" id="filtro_fecha_creacion_desde" name="filtro_fecha_creacion_desde" value="<?php echo isset($_SESSION['filtros_usuarios']['fecha_creacion_desde']) ? $_SESSION['filtros_usuarios']['fecha_creacion_desde'] : ''; ?>"> <!-- Input de fecha desde -->
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de fecha de creación hasta -->
                            <label for="filtro_fecha_creacion_hasta">Creado hasta:</label> <!-- Etiqueta para el input de fecha -->
                            <input type="date" id="filtro_fecha_creacion_hasta" name="filtro_fecha_creacion_hasta" value="<?php echo isset($_SESSION['filtros_usuarios']['fecha_creacion_hasta']) ? $_SESSION['filtros_usuarios']['fecha_creacion_hasta'] : ''; ?>"> <!-- Input de fecha hasta -->
                        </li>
                        
                        <br>
                        <!-- SECCIÓN: FECHA DE ÚLTIMA ACTUALIZACIÓN -->
                        <li class="titulo-seccion-filtro"> <!-- Título de sección -->
                            <h3>Fecha de Última Actualización</h3>
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de fecha de actualización desde -->
                            <label for="filtro_fecha_acceso_desde">Última actualización desde:</label> <!-- Etiqueta para el input de fecha -->
                            <input type="date" id="filtro_fecha_actualizacion_desde" name="filtro_fecha_actualizacion_desde" value="<?php echo isset($_SESSION['filtros_usuarios']['fecha_actualizacion_desde']) ? $_SESSION['filtros_usuarios']['fecha_actualizacion_desde'] : ''; ?>"> <!-- Input de fecha desde -->
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de fecha de actualización hasta -->
                            <label for="filtro_fecha_acceso_hasta">Última actualización hasta:</label> <!-- Etiqueta para el input de fecha -->
                            <input type="date" id="filtro_fecha_actualizacion_hasta" name="filtro_fecha_actualizacion_hasta" value="<?php echo isset($_SESSION['filtros_usuarios']['fecha_actualizacion_hasta']) ? $_SESSION['filtros_usuarios']['fecha_actualizacion_hasta'] : ''; ?>"> <!-- Input de fecha hasta -->
                        </li>

                        <br>
                        <!-- SECCIÓN: FECHA DE ÚLTIMO ACCESO -->
                        <li class="titulo-seccion-filtro"> <!-- Título de sección -->
                            <h3>Fecha de Último Acceso</h3>
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de fecha de acceso desde -->
                            <label for="filtro_fecha_acceso_desde">Último acceso desde:</label> <!-- Etiqueta para el input de fecha -->
                            <input type="date" id="filtro_fecha_acceso_desde" name="filtro_fecha_acceso_desde" value="<?php echo isset($_SESSION['filtros_usuarios']['fecha_acceso_desde']) ? $_SESSION['filtros_usuarios']['fecha_acceso_desde'] : ''; ?>"> <!-- Input de fecha desde -->
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de fecha de acceso hasta -->
                            <label for="filtro_fecha_acceso_hasta">Último acceso hasta:</label> <!-- Etiqueta para el input de fecha -->
                            <input type="date" id="filtro_fecha_acceso_hasta" name="filtro_fecha_acceso_hasta" value="<?php echo isset($_SESSION['filtros_usuarios']['fecha_acceso_hasta']) ? $_SESSION['filtros_usuarios']['fecha_acceso_hasta'] : ''; ?>"> <!-- Input de fecha hasta -->
                        </li>
                    </div> <!-- Fin de la parte de filtros para usuarios -->

                    <div id="parte-filtros-pedidos"> <!-- Parte de filtros para pedidos -->
                        <!-- FILTROS DE PEDIDOS -->
                        <li class="titulo-seccion-filtro"> <!-- Elemento lista para título de sección -->
                            <h3>Tipos y Estados</h3> <!-- Título de la sección de filtros de pedidos -->
                        </li>

                        <!-- Tipo de operación -->
                        <li class="categoria-filtro"> <!-- Elemento para filtro de tipo de operación -->
                            <label for="filtro_pedido_tipo">Tipo de operación:</label> <!-- Etiqueta para el select de tipo de operación -->
                            <select id="filtro_pedido_tipo" name="filtro_pedido_tipo"> <!-- Select de tipo de operación -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_pedidos']['tipo']) || $_SESSION['filtros_pedidos']['tipo'] == 'null') ? ' selected' : ''; ?>>Todos los tipos</option> <!-- Opción por defecto -->
                                <option value="COMPRA"<?php echo (isset($_SESSION['filtros_pedidos']['tipo']) && $_SESSION['filtros_pedidos']['tipo'] == 'COMPRA') ? ' selected' : ''; ?>>COMPRA</option> <!-- Opción de compra -->
                                <option value="RESERVA"<?php echo (isset($_SESSION['filtros_pedidos']['tipo']) && $_SESSION['filtros_pedidos']['tipo'] == 'RESERVA') ? ' selected' : ''; ?>>RESERVA</option> <!-- Opción de reserva -->
                                <option value="SOLICITUD_DEVOLUCION"<?php echo (isset($_SESSION['filtros_pedidos']['tipo']) && $_SESSION['filtros_pedidos']['tipo'] == 'SOLICITUD_DEVOLUCION') ? ' selected' : ''; ?>>SOLICITUD_DEVOLUCIÓN</option> <!-- Opción de solicitud de devolución -->
                                <option value="DEVOLUCION"<?php echo (isset($_SESSION['filtros_pedidos']['tipo']) && $_SESSION['filtros_pedidos']['tipo'] == 'DEVOLUCION') ? ' selected' : ''; ?>>DEVOLUCIÓN</option> <!-- Opción de devolución -->
                            </select>
                        </li>

                        <!-- Estado del pedido -->
                        <li class="categoria-filtro"> <!-- Elemento para filtro de estado del pedido -->
                            <label for="filtro_pedido_estado">Estado del pedido:</label> <!-- Etiqueta para el select de estado del pedido -->
                            <select id="filtro_pedido_estado" name="filtro_pedido_estado"> <!-- Select de estado del pedido -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_pedidos']['estado']) || $_SESSION['filtros_pedidos']['estado'] == 'null') ? ' selected' : ''; ?>>Todos los estados</option> <!-- Opción por defecto -->
                                <option value="PENDIENTE"<?php echo (isset($_SESSION['filtros_pedidos']['estado']) && $_SESSION['filtros_pedidos']['estado'] == 'PENDIENTE') ? ' selected' : ''; ?>>PENDIENTE</option> <!-- Opción de pendiente -->
                                <option value="PAGADA"<?php echo (isset($_SESSION['filtros_pedidos']['estado']) && $_SESSION['filtros_pedidos']['estado'] == 'PAGADA') ? ' selected' : ''; ?>>PAGADA</option> <!-- Opción de pagada -->
                                <option value="CANCELADA"<?php echo (isset($_SESSION['filtros_pedidos']['estado']) && $_SESSION['filtros_pedidos']['estado'] == 'CANCELADA') ? ' selected' : ''; ?>>CANCELADA</option> <!-- Opción de cancelada -->
                                <option value="APROBADA"<?php echo (isset($_SESSION['filtros_pedidos']['estado']) && $_SESSION['filtros_pedidos']['estado'] == 'APROBADA') ? ' selected' : ''; ?>>APROBADA</option> <!-- Opción de aprobada -->
                                <option value="RECHAZADA"<?php echo (isset($_SESSION['filtros_pedidos']['estado']) && $_SESSION['filtros_pedidos']['estado'] == 'RECHAZADA') ? ' selected' : ''; ?>>RECHAZADA</option> <!-- Opción de rechazada -->
                                <option value="PENDIENTE_REVISION"<?php echo (isset($_SESSION['filtros_pedidos']['estado']) && $_SESSION['filtros_pedidos']['estado'] == 'PENDIENTE_REVISION') ? ' selected' : ''; ?>>PENDIENTE REVISIÓN</option> <!-- Opción de pendiente de revisión -->
                                <option value="RESERVADA"<?php echo (isset($_SESSION['filtros_pedidos']['estado']) && $_SESSION['filtros_pedidos']['estado'] == 'RESERVADA') ? ' selected' : ''; ?>>RESERVADA</option> <!-- Opción de reservada -->
                                <option value="COMPLETADA"<?php echo (isset($_SESSION['filtros_pedidos']['estado']) && $_SESSION['filtros_pedidos']['estado'] == 'COMPLETADA') ? ' selected' : ''; ?>>COMPLETADA</option> <!-- Opción de completada -->
                            </select>
                        </li>

                        <!-- Estado del pedido -->
                        <li class="categoria-filtro"> <!-- Elemento para filtro de estado del detalle del pedido -->
                            <label for="filtro_pedido_estado_detalle">Estado del detalle:</label> <!-- Etiqueta para el select de estado del detalle del pedido -->
                            <select id="filtro_pedido_estado_detalle" name="filtro_pedido_estado_detalle"> <!-- Select de estado del detalle del pedido -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_pedidos']['estado_detalle']) || $_SESSION['filtros_pedidos']['estado_detalle'] == 'null') ? ' selected' : ''; ?>>Todos los estados</option> <!-- Opción por defecto -->
                                <option value="PAGADO"<?php echo (isset($_SESSION['filtros_pedidos']['estado_detalle']) && $_SESSION['filtros_pedidos']['estado_detalle'] == 'PAGADO') ? ' selected' : ''; ?>>PAGADO</option> <!-- Opción de pagado -->
                                <option value="RESERVADO"<?php echo (isset($_SESSION['filtros_pedidos']['estado_detalle']) && $_SESSION['filtros_pedidos']['estado_detalle'] == 'RESERVADO') ? ' selected' : ''; ?>>RESERVADO</option> <!-- Opción de reservado -->
                                <option value="CANCELADO"<?php echo (isset($_SESSION['filtros_pedidos']['estado_detalle']) && $_SESSION['filtros_pedidos']['estado_detalle'] == 'CANCELADO') ? ' selected' : ''; ?>>CANCELADO</option> <!-- Opción de cancelado -->
                                <option value="PENDIENTE_REVISION"<?php echo (isset($_SESSION['filtros_pedidos']['estado_detalle']) && $_SESSION['filtros_pedidos']['estado_detalle'] == 'PENDIENTE_REVISION') ? ' selected' : ''; ?>>PENDIENTE REVISIÓN</option> <!-- Opción de pendiente de revisión -->
                                <option value="DEVUELTO"<?php echo (isset($_SESSION['filtros_pedidos']['estado_detalle']) && $_SESSION['filtros_pedidos']['estado_detalle'] == 'DEVUELTO') ? ' selected' : ''; ?>>DEVUELTO</option> <!-- Opción de devuelto -->
                                <option value="RECHAZADA"<?php echo (isset($_SESSION['filtros_pedidos']['estado_detalle']) && $_SESSION['filtros_pedidos']['estado_detalle'] == 'RECHAZADA') ? ' selected' : ''; ?>>RECHAZADA</option> <!-- Opción de rechazada -->
                                <option value="APROBADA"<?php echo (isset($_SESSION['filtros_pedidos']['estado_detalle']) && $_SESSION['filtros_pedidos']['estado_detalle'] == 'APROBADA') ? ' selected' : ''; ?>>APROBADA</option> <!-- Opción de aprobada -->
                            </select>
                        </li>

                        <br> <!-- Salto de línea para separar secciones -->
                        <li class="titulo-seccion-filtro"> <!-- Elemento lista para el título de sección -->
                            <h3>Usuarios relacionados</h3> <!-- Título de la sección de filtros de usuarios relacionados -->
                        </li>

                        <!-- Usuario: acrónimo -->
                        <li class="categoria-filtro"> <!-- Categoría de filtro para acrónimo -->
                            <label for="filtro_pedido_acronimo">Acrónimo:</label> <!-- Etiqueta para el select de acrónimo -->
                            <select id="filtro_pedido_acronimo" name="filtro_pedido_acronimo"> <!-- Select de acrónimo -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_pedidos']['acronimo']) || $_SESSION['filtros_pedidos']['acronimo'] == 'null') ? ' selected' : ''; ?>>Todos los acrónimos</option> <!-- Opción por defecto -->
                                <?php $acronimos_historial = array_unique(array_column($historial_general, 'usuario_acronimo')); /* Extraigo los acrónimos únicos del historial */
                                sort($acronimos_historial); /* Ordeno alfabéticamente */
                                foreach ($acronimos_historial as $acronimo) { /* Recorro los acrónimos únicos */
                                    if (!empty($acronimo)) { ?> <!-- Verifico que no esté vacío -->
                                        <option value="<?php echo htmlspecialchars($acronimo); ?>" <?php echo (isset($_SESSION['filtros_pedidos']['acronimo']) && $_SESSION['filtros_pedidos']['acronimo'] == $acronimo) ? 'selected' : ''; ?>> <!-- Opción del acrónimo con selección condicional -->
                                            <?php echo htmlspecialchars($acronimo); ?> <!-- Acrónimo escapado -->
                                        </option>
                                    <?php } 
                                } ?>
                            </select>
                        </li>

                        <!-- Usuario: nombre -->
                        <li class="categoria-filtro"> <!-- Categoría de filtro para nombre -->
                            <label for="filtro_pedido_nombre">Nombre:</label> <!-- Etiqueta para el select de nombre -->
                            <select id="filtro_pedido_nombre" name="filtro_pedido_nombre"> <!-- Select de nombre -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_pedidos']['nombre']) || $_SESSION['filtros_pedidos']['nombre'] == 'null') ? ' selected' : ''; ?>>Todos los nombres</option> <!-- Opción por defecto -->
                                <?php $nombres_historial = array_unique(array_column($historial_general, 'usuario_nombre')); /* Extraigo los nombres únicos del historial */
                                sort($nombres_historial); /* Ordeno alfabéticamente */
                                foreach ($nombres_historial as $nombre) { /* Recorro los nombres únicos */
                                    if (!empty($nombre)) { ?> <!-- Verifico que no esté vacío -->
                                        <option value="<?php echo htmlspecialchars($nombre); ?>" <?php echo (isset($_SESSION['filtros_pedidos']['nombre']) && $_SESSION['filtros_pedidos']['nombre'] == $nombre) ? 'selected' : ''; ?>> <!-- Opción del nombre con selección condicional -->
                                            <?php echo htmlspecialchars($nombre); ?> <!-- Nombre escapado -->
                                        </option>
                                    <?php } 
                                } ?>
                            </select>
                        </li>

                        <!-- Usuario: apellidos -->
                        <li class="categoria-filtro"> <!-- Categoría de filtro para apellidos -->
                            <label for="filtro_pedido_apellidos">Apellidos:</label> <!-- Etiqueta para el select de apellidos -->
                            <select id="filtro_pedido_apellidos" name="filtro_pedido_apellidos"> <!-- Select de apellidos -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_pedidos']['apellidos']) || $_SESSION['filtros_pedidos']['apellidos'] == 'null') ? ' selected' : ''; ?>>Todos los apellidos</option> <!-- Opción por defecto -->
                                <?php $apellidos_historial = array_unique(array_column($historial_general, 'usuario_apellidos')); /* Extraigo los apellidos únicos del historial */
                                sort($apellidos_historial); /* Ordeno alfabéticamente */
                                foreach ($apellidos_historial as $apellidos) {  /* Recorro los apellidos únicos */
                                    if (!empty($apellidos)) { ?> <!-- Verifico que no esté vacío -->
                                        <option value="<?php echo htmlspecialchars($apellidos); ?>" <?php echo (isset($_SESSION['filtros_pedidos']['apellidos']) && $_SESSION['filtros_pedidos']['apellidos'] == $apellidos) ? 'selected' : ''; ?>> <!-- Opción de apellidos con selección condicional -->
                                            <?php echo htmlspecialchars($apellidos); ?> <!-- Apellidos escapados -->
                                        </option>
                                    <?php } 
                                } ?>
                            </select>
                        </li>

                        <br> <!-- Salto de línea para separar secciones -->
                        <li class="titulo-seccion-filtro"> <!-- Elemento lista para el título de sección -->
                            <h3>Pagos</h3> <!-- Título de la sección de filtros de pagos -->
                        </li>
                        <!-- Método de pago -->
                        <li class="categoria-filtro"> <!-- Elemento para filtro de método de pago -->
                            <label for="filtro_pedido_metodo_pago">Método de pago:</label> <!-- Etiqueta para el select de método de pago -->
                            <select id="filtro_pedido_metodo_pago" name="filtro_pedido_metodo_pago"> <!-- Select de método de pago -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_pedidos']['metodo_pago']) || $_SESSION['filtros_pedidos']['metodo_pago'] == 'null') ? ' selected' : ''; ?>>Todos los métodos</option> <!-- Opción por defecto -->
                                <option value="tarjeta"<?php echo (isset($_SESSION['filtros_pedidos']['metodo_pago']) && $_SESSION['filtros_pedidos']['metodo_pago'] == 'tarjeta') ? ' selected' : ''; ?>>Tarjeta</option> <!-- Opción de tarjeta -->
                                <option value="paypal"<?php echo (isset($_SESSION['filtros_pedidos']['metodo_pago']) && $_SESSION['filtros_pedidos']['metodo_pago'] == 'paypal') ? ' selected' : ''; ?>>PayPal</option> <!-- Opción de PayPal -->
                            </select>
                        </li>
                        <!-- Económicos: total mínimo/máximo -->
                        <li class="categoria-filtro"> <!-- Elemento para filtro de total mínimo -->
                            <label for="filtro-pedido-total-min">Total mínimo:</label> <!-- Etiqueta para el input de total mínimo -->
                            <input type="range" id="filtro-pedido-total-min" name="filtro_pedido_total_min" value="<?php echo isset($_SESSION['filtros_pedidos']['total_min']) ? htmlspecialchars($_SESSION['filtros_pedidos']['total_min']) : $total_min_historial; ?>" min="<?php echo $total_min_historial; ?>" max="<?php echo $total_max_historial; ?>" step="0.01" oninput="document.getElementById('output-pedido-total-min').value = parseFloat(this.value).toFixed(2).replace('.', ',')"> <!-- Input de total mínimo -->
                            <output for="filtro-pedido-total-min" id="output-pedido-total-min"><?php echo number_format(isset($_SESSION['filtros_pedidos']['total_min']) ? $_SESSION['filtros_pedidos']['total_min'] : $total_min_historial, 2, ',', ''); ?></output> <!-- Output para mostrar el valor formateado -->
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de total máximo -->
                            <label for="filtro-pedido-total-max">Total máximo:</label> <!-- Etiqueta para el input de total máximo -->
                            <input type="range" id="filtro-pedido-total-max" name="filtro_pedido_total_max" value="<?php echo isset($_SESSION['filtros_pedidos']['total_max']) ? htmlspecialchars($_SESSION['filtros_pedidos']['total_max']) : $total_max_historial; ?>" min="<?php echo $total_min_historial; ?>" max="<?php echo $total_max_historial; ?>" step="0.01" oninput="document.getElementById('output-pedido-total-max').value = parseFloat(this.value).toFixed(2).replace('.', ',')"> <!-- Input de total máximo -->
                            <output for="filtro-pedido-total-max" id="output-pedido-total-max"><?php echo number_format(isset($_SESSION['filtros_pedidos']['total_max']) ? $_SESSION['filtros_pedidos']['total_max'] : $total_max_historial, 2, ',', ''); ?></output> <!-- Output para mostrar el valor formateado -->
                        </li>

                        <br> <!-- Salto de línea para separar secciones -->
                        <!-- Fechas: creación -->
                        <li class="titulo-seccion-filtro"> <!-- Elemento lista para el título de sección -->
                            <h3>Fecha de Creación</h3> <!-- Título de la sección de filtros de fecha de creación -->
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de fecha de creación desde -->
                            <label for="filtro_pedido_creado_desde">Creado desde:</label> <!-- Etiqueta para el input de fecha de creación desde -->
                            <input type="date" id="filtro_pedido_creado_desde" name="filtro_pedido_creado_desde" value="<?php echo isset($_SESSION['filtros_pedidos']['creado_desde']) ? htmlspecialchars($_SESSION['filtros_pedidos']['creado_desde']) : ''; ?>"> <!-- Input de fecha de creación desde -->
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de fecha de creación hasta -->
                            <label for="filtro_pedido_creado_hasta">Creado hasta:</label> <!-- Etiqueta para el input de fecha de creación hasta -->
                            <input type="date" id="filtro_pedido_creado_hasta" name="filtro_pedido_creado_hasta" value="<?php echo isset($_SESSION['filtros_pedidos']['creado_hasta']) ? htmlspecialchars($_SESSION['filtros_pedidos']['creado_hasta']) : ''; ?>"> <!-- Input de fecha de creación hasta -->
                        </li>

                        <br> <!-- Salto de línea para separar secciones -->
                        <!-- Fechas: actualización -->
                        <li class="titulo-seccion-filtro"> <!-- Elemento lista para el título de sección -->
                            <h3>Fecha de Actualización</h3> <!-- Título de la sección de filtros de fecha de actualización -->
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de fecha de actualización desde -->
                            <label for="filtro_pedido_actualizado_desde">Actualizado desde:</label> <!-- Etiqueta para el input de fecha de actualización desde -->
                            <input type="date" id="filtro_pedido_actualizado_desde" name="filtro_pedido_actualizado_desde" value="<?php echo isset($_SESSION['filtros_pedidos']['actualizado_desde']) ? htmlspecialchars($_SESSION['filtros_pedidos']['actualizado_desde']) : ''; ?>"> <!-- Input de fecha de actualización desde -->
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de fecha de actualización hasta -->
                            <label for="filtro_pedido_actualizado_hasta">Actualizado hasta:</label> <!-- Etiqueta para el input de fecha de actualización hasta -->
                            <input type="date" id="filtro_pedido_actualizado_hasta" name="filtro_pedido_actualizado_hasta" value="<?php echo isset($_SESSION['filtros_pedidos']['actualizado_hasta']) ? htmlspecialchars($_SESSION['filtros_pedidos']['actualizado_hasta']) : ''; ?>"> <!-- Input de fecha de actualización hasta -->
                        </li>
                    </div>
                    
                    <div id="parte-filtros-juegos"> <!-- Parte de filtros para juegos -->
                    
                        <!-- FILTROS DE JUEGOS -->
                        <li class="categoria-filtro"> <!-- Elemento para filtro de tipos -->
                            <label for="id_preferencia_tipo">Tipos:</label> <!-- Etiqueta para el select de tipos -->
                            <select id="id_preferencia_tipo" name="id_preferencia_tipo" required> <!-- Select de tipos de juego -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_elegidos']['tipo']) || $_SESSION['filtros_elegidos']['tipo'] == 'null') ? 'selected' : ''; ?>>Todos los tipos</option> <!-- Opción por defecto -->
                                <?php foreach ($categorias as $categoria) { /* Recorro todas las categorías */
                                    if($categoria['tipo_filtro'] === 'tipos'){ /* Si es un tipo de juego */
                                ?>
                                    <option value="<?php echo $categoria['id_fijo']; ?>" <?php echo (isset($_SESSION['filtros_elegidos']['tipo']) && $_SESSION['filtros_elegidos']['tipo'] == $categoria['id_fijo']) ? 'selected' : ''; ?>> <!-- Opción del tipo con selección condicional -->
                                        <?php echo htmlspecialchars($categoria['nombre']); ?> <!-- Nombre del tipo escapado -->
                                    </option>
                                <?php } }?> <!-- Fin del foreach y condicional -->
                            </select>
                        </li>                 
                        <li class="categoria-filtro"> <!-- Elemento para filtro de géneros -->
                            <label for="id_preferencia_genero">Géneros:</label> <!-- Etiqueta para el select de géneros -->
                            <select id="id_preferencia_genero" name="id_preferencia_genero" required> <!-- Select de géneros -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_elegidos']['genero']) || $_SESSION['filtros_elegidos']['genero'] == 'null') ? 'selected' : ''; ?>>Todos los géneros</option> <!-- Opción por defecto -->
                                <?php foreach ($categorias as $categoria) { /* Recorro todas las categorías */
                                    if($categoria['tipo_filtro'] === 'generos'){ /* Si es un género */
                                ?>
                                    <option value="<?php echo $categoria['id_fijo']; ?>" <?php echo (isset($_SESSION['filtros_elegidos']['genero']) && $_SESSION['filtros_elegidos']['genero'] == $categoria['id_fijo']) ? 'selected' : ''; ?>> <!-- Opción del género con selección condicional -->
                                        <?php echo htmlspecialchars($categoria['nombre']); ?> <!-- Nombre del género escapado -->
                                    </option>
                                <?php } }?> <!-- Fin del foreach y condicional -->
                            </select>
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de categorías -->
                            <label for="id_preferencia_categoria">Categorías:</label> <!-- Etiqueta para el select de categorías -->
                            <select id="id_preferencia_categoria" name="id_preferencia_categoria" required> <!-- Select de categorías -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_elegidos']['categoria']) || $_SESSION['filtros_elegidos']['categoria'] == 'null') ? 'selected' : ''; ?>>Todas las categorías</option> <!-- Opción por defecto -->
                                <?php foreach ($categorias as $categoria) { /* Recorro todas las categorías */
                                    if($categoria['tipo_filtro'] === 'categorias'){ /* Si es una categoría */
                                ?>
                                    <option value="<?php echo $categoria['id_fijo']; ?>" <?php echo (isset($_SESSION['filtros_elegidos']['categoria']) && $_SESSION['filtros_elegidos']['categoria'] == $categoria['id_fijo']) ? 'selected' : ''; ?>> <!-- Opción de la categoría con selección condicional -->
                                        <?php echo htmlspecialchars($categoria['nombre']); ?> <!-- Nombre de la categoría escapado -->
                                    </option>
                                <?php } }?> <!-- Fin del foreach y condicional -->
                            </select>
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de modos -->
                            <label for="id_preferencia_modo">Modos de juego:</label> <!-- Etiqueta para el select de modos -->
                            <select id="id_preferencia_modo" name="id_preferencia_modo" required> <!-- Select de modos de juego -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_elegidos']['modo']) || $_SESSION['filtros_elegidos']['modo'] == 'null') ? 'selected' : ''; ?>>Todos los modos</option> <!-- Opción por defecto -->
                                <?php foreach ($categorias as $categoria) { /* Recorro todas las categorías */
                                    if($categoria['tipo_filtro'] === 'modos'){ /* Si es un modo de juego */
                                ?>
                                    <option value="<?php echo $categoria['id_fijo']; ?>" <?php echo (isset($_SESSION['filtros_elegidos']['modo']) && $_SESSION['filtros_elegidos']['modo'] == $categoria['id_fijo']) ? 'selected' : ''; ?>> <!-- Opción del modo con selección condicional -->
                                        <?php echo htmlspecialchars($categoria['nombre']); ?> <!-- Nombre del modo escapado -->
                                    </option>
                                <?php } }?> <!-- Fin del foreach y condicional -->
                            </select>
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de clasificaciones PEGI -->
                            <label for="id_preferencia_pegi">Clasificaciones PEGI:</label> <!-- Etiqueta para el select de PEGI -->
                            <select id="id_preferencia_pegi" name="id_preferencia_pegi" required> <!-- Select de clasificaciones PEGI -->
                                <option value="null"<?php echo (!isset($_SESSION['filtros_elegidos']['pegi']) || $_SESSION['filtros_elegidos']['pegi'] == 'null') ? 'selected' : ''; ?>>Todas las clasificaciones PEGI</option> <!-- Opción por defecto -->
                                <?php foreach ($categorias as $categoria) { /* Recorro todas las categorías */
                                    if($categoria['tipo_filtro'] === 'clasificacionPEGI'){ /* Si es una clasificación PEGI */
                                ?>
                                    <option value="<?php echo $categoria['id_fijo']; ?>" <?php echo (isset($_SESSION['filtros_elegidos']['pegi']) && $_SESSION['filtros_elegidos']['pegi'] == $categoria['id_fijo']) ? 'selected' : ''; ?>> <!-- Opción de la clasificación PEGI con selección condicional -->
                                        <?php echo htmlspecialchars($categoria['nombre']); ?> <!-- Nombre de la clasificación escapado -->
                                    </option>
                                <?php } }?> <!-- Fin del foreach y condicional -->
                            </select>
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de precio mínimo -->
                            <label for="precio-min">Precio mínimo:</label> <!-- Etiqueta para el range de precio mínimo -->
                            <input type="range" id="precio-min" name="precio_min" value="<?php echo (isset($_SESSION['filtros_elegidos']['precio_min']) ? $_SESSION['filtros_elegidos']['precio_min'] : 0); ?>" min="0" max="100" step="1" oninput="document.getElementById('output-min').value = String(this.value).replace('.', ',')"> <!-- Range para precio mínimo -->
                            <output for="precio-min" id="output-min"><?php echo (isset($_SESSION['filtros_elegidos']['precio_min']) ? $_SESSION['filtros_elegidos']['precio_min'] : 0); ?></output> <!-- Muestra el valor del precio mínimo -->
                        </li>
                        <li class="categoria-filtro"> <!-- Elemento para filtro de precio máximo -->
                            <label for="precio-max">Precio máximo:</label> <!-- Etiqueta para el range de precio máximo -->
                            <input type="range" id="precio-max" name="precio_max" value="<?php echo (isset($_SESSION['filtros_elegidos']['precio_max']) ? $_SESSION['filtros_elegidos']['precio_max'] : 100); ?>" min="0" max="100" step="1" oninput="document.getElementById('output-max').value = String(this.value).replace('.', ',')"> <!-- Range para precio máximo -->
                            <output for="precio-max" id="output-max"><?php echo (isset($_SESSION['filtros_elegidos']['precio_max']) ? $_SESSION['filtros_elegidos']['precio_max'] : 100); ?></output> <!-- Muestra el valor del precio máximo -->
                        </li>
                    </div> <!-- Fin de la parte de filtros para juegos -->
                    
                    <li class="acciones-filtro"> <!-- Elemento para el botón de aplicar filtros -->
                        <button type="submit" class="boton-aplicar" id="aplicar-filtros">Aplicar filtros</button> <!-- Botón para aplicar los filtros seleccionados -->
                    </li>
                    <li class="acciones-filtro"> <!-- Elemento para el botón de restablecer filtros -->
                        <button class="boton-limpiar" id="limpiar-filtros" onclick="restablecerFiltros(event)">Restablecer filtros</button> <!-- Botón para limpiar todos los filtros -->
                    </li>
                </form> <!-- Fin del formulario de filtros -->
                <hr/> <!-- Separador horizontal -->
                <br/> <!-- Salto de línea -->
                <div class="zona-boton-volver"> <!-- Contenedor del botón volver -->
                    <button onclick="mostrarMenuPrincipal(); restaurarFiltrosDeSesion();" class="boton-volver" id="boton-volver" title="Volver al menú principal">Volver atrás</button> <!-- Botón para volver al menú principal -->
                </div>
                <br/> <!-- Salto de línea -->
            </ul> <!-- Fin de la lista del menú de filtros -->
        </nav> <!-- Fin de la navegación lateral -->
    </header> <!-- Fin del header -->
