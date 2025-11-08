<?php

    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */
    session_start(); /* Inicio la sesión */

    // Verificar que el usuario esté logueado y sea administrador
    if(!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
        header('Location: ../publico/index.php'); /* Redirijo si no es admin */
        exit; /* Termino la ejecución */
    }

    // Verificar que llegue el ID del juego y el tipo de edición
    if(!isset($_POST['id_juego']) || !isset($_POST['tipo_edicion'])) {
        $_SESSION['error_general'] = 'Datos incompletos para procesar la edición.'; /* Mensaje de error */
        header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel */
        exit; /* Termino la ejecución */
    }

    $id_juego = (int)$_POST['id_juego']; /* Convierto a entero el ID del juego */
    $tipo_edicion = $_POST['tipo_edicion']; /* Obtengo el tipo de edición */

    try { /* Inicio bloque try para capturar errores */
        
        switch($tipo_edicion) { /* Según el tipo de edición */
            
            case 'info_basica': /* Edición de información básica */
                $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : ''; /* Obtengo y limpio el nombre */
                $slug = isset($_POST['slug']) ? trim($_POST['slug']) : ''; /* Obtengo y limpio el slug */
                $precio = isset($_POST['precio']) ? (float)$_POST['precio'] : 0; /* Convierto a decimal el precio */
                $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : ''; /* Obtengo el tipo */

                // Verificar que el slug no exista en otro juego
                $consulta = $conexion->prepare("SELECT id FROM juegos WHERE slug = :slug AND id != :id_juego"); /* Preparo consulta */
                $consulta->bindParam(':slug', $slug, PDO::PARAM_STR); /* Vinculo el slug */
                $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el ID del juego */
                $consulta->execute(); /* Ejecuto la consulta */
                
                if($consulta->fetch()) { /* Si existe otro juego con ese slug */
                    $_SESSION['error_slug_existente'] = 'El slug ya está en uso por otro juego.'; /* Mensaje de error */
                    header('Location: ../vistas/editar_juego.php?id=' . $id_juego); /* Redirijo de vuelta */
                    exit; /* Termino la ejecución */
                }

                // Actualizar la información básica
                $consulta = $conexion->prepare("UPDATE juegos SET nombre = :nombre, slug = :slug, precio = :precio, tipo = :tipo WHERE id = :id_juego"); /* Preparo consulta de actualización */
                $consulta->bindParam(':nombre', $nombre, PDO::PARAM_STR); /* Vinculo el nombre */
                $consulta->bindParam(':slug', $slug, PDO::PARAM_STR); /* Vinculo el slug */
                $consulta->bindParam(':precio', $precio); /* Vinculo el precio */
                $consulta->bindParam(':tipo', $tipo, PDO::PARAM_STR); /* Vinculo el tipo */
                $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el ID del juego */
                $consulta->execute(); /* Ejecuto la actualización */

                $_SESSION['mensaje_exito'] = 'Información básica actualizada correctamente.'; /* Mensaje de éxito */
                break;

            case 'desarrollo': /* Edición de información de desarrollo */
                $desarrollador = isset($_POST['desarrollador']) ? trim($_POST['desarrollador']) : ''; /* Obtengo y limpio el desarrollador */
                $distribuidor = isset($_POST['distribuidor']) ? trim($_POST['distribuidor']) : ''; /* Obtengo y limpio el distribuidor */
                $fecha_lanzamiento = isset($_POST['fecha_lanzamiento']) ? $_POST['fecha_lanzamiento'] : null; /* Obtengo la fecha o null */

                // Actualizar la información de desarrollo
                $consulta = $conexion->prepare("UPDATE juegos SET desarrollador = :desarrollador, distribuidor = :distribuidor, fecha_lanzamiento = :fecha_lanzamiento WHERE id = :id_juego"); /* Preparo consulta de actualización */
                $consulta->bindParam(':desarrollador', $desarrollador, PDO::PARAM_STR); /* Vinculo el desarrollador */
                $consulta->bindParam(':distribuidor', $distribuidor, PDO::PARAM_STR); /* Vinculo el distribuidor */
                $consulta->bindParam(':fecha_lanzamiento', $fecha_lanzamiento); /* Vinculo la fecha */
                $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el ID del juego */
                $consulta->execute(); /* Ejecuto la actualización */

                $_SESSION['mensaje_exito'] = 'Información de desarrollo actualizada correctamente.'; /* Mensaje de éxito */
                break;

            case 'contenido': /* Edición de contenido textual */
                $resumen = isset($_POST['resumen']) ? trim($_POST['resumen']) : ''; /* Obtengo y limpio el resumen */
                $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : ''; /* Obtengo y limpio la descripción */
                $requisitos = isset($_POST['requisitos']) ? trim($_POST['requisitos']) : ''; /* Obtengo y limpio los requisitos */

                // Actualizar el contenido textual
                $consulta = $conexion->prepare("UPDATE juegos SET resumen = :resumen, descripcion = :descripcion, requisitos = :requisitos WHERE id = :id_juego"); /* Preparo consulta de actualización */
                $consulta->bindParam(':resumen', $resumen, PDO::PARAM_STR); /* Vinculo el resumen */
                $consulta->bindParam(':descripcion', $descripcion, PDO::PARAM_STR); /* Vinculo la descripción */
                $consulta->bindParam(':requisitos', $requisitos, PDO::PARAM_STR); /* Vinculo los requisitos */
                $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el ID del juego */
                $consulta->execute(); /* Ejecuto la actualización */

                $_SESSION['mensaje_exito'] = 'Descripciones actualizadas correctamente.'; /* Mensaje de éxito */
                break;

            case 'portada': /* Edición de la portada */
                // Verificar que se haya subido un archivo
                if(!isset($_FILES['portada']) || $_FILES['portada']['error'] === UPLOAD_ERR_NO_FILE) {
                    $_SESSION['error_general'] = 'No se seleccionó ningún archivo.'; /* Mensaje de error */
                    break;
                }

                // Verificar errores en la subida
                if($_FILES['portada']['error'] !== UPLOAD_ERR_OK) {
                    $_SESSION['error_general'] = 'Error al subir el archivo.'; /* Mensaje de error */
                    break;
                }

                // Obtener información del archivo
                $archivo_temporal = $_FILES['portada']['tmp_name']; /* Ruta temporal del archivo */
                $nombre_original = $_FILES['portada']['name']; /* Nombre original del archivo */
                $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION)); /* Obtengo la extensión en minúsculas */

                // Validar extensión
                $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp']; /* Extensiones permitidas */
                if(!in_array($extension, $extensiones_permitidas)) { /* Si la extensión no está permitida */
                    $_SESSION['error_general'] = 'Formato de imagen no permitido. Use JPG, PNG, GIF o WEBP.'; /* Mensaje de error */
                    break;
                }

                // Validar tipo MIME (tipo de formato o archivo)
                $finfo = finfo_open(FILEINFO_MIME_TYPE); /* Abro información de archivo */
                $mime_type = finfo_file($finfo, $archivo_temporal); /* Obtengo el tipo MIME */
                finfo_close($finfo); /* Cierro */
                
                $mimes_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']; /* Tipos MIME permitidos */
                if(!in_array($mime_type, $mimes_permitidos)) { /* Si el MIME no está permitido */
                    $_SESSION['error_general'] = 'El archivo no es una imagen válida.'; /* Mensaje de error */
                    break;
                }

                // Validar tamaño (máximo 5MB)
                $tamano_maximo = 5 * 1024 * 1024; /* 5 MB en bytes */
                if($_FILES['portada']['size'] > $tamano_maximo) { /* Si el tamaño excede el máximo */
                    $_SESSION['error_general'] = 'La imagen es demasiado grande. Máximo 5MB.'; /* Mensaje de error */
                    break;
                }

                $consulta = $conexion->prepare("SELECT slug FROM juegos WHERE id = :id_juego"); /* Preparo consulta para obtener el slug (nombre amigable) */
                $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el ID */
                $consulta->execute(); /* Ejecuto la consulta */
                $slug_juego = $consulta->fetchColumn(); /* Obtengo el slug del juego */

                // Generar nombre único para evitar sobrescrituras
                $nombre_archivo = $slug_juego . '_' . time() . '.' . $extension; /* Nombre único usando slug y timestamp (fecha y hora actual) */
                $ruta_destino = __DIR__ . '/../recursos/imagenes/portadas/' . $nombre_archivo; /* Ruta completa de destino */
                $ruta_bd = 'recursos/imagenes/portadas/' . $nombre_archivo; /* Ruta relativa para guardar en BD */

                // Mover el archivo a la carpeta de destino
                if(!move_uploaded_file($archivo_temporal, $ruta_destino)) {
                    $_SESSION['error_general'] = 'Error al guardar la imagen en el servidor.'; /* Mensaje de error */
                    break;
                }

                // Obtener la portada antigua para eliminarla
                $consulta = $conexion->prepare("SELECT portada FROM juegos WHERE id = :id_juego"); /* Preparo consulta */
                $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el ID */
                $consulta->execute(); /* Ejecuto */
                $portada_antigua = $consulta->fetchColumn(); /* Obtengo la ruta antigua */

                // Actualizar la portada en la base de datos
                $consulta = $conexion->prepare("UPDATE juegos SET portada = :portada WHERE id = :id_juego"); /* Preparo consulta de actualización */
                $consulta->bindParam(':portada', $ruta_bd, PDO::PARAM_STR); /* Vinculo la nueva ruta */
                $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el ID del juego */
                $consulta->execute(); /* Ejecuto la actualización */

                $_SESSION['mensaje_exito'] = 'Portada actualizada correctamente.'; /* Mensaje de éxito */
                break;

            case 'filtros': /* Edición de filtros (categorización) */
                // Obtener todos los filtros seleccionados
                $generos = $_POST['generos'] ?? []; /* Obtengo array de géneros seleccionados */
                $categorias = $_POST['categorias'] ?? []; /* Obtengo array de categorías seleccionadas */
                $modos = $_POST['modos'] ?? []; /* Obtengo array de modos seleccionados */
                $clasificaciones_pegi = $_POST['clasificaciones_pegi'] ?? []; /* Obtengo array de clasificaciones PEGI seleccionadas */

                // Combinar todos los filtros en un solo array
                $todos_filtros = array_merge($generos, $categorias, $modos, $clasificaciones_pegi); /* Combino todos los filtros */

                // Eliminar los filtros actuales del juego
                $consulta = $conexion->prepare("DELETE FROM juegos_filtros WHERE id_juego = :id_juego"); /* Preparo consulta para eliminar filtros actuales */
                $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el ID del juego */
                $consulta->execute(); /* Ejecuto la eliminación */

                // Insertar los nuevos filtros
                if(!empty($todos_filtros)) { /* Si hay filtros seleccionados */
                    $consulta = $conexion->prepare("INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES (:id_juego, :id_filtro)"); /* Preparo consulta de inserción */
                    
                    foreach($todos_filtros as $id_filtro) { /* Recorro cada filtro seleccionado */
                        $id_filtro = (int)$id_filtro; /* Convierto el ID del filtro a entero */
                        $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el ID del juego */
                        $consulta->bindParam(':id_filtro', $id_filtro, PDO::PARAM_INT); /* Vinculo el ID del filtro */
                        $consulta->execute(); /* Ejecuto la inserción */
                    }
                }

                // Actualizar timestamp del juego
                $consulta = $conexion->prepare("UPDATE juegos SET actualizado_en = NOW() WHERE id = :id_juego"); /* Preparo consulta de actualización */
                $consulta->bindParam(':id_juego', $id_juego, PDO::PARAM_INT); /* Vinculo el ID del juego */
                $consulta->execute(); /* Ejecuto la actualización */

                $_SESSION['mensaje_exito'] = 'Categorización actualizada correctamente.'; /* Mensaje de éxito */
                break;

            default: /* Tipo de edición no reconocido */
                $_SESSION['error_general'] = 'Tipo de edición no válido.'; /* Mensaje de error */
                break;
        }

    } catch (PDOException $e) { /* Si hay error en la base de datos */
        $_SESSION['error_general'] = 'Error al actualizar el juego: ' . $e->getMessage(); /* Mensaje de error con detalles */
    }

    // Redireccionar de vuelta a la página de edición del juego
    header('Location: ../vistas/editar_juego.php?id=' . $id_juego); /* Redirijo de vuelta */
    exit; /* Termino la ejecución */
    
?>
