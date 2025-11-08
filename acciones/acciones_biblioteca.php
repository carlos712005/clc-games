<?php

    session_start(); /* Inicio la sesión para poder acceder a las variables de sesión */
    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */
    require_once __DIR__ . "/../funciones/mostrar_juegos.php"; /* Incluyo la función para mostrar juegos */

    function mostrarBiblioteca($conexion, $id_usuario) {
        try { /* Inicio bloque try para capturar errores */
            // Obtener los juegos de la biblioteca completos con toda su información
            $consulta = $conexion->prepare("
                SELECT j.id, j.nombre, j.portada, j.tipo, j.activo, j.precio, j.resumen 
                FROM biblioteca b 
                INNER JOIN juegos j ON b.id_juego = j.id 
                WHERE b.id_usuario = :id_usuario 
                ORDER BY j.tipo DESC, b.fecha_adquisicion DESC
            "); /* Preparo la consulta para obtener los datos completos de los juegos de la biblioteca ordenados primero por tipo y luego por fecha */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */

            $juegos_biblioteca = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los juegos de la biblioteca con sus datos completos */

            if(empty($juegos_biblioteca)) { /* Si no hay juegos en la biblioteca */
                ?> <!-- Inicio HTML para mensaje de "sin juegos" -->
                <div class="sin-juegos"> <!-- Contenedor para el mensaje -->
                    <h2 data-translate="no_hay_juegos">No hay juegos en la biblioteca.</h2> <!-- Mensaje informativo -->
                </div>
                <?php /* Vuelvo a PHP */
            } else {
                mostrarJuegos($juegos_biblioteca, $conexion, null, $id_usuario); /* Muestro los juegos de la biblioteca pasando el array completo */
            }
        } catch (PDOException $e) { /* Si hay error al mostrar los elementos de la biblioteca */
            error_log("Error al mostrar los elementos de la biblioteca: " . $e->getMessage()); /* Registro el error en el log */
            // También envío el error al cliente para debug
            echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()]);
            return []; /* Retorno array vacío */
        }
    }

    if(isset($_POST['accion'])) { /* Verifico que llegue la acción a realizar */
        $accion = $_POST['accion']; /* Obtengo la acción a realizar */
        switch($accion) { /* Según la acción a realizar */
            case 'mostrar': /* Si la acción es obtener los elementos de la biblioteca */
                $id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : $_SESSION['id_usuario']; /* Obtengo el ID del usuario a mostrar, si no se pasa, uso el del usuario logueado */
                // Si no es admin y está intentando ver biblioteca de otro usuario, denegar
                if($_SESSION['id_rol'] != 1 && $_SESSION['id_usuario'] != $id_usuario) {
                    echo json_encode(["error" => "Acceso denegado"]); /* Retorno un error en formato JSON */
                    break;
                }
                mostrarBiblioteca($conexion, $id_usuario); /* Llamo a la función para mostrar los elementos de la biblioteca */
                break;
            default: /* Si la acción no es reconocida */
                echo json_encode(["error" => "Acción no válida"]); /* Retorno un error en formato JSON */
                break;
        }
    } else { /* Si no se recibió ninguna acción */
        echo json_encode(["error" => "No se recibió ninguna acción"]); /* Retorno un error en formato JSON */
    }

?>