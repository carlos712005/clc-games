<?php

    session_start(); /* Inicio la sesión para poder manejar mensajes y errores */
    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */

    if ($_SERVER['REQUEST_METHOD'] === 'POST') { /* Verifico que la petición sea POST */
        $tipo_edicion = isset($_POST['tipo_edicion']) ? $_POST['tipo_edicion'] : 'completo'; /* Tipo de edición específica */

        /* Determinar el ID del usuario a editar: si llega por POST (admin editando a otro), usarlo; si no, usar el propio */
        $id_objetivo = null; /* Inicializo la variable del ID del usuario a editar */
        if (isset($_POST['id_usuario']) && is_numeric($_POST['id_usuario'])) { /* Si llega por POST y es numérico */
            $id_objetivo = (int) $_POST['id_usuario']; /* Uso el ID recibido */
        } elseif (isset($_SESSION['id_usuario'])) { /* Si existe en sesión */
            $id_objetivo = (int) $_SESSION['id_usuario']; /* Uso el ID del usuario logueado */
        }

        /* Verificaciones de seguridad: debe existir sesión y permisos adecuados */
        if ($id_objetivo === null || !isset($_SESSION['id_usuario'])) { /* Si no hay sesión o ID objetivo */
            $_SESSION['error_general'] = 'Sesión no válida o usuario no especificado.'; /* Establezco el mensaje de error */
            header('Location: ../publico/index.php'); /* Redirijo al índice público */
            exit; /* Termino la ejecución */
        }

        /* Si intenta editar a otro usuario y no es admin, denegar */
        if ($id_objetivo !== (int)$_SESSION['id_usuario'] && (!isset($_SESSION['id_rol']) || (int)$_SESSION['id_rol'] !== 1)) { /* Si no es admin */
            $_SESSION['error_general'] = 'No tienes permisos para editar este usuario.'; /* Establezco el mensaje de error */
            header('Location: ../publico/index.php'); /* Redirijo al índice público */
            exit; /* Termino la ejecución */
        }

        /* URL de redirección de vuelta a editar_datos, preservando ?id= cuando sea edición de otro usuario */
        $redirectUrl = '../publico/editar_datos.php' . ($id_objetivo !== (int)$_SESSION['id_usuario'] ? ('?id=' . $id_objetivo) : '');

        // Inicializar variables según el tipo de edición
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : ''; /* Nombre real del usuario */
        $acronimo = isset($_POST['acronimo']) ? trim($_POST['acronimo']) : ''; /* Acrónimo (nombre de usuario) */
        $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';  /* Apellidos del usuario */
        $dni = isset($_POST['dni']) ? trim($_POST['dni']) : ''; /* DNI del usuario */
        $email = isset($_POST['email']) ? trim($_POST['email']) : ''; /* Email del usuario */
        $pass1 = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : ''; /* Nueva contraseña */
        $pass2 = isset($_POST['contrasena-confirmar']) ? trim($_POST['contrasena-confirmar']) : ''; /* Confirmación */
        $id_preferencia_genero = isset($_POST['id_preferencia_genero']) ? (int)$_POST['id_preferencia_genero'] : 0; /* Preferencia de género del usuario */
        $id_preferencia_categoria = isset($_POST['id_preferencia_categoria']) ? (int)$_POST['id_preferencia_categoria'] : 0; /* Preferencia de categoría del usuario */
        $id_preferencia_modo = isset($_POST['id_preferencia_modo']) ? (int)$_POST['id_preferencia_modo'] : 0; /* Preferencia de modo del usuario */
        $id_preferencia_pegi = isset($_POST['id_preferencia_pegi']) ? (int)$_POST['id_preferencia_pegi'] : 0; /* Preferencia de PEGI del usuario */


        try { /* Inicio bloque try para capturar errores de base de datos */
            
            // Manejar según el tipo de edición
            switch($tipo_edicion) {
                case 'info_personal': /* Si se está editando la información personal */
                    // Verificar si el acrónimo (nombre de usuario) del usuario ya existe
                    $consulta = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE acronimo = :acronimo AND id != :id_usuario"); /* Preparo la consulta para verificar si el acrónimo ya existe */
                    $consulta->bindParam(':acronimo', $acronimo); /* Vinculo el parámetro del acrónimo */
                    $consulta->bindParam(':id_usuario', $id_objetivo, PDO::PARAM_INT); /* Vinculo el ID del usuario a editar */
                    $consulta->execute(); /* Ejecuto la consulta */
                    $existeUsuario = $consulta->fetchColumn(); /* Obtengo el número de usuarios con ese acrónimo, el cual solo será uno, dado que el acrónimo es único */

                    if ($existeUsuario) { /* Si el acrónimo ya existe */
                        $_SESSION['error_acronimo_existente'] = 'El nombre de usuario ya está registrado. Por favor, elige otro.'; /* Establezco el mensaje de error en sesión */
                        header('Location: ' . $redirectUrl); /* Redirijo de vuelta al formulario de edición de datos */
                        exit; /* Termino la ejecución */
                    }

                    // Verificar si el DNI del usuario ya existe
                    $consulta = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE dni = :dni AND id != :id_usuario"); /* Preparo la consulta para verificar si el DNI ya existe */
                    $consulta->bindParam(':dni', $dni); /* Vinculo el parámetro del DNI */
                    $consulta->bindParam(':id_usuario', $id_objetivo, PDO::PARAM_INT); /* Vinculo el ID del usuario a editar */
                    $consulta->execute(); /* Ejecuto la consulta */
                    $existeUsuario = $consulta->fetchColumn(); /* Obtengo el número de usuarios con ese DNI, el cual solo será uno, dado que el DNI es único */

                    if ($existeUsuario) { /* Si el DNI ya existe */
                        $_SESSION['error_dni_existente'] = 'El DNI ya está registrado. Por favor, introduce otro.'; /* Establezco el mensaje de error en sesión */
                        header('Location: ' . $redirectUrl); /* Redirijo de vuelta al formulario de edición de datos */
                        exit; /* Termino la ejecución */
                    }

                    // Verificar si el email ya existe
                    $consulta = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email AND id != :id_usuario"); /* Preparo la consulta para verificar si el email ya existe */
                    $consulta->bindParam(':email', $email); /* Vinculo el parámetro del email */
                    $consulta->bindParam(':id_usuario', $id_objetivo, PDO::PARAM_INT); /* Vinculo el ID del usuario a editar */
                    $consulta->execute(); /* Ejecuto la consulta */
                    $existeEmail = $consulta->fetchColumn(); /* Obtengo el número de usuarios con ese email, el cual solo será uno, dado que el email es único */

                    if ($existeEmail) { /* Si el email ya existe */
                        $_SESSION['error_email_existente'] = 'El email ya está registrado. Por favor, usa otro.'; /* Establezco el mensaje de error en sesión */
                        header('Location: ' . $redirectUrl); /* Redirijo de vuelta al formulario de edición de datos */
                        exit; /* Termino la ejecución */
                    }

                    // Actualizar información personal
                    $consulta = $conexion->prepare("UPDATE usuarios SET acronimo = :acronimo, nombre = :nombre, apellidos = :apellidos, dni = :dni, email = :email WHERE id = :id_usuario"); /* Preparo la consulta para actualizar la información personal */
                    $consulta->bindParam(':acronimo', $acronimo); /* Vinculo el parámetro del acrónimo */
                    $consulta->bindParam(':nombre', $nombre); /* Vinculo el parámetro del nombre */
                    $consulta->bindParam(':apellidos', $apellidos); /* Vinculo el parámetro de los apellidos */
                    $consulta->bindParam(':dni', $dni); /* Vinculo el parámetro del DNI */
                    $consulta->bindParam(':email', $email); /* Vinculo el parámetro del email */
                    $consulta->bindParam(':id_usuario', $id_objetivo, PDO::PARAM_INT); /* Vinculo el ID del usuario a editar */
                    $consulta->execute(); /* Ejecuto la consulta */

                    // Actualizar variables de sesión solo si se edita el propio perfil
                    if ($id_objetivo === (int)$_SESSION['id_usuario']) {
                        $_SESSION['acronimo_usuario'] = $acronimo; /* Actualizo el acrónimo en sesión */
                        $_SESSION['nombre_usuario'] = $nombre; /* Actualizo el nombre en sesión */
                        $_SESSION['apellidos_usuario'] = $apellidos; /* Actualizo los apellidos en sesión */
                        $_SESSION['dni_usuario'] = $dni; /* Actualizo el DNI en sesión */
                        $_SESSION['email_usuario'] = $email; /* Actualizo el email en sesión */
                    }

                    $_SESSION['mensaje_exito'] = 'La información personal ha sido actualizada correctamente.'; /* Establezco el mensaje de éxito en sesión */
                    break;

                case 'preferencias': /* Si se están editando las preferencias */
                    // Eliminar preferencias existentes del usuario objetivo
                    $consulta = $conexion->prepare("DELETE FROM preferencias_usuario WHERE id_usuario = :id_usuario"); /* Preparo la consulta para eliminar las preferencias existentes */
                    $consulta->bindParam(':id_usuario', $id_objetivo, PDO::PARAM_INT); /* Vinculo el ID del usuario objetivo */
                    $consulta->execute(); /* Ejecuto la consulta */

                    // Insertar nuevas preferencias
                    $consulta = $conexion->prepare("INSERT INTO preferencias_usuario (id_usuario, id_filtro) VALUES (:id_usuario, :id_filtro)"); /* Preparo la consulta para insertar las nuevas preferencias */
                    $consulta->bindParam(':id_usuario', $id_objetivo, PDO::PARAM_INT); /* Vinculo el ID del usuario objetivo */

                    if($id_preferencia_genero !== 0) { /* Si hay preferencia de género */
                        $consulta->bindParam(':id_filtro', $id_preferencia_genero); /* Vinculo el parámetro del ID del filtro */
                        $consulta->execute(); /* Ejecuto la consulta */
                    }
                    if($id_preferencia_categoria !== 0) { /* Si hay preferencia de categoría */
                        $consulta->bindParam(':id_filtro', $id_preferencia_categoria); /* Vinculo el parámetro del ID del filtro */
                        $consulta->execute(); /* Ejecuto la consulta */
                    }
                    if($id_preferencia_modo !== 0) { /* Si hay preferencia de modo */
                        $consulta->bindParam(':id_filtro', $id_preferencia_modo); /* Vinculo el parámetro del ID del filtro */
                        $consulta->execute(); /* Ejecuto la consulta */
                    }
                    if($id_preferencia_pegi !== 0) { /* Si hay preferencia de calificación PEGI */
                        $consulta->bindParam(':id_filtro', $id_preferencia_pegi); /* Vinculo el parámetro del ID del filtro */
                        $consulta->execute(); /* Ejecuto la consulta */
                    }

                    // Si se edita el propio perfil, actualizar preferencias en sesión
                    if ($id_objetivo === (int)$_SESSION['id_usuario']) {
                        $consulta = $conexion->prepare("
                            SELECT 
                                f.id_fijo,
                                f.nombre,
                                f.tipo_filtro,
                                f.clave,
                                f.orden
                            FROM preferencias_usuario pu
                            INNER JOIN filtros f ON pu.id_filtro = f.id_fijo
                            WHERE pu.id_usuario = :id_usuario
                        "); /* Preparo la consulta para obtener las preferencias del usuario */
                        $consulta->bindParam(':id_usuario', $id_objetivo, PDO::PARAM_INT); /* Vinculo el ID del usuario */
                        $consulta->execute(); /* Ejecuto la consulta */
                        $_SESSION['preferencias_usuario'] = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Guardo las preferencias en sesión */

                        $_SESSION['filtros_elegidos'] = [ /* Creo el array de filtros elegidos con valores por defecto */
                            'tipo' => 0, /* Sin filtro de tipo por defecto */
                            'genero' => 0, /* Sin filtro de género por defecto */
                            'categoria' => 0, /* Sin filtro de categoría por defecto */
                            'modo' => 0, /* Sin filtro de modo por defecto */
                            'pegi' => 0, /* Sin filtro de PEGI por defecto */
                            'precio_min' => 0, /* Sin filtro de precio mínimo por defecto */
                            'precio_max' => 100 /* Sin filtro de precio máximo por defecto */
                        ];

                        foreach ($_SESSION['preferencias_usuario'] as $preferencia) { /* Recorro todas las preferencias del usuario */
                            switch ($preferencia['tipo_filtro']) { /* Según el tipo de filtro */
                                case 'generos': /* Si es un género */
                                    $_SESSION['filtros_elegidos']['genero'] = $preferencia['id_fijo']; /* Guardo el género preferido */
                                    break;
                                case 'categorias': /* Si es una categoría */
                                    $_SESSION['filtros_elegidos']['categoria'] = $preferencia['id_fijo']; /* Guardo la categoría preferida */
                                    break;
                                case 'modos': /* Si es un modo de juego */
                                    $_SESSION['filtros_elegidos']['modo'] = $preferencia['id_fijo']; /* Guardo el modo preferido */
                                    break;
                                case 'clasificacionPEGI': /* Si es una clasificación PEGI */
                                    $_SESSION['filtros_elegidos']['pegi'] = $preferencia['id_fijo']; /* Guardo la clasificación PEGI preferida */
                                    break;
                            }
                        }
                    }

                    $_SESSION['mensaje_exito'] = 'Las preferencias han sido actualizadas correctamente.'; /* Establezco el mensaje de éxito en sesión */
                    break;

                case 'contrasena': /* Si se está editando la contraseña */
                    // Verificar que las contraseñas no estén vacías y coincidan
                    if ($pass1 === '' || $pass2 === '' || $pass1 !== $pass2) {
                        $_SESSION['error_contrasena_no_coincide'] = 'Las contraseñas no coinciden o están vacías. Por favor, inténtalo de nuevo.'; /* Establezco el mensaje de error en sesión */
                        header('Location: ' . $redirectUrl); /* Redirijo de vuelta al formulario de edición de datos */
                        exit; /* Termino la ejecución */
                    }

                    $password_hash = password_hash($pass1, PASSWORD_DEFAULT); /* Hasheo la nueva contraseña */

                    // Actualizar contraseña
                    $consulta = $conexion->prepare("UPDATE usuarios SET contrasena = :contrasena WHERE id = :id_usuario"); /* Preparo la consulta para actualizar la contraseña */
                    $consulta->bindParam(':contrasena', $password_hash); /* Vinculo el parámetro de la contraseña */
                    $consulta->bindParam(':id_usuario', $id_objetivo, PDO::PARAM_INT); /* Vinculo el ID del usuario */
                    $consulta->execute(); /* Ejecuto la consulta */

                    $_SESSION['mensaje_exito'] = 'La contraseña ha sido cambiada correctamente.'; /* Establezco el mensaje de éxito en sesión */
                    break;

                default: /* Si el tipo de edición no es válido */
                    $_SESSION['error_general'] = 'Tipo de edición no válido.'; /* Establezco el mensaje de error en sesión */
                    break;
            }

            header('Location: ' . $redirectUrl); /* Redirijo al formulario de edición de datos */
            exit; /* Termino la ejecución */

        } catch (PDOException $e) { /* Si hay error en cualquier consulta de base de datos */
            $_SESSION['error_general'] = 'Error al actualizar los datos: ' . $e->getMessage(); /* Guardo el error en sesión */
            header('Location: ' . (isset($redirectUrl) ? $redirectUrl : '../publico/editar_datos.php')); /* Redirijo de vuelta al formulario de edición de datos */
            exit; /* Termino la ejecución */
        }
        
    } else { /* Si la petición no es POST */
        $_SESSION['error_general'] = 'Método no permitido.'; /* Establezco mensaje de error */
        header('Location: ../publico/editar_datos.php'); /* Redirijo de vuelta al formulario de edición de datos */
        exit; /* Termino la ejecución */
    }

?>
