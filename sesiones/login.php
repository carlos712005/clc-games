<?php

    session_start(); /* Inicio la sesión para poder guardar los datos del usuario logueado */
    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */

    // Proceso de inicio de sesión
    if (isset($_POST['usuario']) && isset($_POST['contrasena'])) { /* Verifico que lleguen los datos del formulario */
        $usuario = trim($_POST['usuario']); /* Obtengo el usuario y elimino espacios en blanco */
        $contrasena = trim($_POST['contrasena']); /* Obtengo la contraseña y elimino espacios en blanco */

        try { /* Inicio bloque try para capturar errores de base de datos */
            $consulta = $conexion->prepare("SELECT * FROM usuarios WHERE acronimo = :acronimo OR email = :email"); /* Busco el usuario por nombre o email */
            $consulta->bindParam(':acronimo', $usuario, PDO::PARAM_STR); /* Vinculo el parámetro del nombre de usuario */
            $consulta->bindParam(':email', $usuario, PDO::PARAM_STR); /* Vinculo el parámetro del email */
            $consulta->execute(); /* Ejecuto la consulta */

            $usuario_bd = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del usuario de la base de datos */

            $ultima_actualizacion = $usuario_bd ? $usuario_bd['actualizado_en'] : null; /* Obtengo la última actualización si el usuario existe */

            if ($usuario_bd && password_verify($contrasena, $usuario_bd['contrasena'])) { /* Si encuentro el usuario y la contraseña coincide */

                // Actualizar último acceso del usuario y guardar la ultima actualización, para evitar que la columna actualizado_en se actualice al iniciar sesión
                try { /* Inicio bloque try para actualizar el último acceso */
                    $actualizarAcceso = $conexion->prepare("UPDATE usuarios SET ultimo_acceso = NOW(), actualizado_en = :ultima_actualizacion WHERE id = :id_usuario"); /* Preparo consulta para actualizar último acceso */
                    $actualizarAcceso->bindParam(':id_usuario', $usuario_bd['id'], PDO::PARAM_INT); /* Vinculo el ID del usuario */
                    $actualizarAcceso->bindParam(':ultima_actualizacion', $ultima_actualizacion, PDO::PARAM_STR); /* Vinculo la última actualización */
                    $actualizarAcceso->execute(); /* Ejecuto la actualización */
                } catch (PDOException $e) { /* Si hay error al actualizar el último acceso */
                    $_SESSION['mensaje_error'] = 'Error al actualizar el último acceso: ' . $e->getMessage(); /* Guardo el error en sesión */
                    header('Location: formulario_autenticacion.php'); /* Redirijo de vuelta al login */
                    exit; /* Termino la ejecución */
                }
                
                $_SESSION['id_usuario'] = $usuario_bd['id']; /* Guardo el ID del usuario en la sesión */
                $_SESSION['acronimo_usuario'] = $usuario_bd['acronimo']; /* Guardo el acrónimo de usuario en la sesión */
                $_SESSION['nombre_usuario'] = $usuario_bd['nombre']; /* Guardo el nombre real en la sesión */
                $_SESSION['apellidos_usuario'] = $usuario_bd['apellidos']; /* Guardo los apellidos en la sesión */
                $_SESSION['dni_usuario'] = $usuario_bd['dni']; /* Guardo el DNI en la sesión */
                $_SESSION['email_usuario'] = $usuario_bd['email']; /* Guardo el email en la sesión */
                $_SESSION['id_rol'] = $usuario_bd['id_rol']; /* Guardo el rol del usuario en la sesión */

                // Obtener y almacenar preferencias del usuario
                $_SESSION['preferencias_usuario'] = obtenerPreferencias($conexion, $usuario_bd['id']); /* Obtengo las preferencias del usuario y las guardo en sesión */
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
                
                if ($usuario_bd['id_rol'] == 1) { /* Si el usuario es administrador */
                    header('Location: ../vistas/modo_vision.php'); /* Redirijo al panel de administración */
                    exit; /* Termino la ejecución */
                } elseif ($usuario_bd['id_rol'] == 2) { /* Si el usuario es cliente normal */
                    header('Location: ../publico/index.php'); /* Redirijo al index principal */
                    exit; /* Termino la ejecución */
                } else { /* Si el rol no es reconocido */
                    $_SESSION['mensaje_error'] = 'Rol de usuario no reconocido.'; /* Establezco mensaje de error */
                    header('Location: formulario_autenticacion.php'); /* Redirijo de vuelta al login */
                    exit; /* Termino la ejecución */
                }
            } else { /* Si no encuentro el usuario o la contraseña es incorrecta */
                $_SESSION['mensaje_error'] = 'Usuario o contraseña incorrectos.'; /* Establezco mensaje de error */
                header('Location: formulario_autenticacion.php'); /* Redirijo de vuelta al login */
                exit; /* Termino la ejecución */
            }
        } catch (PDOException $e) { /* Si hay error en la consulta de la base de datos */
            $_SESSION['mensaje_error'] = 'Error al conectar con la base de datos: ' . $e->getMessage(); /* Guardo el error en sesión */
            header('Location: formulario_autenticacion.php'); /* Redirijo de vuelta al login */
            exit; /* Termino la ejecución */
        }
    } else { /* Si no llegaron los datos del formulario */
        $_SESSION['mensaje_error'] = 'Por favor, ingrese sus credenciales.'; /* Establezco mensaje de error */
        header('Location: formulario_autenticacion.php'); /* Redirijo de vuelta al login */
        exit; /* Termino la ejecución */
    }

    // Función que obtiene las preferencias de juegos del usuario
    function obtenerPreferencias($conexion, $id_usuario) {
        try { /* Inicio bloque try para capturar errores */
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
            "); /* Preparo consulta para obtener las preferencias del usuario con JOIN a filtros */
            
            $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID del usuario */
            $consulta->execute(); /* Ejecuto la consulta */
            
            return $consulta->fetchAll(PDO::FETCH_ASSOC); /* Retorno todas las preferencias como array asociativo */
            
        } catch (PDOException $e) { /* Si hay error al obtener las preferencias */
            $_SESSION['mensaje_error'] = 'Error al obtener preferencias: ' . $e->getMessage(); /* Guardo el error en sesión */
            header('Location: formulario_autenticacion.php'); /* Redirijo de vuelta al login */
            exit; /* Termino la ejecución */
        }
    }

?>
