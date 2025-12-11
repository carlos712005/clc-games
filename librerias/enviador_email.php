<?php

    // Importar clases de PHPMailer
    use PHPMailer\PHPMailer\PHPMailer; /* Importo la clase PHPMailer */
    use PHPMailer\PHPMailer\Exception; /* Importo la clase Exception */

    // Cargar archivos de la librería
    require_once __DIR__ . '/PHPMailer/src/Exception.php'; /* Incluyo el archivo Exception.php */
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php'; /* Incluyo el archivo PHPMailer.php */
    require_once __DIR__ . '/PHPMailer/src/SMTP.php'; /* Incluyo el archivo SMTP.php */

    //Función para enviar un email en formato HTML usando Gmail (SMTP)
    function enviarEmail($destinatario, $asunto, $mensaje_html) {
        $mail = new PHPMailer(true); /* Creo una instancia de PHPMailer */

        try { /* Inicio bloque try para capturar posibles excepciones */
            // Configuración del servidor SMTP de Gmail
            $mail->isSMTP(); /* Usar SMTP */
            $mail->Host       = 'smtp.gmail.com'; /* Servidor SMTP de Gmail */
            $mail->SMTPAuth   = true; /* Habilitar autenticación SMTP */

            // Datos de autenticación
            $mail->Username   = 'clcgamesoficial@gmail.com'; /* Gmail de la página */
            $mail->Password   = 'byiqmoskhtysooso'; /* Contraseña de aplicación (16 caracteres) */
            
            // Cifrado y puerto
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; /* SSL */
            $mail->Port       = 465; /* Puerto SSL de Gmail */

            // Configuración del email
            $mail->CharSet = 'UTF-8'; /* Codificación UTF-8 */
            $mail->setFrom('clcgamesoficial@gmail.com', 'CLC Games'); /* Remitente */
            $mail->addAddress($destinatario); /* Destinatario */
            $mail->addReplyTo('clcgamesoficial@gmail.com', 'Soporte CLC Games'); /* Responder a */

            // Contenido
            $mail->isHTML(true); /* Formato HTML */
            $mail->Subject = $asunto; /* Asunto */
            $mail->Body    = $mensaje_html; /* Cuerpo del mensaje */

            // Enviar
            return $mail->send(); /* Enviar el email y retornar el resultado */
            
        } catch (Exception $e) { /* Capturo excepciones y posibles errores */
            error_log("Error al enviar correo: {$mail->ErrorInfo}"); /* Registro el error para depuración */
            return false; /* Retorno false en caso de error */
        }
    }
    
?>
