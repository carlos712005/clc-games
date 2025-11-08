<?php

    session_start(); /* Inicio la sesión para poder manejar mensajes y errores */
    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */

    if ($_SERVER['REQUEST_METHOD'] === 'POST') { /* Verifico que la petición sea POST */
        $nombre = trim($_POST['nombre']); /* Obtengo el nombre y elimino espacios en blanco */
        $acronimo = trim($_POST['acronimo']); /* Obtengo el nombre de usuario y elimino espacios */
        $apellidos = trim($_POST['apellidos']); /* Obtengo los apellidos y elimino espacios */
        $dni = trim($_POST['dni']); /* Obtengo el DNI y elimino espacios */
        $email = trim($_POST['email']); /* Obtengo el email y elimino espacios */
        $password = password_hash(trim($_POST['contrasena']), PASSWORD_DEFAULT); /* Hash la contraseña para mayor seguridad */
        $password_confirmar = password_hash(trim($_POST['contrasena-confirmar']), PASSWORD_DEFAULT); /* Hash la contraseña de confirmación para mayor seguridad */
        $id_preferencia_genero = (int)$_POST['id_preferencia_genero']; /* Convierto la preferencia de género a entero */
        $id_preferencia_categoria = (int)$_POST['id_preferencia_categoria']; /* Convierto la preferencia de categoría a entero */
        $id_preferencia_modo = (int)$_POST['id_preferencia_modo']; /* Convierto la preferencia de modo a entero */
        $id_preferencia_pegi = (int)$_POST['id_preferencia_pegi']; /* Convierto la preferencia PEGI a entero */

        $_SESSION['datos_formulario_registro'] = []; /* Vacío los datos del formulario en sesión por si ya existían
                                                        y los dejo así para usarlos solo en caso de error, por lo que
                                                        si no hay errores los posibles datos sensibles del usuario 
                                                        (como la contraseña), guardados aquí, desaparecerán*/

        // Función para guardar los datos del formulario en sesión
        function guardarDatosFormulario($nombre, $acronimo, $apellidos, $dni, $email, $id_preferencia_genero, $id_preferencia_categoria, $id_preferencia_modo, $id_preferencia_pegi) {
            $datos = [
                'nombre' => $nombre, /* Guardo el nombre */
                'acronimo' => $acronimo, /* Guardo el acrónimo */
                'apellidos' => $apellidos, /* Guardo los apellidos */
                'dni' => $dni, /* Guardo el DNI */
                'email' => $email, /* Guardo el email */
                'contrasena' => isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '', /* Guardo la contraseña sin hashear para que el usuario la vea */
                'contrasena-confirmar' => isset($_POST['contrasena-confirmar']) ? trim($_POST['contrasena-confirmar']) : '', /* Guardo la contraseña de confirmación sin hashear para que el usuario la vea */
                'genero' => $id_preferencia_genero, /* Guardo la preferencia de género */
                'categoria' => $id_preferencia_categoria, /* Guardo la preferencia de categoría */
                'modo' => $id_preferencia_modo, /* Guardo la preferencia de modo */
                'pegi' => $id_preferencia_pegi /* Guardo la preferencia PEGI */
            ]; /* Array con los datos del formulario */
            $_SESSION['datos_formulario_registro'] = $datos; // Guardar datos en sesión
        }

        try { /* Inicio bloque try para capturar errores de base de datos */
            // Verificar si el acrónimo (nombre de usuario) del usuario ya existe
            $consulta = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE acronimo = :acronimo"); /* Preparo consulta para verificar si el usuario existe */
            $consulta->bindParam(':acronimo', $acronimo); /* Vinculo el parámetro del nombre de usuario */
            $consulta->execute(); /* Ejecuto la consulta */
            $existeUsuario = $consulta->fetchColumn(); /* Obtengo el resultado de la consulta */

            if ($existeUsuario) { /* Si el nombre de usuario ya existe */
                $_SESSION['error_acronimo_existente'] = 'El nombre de usuario ya está registrado. Por favor, elige otro.'; /* Establezco mensaje de error específico */
                guardarDatosFormulario($nombre, $acronimo, $apellidos, $dni, $email, $id_preferencia_genero, $id_preferencia_categoria, $id_preferencia_modo, $id_preferencia_pegi); /* Guardo los datos del formulario en sesión */
                header('Location: registro.php'); /* Redirijo de vuelta al registro */
                exit; /* Termino la ejecución */
            }

            // Verificar si el DNI del usuario ya existe
            $consulta = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE dni = :dni"); /* Preparo consulta para verificar si el DNI existe */
            $consulta->bindParam(':dni', $dni); /* Vinculo el parámetro del DNI */
            $consulta->execute(); /* Ejecuto la consulta */
            $existeDNI = $consulta->fetchColumn(); /* Obtengo el resultado de la consulta */

            if ($existeDNI) { /* Si el DNI ya existe */
                $_SESSION['error_dni_existente'] = 'El DNI ya está registrado. Por favor, introduce otro.'; /* Establezco mensaje de error específico */
                guardarDatosFormulario($nombre, $acronimo, $apellidos, $dni, $email, $id_preferencia_genero, $id_preferencia_categoria, $id_preferencia_modo, $id_preferencia_pegi); /* Guardo los datos del formulario en sesión */
                header('Location: registro.php'); /* Redirijo de vuelta al registro */
                exit; /* Termino la ejecución */
            }

            // Verificar si el email ya existe
            $consulta = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email"); /* Preparo consulta para verificar si el email existe */
            $consulta->bindParam(':email', $email); /* Vinculo el parámetro del email */
            $consulta->execute(); /* Ejecuto la consulta */
            $existeEmail = $consulta->fetchColumn(); /* Obtengo el resultado de la consulta */

            if ($existeEmail) { /* Si el email ya existe */
                $_SESSION['error_email_existente'] = 'El email ya está registrado. Por favor, usa otro.'; /* Establezco mensaje de error específico */
                guardarDatosFormulario($nombre, $acronimo, $apellidos, $dni, $email, $id_preferencia_genero, $id_preferencia_categoria, $id_preferencia_modo, $id_preferencia_pegi); /* Guardo los datos del formulario en sesión */
                header('Location: registro.php'); /* Redirijo de vuelta al registro */
                exit; /* Termino la ejecución */
            }

            // Verificar que las contraseñas coincidan
            if (trim($_POST['contrasena']) !== trim($_POST['contrasena-confirmar'])) { /* Si las contraseñas no coinciden */
                $_SESSION['error_contrasena_no_coincide'] = 'Las contraseñas no coinciden. Por favor, inténtalo de nuevo.'; /* Establezco mensaje de error específico */
                guardarDatosFormulario($nombre, $acronimo, $apellidos, $dni, $email, $id_preferencia_genero, $id_preferencia_categoria, $id_preferencia_modo, $id_preferencia_pegi); /* Guardo los datos del formulario en sesión */
                header('Location: registro.php'); /* Redirijo de vuelta al registro */
                exit; /* Termino la ejecución */
            }

            // Insertar el nuevo usuario
            $consulta = $conexion->prepare("INSERT INTO usuarios (acronimo, nombre, apellidos, dni, email, contrasena, id_rol) VALUES (:acronimo, :nombre, :apellidos, :dni, :email, :contrasena, :id_rol)"); /* Preparo la inserción del nuevo usuario */
            $consulta->bindParam(':acronimo', $acronimo); /* Vinculo el nombre de usuario */
            $consulta->bindParam(':nombre', $nombre); /* Vinculo el nombre real */
            $consulta->bindParam(':apellidos', $apellidos); /* Vinculo los apellidos */
            $consulta->bindParam(':dni', $dni); /* Vinculo el DNI */
            $consulta->bindParam(':email', $email); /* Vinculo el email */
            $consulta->bindParam(':contrasena', $password); /* Vinculo la contraseña hasheada */
            $consulta->bindValue(':id_rol', 2); /* Establezco el rol como cliente (2) */
            $consulta->execute(); /* Ejecuto la inserción del usuario */

            $id_usuario = $conexion->lastInsertId(); /* Obtengo el ID del usuario que acabo de crear */

            // Insertar las preferencias del usuario
            $consulta = $conexion->prepare("INSERT INTO preferencias_usuario (id_usuario, id_filtro) VALUES (:id_usuario, :id_filtro)"); /* Preparo la inserción de preferencias */
            $consulta->bindParam(':id_usuario', $id_usuario); /* Vinculo el ID del usuario una vez */

            if($id_preferencia_genero !== 0) { /* Si se ha seleccionado una preferencia de género (diferente de 0) */
                $consulta->bindParam(':id_filtro', $id_preferencia_genero); /* Vinculo la preferencia de género */
                $consulta->execute(); /* Ejecuto la inserción de la preferencia de género */
            }

            if($id_preferencia_categoria !== 0) { /* Si se ha seleccionado una preferencia de categoría (diferente de 0) */
                $consulta->bindParam(':id_filtro', $id_preferencia_categoria); /* Vinculo la preferencia de categoría */
                $consulta->execute(); /* Ejecuto la inserción de la preferencia de categoría */
            }
            if($id_preferencia_modo !== 0) { /* Si se ha seleccionado una preferencia de modo (diferente de 0) */
                $consulta->bindParam(':id_filtro', $id_preferencia_modo); /* Vinculo la preferencia de modo */
                $consulta->execute(); /* Ejecuto la inserción de la preferencia de modo */
            }
            if($id_preferencia_pegi !== 0) { /* Si se ha seleccionado una preferencia PEGI (diferente de 0) */
                $consulta->bindParam(':id_filtro', $id_preferencia_pegi); /* Vinculo la preferencia PEGI */
                $consulta->execute(); /* Ejecuto la inserción de la preferencia PEGI */
            }

            if(isset($_SESSION['modo_admin']) && $_SESSION['modo_admin'] === true) { /* Si el registro se hace desde el modo admin */
                $_SESSION['mensaje_exito'] = 'Usuario añadido correctamente.'; /* Establezco mensaje de éxito */
                header('Location: ../vistas/panel_administrador.php'); /* Redirijo al panel de administrador */
            } else { /* Si el registro no se hace desde el modo admin */
                $_SESSION['mensaje_exito'] = 'Registro exitoso. Ahora puedes iniciar sesión.'; /* Establezco mensaje de éxito */
                header('Location: formulario_autenticacion.php'); /* Redirijo al formulario de login */
            }
            exit; /* Termino la ejecución */
        } catch (PDOException $e) { /* Si hay error en cualquier consulta de base de datos */
            $_SESSION['error_general'] = 'Error al registrar el usuario: ' . $e->getMessage(); /* Guardo el error en sesión */
            header('Location: registro.php'); /* Redirijo de vuelta al registro */
            exit; /* Termino la ejecución */
        }
    } else { /* Si la petición no es POST */
        $_SESSION['error_general'] = 'Método no permitido.'; /* Establezco mensaje de error */
        header('Location: registro.php'); /* Redirijo de vuelta al registro */
        exit; /* Termino la ejecución */
    }

?>
