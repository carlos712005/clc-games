<?php

    session_start(); // Inicio la sesión para acceder a las variables de usuario
    
    // Verificar que el usuario esté logueado y sea administrador
    if(!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
        $_SESSION['error_general'] = 'No tienes permisos para acceder a esta página.'; // Mensaje de error
        header('Location: ../publico/index.php'); // Redirijo al índice público
        exit; // Termino la ejecución
    }
    
    // Inicializar la variable de si es modo administrador si no existe
    if(!isset($_SESSION['modo_admin'])) {
        $_SESSION['modo_admin'] = false; // Indico que no estamos en modo administrador por defecto
    }

    // Inicializar la variable que indica si se estaba en modo administrador
    if(!isset($_SESSION['estaba_administrando'])) {
        $_SESSION['estaba_administrando'] = false; // Inicializo si no existe y indico que no estaba en modo administrador
    }

    // Verificar si el modo seleccionado es usuario
    if(isset($_GET['modo']) && $_GET['modo'] === 'usuario') {
        // Verificar si se estaba en modo administrador
        if($_SESSION['estaba_administrando'] == true) {
            intercambiarValores(); // Intercambio los valores de los filtros
            $_SESSION['modo_admin'] = false; // Indico que no estamos en modo administrador
        }
        header('Location: ../publico/index.php'); // Redirijo al índice
        exit; // Termino la ejecución

    //Verificar si el modo seleccionado es administrador
    } else if(isset($_GET['modo']) && $_GET['modo'] === 'administrador') {
        // Verificar si no se estaba en modo administrador
        if($_SESSION['estaba_administrando'] === false) {
            // Inicializar los filtros del panel administrador si no existen
            if(!isset($_SESSION['filtros_administrador'])) {
                // Si no existen filtros de administrador, los creo con valores por defecto
                $_SESSION['filtros_administrador'] = [
                    'tipo' => 0, /* Sin filtro de tipo por defecto */
                    'genero' => 0, /* Sin filtro de género por defecto */
                    'categoria' => 0, /* Sin filtro de categoría por defecto */
                    'modo' => 0, /* Sin filtro de modo por defecto */
                    'pegi' => 0, /* Sin filtro de PEGI por defecto */
                    'precio_min' => 0, /* Sin filtro de precio mínimo por defecto */
                    'precio_max' => 100 /* Sin filtro de precio máximo por defecto */
                ]; /* Creo un array con todos los filtros elegidos con valores por defecto */
            }
            intercambiarValores(); // Intercambio los valores de los filtros
            $_SESSION['modo_admin'] = true; // Indico que estamos en modo administrador
        }
        header('Location: ../vistas/panel_administrador.php'); // Redirijo al panel de administrador
        exit; // Termino la ejecución
        
    // Verificar si el modo seleccionado es salir
    } else if(isset($_GET['modo']) && $_GET['modo'] === 'salir') {
        // Verificar si se ha recibido el parámetro administrando
        if(isset($_GET['administrando'])) {
            // Si estaba en modo administrador
            if($_GET['administrando'] == 1) {
                $_SESSION['estaba_administrando'] = true; // Indico que estaba en modo administrador
            // Si no estaba en modo administrador
            } else if($_GET['administrando'] == 0) {
                $_SESSION['estaba_administrando'] = false; // Indico que no estaba en modo administrador
            }
            $_SESSION['modo_admin'] = false; // Indico que no estamos en modo administrador
            $_SESSION['modo_edicion'] = null; // Restablezco el modo de edición
        }
        header('Location: ../vistas/modo_vision.php'); // Redirijo al modo de visión
        exit; // Termino la ejecución
    }

    // Función para intercambiar los valores de los filtros entre modo usuario y  modo administrador
    function intercambiarValores() {
        $_SESSION['filtros_temporales'] = [
            'tipo' => $_SESSION['filtros_elegidos']['tipo'], /* Guardo el tipo elegido */
            'genero' => $_SESSION['filtros_elegidos']['genero'], /* Guardo el género elegido */
            'categoria' => $_SESSION['filtros_elegidos']['categoria'], /* Guardo la categoría elegida */
            'modo' => $_SESSION['filtros_elegidos']['modo'], /* Guardo el modo elegido */
            'pegi' => $_SESSION['filtros_elegidos']['pegi'], /* Guardo la clasificación PEGI elegida */
            'precio_min' => $_SESSION['filtros_elegidos']['precio_min'], /* Guardo el precio mínimo */
            'precio_max' => $_SESSION['filtros_elegidos']['precio_max'] /* Guardo el precio máximo */
        ]; /* Creo un array de filtros temporales con todos los filtros elegidos */

        $_SESSION['filtros_elegidos'] = [
            'tipo' => $_SESSION['filtros_administrador']['tipo'], /* Guardo el tipo elegido */
            'genero' => $_SESSION['filtros_administrador']['genero'], /* Guardo el género elegido */
            'categoria' => $_SESSION['filtros_administrador']['categoria'], /* Guardo la categoría elegida */
            'modo' => $_SESSION['filtros_administrador']['modo'], /* Guardo el modo elegido */
            'pegi' => $_SESSION['filtros_administrador']['pegi'], /* Guardo la clasificación PEGI elegida */
            'precio_min' => $_SESSION['filtros_administrador']['precio_min'], /* Guardo el precio mínimo */
            'precio_max' => $_SESSION['filtros_administrador']['precio_max'] /* Guardo el precio máximo */
        ]; /* Actualizo el array de filtros elegidos con todos los filtros de administración */

        $_SESSION['filtros_administrador'] = [
            'tipo' => $_SESSION['filtros_temporales']['tipo'], /* Guardo el tipo elegido */
            'genero' => $_SESSION['filtros_temporales']['genero'], /* Guardo el género elegido */
            'categoria' => $_SESSION['filtros_temporales']['categoria'], /* Guardo la categoría elegida */
            'modo' => $_SESSION['filtros_temporales']['modo'], /* Guardo el modo elegido */
            'pegi' => $_SESSION['filtros_temporales']['pegi'], /* Guardo la clasificación PEGI elegida */
            'precio_min' => $_SESSION['filtros_temporales']['precio_min'], /* Guardo el precio mínimo */
            'precio_max' => $_SESSION['filtros_temporales']['precio_max'] /* Guardo el precio máximo */
        ]; /* Actualizo el array de filtros de administración con todos los filtros elegidos guardados en filtros temporales */
    }

?>
