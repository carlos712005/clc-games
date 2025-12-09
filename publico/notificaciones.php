    <?php require_once __DIR__ . "/../vistas/comunes/encabezado.php"; /* Incluyo el encabezado común */ ?>

    <?php // Verificar sesión y redirigir con JavaScript si es necesario
        if(!isset($_SESSION['id_usuario'])) { /* Si no está logueado */
            echo '<script>window.location.href = "index.php";</script>'; /* Redirijo con JavaScript */
            exit; /* Termino la ejecución */
        }
    ?>

    <link rel="stylesheet" href="../recursos/css/estilos_notificaciones.css" type="text/css"> <!-- Estilos específicos de notificaciones -->

    <main> <!-- Contenedor principal -->
        <h1>Mis Notificaciones</h1> <!-- Título de la página -->
        <hr> <!-- Línea separadora decorativa -->

        <!-- Contenedor de notificaciones -->
        <div id="contenedor-notificaciones" class="contenedor-notificaciones"> <!-- Contenedor donde se cargarán las notificaciones -->
            <!-- Los botones globales y las notificaciones se cargarán aquí dinámicamente --> 
        </div> <!-- Fin del contenedor de notificaciones -->

    </main> <!-- Fin del contenedor principal -->

    <!-- Pie de página -->
    <?php require_once __DIR__ . "/../vistas/comunes/pie.php"; ?>  <!-- Incluyo el pie de página común -->

    <script src="../recursos/js/notificaciones.js" defer></script> <!-- Script de notificaciones -->