<?php

    session_start(); /* Inicio la sesión para poder acceder a las variables de sesión */
    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */

    //Función para crear un comentario
    function crearComentario($conexion, $id_usuario, $id_juego, $texto_comentario) {
        try { /* Inicio bloque try para capturar errores */
            if (empty($texto_comentario)) { /* Si el comentario está vacío */
                $_SESSION['error_general_comentario'] = "El comentario no puede estar vacío."; /* Establezco mensaje de error */
                header('Location: ../publico/detalles_juego.php?id=' . $id_juego); /* Redirijo de vuelta a detalles del juego */
                exit; /* Termino la ejecución */
            }

            // Verificar que el comentario no exista ya
            $consulta = $conexion->prepare("SELECT id FROM comentarios WHERE id_usuario = :id_usuario AND id_juego = :id_juego"); /* Preparo la consulta para verificar si el comentario ya existe */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */

            $existe_comentario = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el ID del comentario si existe */

            if ($existe_comentario) { /* Si el comentario ya existe */
                $_SESSION['error_general_comentario'] = "Ya tienes un comentario para este juego."; /* Establezco mensaje de error */
                header('Location: ../publico/detalles_juego.php?id=' . $id_juego); /* Redirijo de vuelta a detalles del juego */
                exit; /* Termino la ejecución */
            }

            // Insertar el nuevo comentario
            $consulta = $conexion->prepare("INSERT INTO comentarios (id_usuario, id_juego, comentario) VALUES (:id_usuario, :id_juego, :comentario)"); /* Preparo la consulta para insertar el comentario */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->bindParam(':comentario', $texto_comentario, PDO::PARAM_STR); /* Vinculo el parámetro del texto del comentario */
            $consulta->execute(); /* Ejecuto la consulta */

            $_SESSION['mensaje_exito_comentario'] = "Comentario creado correctamente."; /* Establezco mensaje de éxito */
            header('Location: ../publico/detalles_juego.php?id=' . $id_juego); /* Redirijo de vuelta a detalles del juego */
            exit; /* Termino la ejecución */
        } catch (PDOException $e) { /* Si hay error al crear el comentario */
            $_SESSION['error_general_comentario'] = "Error al crear el comentario: " . $e->getMessage(); /* Establezco mensaje de error */
            header('Location: ../publico/detalles_juego.php?id=' . $id_juego); /* Redirijo de vuelta a detalles del juego */
            exit; /* Termino la ejecución */
        }
    }

    //Función para editar un comentario
    function editarComentario($conexion, $id_usuario, $id_juego, $texto_comentario) {
        try { /* Inicio bloque try para capturar errores */
            if (empty($texto_comentario)) { /* Si el comentario está vacío */
                
                $_SESSION['error_general_comentario'] = "El comentario no puede estar vacío."; /* Establezco mensaje de error */
                header('Location: ../publico/detalles_juego.php?id=' . $id_juego); /* Redirijo de vuelta a detalles del juego */
                exit; /* Termino la ejecución */
            }

            // Verificar que el comentario exista
            $consulta = $conexion->prepare("SELECT id FROM comentarios WHERE id_usuario = :id_usuario AND id_juego = :id_juego"); /* Preparo la consulta para verificar si el comentario existe */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */

            $existe_comentario = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el ID del comentario si existe */

            if (!$existe_comentario) { /* Si el comentario no existe */
                $_SESSION['error_general_comentario'] = "No tienes un comentario para este juego."; /* Establezco mensaje de error */
                header('Location: ../publico/detalles_juego.php?id=' . $id_juego); /* Redirijo de vuelta a detalles del juego */
                exit; /* Termino la ejecución */
            }

            // Actualizar el comentario
            $consulta = $conexion->prepare("UPDATE comentarios SET comentario = :comentario, actualizado_en = NOW() WHERE id_usuario = :id_usuario AND id_juego = :id_juego"); /* Preparo la consulta para actualizar el comentario */
            $consulta->bindParam(':comentario', $texto_comentario, PDO::PARAM_STR); /* Vinculo el parámetro del texto del comentario */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */

            $_SESSION['mensaje_exito_comentario'] = "Comentario editado correctamente."; /* Establezco mensaje de éxito */
            header('Location: ../publico/detalles_juego.php?id=' . $id_juego); /* Redirijo de vuelta a detalles del juego */
            exit; /* Termino la ejecución */
        } catch (PDOException $e) { /* Si hay error al editar el comentario */
            $_SESSION['error_general_comentario'] = "Error al editar el comentario: " . $e->getMessage(); /* Establezco mensaje de error */
            header('Location: ../publico/detalles_juego.php?id=' . $id_juego); /* Redirijo de vuelta a detalles del juego */
            exit; /* Termino la ejecución */
        }
    }

    //Función para eliminar un comentario
    function eliminarComentario($conexion, $id_usuario, $id_juego) {
        try { /* Inicio bloque try para capturar errores */
            // Verificar que el comentario exista
            $consulta = $conexion->prepare("SELECT id FROM comentarios WHERE id_usuario = :id_usuario AND id_juego = :id_juego"); /* Preparo la consulta para verificar si el comentario existe */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */

            $existe_comentario = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el ID del comentario si existe */

            if (!$existe_comentario) { /* Si el comentario no existe */
                $_SESSION['error_general_comentario'] = "No tienes un comentario para este juego."; /* Establezco mensaje de error */
                return; /* Termino la ejecución */
            }

            // Eliminar el comentario
            $consulta = $conexion->prepare("DELETE FROM comentarios WHERE id_usuario = :id_usuario AND id_juego = :id_juego"); /* Preparo la consulta para eliminar el comentario */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */

            $_SESSION['mensaje_exito_comentario'] = "Comentario eliminado correctamente."; /* Establezco mensaje de éxito */
            return; /* Termino la ejecución */
        } catch (PDOException $e) { /* Si hay error al eliminar el comentario */
            $_SESSION['error_general_comentario'] = "Error al eliminar el comentario: " . $e->getMessage(); /* Establezco mensaje de error */
            return; /* Termino la ejecución */
        }
    }

    //Función para eliminar un comentario por parte del administrador
    function eliminarComentarioAdmin($conexion, $id_comentario) {
        try { /* Inicio bloque try para capturar errores */
            // Verificar que el comentario exista y obtener id_juego para redirigir correctamente
            $consulta = $conexion->prepare("SELECT id, id_juego FROM comentarios WHERE id = :id_comentario"); /* Preparo la consulta para verificar si el comentario existe */
            $consulta->bindParam(':id_comentario', $id_comentario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del comentario */
            $consulta->execute(); /* Ejecuto la consulta */

            $existe_comentario = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el comentario si existe */

            if (!$existe_comentario) { /* Si el comentario no existe */
                $_SESSION['error_general_comentario'] = "El comentario no existe."; /* Establezco mensaje de error */
                return; /* Termino la ejecución */
            }
            $id_juego = (int)$existe_comentario['id_juego'];

            // Eliminar el comentario
            $consulta = $conexion->prepare("DELETE FROM comentarios WHERE id = :id_comentario"); /* Preparo la consulta para eliminar el comentario */
            $consulta->bindParam(':id_comentario', $id_comentario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del comentario */
            $consulta->execute(); /* Ejecuto la consulta */

            $_SESSION['mensaje_exito_comentario'] = "Comentario eliminado correctamente."; /* Establezco mensaje de éxito */
            return; /* Termino la ejecución */
        } catch (PDOException $e) { /* Si hay error al eliminar el comentario */
            $_SESSION['error_general_comentario'] = "Error al eliminar el comentario: " . $e->getMessage(); /* Establezco mensaje de error */
            return; /* Termino la ejecución */
        }
    }

    //Función para crear una valoración
    function crearValoracion($conexion, $id_usuario, $id_juego, $valoracion) {
        try { /* Inicio bloque try para capturar errores */
            if ($valoracion < 1 || $valoracion > 5) { /* Si la valoración no está entre 1 y 5 */
                $_SESSION['error_general_valoracion'] = "La valoración debe estar entre 1 y 5."; /* Establezco mensaje de error */
                header('Location: ../publico/detalles_juego.php?id=' . $id_juego); /* Redirijo de vuelta a detalles del juego */
                exit; /* Termino la ejecución */
            }

            // Verificar que la valoración no exista ya
            $consulta = $conexion->prepare("SELECT id FROM valoraciones WHERE id_usuario = :id_usuario AND id_juego = :id_juego"); /* Preparo la consulta para verificar si la valoración ya existe */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */

            $existe_valoracion = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el ID de la valoración si existe */

            if ($existe_valoracion) { /* Si la valoración ya existe */
                $_SESSION['error_general_valoracion'] = "Ya tienes una valoración para este juego."; /* Establezco mensaje de error */
                header('Location: ../publico/detalles_juego.php?id=' . $id_juego); /* Redirijo de vuelta a detalles del juego */
                exit; /* Termino la ejecución */
            }

            // Insertar la nueva valoración
            $consulta = $conexion->prepare("INSERT INTO valoraciones (id_usuario, id_juego, valoracion) VALUES (:id_usuario, :id_juego, :valoracion)"); /* Preparo la consulta para insertar la valoración */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->bindParam(':valoracion', $valoracion, PDO::PARAM_INT); /* Vinculo el parámetro de la valoración */
            $consulta->execute(); /* Ejecuto la consulta */

            $_SESSION['mensaje_exito_valoracion'] = "Valoración creada correctamente."; /* Establezco mensaje de éxito */
            header('Location: ../publico/detalles_juego.php?id=' . $id_juego); /* Redirijo de vuelta a detalles del juego */
            exit; /* Termino la ejecución */
        } catch (PDOException $e) { /* Si hay error al crear la valoración */
            $_SESSION['error_general_valoracion'] = "Error al crear la valoración: " . $e->getMessage(); /* Establezco mensaje de error */
            header('Location: ../publico/detalles_juego.php?id=' . $id_juego); /* Redirijo de vuelta a detalles del juego */
            exit; /* Termino la ejecución */
        }
    }

    //Función para editar una valoración
    function editarValoracion($conexion, $id_usuario, $id_juego, $valoracion) {
        try { /* Inicio bloque try para capturar errores */
            if ($valoracion < 1 || $valoracion > 5) { /* Si la valoración no está entre 1 y 5 */
                $_SESSION['error_general_valoracion'] = "La valoración debe estar entre 1 y 5."; /* Establezco mensaje de error */
                header('Location: ../publico/detalles_juego.php?id=' . $id_juego); /* Redirijo de vuelta a detalles del juego */
                exit; /* Termino la ejecución */
            }

            // Verificar que la valoración exista
            $consulta = $conexion->prepare("SELECT id FROM valoraciones WHERE id_usuario = :id_usuario AND id_juego = :id_juego"); /* Preparo la consulta para verificar si la valoración existe */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */

            $existe_valoracion = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el ID de la valoración si existe */

            if (!$existe_valoracion) { /* Si la valoración no existe */
                $_SESSION['error_general_valoracion'] = "No tienes una valoración para este juego."; /* Establezco mensaje de error */
                header('Location: ../publico/detalles_juego.php?id=' . $id_juego); /* Redirijo de vuelta a detalles del juego */
                exit; /* Termino la ejecución */
            }

            // Actualizar la valoración
            $consulta = $conexion->prepare("UPDATE valoraciones SET valoracion = :valoracion WHERE id_usuario = :id_usuario AND id_juego = :id_juego"); /* Preparo la consulta para actualizar la valoración */
            $consulta->bindParam(':valoracion', $valoracion, PDO::PARAM_INT); /* Vinculo el parámetro de la valoración */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */

            $_SESSION['mensaje_exito_valoracion'] = "Valoración editada correctamente."; /* Establezco mensaje de éxito */
            header('Location: ../publico/detalles_juego.php?id=' . $id_juego); /* Redirijo de vuelta a detalles del juego */
            exit; /* Termino la ejecución */
        } catch (PDOException $e) { /* Si hay error al editar la valoración */
            $_SESSION['error_general_valoracion'] = "Error al editar la valoración: " . $e->getMessage(); /* Establezco mensaje de error */
            header('Location: ../publico/detalles_juego.php?id=' . $id_juego); /* Redirijo de vuelta a detalles del juego */
            exit; /* Termino la ejecución */
        }
    }

    //Función para eliminar una valoración
    function eliminarValoracion($conexion, $id_usuario, $id_juego) {
        try { /* Inicio bloque try para capturar errores */
            // Verificar que la valoración exista
            $consulta = $conexion->prepare("SELECT id FROM valoraciones WHERE id_usuario = :id_usuario AND id_juego = :id_juego"); /* Preparo la consulta para verificar si la valoración existe */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */

            $existe_valoracion = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el ID de la valoración si existe */

            if (!$existe_valoracion) { /* Si la valoración no existe */
                $_SESSION['error_general_valoracion'] = "No tienes una valoración para este juego."; /* Establezco mensaje de error */
                return; /* Termino la ejecución */
            }

            // Eliminar la valoración
            $consulta = $conexion->prepare("DELETE FROM valoraciones WHERE id_usuario = :id_usuario AND id_juego = :id_juego"); /* Preparo la consulta para eliminar la valoración */
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */

            $_SESSION['mensaje_exito_valoracion'] = "Valoración eliminada correctamente."; /* Establezco mensaje de éxito */
            return; /* Termino la ejecución */
        } catch (PDOException $e) { /* Si hay error al eliminar la valoración */
            $_SESSION['error_general_valoracion'] = "Error al eliminar la valoración: " . $e->getMessage(); /* Establezco mensaje de error */
            return; /* Termino la ejecución */
        }
    }

    //Función para eliminar todas las valoraciones de un juego por parte del administrador
    function eliminarTodasValoracionesAdmin($conexion, $id_juego) {
        try { /* Inicio bloque try para capturar errores */
            // Eliminar todas las valoraciones del juego
            $consulta = $conexion->prepare("DELETE FROM valoraciones WHERE id_juego = :id_juego"); /* Preparo la consulta para eliminar todas las valoraciones */
            $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el parámetro del ID del juego */
            $consulta->execute(); /* Ejecuto la consulta */

            $_SESSION['mensaje_exito_valoracion'] = "Todas las valoraciones han sido eliminadas correctamente."; /* Establezco mensaje de éxito */
            return; /* Termino la ejecución */
        } catch (PDOException $e) { /* Si hay error al eliminar las valoraciones */
            $_SESSION['error_general_valoracion'] = "Error al eliminar las valoraciones: " . $e->getMessage(); /* Establezco mensaje de error */
            return; /* Termino la ejecución */
        }
    }

    if(isset($_POST['accion'])) { /* Verifico que llegue la acción a realizar */
        $accion = $_POST['accion']; /* Obtengo la acción a realizar */
        switch($accion) { /* Según la acción a realizar */
            case 'crear_comentario': /* Si la acción es crear un comentario */
                if(isset($_POST['id_juego']) && isset($_POST['comentario'])) { /* Verifico que lleguen los datos necesarios */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    $texto_comentario = trim($_POST['comentario']); /* Obtengo el texto del comentario y le quito espacios en blanco */
                    crearComentario($conexion, $_SESSION['id_usuario'], $id_juego, $texto_comentario); /* Llamo a la función para crear el comentario */
                }
                break;
            case 'editar_comentario': /* Si la acción es editar un comentario */
                if(isset($_POST['id_juego']) && isset($_POST['comentario'])) { /* Verifico que lleguen los datos necesarios */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    $texto_comentario = trim($_POST['comentario']); /* Obtengo el texto del comentario y le quito espacios en blanco */
                    editarComentario($conexion, $_SESSION['id_usuario'], $id_juego, $texto_comentario); /* Llamo a la función para editar el comentario */
                }
                break;
            case 'eliminar_comentario': /* Si la acción es eliminar un comentario */
                if(isset($_POST['id_juego'])) { /* Verifico que llegue el ID del juego */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    eliminarComentario($conexion, $_SESSION['id_usuario'], $id_juego); /* Llamo a la función para eliminar el comentario */
                }
                break;
            case 'eliminar_comentario_admin': /* Si la acción es eliminar un comentario por parte del administrador */
                if(isset($_POST['id_comentario'])) { /* Verifico que llegue el ID del comentario */
                    $id_comentario = intval($_POST['id_comentario']); /* Obtengo el ID del comentario y lo convierto a entero */
                    eliminarComentarioAdmin($conexion, $id_comentario); /* Llamo a la función para eliminar el comentario */
                }
                break;
            case 'crear_valoracion': /* Si la acción es crear una valoración */
                if(isset($_POST['id_juego']) && isset($_POST['valoracion'])) { /* Verifico que lleguen los datos necesarios */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    $valoracion = intval($_POST['valoracion']); /* Obtengo la valoración y la convierto a entero */
                    crearValoracion($conexion, $_SESSION['id_usuario'], $id_juego, $valoracion); /* Llamo a la función para crear la valoración */
                }
                break;
            case 'editar_valoracion': /* Si la acción es editar una valoración */
                if(isset($_POST['id_juego']) && isset($_POST['valoracion'])) { /* Verifico que lleguen los datos necesarios */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    $valoracion = intval($_POST['valoracion']); /* Obtengo la valoración y la convierto a entero */
                    editarValoracion($conexion, $_SESSION['id_usuario'], $id_juego, $valoracion); /* Llamo a la función para editar la valoración */
                }
                break;
            case 'eliminar_valoracion': /* Si la acción es eliminar una valoración */
                if(isset($_POST['id_juego'])) { /* Verifico que llegue el ID del juego */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    eliminarValoracion($conexion, $_SESSION['id_usuario'], $id_juego); /* Llamo a la función para eliminar la valoración */
                }
                break;
            case 'eliminar_todas_valoraciones_admin': /* Si la acción es eliminar todas las valoraciones por parte del administrador */
                if(isset($_POST['id_juego'])) { /* Verifico que llegue el ID del juego */
                    $id_juego = intval($_POST['id_juego']); /* Obtengo el ID del juego y lo convierto a entero */
                    eliminarTodasValoracionesAdmin($conexion, $id_juego); /* Llamo a la función para eliminar todas las valoraciones */
                }
                break;
            default: /* Si la acción no es válida */
                $_SESSION['error_general'] = "Acción no válida"; /* Establezco mensaje de error */
                header('Location: ../publico/index.php'); /* Redirijo al inicio */
                exit; /* Termino la ejecución */
        }
    } else { /* Si no se recibió ninguna acción */
        $_SESSION['error_general'] = "No se recibió ninguna acción"; /* Establezco mensaje de error */
        header('Location: ../publico/index.php'); /* Redirijo al inicio */
        exit; /* Termino la ejecución */
    }
    
?>
