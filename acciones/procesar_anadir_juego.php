<?php

    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */
    session_start(); /* Inicio la sesión */

    // Verificar que el usuario esté logueado y sea administrador
    if(!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
        header('Location: ../publico/index.php'); /* Redirijo si no es admin */
        exit; /* Termino la ejecución */
    }

    // Verificar que el método sea POST
    if($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel */
        exit; /* Termino la ejecución */
    }

    try { /* Inicio bloque try para capturar errores */
        
        $nombre = trim($_POST['nombre']); /* Obtengo y limpio el nombre */
        $slug = trim($_POST['slug']); /* Obtengo y limpio el slug */
        $precio = (float)$_POST['precio']; /* Convierto a decimal el precio */
        $tipo = $_POST['tipo']; /* Obtengo el tipo */

        // Validar que los campos básicos obligatorios no estén vacíos
        if(empty($nombre) || empty($slug) || empty($tipo)) {
            $_SESSION['error_general'] = 'Los campos de información básica son obligatorios.'; /* Mensaje de error */
            header('Location: ../vistas/anadir_juego.php'); /* Redirijo de vuelta */
            exit; /* Termino la ejecución */
        }

        // Verificar que el slug no exista ya en la base de datos
        $consulta = $conexion->prepare("SELECT id FROM juegos WHERE slug = :slug"); /* Preparo consulta */
        $consulta->bindParam(':slug', $slug, PDO::PARAM_STR); /* Vinculo el slug */
        $consulta->execute(); /* Ejecuto la consulta */
        
        if($consulta->fetch()) { /* Si ya existe un juego con ese slug */
            $_SESSION['error_slug_existente'] = 'El slug ya está en uso. Por favor, elige otro diferente.'; /* Mensaje de error */
            header('Location: ../vistas/anadir_juego.php'); /* Redirijo de vuelta */
            exit; /* Termino la ejecución */
        }

        $desarrollador = trim($_POST['desarrollador']); /* Obtengo y limpio el desarrollador */
        $distribuidor = trim($_POST['distribuidor']); /* Obtengo y limpio el distribuidor */
        $fecha_lanzamiento = $_POST['fecha_lanzamiento']; /* Obtengo la fecha */

        // Validar que los campos de desarrollo no estén vacíos
        if(empty($desarrollador) || empty($distribuidor) || empty($fecha_lanzamiento)) {
            $_SESSION['error_general'] = 'Los campos de desarrollo y publicación son obligatorios.'; /* Mensaje de error */
            header('Location: ../vistas/anadir_juego.php'); /* Redirijo de vuelta */
            exit; /* Termino la ejecución */
        }

        $resumen = trim($_POST['resumen']); /* Obtengo y limpio el resumen */
        $descripcion = trim($_POST['descripcion']); /* Obtengo y limpio la descripción */
        $requisitos = trim($_POST['requisitos']); /* Obtengo y limpio los requisitos */

        // Validar que las descripciones no estén vacías
        if(empty($resumen) || empty($descripcion) || empty($requisitos)) {
            $_SESSION['error_general'] = 'Todos los campos de descripción son obligatorios.'; /* Mensaje de error */
            header('Location: ../vistas/anadir_juego.php'); /* Redirijo de vuelta */
            exit; /* Termino la ejecución */
        }

        // Verificar que se haya subido una imagen de portada
        if(!isset($_FILES['portada']) || $_FILES['portada']['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['error_general'] = 'Debes seleccionar una imagen de portada.'; /* Mensaje de error */
            header('Location: ../vistas/anadir_juego.php'); /* Redirijo de vuelta */
            exit; /* Termino la ejecución */
        }

        // Verificar errores en la subida
        if($_FILES['portada']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error_general'] = 'Error al subir el archivo de portada.'; /* Mensaje de error */
            header('Location: ../vistas/anadir_juego.php'); /* Redirijo de vuelta */
            exit; /* Termino la ejecución */
        }

        // Obtener información del archivo
        $archivo_temporal = $_FILES['portada']['tmp_name']; /* Ruta temporal del archivo */
        $nombre_original = $_FILES['portada']['name']; /* Nombre original del archivo */
        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION)); /* Obtengo la extensión en minúsculas */

        // Validar extensión
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp']; /* Extensiones permitidas */
        // Verificar que la extensión sea válida
        if(!in_array($extension, $extensiones_permitidas)) { /* Si la extensión no está permitida */
            $_SESSION['error_general'] = 'Formato de imagen no permitido. Use JPG, PNG, GIF o WEBP.'; /* Mensaje de error */
            header('Location: ../vistas/anadir_juego.php'); /* Redirijo de vuelta */
            exit; /* Termino la ejecución */
        }

        // Validar tipo MIME (tipo de formato o archivo)
        $finfo = finfo_open(FILEINFO_MIME_TYPE); /* Abro información de archivo */
        $mime_type = finfo_file($finfo, $archivo_temporal); /* Obtengo el tipo MIME */
        finfo_close($finfo); /* Cierro */
        
        $mimes_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']; /* Tipos MIME permitidos */
        // Verificar que el tipo MIME sea válido
        if(!in_array($mime_type, $mimes_permitidos)) { /* Si el MIME no está permitido */
            $_SESSION['error_general'] = 'El archivo no es una imagen válida.'; /* Mensaje de error */
            header('Location: ../vistas/anadir_juego.php'); /* Redirijo de vuelta */
            exit; /* Termino la ejecución */
        }

        // Validar tamaño (máximo 5MB)
        $tamano_maximo = 5 * 1024 * 1024; /* 5 MB en bytes */
        if($_FILES['portada']['size'] > $tamano_maximo) { /* Si el tamaño excede el máximo */
            $_SESSION['error_general'] = 'La imagen es demasiado grande. Máximo 5MB.'; /* Mensaje de error */
            header('Location: ../vistas/anadir_juego.php'); /* Redirijo de vuelta */
            exit; /* Termino la ejecución */
        }

        // Generar nombre único para evitar sobrescrituras
        $nombre_archivo = $slug . '_' . time() . '.' . $extension; /* Nombre único usando slug y timestamp (fecha y hora actual) */
        $ruta_destino = __DIR__ . '/../recursos/imagenes/portadas/' . $nombre_archivo; /* Ruta completa de destino */
        $ruta_bd = 'recursos/imagenes/portadas/' . $nombre_archivo; /* Ruta relativa para guardar en BD */

        // Mover el archivo a la carpeta de destino
        if(!move_uploaded_file($archivo_temporal, $ruta_destino)) {
            $_SESSION['error_general'] = 'Error al guardar la imagen en el servidor.'; /* Mensaje de error */
            header('Location: ../vistas/anadir_juego.php'); /* Redirijo de vuelta */
            exit; /* Termino la ejecución */
        }

        $generos = $_POST['generos'] ?? []; /* Obtengo array de géneros seleccionados */
        $categorias = $_POST['categorias'] ?? []; /* Obtengo array de categorías seleccionadas */
        $modos = $_POST['modos'] ?? []; /* Obtengo array de modos seleccionados */
        $clasificaciones_pegi = $_POST['clasificaciones_pegi'] ?? []; /* Obtengo array de clasificaciones PEGI seleccionadas */

        // Validar que haya al menos un filtro de cada tipo obligatorio
        if(empty($generos) || empty($categorias) || empty($modos) || empty($clasificaciones_pegi)) {
            $_SESSION['error_general'] = 'Debes seleccionar al menos un filtro de cada categoría (géneros, categorías, modos, clasificaciones PEGI).'; /* Mensaje de error */
            header('Location: ../vistas/anadir_juego.php'); /* Redirijo de vuelta */
            exit; /* Termino la ejecución */
        }

        // Combinar todos los filtros en un solo array
        $todos_filtros = array_merge($generos, $categorias, $modos, $clasificaciones_pegi); /* Combino todos los filtros */

        $consulta = $conexion->prepare("
            INSERT INTO juegos 
            (nombre, slug, precio, tipo, desarrollador, distribuidor, fecha_lanzamiento, resumen, descripcion, requisitos, portada) 
            VALUES 
            (:nombre, :slug, :precio, :tipo, :desarrollador, :distribuidor, :fecha_lanzamiento, :resumen, :descripcion, :requisitos, :portada)
        "); /* Preparo consulta para insertar el juego en la base de datos */

        $consulta->bindParam(':nombre', $nombre, PDO::PARAM_STR); /* Vinculo el nombre */
        $consulta->bindParam(':slug', $slug, PDO::PARAM_STR); /* Vinculo el slug */
        $consulta->bindParam(':precio', $precio); /* Vinculo el precio */
        $consulta->bindParam(':tipo', $tipo, PDO::PARAM_STR); /* Vinculo el tipo */
        $consulta->bindParam(':desarrollador', $desarrollador, PDO::PARAM_STR); /* Vinculo el desarrollador */
        $consulta->bindParam(':distribuidor', $distribuidor, PDO::PARAM_STR); /* Vinculo el distribuidor */
        $consulta->bindParam(':fecha_lanzamiento', $fecha_lanzamiento); /* Vinculo la fecha */
        $consulta->bindParam(':resumen', $resumen, PDO::PARAM_STR); /* Vinculo el resumen */
        $consulta->bindParam(':descripcion', $descripcion, PDO::PARAM_STR); /* Vinculo la descripción */
        $consulta->bindParam(':requisitos', $requisitos, PDO::PARAM_STR); /* Vinculo los requisitos */
        $consulta->bindParam(':portada', $ruta_bd, PDO::PARAM_STR); /* Vinculo la ruta de la portada */
        $consulta->execute(); /* Ejecuto la inserción */

        // Obtener el ID del juego recién creado
        $id_juego_nuevo = $conexion->lastInsertId(); /* Obtengo el ID del último registro insertado */

        if(!empty($todos_filtros)) { /* Si hay filtros seleccionados */
            $consulta = $conexion->prepare("INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES (:id_juego, :id_filtro)"); /* Preparo consulta para insertar los filtros del juego en juegos_filtros */
            
            foreach($todos_filtros as $id_filtro) { /* Recorro cada filtro seleccionado */
                $id_filtro = (int)$id_filtro; /* Convierto a entero */
                $consulta->bindParam(':id_juego', $id_juego_nuevo, PDO::PARAM_INT); /* Vinculo el ID del juego */
                $consulta->bindParam(':id_filtro', $id_filtro, PDO::PARAM_INT); /* Vinculo el ID del filtro */
                $consulta->execute(); /* Ejecuto la inserción */
            }
        }

        $_SESSION['mensaje_exito'] = 'Juego "' . $nombre . '" añadido correctamente.'; /* Mensaje de éxito */
        header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel de administrador */
        exit; /* Termino la ejecución */

    } catch (PDOException $e) { /* Si hay error en la base de datos */
        // Si hay error, intentar eliminar la imagen subida si existe
        if(isset($ruta_destino) && file_exists($ruta_destino)) {
            unlink($ruta_destino); /* Elimino el archivo */
        }
        
        $_SESSION['error_general'] = 'Error al añadir el juego: ' . $e->getMessage(); /* Mensaje de error con detalles */
        header('Location: ../vistas/anadir_juego.php'); /* Redirijo de vuelta */
        exit; /* Termino la ejecución */
    }

?>
