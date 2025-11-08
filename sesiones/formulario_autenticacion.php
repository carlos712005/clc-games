<?php

    // Iniciar sesión para manejar mensajes dinámicos
    session_start(); /* Inicio la sesión para poder mostrar mensajes de error */
    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */

    // Mostrar mensaje de error si existe
    $mensaje_error = isset($_SESSION['mensaje_error']) ? $_SESSION['mensaje_error'] : ''; /* Obtengo el mensaje de error si existe */
    unset($_SESSION['mensaje_error']); /* Limpio el mensaje de error de la sesión */

?>

<!DOCTYPE html>
<html lang="es"> <!-- Documento HTML en español -->

<head>
    <meta charset="UTF-8"> <!-- Codificación de caracteres UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Viewport responsive -->
    <title>CLC Games</title> <!-- Título de la página -->
    <link rel="icon" type="image/x-icon" href="../recursos/imagenes/favicon.ico"> <!-- Favicon del sitio -->
    <link rel="stylesheet" href="../recursos/css/estilos_login.css"> <!-- Estilos específicos del login -->
</head>

<body>
    <h1>Iniciar Sesión</h1> <!-- Título principal de la página -->

    <?php if ($mensaje_error) { ?> <!-- Si hay mensaje de error -->
        <div class="mensaje-error"> <?php echo htmlspecialchars($mensaje_error); ?> </div> <!-- Muestro el error en rojo, escapado -->
    <?php } ?> <!-- Fin del condicional de error -->

    <?php if (isset($_SESSION['mensaje_exito'])) { ?> <!-- Si hay mensaje de éxito -->
        <div class="mensaje-exito"> <?php echo htmlspecialchars($_SESSION['mensaje_exito']); ?> </div> <!-- Muestro el mensaje de éxito -->
        <?php unset($_SESSION['mensaje_exito']); ?> <!-- Limpio el mensaje de la sesión -->
    <?php } ?> <!-- Fin del condicional -->

    <form action="login.php" method="post"> <!-- Formulario que envía a login.php por POST -->
        <label for="usuario">Usuario:</label> <!-- Etiqueta para el campo de usuario -->
        <input type="text" id="usuario" name="usuario" 
               minlength="3" maxlength="50" 
               placeholder="Introduce tu usuario o email"
               title="Mínimo 3 caracteres"
               tabindex="1" required> <!-- Campo de usuario con restricciones y primer en orden de navegación -->

        <label for="contrasena">Contraseña:</label> <!-- Etiqueta para el campo de contraseña -->
        <div class="contenedor-contrasena"> <!-- Contenedor relativo para posicionar el botón mostrar/ocultar -->
            <input type="password" id="contrasena" name="contrasena" 
                   minlength="1" maxlength="255"
                   placeholder="Introduce tu contraseña"
                   title="Introduce tu contraseña"
                   tabindex="2" required> <!-- Campo de contraseña con restricciones y segundo en orden -->
            <button type="button" id="boton-contrasena" class="mostrar-ocultar-contrasena" onclick="mostrarOcultarContrasena('contrasena')" tabindex="-1" title="Mostrar contraseña"></button> <!-- Botón para mostrar/ocultar contraseña -->
        </div> <!-- Fin del contenedor de contraseña -->

        <button type="submit" tabindex="3">Iniciar sesión</button> <!-- Botón para enviar el formulario, tercero en orden -->

        <h2>¿Eres nuevo cliente?</h2> <!-- Subtítulo para la sección de registro -->
        <button type="button" onclick="window.location.href='registro.php'" tabindex="4">Crear cuenta</button> <!-- Botón que redirije al registro, cuarto en orden -->
        <br><br> <!-- Espacios adicionales -->

    </form> <!-- Fin del formulario -->

    <br> <!-- Espacio adicional -->
    <a href="../publico/index.php" tabindex="5">Volver al inicio</a> <!-- Enlace de vuelta al index, quinto en orden de navegación -->

    <script src="../recursos/js/mostrar_ocultar_contrasena.js" defer></script> <!-- Script para funcionalidad de mostrar/ocultar contraseña -->

</body>

</html>
