<?php

    // Encabezado de la página
    include __DIR__ . '/../vistas/comunes/encabezado.php'; /* Incluyo el encabezado común con el menú y estilos base */

    echo '<link rel="stylesheet" href="../recursos/css/estilos_detalles_juego.css">'; /* Cargo los estilos específicos para la página de detalles */
        
    // Verificar si se ha proporcionado un ID de juego válido
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { /* Verifico que llegue un ID válido por GET */
        header('Location: index.php'); /* Si no hay ID válido, redirijo al index */
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

        // Determinar la URL de la página anterior
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'detalles_juego.php') === false) { /* Si hay una página anterior y no es otra página de detalles */
            $_SESSION['referer'] = $_SERVER['HTTP_REFERER']; /* Guardo la página de origen en la sesión para poder volver */
        }

        // Si el juego está inactivo y el usuario NO es admin, redirigir al index
        $es_admin = (isset($_SESSION['id_rol']) && (int)$_SESSION['id_rol'] === 1); /* Verifico si el usuario es admin */
        // Si el usuario no es admin o viene de la biblioteca y el juego está inactivo, redirijo
        if((!$es_admin || $_SESSION['referer'] === 'biblioteca.php') && isset($juego['activo']) && (int)$juego['activo'] === 0) {
            echo '<script>window.location.href = "index.php";</script>'; /* Redirijo con JavaScript */
            exit; /* Termino la ejecución */
        }

        // Obtener los filtros asociados al juego actual
        $consulta = $conexion->prepare("SELECT id_juego, id_filtro FROM juegos_filtros WHERE id_juego = :id_juego"); /* Preparo consulta para obtener los filtros del juego */
        $consulta->bindParam(':id_juego', $juego['id'], PDO::PARAM_INT); /* Vinculo el ID del juego */
        $consulta->execute(); /* Ejecuto la consulta */
        $filtros_juego = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los filtros del juego */
        
        // Obtener el ID del filtro de tipo a partir de la clave del tipo
        $consulta = $conexion->prepare("SELECT id_fijo FROM filtros WHERE clave = :tipo"); /* Preparo consulta para obtener el ID del tipo de juego */
        $consulta->bindParam(':tipo', $juego['tipo'], PDO::PARAM_STR); /* Vinculo la clave del tipo */
        $consulta->execute(); /* Ejecuto la consulta */
        $id_tipo = $consulta->fetchColumn(); /* Obtengo solo el ID del tipo */

        // Obtener el nombre del filtro de tipo a partir de la clave del tipo
        $consulta = $conexion->prepare("SELECT nombre FROM filtros WHERE id_fijo = :id_tipo"); /* Preparo consulta para obtener el nombre del tipo */
        $consulta->bindParam(':id_tipo', $id_tipo, PDO::PARAM_STR); /* Vinculo el ID del tipo */
        $consulta->execute(); /* Ejecuto la consulta */
        $nombre_tipo = $consulta->fetchColumn(); /* Obtengo solo el nombre del tipo */

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
        "); /* Preparo consulta compleja para obtener todos los datos detallados de los filtros del juego */

        $consulta->bindParam(':id_juego', $juego['id'], PDO::PARAM_INT); /* Vinculo el ID del juego */
        $consulta->execute(); /* Ejecuto la consulta */
        $datos_filtros_juego = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los datos completos de los filtros */

        // Verificar si el juego está en favoritos, carrito o biblioteca para el usuario actual
        $esta_en_favoritos = false; /* Inicializo la variable que indica si el juego está en favoritos */
        $esta_en_carrito = false; /* Inicializo la variable que indica si el juego está en el carrito */
        $esta_en_biblioteca = false; /* Inicializo la variable que indica si el juego está en la biblioteca */

        if(isset($_SESSION['id_usuario'])) { /* Si el usuario está logueado */
            // Verificar si el juego está en favoritos para el usuario actual
            $consulta = $conexion->prepare("SELECT id_juego FROM favoritos WHERE id_juego = :id_juego AND id_usuario = :id_usuario"); /* Preparo consulta */
            $consulta->bindParam(':id_juego', $juego['id']); /* Vinculo el ID del juego */
            $consulta->bindParam(':id_usuario', $_SESSION['id_usuario']); /* Vinculo el ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */
            $juego_en_favoritos = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del juego como array asociativo */

            if($juego_en_favoritos) $esta_en_favoritos = true; /* Marco que el juego está en favoritos */

            // Verificar si el juego está en el carrito para el usuario actual
            $consulta = $conexion->prepare("SELECT id_juego FROM carrito WHERE id_juego = :id_juego AND id_usuario = :id_usuario"); /* Preparo consulta */
            $consulta->bindParam(':id_juego', $juego['id']); /* Vinculo el ID del juego */
            $consulta->bindParam(':id_usuario', $_SESSION['id_usuario']); /* Vinculo el ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */
            $juego_en_carrito = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del juego como array asociativo */

            if($juego_en_carrito) $esta_en_carrito = true; /* Marco que el juego está en el carrito */
        
            // Verificar si el juego está en la biblioteca para el usuario actual
            $consulta = $conexion->prepare("SELECT id_juego FROM biblioteca WHERE id_juego = :id_juego AND id_usuario = :id_usuario"); /* Preparo consulta */
            $consulta->bindParam(':id_juego', $juego['id']); /* Vinculo el ID del juego */
            $consulta->bindParam(':id_usuario', $_SESSION['id_usuario']); /* Vinculo el ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */
            $juego_en_biblioteca = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del juego como array asociativo */

            if($juego_en_biblioteca) $esta_en_biblioteca = true; /* Marco que el juego está en la biblioteca */
        }
    } catch (PDOException $e) { /* Si hay error en cualquier consulta */
        echo "Error al obtener los datos: " . $e->getMessage(); /* Muestro el mensaje de error */
        exit; /* Termino la ejecución */
    }

    // Determinar la URL de regreso (referer)
    if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'detalles_juego.php') === false) { /* Si hay una página anterior y no es otra página de detalles */
        $_SESSION['referer'] = $_SERVER['HTTP_REFERER']; /* Guardo la página de origen en sesión para poder volver */
    }

    if(isset($_SESSION['modo_admin'])) { /* Si está en modo admin */
        $rutaRegreso = $_SESSION['referer'] ?? '../vistas/panel_administrador.php'; /* Si no hay página anterior, uso el panel de admin */
    } else { /* Si no está en modo admin */
        $rutaRegreso = $_SESSION['referer'] ?? 'index.php'; /* Si no hay página anterior, uso el index por defecto */
    }

