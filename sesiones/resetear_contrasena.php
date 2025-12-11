<?php

    require_once __DIR__ . '/../config/conexion.php'; /* Incluyo la conexión a la base de datos */

    // Función para validar la clave de restablecimiento
    function validarClave($clave, $conexion) {
        /* Busco clave en BD */
        $consulta = $conexion->prepare('SELECT email, expira_en, usado FROM claves WHERE clave = :clave'); /* Preparo consulta */
        $consulta->bindParam(':clave', $clave, PDO::PARAM_STR); /* Enlazo clave */
        $consulta->execute(); /* Ejecuto consulta */
        $clave = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo resultado */

        /* Verifico si la clave existe */
        if (!$clave) {
            return [null, 'Clave inválida']; /* Retorno error si no existe */
        }

        /* Verifico si ya fue usado */
        if ($clave['usado'] == 1) {
            return [null, 'El enlace ya fue utilizado']; /* Retorno error si ya fue usado */
        }

        /* Verifico expiración */
        if (strtotime($clave['expira_en']) < time()) {
            return [null, 'El enlace ha caducado']; /* Retorno error si ha expirado */
        }

        /* Busco usuario */
        $consulta_user = $conexion->prepare('SELECT id, email, nombre FROM usuarios WHERE email = :email'); /* Preparo consulta */
        $consulta_user->bindParam(':email', $clave['email'], PDO::PARAM_STR); /* Enlazo email */
        $consulta_user->execute(); /* Ejecuto consulta */
        $usuario = $consulta_user->fetch(PDO::FETCH_ASSOC); /* Obtengo resultado */

        /* Verifico si el usuario existe */
        if (!$usuario) {
            return [null, 'El usuario no existe']; /* Retorno error si no existe */
        }

        return [$usuario, null]; /* Retorno usuario y null si todo está bien */
    }

    $clave = isset($_GET['clave']) ? $_GET['clave'] : (isset($_POST['clave']) ? $_POST['clave'] : ''); /* Obtengo clave de GET o POST */
    $mensaje_error = ''; /* Inicializo mensaje de error */
    $mensaje_exito = ''; /* Inicializo mensaje de éxito */
    $form_activo = true; /* Indica si el formulario debe mostrarse */

    /* Si la clave está vacía */
    if (empty($clave)) {
        $mensaje_error = 'Falta la clave de restablecimiento.'; /* Mensaje de error */
        $form_activo = false; /* Desactivo formulario */
    } else { /* Valido la clave */
        [$usuario_clave, $error_clave] = validarClave($clave, $conexion); /* Valido clave */

        if ($error_clave) { /* Si hay error */
            $mensaje_error = $error_clave; /* Asigno mensaje de error */
            $form_activo = false; /* Desactivo formulario */
        }
    }

    /* Si se envió el formulario */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $form_activo) {
        $pass1 = isset($_POST['nueva_contrasena']) ? trim($_POST['nueva_contrasena']) : ''; /* Nueva contraseña */
        $pass2 = isset($_POST['confirmar_contrasena']) ? trim($_POST['confirmar_contrasena']) : ''; /* Confirmar contraseña */

        if (strlen($pass1) < 8) { /* Verifico longitud mínima */
            $mensaje_error = 'La contraseña debe tener al menos 8 caracteres.'; /* Mensaje de error */
        } elseif ($pass1 !== $pass2) { /* Verifico que coincidan */
            $mensaje_error = 'Las contraseñas no coinciden.'; /* Mensaje de error */
        } else { /* Valido usuario asociado a la clave */
            [$usuario_clave, $error_clave] = validarClave($clave, $conexion); /* Revalido clave */
            if ($error_clave) { /* Si hay error */
                $mensaje_error = $error_clave; /* Asigno mensaje de error */
                $form_activo = false; /* Desactivo formulario */
            } else { /* Si no hay error */
                /* Actualizo contraseña */
                $hash = password_hash($pass1, PASSWORD_BCRYPT); /* Hasheo la nueva contraseña */
                $consulta = $conexion->prepare('UPDATE usuarios SET contrasena = :contrasena WHERE id = :id'); /* Preparo actualización */
                $consulta->bindParam(':contrasena', $hash, PDO::PARAM_STR); /* Enlazo contraseña */
                $consulta->bindParam(':id', $usuario_clave['id'], PDO::PARAM_INT); /* Enlazo ID */
                $consulta->execute(); /* Ejecuto actualización */

                /* Marco clave como usada */
                $consulta = $conexion->prepare('UPDATE claves SET usado = 1 WHERE clave = :clave'); /* Preparo actualización */
                $consulta->bindParam(':clave', $clave, PDO::PARAM_STR); /* Enlazo clave */
                $consulta->execute(); /* Ejecuto actualización */

                $mensaje_exito = 'Contraseña actualizada correctamente. Ya puedes iniciar sesión con tu nueva contraseña.'; /* Mensaje de éxito */
                $form_activo = false; /* Desactivo formulario */
            }
        }
    }

