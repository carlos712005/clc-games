<?php

	session_start(); /* Inicio la sesión */

	// Verificar que la solicitud sea GET
	if($_SERVER['REQUEST_METHOD'] !== 'GET') {
		echo 'Método no permitido'; /* Devuelvo mensaje de método no permitido */
		exit; /* Termino la ejecución */
	}

	// Verificar que se haya proporcionado el parámetro 'ir_a'
	if(!isset($_GET['ir_a'])) {
		echo 'Página no encontrada'; /* Devuelvo mensaje de página no encontrada */
		exit; /* Termino la ejecución */
	}

	$ir_a = $_GET['ir_a']; /* Obtengo el destino */
	
	// Limpiar búsqueda si existen los datos
	if( isset($_SESSION['texto_busqueda']) || isset($_SESSION['datos_busqueda']) ) {
		unset($_SESSION['texto_busqueda']); /* Elimino el texto de búsqueda */
		unset($_SESSION['datos_busqueda']); /* Elimino los datos de búsqueda */
	}
	
	header('Location: ' . $ir_a); /* Redirijo al destino recibido */
	exit; /* Termino la ejecución */
	
?>
