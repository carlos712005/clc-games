<?php

    session_start(); /* Inicio la sesión para poder acceder a las variables de sesión */

    $_SESSION = []; /* Limpio completamente todas las variables de sesión */

    session_destroy(); /* Destruyo la sesión del servidor para cerrarla definitivamente */

    // Crear una nueva sesión con modo_admin en false
    session_start(); /* Inicio una nueva sesión */
    $_SESSION['modo_admin'] = false; /* Indico que no está en modo administrador */

    header('Location: ../publico/index.php'); /* Redirijo al usuario al index después de cerrar sesión */
    exit; /* Termino la ejecución del script para evitar que continúe */

?>