?>

<!DOCTYPE html>
<html lang="es"> <!-- Documento HTML en español -->
<head>
    <meta charset="UTF-8"> <!-- Codificación de caracteres UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Viewport responsive -->
    <title>CLC Games</title> <!-- Título de la página -->
    <link rel="icon" type="image/x-icon" href="../recursos/imagenes/favicon.ico"> <!-- Favicon del sitio -->
    <link rel="stylesheet" href="../recursos/css/estilos_email_recuperacion.css"> <!-- Estilos específicos del restablecimiento de contraseña -->
    <link rel="stylesheet" href="../recursos/css/estilos_login.css"> <!-- Reuso estilos de campos de contraseña -->
</head>
<body class="pagina-restablecer">

    <?php if ($form_activo) { ?> <!-- Si el formulario está activo -->
        <h1>Restablecer contraseña</h1> <!-- Título principal -->
    <?php } ?>

    <?php if ($mensaje_error) { ?> <!-- Si hay mensaje de error -->
        <div class="mensaje-error"><?php echo htmlspecialchars($mensaje_error); ?></div> <!-- Muestro el error, escapado -->
    <?php } ?>

    <?php if ($mensaje_exito) { ?> <!-- Si hay mensaje de éxito -->
        <div class="mensaje-exito"><?php echo htmlspecialchars($mensaje_exito); ?></div> <!-- Muestro el éxito, escapado -->
    <?php } ?>

    <a href="login.php" class="boton-ir-login <?php echo $mensaje_exito ? 'visible' : 'oculto'; ?>">Ir a la página de inicio de sesión</a> <!-- Botón para ir al login, visible solo con éxito -->

    <?php if ($form_activo) { ?> <!-- Si el formulario está activo -->
        <form method="POST" action="resetear_contrasena.php" class="formulario-restablecer"> <!-- Formulario de restablecimiento -->
            <input type="hidden" name="clave" value="<?php echo htmlspecialchars($clave); ?>"> <!-- Campo oculto con la clave -->

            <label for="nueva_contrasena">Nueva contraseña</label> <!-- Etiqueta para nueva contraseña -->
            <div class="contenedor-contrasena">
                <input type="password" id="nueva_contrasena" name="nueva_contrasena" 
                        placeholder="Introduce tu contraseña" 
                        minlength="8" maxlength="255" 
                        pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}"
                        title="Mínimo 8 caracteres, debe contener al menos: 1 minúscula, 1 mayúscula, 1 número y 1 carácter especial (@$!%*?&)" 
                        required> <!-- Campo de nueva contraseña -->
                <button type="button" id="boton-nueva_contrasena" class="mostrar-ocultar-contrasena" onclick="mostrarOcultarContrasena('nueva_contrasena')" title="Mostrar contraseña"></button> <!-- Botón para mostrar/ocultar -->
            </div>

            <label for="confirmar_contrasena">Confirmar contraseña</label> <!-- Etiqueta para confirmar contraseña -->
            <div class="contenedor-contrasena">
                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" 
                        placeholder="Confirma tu contraseña" 
                        minlength="8" maxlength="255" 
                        pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}"
                        title="Mínimo 8 caracteres, debe contener al menos: 1 minúscula, 1 mayúscula, 1 número y 1 carácter especial (@$!%*?&)" 
                        required> <!-- Campo de confirmación -->
                <button type="button" id="boton-confirmar_contrasena" class="mostrar-ocultar-contrasena" onclick="mostrarOcultarContrasena('confirmar_contrasena')" title="Mostrar contraseña"></button> <!-- Botón para mostrar/ocultar -->
            </div>

            <button type="submit" class="boton-guardar">Guardar contraseña</button> <!-- Botón para enviar el formulario -->
        </form>
        <p class="nota-aviso">El enlace caduca en 2 horas. Si ya cambiaste la contraseña, ignora este enlace.</p> <!-- Nota de aviso -->
    <?php } ?>
    <script src="../recursos/js/mostrar_ocultar_contrasena.js" defer></script> <!-- Script para mostrar/ocultar contraseñas -->
</body>
</html>
