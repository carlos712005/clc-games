    <!-- Encabezado -->
    <?php include __DIR__ . '/../vistas/comunes/encabezado.php'; ?> <!-- Incluyo el encabezado con menú y estilos -->

    <!--Función para mostrar los juegos -->
    <?php include __DIR__ . '/../funciones/mostrar_juegos.php'; ?> <!-- Cargo la función que muestra los juegos filtrados -->
    
    <link rel="stylesheet" href="../recursos/css/estilos_carrusel.css" type="text/css"> <!-- Estilos del carrusel de imágenes -->

    <link rel="stylesheet" href="../recursos/css/estilos_index.css" type="text/css"> <!-- Estilos generales del index -->
    
    <main> <!-- Contenedor principal del index -->

        <section id="carrusel"> <!-- Sección del carrusel de imágenes promocionales y algunas partes de la página -->

            <div id="carrusel-contenido"></div> <!-- Contenedor donde se cargarán las diapositivas del carrusel -->

            <!-- Botones de navegación -->                
            <div class="circulos"> <!-- Contenedor para los controles de navegación del carrusel -->
                <!-- Contenedor para los botones de navegación y círculos del carrusel -->

                <button onclick="cambiarManual(-1)" class="boton anterior" title="Ir a la diapositiva anterior">&#60;</button> <!-- Botón flecha izquierda para ir atrás -->
                <!-- Botón de navegación hacia el carrusel anterior -->

                <button onclick="cambiarIndice(0)" class="circulo activo" title="Ir a la diapositiva número 1"></button> <!-- Círculo para la primera diapositiva, activo por defecto -->
                <!-- Círculo representando el carrusel actual, marcado como activo -->

                <button onclick="cambiarIndice(1)" class="circulo" title="Ir a la diapositiva número 2"></button> <!-- Círculo para la segunda diapositiva -->
                <!-- Círculo que enlaza al segundo carrusel -->

                <button onclick="cambiarIndice(2)" class="circulo" title="Ir a la diapositiva número 3"></button> <!-- Círculo para la tercera diapositiva -->
                <!-- Círculo que enlaza al tercer carrusel -->

                <button onclick="cambiarIndice(3)" class="circulo" title="Ir a la diapositiva número 4"></button> <!-- Círculo para la cuarta diapositiva -->
                <!-- Círculo que enlaza al cuarto carrusel -->

                <button onclick="cambiarIndice(4)" class="circulo" title="Ir a la diapositiva número 5"></button> <!-- Círculo para la quinta diapositiva -->
                <!-- Círculo que enlaza al quinto carrusel -->

                <button onclick="cambiarIndice(5)" class="circulo" title="Ir a la diapositiva número 6"></button> <!-- Círculo para la sexta diapositiva -->
                <!-- Círculo que enlaza al sexto carrusel -->

                <?php if(!isset($_SESSION['id_usuario'])) { ?> <!-- Si el usuario NO está logueado -->
                    <button onclick="cambiarIndice(6)" class="circulo" title="Ir a la diapositiva número 7"></button> <!-- Círculo para séptima diapositiva (solo para usuarios no logueados) -->
                    <!-- Círculo que enlaza al séptimo carrusel -->
                <?php } ?> <!-- Fin del condicional para usuarios no logueados -->

                <?php if(isset($_SESSION['id_usuario'])) { ?> <!-- Si el usuario SÍ está logueado -->
                    <button onclick="cambiarIndice(6)" class="circulo" title="Ir a la diapositiva número 8"></button> <!-- Círculo para octava diapositiva (solo para usuarios logueados) -->
                <!-- Círculo que enlaza al octavo carrusel -->
                <?php } ?> <!-- Fin del condicional para usuarios logueados -->

                <button onclick="cambiarManual(1)" class="boton siguiente" title="Ir a la diapositiva siguiente">&#62;</button> <!-- Botón flecha derecha para ir adelante -->
                <!-- Botón de navegación hacia el siguiente carrusel -->

            </div> <!-- Fin del contenedor de controles del carrusel -->

        </section> <!-- Fin de la sección del carrusel -->

        <div id="contenedor-juegos" class="contenedor-juegos"> <!-- Contenedor principal para mostrar todos los juegos -->

            <?php /* Inicio bloque PHP para obtener juegos de la base de datos */
                try { /* Inicio bloque try para capturar errores de base de datos */
                    if(isset($_SESSION['id_usuario'])) { /* Si el usuario está logueado */
                        $consulta = $conexion->prepare("
                            SELECT j.id, j.nombre, j.portada, j.tipo, j.activo, j.precio, j.resumen, b.id_juego AS en_biblioteca
                            FROM juegos j
                            LEFT JOIN biblioteca b ON j.id = b.id_juego AND b.id_usuario = :id_usuario
                            WHERE j.activo = 1
                            ORDER BY b.id_juego ASC, j.actualizado_en DESC
                        "); /* Preparo consulta para obtener los juegos solo activos, ordenados por biblioteca y fecha de actualización */
                        $consulta->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT); /* Vinculo el ID del usuario */
                    } else { /* Si el usuario no está logueado */
                        $consulta = $conexion->prepare("SELECT id, nombre, portada, tipo, activo, precio, resumen FROM juegos WHERE activo = 1 ORDER BY actualizado_en DESC"); /* Preparo consulta para obtener solo juegos activos ordenados por fecha de actualización */
                    }
                    $consulta->execute(); /* Ejecuto la consulta */
                    $juegos = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los juegos como array asociativo */
                } catch (PDOException $e) { /* Si hay error en la consulta */
                    $_SESSION['mensaje_error'] = 'Error al conectar con la base de datos: ' . $e->getMessage(); /* Guardo el error en sesión */
                    header('Location: index.php'); /* Redirijo al mismo index */
                    exit; /* Termino la ejecución */
                }

            ?> <!-- Fin del bloque PHP -->

            <div class="juegos"> <!-- Contenedor específico para las tarjetas de juegos -->
                <!-- Aquí se cargarán los juegos dinámicamente -->
                <?php /* Llamo a la función que muestra los juegos filtrados */
                    mostrarJuegos($juegos, $conexion); /* Paso los juegos y la conexión a la función */
                ?> <!-- Fin de la llamada a la función -->
            </div> <!-- Fin del contenedor de juegos -->

        </div> <!-- Fin del contenedor principal de juegos -->

    </main> <!-- Fin del contenedor principal -->

    <script> // Inicio del script
        // Paso los datos de sesión a JavaScript
        window.datosUsuario = { /* Creo objeto global para pasar datos del usuario a JavaScript */
            <?php if (isset($_SESSION['id_usuario'])) { /* Si hay usuario logueado */ ?>
                autenticado: true /* Marco como autenticado */
            <?php } else { /* Si no hay usuario logueado */ ?>
                autenticado: false /* Marco como no autenticado */
            <?php } ?> <!-- Fin del condicional -->
        }; /* Fin del objeto datosUsuario */
    </script> <!-- Fin del script -->

    <script src="../recursos/js/carrusel.js"></script> <!-- Cargo el JavaScript del carrusel -->

    <!-- Pie de página -->
    <?php include __DIR__ . '/../vistas/comunes/pie.php'; ?> <!-- Incluyo el pie de página común -->
