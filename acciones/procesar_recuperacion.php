<?php

    header('Content-Type: application/json; charset=utf-8'); /* Establecer header JSON */
    
    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */
    require_once __DIR__ . "/../librerias/enviador_email.php"; /* Incluyo la función para enviar emails */

    /* Validar que sea una petición POST */
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']); /* Retorno error */
        exit; /* Termino ejecución */
    }

    /* Obtener datos del POST */
    $email = isset($_POST['email']) ? trim($_POST['email']) : ''; /* Email del usuario */
    $opcion = isset($_POST['opcion']) ? intval($_POST['opcion']) : 0; /* Opción seleccionada */

    /* Validar que tenemos datos */
    if (empty($email) || $opcion === 0) {
        echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos']); /* Retorno error */
        exit; /* Termino ejecución */
    }

    /* Validar email */
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'mensaje' => 'Email inválido']); /* Retorno error */
        exit; /* Termino ejecución */
    }

    /* Validar opción */
    if ($opcion !== 1 && $opcion !== 2) {
        echo json_encode(['success' => false, 'mensaje' => 'Opción inválida']); /* Retorno error */
        exit; /* Termino ejecución */
    }

    try { /* Inicio bloque try para capturar posibles errores */
        /* Buscar usuario por email */
        $consulta = $conexion->prepare("SELECT id, email, acronimo, nombre, contrasena FROM usuarios WHERE email = :email"); /* Preparo consulta */
        $consulta->bindParam(':email', $email, PDO::PARAM_STR); /* Enlazo el email */
        $consulta->execute(); /* Ejecuto consulta */

        /* Obtener datos del usuario */
        $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

        /* Si el usuario no existe */
        if (!$usuario) {
            echo json_encode(['success' => false, 'mensaje' => 'El correo electrónico no está registrado']); /* Retorno error */
            exit; /* Termino ejecución */
        }

        /* Procesar según la opción seleccionada */
        if ($opcion === 1) {
            /* OPCIÓN 1: Generar contraseña temporal */
            procesarOpcion1($conexion, $usuario, $email);
        } else if ($opcion === 2) {
            /* OPCIÓN 2: Generar clave de restablecimiento */
            procesarOpcion2($conexion, $usuario, $email);
        }

    } catch (PDOException $e) { /* Capturo errores de BD */
        echo json_encode(['success' => false, 'mensaje' => 'Error en la base de datos: ' . $e->getMessage()]); /* Retorno error con detalles */
        exit; /* Termino ejecución */
    } catch (Exception $e) { /* Capturo errores generales */
        echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]); /* Retorno error con detalles */
        exit; /* Termino ejecución */
    }

    // Función para procesar la opción 1: Generar contraseña temporal
    function procesarOpcion1($conexion, $usuario, $email) {
        /* Generar contraseña temporal segura (12 caracteres) */
        $contrasena_temporal = generarContrasenaTemporal();

        try { /* Inicio bloque try para capturar posibles errores */
            /* Hash de la contraseña temporal */
            $contrasena_hash = password_hash($contrasena_temporal, PASSWORD_BCRYPT);

            /* Actualizar contraseña en BD */
            $consulta = $conexion->prepare("UPDATE usuarios SET contrasena = :contrasena WHERE email = :email"); /* Preparo consulta */
            $consulta->bindParam(':contrasena', $contrasena_hash, PDO::PARAM_STR); /* Enlazo la nueva contraseña */
            $consulta->bindParam(':email', $email, PDO::PARAM_STR); /* Enlazo el email */
            $consulta->execute();

            /* Enviar email con contraseña temporal */
            enviarEmailContrasenaTemporal($usuario, $contrasena_temporal);
            
            echo json_encode([
                'success' => true, /* Retorno éxito */
                'mensaje' => 'Se ha enviado una contraseña temporal a tu correo electrónico' /* Mensaje de éxito */
            ]); /* Retorno json de éxito */
            exit; /* Termino ejecución */

        } catch (PDOException $e) { /* Capturo errores de BD */
            echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar la contraseña']); /* Retorno error */
        }
    }

    // Función para procesar la opción 2: Generar clave de restablecimiento
    function procesarOpcion2($conexion, $usuario, $email) {
        /* Genero clave aleatoria segura */
        $clave = bin2hex(random_bytes(32)); /* Clave de 64 caracteres hexadecimales */
        
        /* Guardo clave en BD con expiración de 2 horas */
        $expira = date('Y-m-d H:i:s', time() + (60 * 60 * 2)); /* 2 horas desde ahora */
        $consulta = $conexion->prepare("INSERT INTO claves (email, clave, expira_en) VALUES (:email, :clave, :expira)"); /* Preparo inserción */
        $consulta->bindParam(':email', $email, PDO::PARAM_STR); /* Enlazo email */
        $consulta->bindParam(':clave', $clave, PDO::PARAM_STR); /* Enlazo clave */
        $consulta->bindParam(':expira', $expira, PDO::PARAM_STR); /* Enlazo expiración */
        $consulta->execute(); /* Ejecuto inserción */
        $hostBase = obtenerHostBase(); /* Obtengo host base */
        $enlace = $hostBase . '/sesiones/resetear_contrasena.php?clave=' . urlencode($clave); /* Construir enlace */

        $mensaje = construirEmailReset($usuario['nombre'], $enlace, $hostBase); /* Construir mensaje HTML */
        enviarEmail($usuario['email'], 'Restablecer contraseña - CLC Games', $mensaje); /* Enviar email */

        echo json_encode([
            'success' => true, /* Retorno éxito */
            'mensaje' => 'Hemos enviado un enlace de restablecimiento a tu correo.' /* Mensaje de éxito */
        ]); /* Retorno json de éxito */
    }

    // Función para generar una contraseña temporal segura
    function generarContrasenaTemporal() {
        $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%'; /* Conjunto de caracteres */
        $contrasena = ''; /* Inicializo contraseña */
        $longitud = 12; /* Longitud de la contraseña */

        /* Generar contraseña aleatoria */
        for ($i = 0; $i < $longitud; $i++) { /* Recorro longitud */
            $contrasena .= $caracteres[random_int(0, strlen($caracteres) - 1)]; /* Agrego carácter aleatorio */
        }

        return $contrasena; /* Retorno la contraseña generada */
    }

    // Función para enviar email con contraseña temporal
    function enviarEmailContrasenaTemporal($usuario, $contrasena_temporal) {
        $destinatario = $usuario['email']; /* Email del destinatario */
        $asunto = 'Contraseña temporal - CLC Games'; /* Asunto del email */
        $nombre_usuario = htmlspecialchars($usuario['nombre']); /* Nombre del usuario escapado */
        $contrasena_segura = htmlspecialchars($contrasena_temporal); /* Contraseña temporal escapada */

        $mensaje = construirEmailRecuperacion($nombre_usuario, $contrasena_segura); /* Construyo el mensaje HTML */

        return enviarEmail($destinatario, $asunto, $mensaje); /* Envío el email */
    }

    /* Función para construir el email de recuperación */
    function construirEmailRecuperacion($nombre_usuario, $contrasena_temporal) {
        $contenido = "
            <html>
                <head>
                    <meta charset='UTF-8'>
                    <title>Recuperación de Contraseña</title>
                </head>
                <body style='font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0;'>
                    <div style='max-width: 600px; margin: 20px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);'>
                        <h1 style='color: #21b7c2; text-align: center; margin: 0 0 20px 0;'>Recuperación de Contraseña</h1>
                        
                        <p style='color: #333; line-height: 1.6;'>Hola <strong>$nombre_usuario</strong>,</p>
                        
                        <p style='color: #333; line-height: 1.6;'>Has solicitado una nueva contraseña temporal para tu cuenta en <strong>CLC Games</strong>.</p>
                        
                        <div style='background: #f9f9f9; padding: 15px; border-left: 4px solid #21b7c2; margin: 20px 0;'>
                            <p style='color: #333; line-height: 1.6;'>Tu contraseña temporal es:</p>
                            <div style='background: #21b7c2; color: #fff; padding: 15px; text-align: center; font-size: 18px; font-weight: bold; border-radius: 4px; margin: 20px 0; font-family: monospace;'>$contrasena_temporal</div>
                        </div>
                        
                        <p style='color: #333; line-height: 1.6;'><span style='color: #d9534f; font-weight: bold;'>Importante:</span></p>
                        <ul style='color: #333; line-height: 1.6;'>
                            <li>Esta contraseña es válida hasta que cambies tu contraseña personal.</li>
                            <li>Por motivos de seguridad, <strong>cambia tu contraseña lo antes posible</strong>.</li>
                            <li>No compartas esta contraseña con nadie.</li>
                            <li>Si no solicitaste esta recuperación, ignora este email.</li>
                        </ul>
                        
                        <p style='color: #333; line-height: 1.6;'>Accede a tu cuenta con esta contraseña temporal en: <a href='http://clcgames.infinityfreeapp.com/sesiones/formulario_autenticacion.php' style='color: #21b7c2; text-decoration: none;'>http://clcgames.infinityfreeapp.com/sesiones/formulario_autenticacion.php</a></p>
                        
                        <div style='text-align: center; color: #999; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                            <p style='color: #999;'>Este es un email automático, por favor no respondas a este mensaje.</p>
                            <p style='color: #999;'>&copy; 2025 Carlos Lancho Cuadrado. CLC Games está licenciado bajo <a href='https://creativecommons.org/licenses/by-nc-nd/4.0/' style='color: #21b7c2; text-decoration: none;'>CC BY-NC-ND 4.0</a></p>
                        </div>
                    </div>
                </body>
            </html>
        "; /* Construyo el contenido del email */

        return $contenido; /* Retorno el contenido del email */
    }

    /* Función para construir el email de restablecimiento */
    function construirEmailReset($nombre_usuario, $enlace, $hostBase) {
        $contenido = "
            <html>
                <head>
                    <meta charset='UTF-8'>
                    <title>Restablecer Contraseña</title>
                </head>
                <body style='font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0;'>
                    <div style='max-width: 600px; margin: 20px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);'>
                        <h1 style='color: #21b7c2; text-align: center; margin: 0 0 20px 0;'>Restablecer Contraseña</h1>
                        
                        <p style='color: #333; line-height: 1.6;'>Hola <strong>$nombre_usuario</strong>,</p>
                        
                        <p style='color: #333; line-height: 1.6;'>Has solicitado restablecer tu contraseña en <strong>CLC Games</strong>.</p>
                        
                        <div style='background: #f9f9f9; padding: 15px; border-left: 4px solid #21b7c2; margin: 20px 0;'>
                            <p style='color: #333; line-height: 1.6;'>Haz clic en el siguiente enlace para crear una nueva contraseña:</p>
                            <p style='color: #333; line-height: 1.6;'><a href='$enlace' style='color: #21b7c2; text-decoration: none;'>$enlace</a></p>
                        </div>
                        
                        <p style='color: #333; line-height: 1.6;'><span style='color: #d9534f; font-weight: bold;'>Importante:</span></p>
                        <ul style='color: #333; line-height: 1.6;'>
                            <li>El enlace caduca en 2 horas.</li>
                            <li>Si ya cambiaste tu contraseña, este enlace dejará de funcionar.</li>
                            <li>Si no solicitaste este cambio, ignora este email.</li>
                        </ul>
                        
                        <div style='text-align: center; color: #999; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                            <p style='color: #999;'>Este es un email automático, por favor no respondas a este mensaje.</p>
                            <p style='color: #999;'>&copy; 2025 Carlos Lancho Cuadrado. CLC Games está licenciado bajo <a href='https://creativecommons.org/licenses/by-nc-nd/4.0/' style='color: #21b7c2; text-decoration: none;'>CC BY-NC-ND 4.0</a></p>
                        </div>
                    </div>
                </body>
            </html>
        "; /* Construyo el contenido del email */

        return $contenido; /* Retorno el contenido del email */
    }

    /* Función para obtener el host base del sitio */
    function obtenerHostBase() {
        $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http'; /* Determino protocolo */
        $host = $_SERVER['HTTP_HOST']; /* Obtengo host */
        return $protocolo . '://' . $host; /* Retorno host base */
    }

?>
