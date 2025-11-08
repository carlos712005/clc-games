<?php

    session_start(); // Iniciar sesión
    // Verificar sesión después del encabezado y redirigir con JavaScript si es necesario
    if(!isset($_SESSION['id_usuario']) && !isset($_SESSION['id_rol']) && $_SESSION['id_rol'] != 1) {
        echo '<script>window.location.href = "../publico/index.php";</script>'; /* Redirijo con JavaScript si no está logueado */
        exit; /* Termino la ejecución del script */
    }
    
    $_SESSION['modo_admin'] = false; /* Indico que no estamos en modo administrador */

?>

<!doctype html>
<html lang="es"> <!-- Documento HTML en español -->

<head>
  <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" /> <!-- Codificación de caracteres UTF-8 y Viewport responsive-->
  <title>CLC Games</title> <!-- Título de la página -->
  <link rel="icon" type="image/x-icon" href="../recursos/imagenes/favicon.ico"> <!-- Favicon del sitio -->
  <link rel="stylesheet" href="../recursos/css/estilos_modo_vision.css" type="text/css"> <!-- Estilos generales del modo de visión -->
</head>

<body>
    <main> <!-- Contenedor principal del modo de visión -->

        <h1 data-translate="modo_vision">Seleccione un modo de visión</h1> <!-- Título principal de la página -->
        <hr> <!-- Línea horizontal decorativa -->

        <div class="contenedor-principal"> <!-- Contenedor principal del modo de visión -->

            <div class="informacion"> <!-- Contenedor de información del modo de visión -->
                <h3>Información relevante:</h3> <!-- Subtítulo de la sección de información -->
                <hr> <!-- Línea horizontal decorativa -->
                <p data-translate="modo_vision_descripcion">
                    - Si elige "Vista de Usuario" accederá a la página como un usuario normal.
                    <br>
                    - Si elige "Vista de Administrador" podrá gestionar los juegos y los usuarios.
                </p> <!-- Descripción del modo de visión -->
            </div> <!-- Fin del contenedor de información -->

            <div class="opciones"> <!-- Contenedor de opciones del modo de visión -->
                <div class="opcion"> <!-- Contenedor de la opción de vista de usuario -->
                    <a href="../acciones/acciones_vision.php?modo=usuario" id="boton-usuario" data-translate="vista_usuario">
                        <img src="../recursos/imagenes/login.png" alt="Icono de usuario" class="icono-opcion"> <!-- Icono representativo de la vista de usuario -->
                        <span>Vista de Usuario</span> <!-- Texto del botón de vista de usuario -->
                    </a> <!-- Botón para acceder a la vista de usuario -->
                </div>
                <div class="opcion"> <!-- Contenedor de la opción de vista de administrador -->
                    <a href="../acciones/acciones_vision.php?modo=administrador" id="boton-administrador" data-translate="vista_administrador">
                        <img src="../recursos/imagenes/vision.png" alt="Icono de administrador" class="icono-opcion"> <!-- Icono representativo de la vista de administrador -->
                        <span>Vista de Administrador</span> <!-- Texto del botón de vista de administrador -->
                    </a> <!-- Botón para acceder a la vista de administrador -->
                </div>
            </div> <!-- Fin del contenedor de opciones -->

        </div> <!-- Fin del contenedor principal -->

    </main> <!-- Fin del contenedor principal -->
</body>
</html>