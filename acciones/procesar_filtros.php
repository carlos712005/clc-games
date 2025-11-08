<?php

    session_start(); /* Inicio la sesión para poder acceder a las variables de sesión */
    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */

    if ($_SERVER['REQUEST_METHOD'] === 'POST') { /* Verifico que la petición sea POST */

        if (isset($_SESSION['modo_edicion']) && $_SESSION['modo_edicion'] === 'usuarios') { /* Si son filtros de usuarios */
            // Procesar filtros de usuarios
            $filtro_rol = isset($_POST['filtro_rol']) ? $_POST['filtro_rol'] : 'null'; /* Recojo el rol */
            $filtro_acronimo = isset($_POST['filtro_acronimo']) ? $_POST['filtro_acronimo'] : 'null'; /* Recojo el acrónimo */
            $filtro_correo = isset($_POST['filtro_correo']) ? $_POST['filtro_correo'] : 'null'; /* Recojo el correo */
            $filtro_dni = isset($_POST['filtro_dni']) ? $_POST['filtro_dni'] : 'null'; /* Recojo el DNI */
            $filtro_nombre = isset($_POST['filtro_nombre']) ? $_POST['filtro_nombre'] : 'null'; /* Recojo el nombre */
            $filtro_apellidos = isset($_POST['filtro_apellidos']) ? $_POST['filtro_apellidos'] : 'null'; /* Recojo los apellidos */
            $filtro_fecha_creacion_desde = isset($_POST['filtro_fecha_creacion_desde']) && $_POST['filtro_fecha_creacion_desde'] !== '' ? $_POST['filtro_fecha_creacion_desde'] : null; /* Recojo fecha de creación desde */
            $filtro_fecha_creacion_hasta = isset($_POST['filtro_fecha_creacion_hasta']) && $_POST['filtro_fecha_creacion_hasta'] !== '' ? $_POST['filtro_fecha_creacion_hasta'] : null; /* Recojo fecha de creación hasta */
            $filtro_fecha_actualizacion_desde = isset($_POST['filtro_fecha_actualizacion_desde']) && $_POST['filtro_fecha_actualizacion_desde'] !== '' ? $_POST['filtro_fecha_actualizacion_desde'] : null; /* Recojo fecha de última actualización desde */
            $filtro_fecha_actualizacion_hasta = isset($_POST['filtro_fecha_actualizacion_hasta']) && $_POST['filtro_fecha_actualizacion_hasta'] !== '' ? $_POST['filtro_fecha_actualizacion_hasta'] : null; /* Recojo fecha de última actualización hasta */
            $filtro_fecha_acceso_desde = isset($_POST['filtro_fecha_acceso_desde']) && $_POST['filtro_fecha_acceso_desde'] !== '' ? $_POST['filtro_fecha_acceso_desde'] : null; /* Recojo fecha de último acceso desde */
            $filtro_fecha_acceso_hasta = isset($_POST['filtro_fecha_acceso_hasta']) && $_POST['filtro_fecha_acceso_hasta'] !== '' ? $_POST['filtro_fecha_acceso_hasta'] : null; /* Recojo fecha de último acceso hasta */

            // Guardar los filtros de usuarios en la sesión
            $_SESSION['filtros_usuarios'] = [
                'rol' => $filtro_rol, /* Guardo el rol elegido */
                'acronimo' => $filtro_acronimo, /* Guardo el acrónimo elegido */
                'email' => $filtro_correo, /* Guardo el correo elegido */
                'dni' => $filtro_dni, /* Guardo el DNI elegido */
                'nombre' => $filtro_nombre, /* Guardo el nombre elegido */
                'apellidos' => $filtro_apellidos, /* Guardo los apellidos elegidos */
                'fecha_creacion_desde' => $filtro_fecha_creacion_desde, /* Guardo fecha de creación desde */
                'fecha_creacion_hasta' => $filtro_fecha_creacion_hasta, /* Guardo fecha de creación hasta */
                'fecha_actualizacion_desde' => $filtro_fecha_actualizacion_desde, /* Guardo fecha de última actualización desde */
                'fecha_actualizacion_hasta' => $filtro_fecha_actualizacion_hasta, /* Guardo fecha de última actualización hasta */
                'fecha_acceso_desde' => $filtro_fecha_acceso_desde, /* Guardo fecha de último acceso desde */
                'fecha_acceso_hasta' => $filtro_fecha_acceso_hasta /* Guardo fecha de último acceso hasta */
            ]; /* Creo un array con todos los filtros de usuarios elegidos */

        } else { /* Si son filtros de juegos */
            // Procesar filtros de juegos
            $id_preferencia_tipo = isset($_POST['id_preferencia_tipo']) ? (int)$_POST['id_preferencia_tipo'] : 0; /* Recojo el tipo y lo convierto a entero */
            $id_preferencia_genero = isset($_POST['id_preferencia_genero']) ? (int)$_POST['id_preferencia_genero'] : 0; /* Recojo el género y lo convierto a entero */
            $id_preferencia_categoria = isset($_POST['id_preferencia_categoria']) ? (int)$_POST['id_preferencia_categoria'] : 0; /* Recojo la categoría y lo convierto a entero */
            $id_preferencia_modo = isset($_POST['id_preferencia_modo']) ? (int)$_POST['id_preferencia_modo'] : 0; /* Recojo el modo y lo convierto a entero */
            $id_preferencia_pegi = isset($_POST['id_preferencia_pegi']) ? (int)$_POST['id_preferencia_pegi'] : 0; /* Recojo la clasificación PEGI y lo convierto a entero */
            $precio_min = isset($_POST['precio_min']) ? (float)$_POST['precio_min'] : 0; /* Recojo precio mínimo o pongo 0 por defecto */
            $precio_max = isset($_POST['precio_max']) ? (float)$_POST['precio_max'] : 100; /* Recojo precio máximo o pongo 100 por defecto */

            // Guardar los filtros de juegos en la sesión
            $_SESSION['filtros_elegidos'] = [
                'tipo' => $id_preferencia_tipo, /* Guardo el tipo elegido */
                'genero' => $id_preferencia_genero, /* Guardo el género elegido */
                'categoria' => $id_preferencia_categoria, /* Guardo la categoría elegida */
                'modo' => $id_preferencia_modo, /* Guardo el modo elegido */
                'pegi' => $id_preferencia_pegi, /* Guardo la clasificación PEGI elegida */
                'precio_min' => $precio_min, /* Guardo el precio mínimo */
                'precio_max' => $precio_max /* Guardo el precio máximo */
            ]; /* Creo un array con todos los filtros elegidos */
        }

        // Determinar a qué página redirigir según desde donde vino el usuario
        $pagina_destino = '../publico/index.php'; /* Por defecto voy al index */
        
        if (isset($_POST['pagina_origen'])) { /* Si se envió la página de origen */
            $origen = $_POST['pagina_origen']; /* Obtengo la página de origen */
            
            // Verifico la página de origen y redirijo apropiadamente
            if ($origen === 'favoritos') { /* Si venía de favoritos */
                $pagina_destino = '../publico/favoritos.php'; /* Redirijo a favoritos */
            } elseif ($origen === 'index') { /* Si venía del index */
                $pagina_destino = '../publico/index.php'; /* Redirijo al index */
            } elseif ($origen === 'biblioteca') { /* Si venía de la biblioteca */
                $pagina_destino = '../publico/biblioteca.php'; /* Redirijo a la biblioteca */
            } elseif ($origen === 'panel_administrador') { /* Si venía del panel de administrador */
                $pagina_destino = '../vistas/panel_administrador.php'; /* Redirijo al panel de administrador */
            }
            // Si no coincide con ninguna página conocida, mantiene el index por defecto
        }
        
        header('Location: ' . $pagina_destino); /* Redirijo a la página apropiada con los filtros aplicados */
        exit; /* Termino la ejecución para que no siga procesando */

    } else { /* Si no es una petición POST */
        $_SESSION['error_general'] = 'Acceso inválido al procesar los filtros.'; /* Establezco un mensaje de error */
        
        // Intentar redirigir a la página de origen si está disponible en el referer
        $pagina_destino = '../publico/index.php'; /* Por defecto voy al index */
        if (isset($_SERVER['HTTP_REFERER'])) { /* Si hay información del referer */
            $referer = $_SERVER['HTTP_REFERER']; /* Obtengo la página de origen */
            if (strpos($referer, 'favoritos.php') !== false) { /* Si venía de favoritos */
                $pagina_destino = '../publico/favoritos.php'; /* Redirijo a favoritos */
            } elseif (strpos($referer, 'index.php') !== false) { /* Si venía del index */
                $pagina_destino = '../publico/index.php'; /* Redirijo al index */
            } elseif (strpos($referer, 'biblioteca.php') !== false) { /* Si venía de la biblioteca */
                $pagina_destino = '../publico/biblioteca.php'; /* Redirijo a la biblioteca */
            } elseif (strpos($referer, 'panel_administrador.php') !== false) { /* Si venía del panel de administrador */
                $pagina_destino = '../vistas/panel_administrador.php'; /* Redirijo al panel de administrador */
            }
        }
        
        header('Location: ' . $pagina_destino); /* Redirijo a la página apropiada con error */
        exit; /* Termino la ejecución */
    }

?>
