    <!-- Encabezado -->
    <?php include __DIR__ . '/../vistas/comunes/encabezado.php'; ?> <!-- Incluyo el encabezado con menú y estilos -->

    <!--Función para mostrar los juegos -->
    <?php include __DIR__ . '/../funciones/mostrar_juegos.php'; ?> <!-- Cargo la función que muestra los juegos filtrados -->

    <link rel="stylesheet" href="../recursos/css/estilos_index.css" type="text/css"> <!-- Estilos generales del index -->
    
    <main> <!-- Contenedor principal de proximos lanzamientos -->

        <div id="contenedor-juegos" class="contenedor-juegos"> <!-- Contenedor principal para mostrar todos los juegos -->

            <?php
                try { /* Inicio bloque try para capturar errores de base de datos */
                    if(isset($_SESSION['id_usuario'])) { /* Si el usuario está logueado */
                        /* Verificar si hay una búsqueda activa */
                        if(isset($_SESSION['datos_busqueda']) && isset($_SESSION['datos_busqueda']['juegos_encontrados'])) {
                            $ids_juegos = $_SESSION['datos_busqueda']['juegos_encontrados']; /* Obtengo los IDs de juegos encontrados */
                            
                            // Preparar una consulta con los IDs de juegos encontrados
                            $cantidad = count($ids_juegos); /* Cantidad de juegos encontrados */
                            $signos = array_fill(0, $cantidad, '?'); /* Creo un array de forma ['?', '?', '?', ...] */
                            $cadena = implode(',', $signos); /* Uno con comas: '?,?,?' */
                            $consulta = $conexion->prepare("
                                SELECT j.id, j.nombre, j.fecha_lanzamiento, j.portada, j.tipo, j.activo, j.precio, j.resumen, b.id_juego AS en_biblioteca
                                FROM juegos j
                                LEFT JOIN biblioteca b ON j.id = b.id_juego AND b.id_usuario = ?
                                WHERE j.id IN ($cadena) AND j.activo = 1 AND j.fecha_lanzamiento > NOW()
                                ORDER BY b.id_juego ASC, j.actualizado_en DESC
                            "); /* Preparo consulta para obtener los juegos encontrados que estén activos y con fecha de lanzamiento futura, ordenados por biblioteca y fecha de actualización */
                            // Vincular id_usuario primero (posición 1), luego los IDs de juegos
                            $consulta->bindValue(1, $_SESSION['id_usuario'], PDO::PARAM_INT); /* Vinculo el ID del usuario */
                            foreach($ids_juegos as $indice => $id) { /* Recorro los IDs de juegos */
                                $consulta->bindValue($indice + 2, $id, PDO::PARAM_INT); /* Vinculo cada ID de juego (empezando en posición 2) */
                            }
                        } else { /* No hay búsqueda activa */
                            // Obtener todos los juegos activos con fecha de lanzamiento futura
                            $consulta = $conexion->prepare("
                                SELECT j.id, j.nombre, j.fecha_lanzamiento, j.portada, j.tipo, j.activo, j.precio, j.resumen, b.id_juego AS en_biblioteca
                                FROM juegos j
                                LEFT JOIN biblioteca b ON j.id = b.id_juego AND b.id_usuario = :id_usuario
                                WHERE j.activo = 1 AND j.activo = 1 AND j.fecha_lanzamiento > NOW()
                                ORDER BY b.id_juego ASC, j.actualizado_en DESC
                            "); /* Preparo consulta para obtener los juegos solo activos y con fecha de lanzamiento futura, ordenados por biblioteca y fecha de actualización */
                            $consulta->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT); /* Vinculo el ID del usuario */
                        }
                    } else { /* Si el usuario no está logueado */
                        // Verificar si hay una búsqueda activa
                        if(isset($_SESSION['datos_busqueda']) && isset($_SESSION['datos_busqueda']['juegos_encontrados'])) {
                            $ids_juegos = $_SESSION['datos_busqueda']['juegos_encontrados']; /* Obtengo los IDs de juegos encontrados */
                            
                            // Preparar una consulta con los IDs de juegos encontrados
                            $cantidad = count($ids_juegos); /* Cantidad de juegos encontrados */
                            $signos = array_fill(0, $cantidad, '?'); /* Creo un array de forma ['?', '?', '?', ...] */
                            $cadena = implode(',', $signos); /* Uno con comas: '?,?,?' */
                            $consulta = $conexion->prepare("
                                SELECT id, nombre, fecha_lanzamiento, portada, tipo, activo, precio, resumen 
                                FROM juegos
                                WHERE id IN ($cadena) AND activo = 1 AND fecha_lanzamiento > NOW()
                                ORDER BY actualizado_en DESC
                            "); /* Preparo consulta para obtener los juegos encontrados que estén activos y con fecha de lanzamiento futura, ordenados por fecha de actualización */
                            foreach($ids_juegos as $indice => $id) { /* Recorro los IDs de juegos */
                                $consulta->bindValue($indice + 1, $id, PDO::PARAM_INT); /* Vinculo cada ID de juego (empezando en posición 1) */
                            }
                        } else { /* No hay búsqueda activa */
                            // Obtener todos los juegos activos con fecha de lanzamiento futura
                            $consulta = $conexion->prepare("
                                SELECT id, nombre, fecha_lanzamiento, portada, tipo, activo, precio, resumen 
                                FROM juegos 
                                WHERE activo = 1 AND fecha_lanzamiento > NOW() 
                                ORDER BY actualizado_en DESC
                                "); /* Preparo consulta para obtener solo juegos activos y con fecha de lanzamiento futura ordenados por fecha de actualización */
                        }
                    }
                    $consulta->execute(); /* Ejecuto la consulta */
                    $juegos = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los juegos como array asociativo */
                } catch (PDOException $e) { /* Si hay error en la consulta */
                    echo 'Error al conectar con la base de datos: ' . $e->getMessage();
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

    <!-- Pie de página -->
    <?php include __DIR__ . '/../vistas/comunes/pie.php'; ?> <!-- Incluyo el pie de página común -->
