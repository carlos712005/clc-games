    <!-- Encabezado -->
    <?php include __DIR__ . '/../vistas/comunes/encabezado.php'; ?> <!-- Incluyo el encabezado con menú y estilos -->

    <?php
    // Verificar sesión y redirigir con JavaScript si es necesario
    if(!isset($_SESSION['id_usuario'])) {
        echo '<script>window.location.href = "index.php";</script>'; /* Redirijo con JavaScript si no está logueado */
        exit; /* Termino la ejecución del script */
    }

    // Determinar qué usuario ver (puede ser el propio o uno asignado por el administrador)
    $id_usuario = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['id_usuario'];

    // Si no es admin y está intentando ver biblioteca de otro usuario, denegar acceso
    if($_SESSION['id_rol'] != 1 && $_SESSION['id_usuario'] != $id_usuario) {
        echo '<script>window.location.href = "index.php";</script>'; /* Redirijo con JavaScript */
        exit; /* Termino la ejecución del script */
    }

    // Si se está viendo otro usuario, obtener sus datos
    $datos_usuario = null; /* Inicializo variable de datos de usuario */
    if($id_usuario !== $_SESSION['id_usuario']) { /* Si es otro usuario */
        $consulta_usuario = $conexion->prepare("SELECT nombre, apellidos FROM usuarios WHERE id = :id_usuario"); /* Preparo consulta para obtener datos de otro usuario */
        $consulta_usuario->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
        $consulta_usuario->execute(); /* Ejecuto la consulta */
        $datos_usuario = $consulta_usuario->fetch(PDO::FETCH_ASSOC); /* Obtengo los datos del usuario */

        if(!$datos_usuario) { /* Si no se encuentran los datos del usuario */
            echo '<script>window.location.href = "index.php";</script>'; /* Redirijo con JavaScript */
            exit; /* Termino la ejecución del script */
        }
    }
    ?>

    <!--Función para mostrar los juegos -->
    <?php include __DIR__ . '/../funciones/mostrar_juegos.php'; ?> <!-- Cargo la función que muestra los juegos filtrados -->

    <link rel="stylesheet" href="../recursos/css/estilos_biblioteca.css" type="text/css"> <!-- Estilos específicos de la biblioteca -->
    <link rel="stylesheet" href="../recursos/css/estilos_index.css" type="text/css"> <!-- Estilos generales del index -->

    <main> <!-- Contenedor principal -->

        <h1 data-translate="biblioteca"> <!-- Título principal de la página -->
            <?php 
            if($id_usuario !== $_SESSION['id_usuario']) { /* Si es otro usuario */
                echo 'Biblioteca de: ' . htmlspecialchars($datos_usuario['nombre'] . ' ' . $datos_usuario['apellidos']); /* Muestro el nombre del usuario */
            } else { /* Si es el propio usuario */
                echo 'Mi Biblioteca'; /* Muestro "Mi Biblioteca" */
            }
            ?>
        </h1> <!-- Título principal de la página -->
        <hr> <!-- Línea horizontal decorativa -->
        
        <div id="contenedor-juegos" class="contenedor-juegos"> <!-- Contenedor específico para las tarjetas de juegos -->
            <!-- Aquí se cargarán los juegos de la biblioteca dinámicamente -->
        </div> <!-- Fin del contenedor de juegos -->

    </main> <!-- Fin del contenedor principal -->

    <!-- Script para la biblioteca -->
    <script src="../recursos/js/biblioteca.js" defer></script> <!-- Script específico para la biblioteca -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            mostrarBiblioteca(<?php echo $id_usuario ? $id_usuario : 'null'; ?>); /* Al cargar la página, muestro los juegos de la biblioteca */
            eliminarAdvertencia(); /* Al cargar la página, inicio el temporizador para eliminar la advertencia */
        });
    </script>

    <!-- Pie de página -->
    <?php include __DIR__ . '/../vistas/comunes/pie.php'; ?> <!-- Incluyo el pie de página común -->
