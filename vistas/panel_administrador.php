    <!-- Encabezado -->
    <?php include __DIR__ . '/comunes/encabezado.php'; ?> <!-- Incluyo el encabezado con menú y estilos -->

    <?php
    // Verificar sesión después del encabezado y redirigir con JavaScript si es necesario
    if(!isset($_SESSION['id_usuario']) && !isset($_SESSION['id_rol']) && $_SESSION['id_rol'] != 1) {
        echo '<script>window.location.href = "../publico/index.php";</script>'; /* Redirijo con JavaScript si no está logueado */
        exit; /* Termino la ejecución del script */
    }
    ?>

    <link rel="stylesheet" href="../recursos/css/estilos_panel_administrador.css" type="text/css"> <!-- Estilos específicos para la página de panel de administrador -->

    <main> <!-- Cuerpo del panel de administrador -->

        <div id="contenedor-panel-administrador"> <!-- Contenedor principal del panel de administrador (vacío inicialmente) -->
            <!-- El contenido se cargará dinámicamente mediante JavaScript -->
        </div> <!-- Fin del contenedor de opciones del panel de administrador -->

    </main>

    <!-- Script para mostrar mensajes de la sesión -->
    <script>
        // Esperar a que el DOM esté completamente cargado
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar mensaje de éxito si existe
            <?php if (isset($_SESSION['mensaje_exito'])) { ?> /* Si existe un mensaje de éxito */
                modal('modal-exito', '<h1><?php echo htmlspecialchars($_SESSION['mensaje_exito']); ?></h1>', false); /* Muestro el modal con el mensaje */
                <?php unset($_SESSION['mensaje_exito']); ?> /* Elimino el mensaje de la sesión */
            <?php } ?>

            // Mostrar mensaje de error si existe
            <?php if (isset($_SESSION['error_general'])) { ?> /* Si existe un mensaje de error */
                modal('modal-error', '<h1><?php echo htmlspecialchars($_SESSION['error_general']); ?></h1>', false); /* Muestro el modal con el mensaje de error */
                <?php unset($_SESSION['error_general']); ?> /* Elimino el mensaje de la sesión */
            <?php } ?>

            // Mostrar mensaje de advertencia si existe
            <?php if (isset($_SESSION['mensaje_advertencia'])) { ?> /* Si existe un mensaje de advertencia */
                modal('modal-advertencia', '<h2><?php echo htmlspecialchars($_SESSION['mensaje_advertencia']); ?></h2>', false); /* Muestro el modal con el mensaje de advertencia */
                <?php unset($_SESSION['mensaje_advertencia']); ?> /* Elimino el mensaje de la sesión */
            <?php } ?>
        });
    </script>

    <!-- Script para historial (siempre disponible para carga dinámica de pedidos) -->
    <script src="../recursos/js/historial.js" defer></script> <!-- Script específico para historial -->

    <!-- Pie de página -->
    <?php include __DIR__ . '/comunes/pie.php'; ?> <!-- Incluyo el pie de página con estilos -->