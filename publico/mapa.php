<!-- Encabezado -->
<?php include __DIR__ . '/../vistas/comunes/encabezado.php'; ?> <!-- Incluyo el encabezado común con el menú y estilos -->

<!-- Estilos específicos del mapa -->
<link rel="stylesheet" href="../recursos/css/estilos_mapa.css" type="text/css"> <!-- Cargo los estilos específicos del mapa interactivo -->

<main id="mapa-principal"> <!-- Contenedor principal del mapa interactivo -->

    <h2 data-translate="mapa_titulo">Mapa Interactivo - CLC GAMES</h2> <!-- Título principal del mapa -->
    <div class="mapa-contenedor"> <!-- Contenedor que engloba todo el mapa -->

        <!-- Líneas SVG -->
        <svg class="lineas" xmlns="http://www.w3.org/2000/svg"> <!-- Contenedor SVG para dibujar las líneas de conexión -->
            <!-- Conexiones de Inicio -->
            <line x1="50%" y1="3%" x2="18%" y2="35%" /> <!-- Línea desde Inicio hasta Carrito -->
            <line x1="50%" y1="3%" x2="32%" y2="35%" /> <!-- Línea desde Inicio hasta Biblioteca -->
            <line x1="50%" y1="3%" x2="46%" y2="35%" /> <!-- Línea desde Inicio hasta Favoritos -->
            <line x1="50%" y1="3%" x2="60%" y2="35%" /> <!-- Línea desde Inicio hasta Historial -->
            <line x1="50%" y1="3%" x2="74%" y2="35%" /> <!-- Línea desde Inicio hasta Mapa -->
            <line x1="50%" y1="3%" x2="88%" y2="35%" /> <!-- Línea desde Inicio hasta Iniciar sesión -->

            <!-- Conexiones de Biblioteca -->
            <line x1="32%" y1="35%" x2="20%" y2="65%" /> <!-- Línea desde Biblioteca hasta Juegos externos -->
            <line x1="32%" y1="35%" x2="40%" y2="65%" /> <!-- Línea desde Biblioteca hasta Juegos internos -->

            <!-- Conexiones de Iniciar sesión -->
            <line x1="88%" y1="35%" x2="88%" y2="65%" /> <!-- Línea desde Iniciar sesión hasta Crear cuenta -->

            <!-- Conexiones de Juegos internos -->
            <line x1="40%" y1="65%" x2="15%" y2="92%" /> <!-- Línea desde Juegos internos hasta Pong -->
            <line x1="40%" y1="65%" x2="28%" y2="92%" /> <!-- Línea desde Juegos internos hasta Tres en Raya -->
            <line x1="40%" y1="65%" x2="40%" y2="92%" /> <!-- Línea desde Juegos internos hasta Solitario -->
            <line x1="40%" y1="65%" x2="52%" y2="92%" /> <!-- Línea desde Juegos internos hasta Buscaminas -->
            <line x1="40%" y1="65%" x2="65%" y2="92%" /> <!-- Línea desde Juegos internos hasta Ahorcado -->
        </svg>

        <!-- Nodo principal - INICIO (muy arriba) -->
        <div class="punto-interes nodo-principal" style="top: 3%; left: 50%;"> <!-- Nodo principal centrado en la parte superior -->
            <a href="index.php" title="Inicio" data-translate="mapa_inicio">Inicio</a> <!-- Enlace al índice principal -->
        </div>

        <!-- Primera capa de nodos - Separación máxima -->
        <div class="punto-interes nodo-primario" style="top: 35%; left: 18%;"> <!-- Nodo primario para el carrito -->
            <a href="<?php if(isset($_SESSION['id_usuario'])) { echo 'carrito.php'; } ?>" title="Carrito" data-translate="mapa_carrito">Carrito</a> <!-- Enlace al carrito solo si está logueado -->
        </div>
        <div class="punto-interes nodo-primario" style="top: 35%; left: 32%;"> <!-- Nodo primario para la biblioteca -->
            <a href="<?php if(isset($_SESSION['id_usuario'])) { echo 'biblioteca.php'; } ?>" title="Biblioteca" data-translate="mapa_biblioteca">Biblioteca</a> <!-- Enlace a la biblioteca solo si está logueado -->
        </div>
        <div class="punto-interes nodo-primario" style="top: 35%; left: 46%;"> <!-- Nodo primario para favoritos -->
            <a href="<?php if(isset($_SESSION['id_usuario'])) { echo 'favoritos.php'; } ?>" title="Favoritos" data-translate="mapa_favoritos">Favoritos</a> <!-- Enlace a favoritos solo si está logueado -->
        </div>
        <div class="punto-interes nodo-primario" style="top: 35%; left: 60%;"> <!-- Nodo primario para el historial -->
            <a href="<?php if(isset($_SESSION['id_usuario'])) { echo 'historial.php'; } ?>" title="Historial" data-translate="mapa_historial">Historial</a> <!-- Enlace al historial solo si está logueado -->
        </div>
        <div class="punto-interes nodo-primario" style="top: 35%; left: 74%;"> <!-- Nodo primario para el mapa actual -->
            <a href="mapa.php" title="Mapa" data-translate="mapa_mapa">Mapa</a> <!-- Enlace al mapa actual (autoreferencia) -->
        </div>
        <div class="punto-interes nodo-primario" style="top: 35%; left: 88%;"> <!-- Nodo primario para iniciar sesión -->
            <a <?php if(!isset($_SESSION['id_usuario'])) { echo 'href="../sesiones/formulario_autenticacion.php"'; } else { echo 'href="#"'; } ?> title="Iniciar sesión" data-translate="mapa_login">Iniciar sesión</a> <!-- Enlace al formulario de login -->
        </div>

        <!-- Segunda capa de nodos - Separación amplia -->
        <!-- Nodos que extienden de Biblioteca -->
        <div class="punto-interes nodo-secundario" style="top: 65%; left: 20%;"> <!-- Nodo secundario para juegos externos -->
            <a href="<?php if(isset($_SESSION['id_usuario'])) { echo 'biblioteca.php'; } ?>" title="Juegos externos" data-translate="mapa_juegos_externos">Juegos externos</a> <!-- Enlace a juegos externos en la biblioteca -->
        </div>                
        <div class="punto-interes nodo-secundario" style="top: 65%; left: 40%;"> <!-- Nodo secundario para juegos internos -->
            <a href="<?php if(isset($_SESSION['id_usuario'])) { echo 'biblioteca.php'; } ?>" title="Juegos internos" data-translate="mapa_juegos_internos">Juegos internos</a> <!-- Enlace a juegos internos en la biblioteca -->
        </div>

        <!-- Nodos que extienden de Iniciar sesión -->
        <div class="punto-interes nodo-secundario" style="top: 65%; left: 88%;"> <!-- Nodo secundario para crear cuenta -->
            <a <?php if(!isset($_SESSION['id_usuario'])) { echo 'href="../sesiones/registro.php"'; } else { echo 'href="#"'; } ?> title="Crear cuenta" data-translate="mapa_registro">Crear cuenta</a> <!-- Enlace al formulario de registro -->
        </div>

        <!-- Tercera capa de nodos - Juegos específicos hasta abajo -->
        <div class="punto-interes nodo-terciario" style="top: 92%; left: 15%;"> <!-- Nodo terciario para el juego Pong -->
            <a href="juegos/pong.php" title="Pong" data-translate="mapa_pong">Pong</a> <!-- Enlace al juego Pong -->
        </div>
        <div class="punto-interes nodo-terciario" style="top: 92%; left: 28%;"> <!-- Nodo terciario para Tres en Raya -->
            <a href="juegos/tres_en_raya.php" title="Tres en Raya" data-translate="mapa_tres_raya">Tres en Raya</a> <!-- Enlace al juego Tres en Raya -->
        </div>
        <div class="punto-interes nodo-terciario" style="top: 92%; left: 40%;"> <!-- Nodo terciario para Solitario -->
            <a href="juegos/solitario.php" title="Solitario" data-translate="mapa_solitario">Solitario</a> <!-- Enlace al juego Solitario -->
        </div>
        <div class="punto-interes nodo-terciario" style="top: 92%; left: 52%;"> <!-- Nodo terciario para Buscaminas -->
            <a href="juegos/buscaminas.php" title="Buscaminas" data-translate="mapa_buscaminas">Buscaminas</a> <!-- Enlace al juego Buscaminas -->
        </div>
        <div class="punto-interes nodo-terciario" style="top: 92%; left: 65%;"> <!-- Nodo terciario para Ahorcado -->
            <a href="juegos/ahorcado.php" title="Ahorcado" data-translate="mapa_ahorcado">Ahorcado</a> <!-- Enlace al juego Ahorcado -->
        </div>

    </div> <!-- Fin del contenedor del mapa -->
</main> <!-- Fin del contenedor principal -->
        
<!-- Pie de página -->
<?php include __DIR__ . '/../vistas/comunes/pie.php'; ?> <!-- Incluyo el pie de página común -->
