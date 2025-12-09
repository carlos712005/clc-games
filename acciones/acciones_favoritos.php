<?php

    session_start(); /* Inicio la sesión para poder acceder a las variables de sesión */
    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */
    require_once __DIR__ . "/../funciones/mostrar_juegos.php"; /* Incluyo la función para mostrar juegos */

    // Función para agregar un juego a favoritos
    function agregarAFavoritos($conexion, $id_usuario, $id_juego) {
        try { /* Inicio bloque try para capturar errores */
            // Verificar que el juego no esté ya en favoritos
            $consulta = $conexion->prepare("SELECT id FROM favoritos WHERE id_usuario = :id_usuario AND id_juego = :id_juego"); /* Preparo la consulta para verificar si el juego ya está en favoritos */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */

            $existe_juego = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el ID del juego si existe */

            if (!$existe_juego) { /* Si el juego no está en favoritos, lo agrego */
                $consulta = $conexion->prepare("INSERT INTO favoritos (id_usuario, id_juego) VALUES (:id_usuario, :id_juego)"); /* Preparo la consulta para insertar un nuevo juego en favoritos */
                $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
                $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */

                $consulta->execute(); /* Ejecuto la consulta */
            }
        } catch (PDOException $e) { /* Si hay error al agregar a favoritos */
            echo json_encode(["error" => "Error al agregar a favoritos: " . $e->getMessage()]); /* Retorno el error en formato JSON */
        }
    }

    // Función para eliminar un juego de favoritos
    function eliminarDeFavoritos($conexion, $id_usuario, $id_juego) {
        try { /* Inicio bloque try para capturar errores */
            $consulta = $conexion->prepare("DELETE FROM favoritos WHERE id_juego = :id_juego AND id_usuario = :id_usuario"); /* Preparo la consulta para eliminar el juego de favoritos */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */

            $consulta->execute(); /* Ejecuto la consulta */
        } catch (PDOException $e) { /* Si hay error al eliminar de favoritos */
            echo json_encode(["error" => "Error al eliminar de favoritos: " . $e->getMessage()]); /* Retorno el error en formato JSON */
        }
    }

    // Función para mostrar los juegos favoritos de un usuario
    function mostrarFavoritos($conexion, $id_usuario) {
        try { /* Inicio bloque try para capturar errores */
            // Verificar si hay una búsqueda activa
            if(isset($_SESSION['datos_busqueda']) && isset($_SESSION['datos_busqueda']['juegos_encontrados'])) {
                $ids_juegos = $_SESSION['datos_busqueda']['juegos_encontrados']; /* Obtengo los IDs de juegos encontrados */
                
                // Preparar una consulta con los IDs de juegos encontrados
                $cantidad = count($ids_juegos); /* Cantidad de juegos encontrados */
                $signos = array_fill(0, $cantidad, '?'); /* Creo un array de forma ['?', '?', '?', ...] */
                $cadena = implode(',', $signos); /* Uno con comas: '?,?,?' */
                $consulta = $conexion->prepare("
                    SELECT j.id, j.nombre, j.fecha_lanzamiento, j.portada, j.tipo, j.activo, j.precio, j.resumen
                    FROM favoritos f
                    INNER JOIN juegos j ON f.id_juego = j.id AND f.id_usuario = ?
                    WHERE j.id IN ($cadena) AND j.activo = 1
                    ORDER BY f.creado_en DESC
                "); /* Preparo consulta para obtener los juegos favoritos, del usuario, encontrados que estén activos ordenados por fecha */
                // Vincular id_usuario primero (posición 1), luego los IDs de juegos
                $consulta->bindValue(1, $id_usuario, PDO::PARAM_INT); /* Vinculo el ID del usuario */
                foreach($ids_juegos as $indice => $id) { /* Recorro los IDs de juegos */
                    $consulta->bindValue($indice + 2, $id, PDO::PARAM_INT); /* Vinculo cada ID de juego (empezando en posición 2) */
                }
            } else { /* No hay búsqueda activa */
                // Obtener los juegos favoritos completos con toda su información
                $consulta = $conexion->prepare("
                    SELECT j.id, j.nombre, j.fecha_lanzamiento, j.portada, j.tipo, j.activo, j.precio, j.resumen 
                    FROM favoritos f 
                    INNER JOIN juegos j ON f.id_juego = j.id 
                    WHERE f.id_usuario = :id_usuario AND j.activo = 1
                    ORDER BY f.creado_en DESC
                "); /* Preparo la consulta para obtener los datos completos de los juegos favoritos activos ordenados por fecha */
                $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            }
            $consulta->execute(); /* Ejecuto la consulta */

            $juegos_favoritos = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los juegos favoritos con sus datos completos */

            if(empty($juegos_favoritos)) { /* Si no hay juegos en favoritos */
                ?> <!-- Inicio HTML para mensaje de "sin juegos" -->
                <div class="sin-juegos"> <!-- Contenedor para el mensaje -->
                    <h2 data-translate="no_hay_juegos">No hay juegos en favoritos.</h2> <!-- Mensaje informativo -->
                </div>
                <?php /* Vuelvo a PHP */
            } else { /* Si hay juegos en favoritos */
                mostrarJuegos($juegos_favoritos, $conexion); /* Muestro los juegos favoritos pasando el array completo */
            }
        } catch (PDOException $e) { /* Si hay error al mostrar los elementos de favoritos */
            echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()]); /* Retorno el error en formato JSON */
            return []; /* Retorno array vacío */
        }
    }

    if(isset($_POST['accion'])) { /* Verifico que llegue la acción a realizar */
        $accion = $_POST['accion']; /* Obtengo la acción a realizar */
        switch($accion) { /* Según la acción a realizar */
            case 'agregar': /* Si la acción es agregar un elemento a favoritos */
                if(isset($_POST['id_juego'])) { /* Verifico que lleguen los datos necesarios */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    agregarAFavoritos($conexion, $_SESSION['id_usuario'], $id_juego); /* Llamo a la función para agregar a favoritos */
                }
                break;
            case 'eliminar': /* Si la acción es eliminar un elemento de favoritos */
                if(isset($_POST['id_juego'])) { /* Verifico que llegue el ID del juego */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    eliminarDeFavoritos($conexion, $_SESSION['id_usuario'], $id_juego); /* Llamo a la función para eliminar de favoritos */
                }
                break;
            case 'mostrar': /* Si la acción es obtener los elementos de favoritos */
                $id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : $_SESSION['id_usuario']; /* Obtengo el ID del usuario a mostrar, si no se pasa, uso el del usuario logueado */
                // Si no es admin y está intentando ver favoritos de otro usuario, denegar
                if($_SESSION['id_rol'] != 1 && $_SESSION['id_usuario'] != $id_usuario) {
                    echo json_encode(["error" => "Acceso denegado"]); /* Retorno un error en formato JSON */
                    break;
                }
                mostrarFavoritos($conexion, $id_usuario); /* Llamo a la función para mostrar los favoritos */
                break;
            default: /* Acción no reconocida */
                echo json_encode(["error" => "Acción no válida"]); /* Retorno un error en formato JSON */
                break;
        }
    } else { /* Si no se recibió ninguna acción */
        echo json_encode(["error" => "No se recibió ninguna acción"]); /* Retorno un error en formato JSON */
    }
    
?>