?>

<main> <!-- Contenedor principal de la página -->
    <h1><?php echo htmlspecialchars($juego['nombre']); ?></h1> <!-- Título del juego, escapado para seguridad -->

    <?php if(isset($juego['activo']) && $juego['activo'] == 0) { ?> <!-- Si el juego está descatalogado -->
        <p class="juego-descatalogado">Este juego está descatalogado</p> <!-- Mensaje de advertencia -->
    <?php } ?>

    <div class="detalles-contenido"> <!-- Contenedor principal del contenido -->
        <!-- Sección de la imagen -->
        <div class="imagen-seccion"> <!-- Contenedor para la imagen y precio -->
            <img src="../<?php echo htmlspecialchars($juego['portada']); ?>" alt="<?php echo htmlspecialchars($juego['nombre']); ?>" id="imagen-juego"> <!-- Imagen del juego con ruta relativa -->
            <div class="precio-destacado"> <!-- Contenedor destacado para el precio -->
                Precio: <?php if($juego['precio'] !== '0.00') echo str_replace('.', ',', htmlspecialchars($juego['precio'])) . " €"; else echo "Gratis"; ?> <!-- Precio del juego (mostrado con una coma en vez de un punto), si es 0 muestro "Gratis" -->
            </div>
        </div>

        <!-- Información general -->
        <div class="info-general"> <!-- Contenedor para la información básica del juego -->
            <p><strong>Desarrollador:</strong> <?php echo htmlspecialchars($juego['desarrollador']); ?></p> <!-- Desarrollador del juego -->
            <p><strong>Distribuidor:</strong> <?php echo htmlspecialchars($juego['distribuidor']); ?></p> <!-- Distribuidor del juego -->
            <p><strong>Fecha de lanzamiento:</strong> <?php echo htmlspecialchars($juego['fecha_lanzamiento']); ?></p> <!-- Fecha de lanzamiento -->
            <p><strong>Tipo de juego:</strong> <?php echo htmlspecialchars($nombre_tipo); ?></p> <!-- Tipo de juego obtenido anteriormente -->
            <p><strong>Géneros:</strong> <?php /* Inicio sección para mostrar géneros */
                            if(!empty($datos_filtros_juego)) { /* Si el juego tiene filtros asociados */
                                $generos = []; /* Array para almacenar los géneros */
                                foreach($datos_filtros_juego as $filtro) { /* Recorro todos los filtros */
                                    if($filtro['tipo_filtro'] === 'generos') { /* Si es un filtro de género */
                                        $generos[] = htmlspecialchars($filtro['nombre']); /* Añado el género al array */
                                    } 
                                }
                                if(!empty($generos)) { /* Si encontré géneros */
                                    echo implode(', ', $generos) . '.'; /* Los muestro separados por comas */
                                }
                            } ?></p> <!-- Fin de la sección de géneros -->
            <p><strong>Categorías:</strong> <?php /* Inicio sección para mostrar categorías */
                            if(!empty($datos_filtros_juego)) { /* Si el juego tiene filtros asociados */
                                $categorias = []; /* Array para almacenar las categorías */
                                foreach($datos_filtros_juego as $filtro) { /* Recorro todos los filtros */
                                    if($filtro['tipo_filtro'] === 'categorias') { /* Si es un filtro de categoría */
                                        $categorias[] = htmlspecialchars($filtro['nombre']); /* Añado la categoría al array */
                                    } 
                                }
                                if(!empty($categorias)) { /* Si encontré categorías */
                                    echo implode(', ', $categorias) . '.'; /* Las muestro separadas por comas */
                                }
                            } ?></p> <!-- Fin de la sección de categorías -->
            <p><strong>Modos:</strong> <?php /* Inicio sección para mostrar modos de juego */
                            if(!empty($datos_filtros_juego)) { /* Si el juego tiene filtros asociados */
                                $modos = []; /* Array para almacenar los modos */
                                foreach($datos_filtros_juego as $filtro) { /* Recorro todos los filtros */
                                    if($filtro['tipo_filtro'] === 'modos') { /* Si es un filtro de modo */
                                        $modos[] = htmlspecialchars($filtro['nombre']); /* Añado el modo al array */
                                    } 
                                }
                                if(!empty($modos)) { /* Si encontré modos */
                                    echo implode(', ', $modos) . '.'; /* Los muestro separados por comas */
                                }
                            } ?></p> <!-- Fin de la sección de modos -->
            <p><strong>Clasificaciones PEGI:</strong> <?php /* Inicio sección para mostrar clasificaciones PEGI */
                            if(!empty($datos_filtros_juego)) { /* Si el juego tiene filtros asociados */
                                $clasificaciones = []; /* Array para almacenar las clasificaciones */
                                foreach($datos_filtros_juego as $filtro) { /* Recorro todos los filtros */
                                    if($filtro['tipo_filtro'] === 'clasificacionPEGI') { /* Si es un filtro de clasificación PEGI */
                                        $clasificaciones[] = htmlspecialchars($filtro['nombre']); /* Añado la clasificación al array */
                                    } 
                                }
                                if(!empty($clasificaciones)) { /* Si encontré clasificaciones */
                                    echo implode(', ', $clasificaciones) . '.'; /* Las muestro separadas por comas */
                                }
                            } ?></p> <!-- Fin de la sección de clasificaciones PEGI -->
        </div>
    </div>

    <hr> <!-- Línea separadora -->
    <br> <!-- Espacio adicional -->

    <!-- Descripción completa -->
    <div class="descripcion-completa"> <!-- Contenedor para la descripción detallada -->
        <h2>Descripción detallada:</h2> <!-- Título de la sección -->
        <p><?php echo htmlspecialchars($juego['descripcion']); ?></p> <!-- Descripción completa del juego -->
    </div>

    <!-- Requisitos del sistema -->
    <div class="requisitos-sistema"> <!-- Contenedor para los requisitos del sistema -->
        <h2>Requisitos del sistema</h2> <!-- Título de la sección -->
        <p><?php echo htmlspecialchars($juego['requisitos']); ?></p> <!-- Requisitos del sistema -->
    </div>

    <div class="opciones"> <!-- Contenedor para las opciones de acción -->
        <h2>Opciones:</h2> <!-- Título de la sección de opciones -->
        <a href="<?php echo $rutaRegreso; ?>" class="boton-volver"> <!-- Botón para volver a la página anterior -->
            <img src="../recursos/imagenes/atras.png" alt="Icono de Volver" id="icono-volver"> <!-- Icono de volver -->
            <span>Volver atrás</span> <!-- Texto del botón -->
        </a>
        <?php if(!isset($_SESSION['modo_admin']) || $_SESSION['modo_admin'] === false) { ?> <!-- Si no está en modo admin -->
            <?php if($esta_en_favoritos) { ?> <!-- Si el juego ya está en favoritos -->
                <a href="#" onclick="mandarFavoritos('eliminar', <?php echo $juego['id']; ?>, 'modal1', '<h1>Juego eliminado de favoritos</h1>', false)" class="boton-favorito"> <!-- Enlace para eliminar de favoritos -->
                    <img src="../recursos/imagenes/en_favoritos_circulo.png" alt="Icono de Favoritos" id="detalles-icono-favoritos"> <!-- Icono de favoritos -->
                    <span>Eliminar de favoritos</span> <!-- Texto del botón -->
                </a>
            <?php } else { ?> <!-- Si el juego no está en favoritos -->
                <a <?php if(isset($_SESSION['id_usuario'])) { echo 'href="#" onclick="mandarFavoritos(\'agregar\', ' . $juego['id'] . ', \'modal1\', \'<h1>Juego añadido a favoritos</h1>\', false, null, this)"'; } else { echo 'href="../sesiones/formulario_autenticacion.php"'; } ?> class="boton-favorito"> <!-- Enlace para añadir a favoritos -->
                    <img src="../recursos/imagenes/favoritos_circulo.png" alt="Icono de Favoritos" id="detalles-icono-favoritos"> <!-- Icono de favoritos -->
                    <span>Añadir a favoritos</span> <!-- Texto del botón -->
                </a>
            <?php } ?>
            <?php if(!$esta_en_biblioteca) { ?> <!-- Si el juego no está en la biblioteca -->
                <?php if($esta_en_carrito) { ?> <!-- Si el juego ya está en el carrito -->
                    <a href="#" onclick="mandar('eliminar', <?php echo $juego['id']; ?>, 'modal1', '<h1>Juego eliminado del carrito</h1>', false)" id="tarjeta-eliminar<?php echo $juego['id']; ?>" class="detalles-boton-carrito"> <!-- Enlace para quitar del carrito -->
                        <img src="../recursos/imagenes/en_carrito2.png" alt="Icono de Carrito" id="detalles-icono-carrito"> <!-- Icono del carrito -->
                        <span>Quitar del carrito</span> <!-- Texto del botón -->
                    </a>
                <?php } else { ?> <!-- Si el juego no está en el carrito -->
                    <a <?php if (isset($_SESSION['id_usuario'])) { echo 'href="#" onclick="mandar(\'agregar\', ' . $juego['id'] . ', \'modal1\', \'<h1>Juego añadido al carrito</h1>\', false, null, this)"'; } else { echo 'href="../sesiones/formulario_autenticacion.php"'; } ?> id="tarjeta-anadir<?php echo $juego['id']; ?>" class="detalles-boton-carrito"> <!-- Enlace para añadir al carrito -->
                        <img src="../recursos/imagenes/carrito2.png" alt="Icono de Carrito" id="detalles-icono-carrito"> <!-- Icono del carrito -->
                        <span>Añadir al carrito</span> <!-- Texto del botón -->
                    </a>
                <?php } ?>
                <?php 
                $carrito_ficticio = []; /* Inicializo el array del carrito ficticio */
                $carrito_ficticio[] = [ /* Agrego los datos del juego al carrito */
                    'id' => $id_juego, /* ID del juego */
                    'nombre' => $juego['nombre'], /* Nombre del juego */
                    'portada' => $juego['portada'], /* Portada del juego */
                    'tipo' => $nombre_tipo, /* Uso el nombre del tipo en lugar de la clave */
                    'precio' => $juego['precio'], /* Precio del juego */
                    'resumen' => $juego['resumen'] /* Resumen del juego */
                ]; 
                $carrito_json = htmlspecialchars(json_encode($carrito_ficticio), ENT_QUOTES, 'UTF-8'); /* Escapo el JSON para usarlo de forma segura en HTML */
                ?>
                <a <?php if(isset($_SESSION['id_usuario'])) { echo 'href="#" onclick="mostrarResumenPedido(\'' . $carrito_json . '\', true)"'; } else { echo 'href="../sesiones/formulario_autenticacion.php"'; } ?> class="boton-comprar"> <!-- Enlace para comprar directamente -->
                    <img src="../recursos/imagenes/comprar.png" alt="Icono de Comprar" id="icono-comprar"> <!-- Icono de comprar -->
                    <span>Comprar ya</span> <!-- Texto del botón -->
                </a>
            <?php } else { ?> <!-- Si el juego ya está en la biblioteca -->
                <a href="#" onclick="descambiarJuego(<?php echo $juego['id']; ?>, <?php echo $juego['precio']; ?>, '<?php echo $juego['nombre']; ?>')" class="boton-descambiar"> <!-- Enlace para descambiar el juego -->
                    <img src="../recursos/imagenes/descambiar.png" alt="Icono de Descambiar" id="icono-descambiar"> <!-- Icono del carrito -->
                    <span>Descambiar juego</span> <!-- Texto del botón -->
                </a>
                <?php if($juego['tipo'] === 'interno') { ?> <!-- Si el juego es interno (no externo) --> 
                    <a href="#" class="boton-jugar"> <!-- Enlace para jugar al juego -->
                        <img src="../recursos/imagenes/jugar.png" alt="Icono de Jugar" id="icono-jugar"> <!-- Icono de jugar -->
                        <span>Jugar</span> <!-- Texto del botón -->
                    </a>
                <?php } ?>
            <?php } ?>
        <?php } else { ?> <!-- Si está en modo admin -->
            <a href="../vistas/editar_juego.php?id=<?php echo $juego['id']; ?>" class="boton-editar-juego"> <!-- Enlace a editar del juego -->
                <img src="../recursos/imagenes/editar_juego.png" alt="Icono de Editar" id="icono-editar"> <!-- Icono de editar -->
                <span>Editar</span> <!-- Texto del botón -->
            </a>
            <?php if(isset($juego['activo']) && $juego['activo'] == 0) { ?> <!-- Si el juego está descatalogado -->
                <a href="#" onclick="reactivarJuego(<?php echo (int)$juego['id']; ?>, '<?php echo htmlspecialchars($juego['nombre'], ENT_QUOTES); ?>')" class="boton-reactivar"> <!-- Enlace para reactivar juego -->
                    <img src="../recursos/imagenes/reactivar.png" alt="Icono de Reactivar" id="icono-reactivar"> <!-- Icono de reactivar -->
                    <span>Reactivar</span> <!-- Texto del botón -->
                </a>
            <?php } else { ?>
                <a href="#" onclick="eliminarJuego(<?php echo (int)$juego['id']; ?>, '<?php echo htmlspecialchars($juego['nombre'], ENT_QUOTES); ?>')" class="boton-eliminar"> <!-- Enlace para eliminar juego -->
                    <img src="../recursos/imagenes/eliminar_juego.png" alt="Icono de Eliminar" id="icono-eliminar"> <!-- Icono de eliminar -->
                    <span>Eliminar</span> <!-- Texto del botón -->
                </a>
            <?php } ?>
        <?php } ?>
    </div>

</main>

<br> <!-- Espacio antes del pie de página -->

<script src="../recursos/js/carrito.js" defer></script> <!-- Script para funcionalidad de modales -->

<script src="../recursos/js/favoritos.js" defer></script> <!-- Script para funcionalidad de favoritos -->

<!-- Pie de página -->
<?php include __DIR__ . '/../vistas/comunes/pie.php'; ?> <!-- Incluyo el pie de página común -->
