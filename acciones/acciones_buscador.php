<?php

	session_start(); /* Inicio la sesión para poder acceder a las variables de sesión */
	require_once __DIR__ . '/../config/conexion.php'; /* Incluyo la conexión a la base de datos */

	// Verificar que la solicitud sea POST
	if($_SERVER['REQUEST_METHOD'] !== 'POST') {
		echo 'Método no permitido'; /* Devuelvo mensaje de método no permitido */
		exit; /* Termino la ejecución */
	}

	// Función para normalizar texto (minúsculas, sin acentos, sin ñ, sin espacios repetidos)
	function normalizarTexto($texto){
		// Primero paso a minúsculas
		$texto = strtolower($texto);

		// Reemplazo caracteres con acento por su versión sin acento
		$buscar  = array('á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ');
		$reemplazar = array('a', 'e', 'i', 'o', 'u', 'u', 'n');

		$texto = str_replace($buscar, $reemplazar, $texto); /* Reemplazo caracteres */

		// Quito espacios repetidos al principio, final y en medio
		$texto = trim($texto);

		// Reemplazo varios espacios seguidos por uno solo
		while (strpos($texto, "  ") !== false) {
			$texto = str_replace("  ", " ", $texto); /* Reemplazo doble espacio por uno */
		}

		return $texto; /* Devuelvo el texto normalizado */
	}

	// Función para comprobar si todas las palabras de la búsqueda están en el campo dado
	function contienePalabras($palabraBuscada, $datoCampo) {
		// Normalizo ambos textos (minusculas, sin acentos, sin ñ, espacios limpios)
		$busquedaNormalizada = normalizarTexto($palabraBuscada);
		$campoNormalizado    = normalizarTexto($datoCampo);

		// Si la búsqueda está vacía después de normalizar, digo que "coincide"
		if ($busquedaNormalizada === "") {
			return true;
		}

		// Separo la búsqueda en palabras individuales
		// Ejemplo: "mundo abierto cooperativo" -> ['mundo', 'abierto', 'cooperativo']
		$palabras = explode(" ", $busquedaNormalizada);

		// Recorro cada palabra y compruebo si aparece en el campo
		foreach ($palabras as $palabra) {

			// Si por algún motivo viene una palabra vacía, la salto
			if ($palabra === "") {
				continue;
			}

			// strpos devuelve la posición de la palabra dentro del texto o false si no la encuentra
			// Uso === false para distinguir entre "no encontrado" y "posición 0"
			if (strpos($campoNormalizado, $palabra) === false) {
				// En cuanto una palabra no se encuentra, devuelvo false
				return false;
			}
		}

		// Si todas las palabras se han encontrado, devuelvo true
		return true;
	}

	// Verificar que se haya proporcionado la acción
	if(isset($_POST['accion']) && $_POST['accion'] !== '') {

		$pagina_actual = isset($_POST['pagina_actual']) ? $_POST['pagina_actual'] : ''; /* Obtengo la página actual */
		$modo_edicion = isset($_POST['modo_edicion']) ? $_POST['modo_edicion'] : (isset($_SESSION['modo_edicion']) ? $_SESSION['modo_edicion'] : 'juegos'); /* Obtengo el modo de edición del POST o de la sesión */
		
		// Determinar el ID del usuario según el modo administrador
		if(isset($_SESSION['modo_admin']) && $_SESSION['modo_admin']) {
			$id_usuario = isset($_SESSION['id_usuario_buscado']) ? (int)$_SESSION['id_usuario_buscado'] : $_SESSION['id_usuario']; /* Obtengo el ID del usuario buscado o el del administrador */
		} else { /* Si no es modo admin, uso el ID del usuario logueado */
			$id_usuario = $_SESSION['id_usuario']; /* Obtengo el ID del usuario logueado */
		}

		$texto = isset($_POST['texto_busqueda']) ? trim($_POST['texto_busqueda']) : ''; /* Obtengo el texto de búsqueda enviado por POST */

		// Si el texto de búsqueda está vacío, limpio la búsqueda guardada y salgo
		if($texto === '') {
			unset($_SESSION['texto_busqueda']); /* Limpio el texto de búsqueda guardado */
			unset($_SESSION['datos_busqueda']); /* Limpio datos guardados */
			echo 'VACIO'; /* Indico que el texto está vacío */
			exit; /* Termino la ejecución */
		}

		$_SESSION['texto_busqueda'] = $texto; /* Guardo el texto de búsqueda en sesión */
		$_SESSION['datos_busqueda'] = []; /* Guardo datos de búsqueda como un array vacío por defecto */
		$_SESSION['datos_busqueda']['juegos_encontrados'] = []; /* Inicializo array de juegos encontrados */
		$_SESSION['datos_busqueda']['usuarios_encontrados'] = []; /* Inicializo array de usuarios encontrados */
		$_SESSION['datos_busqueda']['historiales_encontrados'] = []; /* Inicializo array de historiales encontrados */

		$es_filtro = false; /* Variable para indicar si se encontró un filtro */
		$sugerencias = []; /* Array para guardar sugerencias de autocompletado */

		try { /* Inicio bloque try para capturar errores */
			
			$agregados = []; /* Array para evitar sugerencias duplicadas */
			
			// Buscar en juegos
			if($pagina_actual === 'index.php' || $pagina_actual === 'reservables.php'
				|| $pagina_actual === 'favoritos.php' || $pagina_actual === 'biblioteca.php'
				|| ($pagina_actual === 'panel_administrador.php' && isset($_SESSION['modo_edicion']) && $_SESSION['modo_edicion'] === 'juegos')) {
			
				if($pagina_actual === 'favoritos.php') { /* Si estamos en favoritos */
					// Obtener los juegos favoritos completos con toda su información
					$consulta = $conexion->prepare("
						SELECT j.id, j.nombre, j.slug, j.desarrollador, j.distribuidor, j.tipo, j.resumen, j.descripcion, j.requisitos 
						FROM favoritos f 
						INNER JOIN juegos j ON f.id_juego = j.id 
						WHERE f.id_usuario = :id_usuario AND j.activo = 1
					"); /* Preparo la consulta para obtener los datos completos de los juegos favoritos que están activos */
					$consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
					$consulta->execute(); /* Ejecuto la consulta */
				} else if($pagina_actual === 'biblioteca.php') { /* Si estamos en biblioteca */
					// Obtener los juegos de la biblioteca completos con toda su información
					$consulta = $conexion->prepare("
						SELECT j.id, j.nombre, j.slug, j.desarrollador, j.distribuidor, j.tipo, j.resumen, j.descripcion, j.requisitos 
						FROM biblioteca b 
						INNER JOIN juegos j ON b.id_juego = j.id 
						WHERE b.id_usuario = :id_usuario
					"); /* Preparo la consulta para obtener los datos completos de los juegos de la biblioteca */
					$consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el parámetro del ID del usuario */
					$consulta->execute(); /* Ejecuto la consulta */
				} else if($pagina_actual === 'reservables.php') { /* Si estamos en reservables */
					// Obtener los juegos reservables completos con toda su información
					$consulta = $conexion->prepare("
						SELECT j.id, j.nombre, j.slug, j.desarrollador, j.distribuidor, j.tipo, j.resumen, j.descripcion, j.requisitos 
						FROM juegos j
						WHERE j.activo = 1 AND j.fecha_lanzamiento > NOW()
					"); /* Preparo la consulta para obtener los datos completos de los juegos reservables */
					$consulta->execute(); /* Ejecuto la consulta */
				} else if($pagina_actual === 'panel_administrador.php' && isset($_SESSION['modo_edicion']) && $_SESSION['modo_edicion'] === 'juegos') { /* Si estamos en panel administrador en modo edición de juegos */
					$consulta = $conexion->query("SELECT id, nombre, slug, desarrollador, distribuidor, tipo, resumen, descripcion, requisitos FROM juegos"); /* Preparo consulta para obtener todos los juegos */				
				} else { /* Si estamos en index.php o panel administrador en modo edición de juegos */
					$consulta = $conexion->query("SELECT id, nombre, slug, desarrollador, distribuidor, tipo, resumen, descripcion, requisitos FROM juegos WHERE activo = 1"); /* Preparo consulta para obtener todos los juegos activos */
				}

				$juegos = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los juegos */
				if(isset($_SESSION['modo_admin']) && $_SESSION['modo_admin'] === true) { /* Si es modo admin */
					$columnas = ['nombre','slug','desarrollador','distribuidor','tipo','resumen','descripcion','requisitos']; /* Columnas a buscar */
				} else { /* Si no es modo admin */
					$columnas = ['nombre','desarrollador','distribuidor','resumen','descripcion','requisitos']; /* Columnas a buscar */
				}

				// Recorro los juegos buscando el texto
				foreach($juegos as $juego) {
					foreach($columnas as $col) { /* Recorro las columnas */
						if($_POST['accion'] === 'realizar_busqueda') { /* Si es para realizar búsqueda */
							if(isset($juego[$col]) && $juego[$col] !== '' && contienePalabras($texto, $juego[$col])) { /* Si la columna contiene el texto */
								$_SESSION['datos_busqueda']['juegos_encontrados'][] = (int)$juego['id']; /* Guardo el ID del juego encontrado */
								break; /* Salgo del bucle de columnas para pasar al siguiente juego */
							}
						} else if($_POST['accion'] === 'coincidencias') { /* Si es para autocompletar */
							if(isset($juego[$col]) && contienePalabras($texto, $juego[$col]) && !in_array($juego[$col], $agregados)) { /* Si la columna contiene el texto y no está ya agregado */
								$sugerencias[] = ['texto' => $juego[$col], 'tipo' => 'juego']; /* Agrego la sugerencia */
								$agregados[] = $juego[$col]; /* Marco como agregado */
								if(count($sugerencias) >= 5) break; /* Si ya hay 5 sugerencias, salgo */
							}
						}
					}
				}

				// Buscar en filtros solo si se está realizando la búsqueda o si hay menos de 8 sugerencias
				if($_POST['accion'] === 'realizar_busqueda' || count($sugerencias) < 8) {
					$consulta2 = $conexion->query("SELECT id_fijo, nombre, tipo_filtro, clave FROM filtros"); /* Preparo consulta para obtener filtros */
					$filtros = $consulta2->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los filtros */
					if(isset($_SESSION['modo_admin']) && $_SESSION['modo_admin'] === true) { /* Si es modo admin */
						$colsFiltros = ['nombre','clave']; /* Columnas a buscar en filtros */
					} else { /* Si no es modo admin */
						$colsFiltros = ['nombre']; /* Columnas a buscar en filtros */
					}

					$filtro_encontrado = false; /* Variable para controlar si se encontró un filtro */
					// Recorro los filtros buscando el texto
					foreach($filtros as $filtro) {
						foreach($colsFiltros as $colFiltro) { /* Recorro las columnas de filtros */
							if($_POST['accion'] === 'realizar_busqueda') { /* Si es para realizar búsqueda */
								if(isset($filtro[$colFiltro]) && $filtro[$colFiltro] !== '' && contienePalabras($texto, $filtro[$colFiltro])) { /* Si la columna contiene el texto */
									$es_filtro = true; /* Marco que es un filtro */
									
									// Si ya hay juegos encontrados, preservarlos
									$juegos_previos = isset($_SESSION['datos_busqueda']['juegos_encontrados']) ? $_SESSION['datos_busqueda']['juegos_encontrados'] : [];
									
									$_SESSION['datos_busqueda'] = [
										'columna' => $colFiltro,
										'id_filtro' => (int)$filtro['id_fijo'],
										'tipo_filtro' => $filtro['tipo_filtro'],
										'juegos_encontrados' => $juegos_previos /* Preservo los juegos encontrados */
									]; /* Guardo los datos del filtro encontrado */
									$filtro_encontrado = true; /* Marco que se encontró un filtro */
									break; /* Salgo del bucle de columnas para pasar al siguiente filtro */
								}
							} else if($_POST['accion'] === 'coincidencias') { /* Si es para autocompletar */
								if(isset($filtro[$colFiltro]) && contienePalabras($texto, $filtro[$colFiltro]) && !in_array($filtro[$colFiltro], $agregados)) { /* Si la columna contiene el texto y no está ya agregado */
									$sugerencias[] = ['texto' => $filtro[$colFiltro], 'tipo' => 'filtro']; /* Agrego la sugerencia */
									$agregados[] = $filtro[$colFiltro]; /* Marco como agregado */
									if(count($sugerencias) >= 8) break; /* Si ya hay 8 sugerencias, salgo */
								}
							}
						}
						if($filtro_encontrado) break; /* Salgo del bucle de filtros si se encontró */
					}
				}

				// Si es búsqueda real y no se encontraron juegos, asigno [-1] para evitar que se muestren todos
				if($_POST['accion'] === 'realizar_busqueda' && empty($_SESSION['datos_busqueda']['juegos_encontrados'])) {
					$_SESSION['datos_busqueda']['juegos_encontrados'] = [-1]; /* Asigno -1 para que no devuelva resultados */
				}

			// Buscar en historial si el usuario está logueado y la página actual es historial.php
			} else if(isset($_SESSION['id_usuario']) && ($pagina_actual === 'historial.php' || ($pagina_actual === 'panel_administrador.php' && $modo_edicion === 'pedidos'))) {
				$historiales = []; /* Inicializo el array de historiales como vacío por defecto */
				$columnas_historial = []; /* Inicializo el array de columnas como vacío por defecto */
				
				if($pagina_actual === 'historial.php') { /* Si estamos en historial.php */
					$consulta_historial = $conexion->prepare("
						SELECT h.id AS id_historial, h.tipo, h.estado, h.total, h.metodo_pago, h.comentario, h.creado_en,
							hc.id AS id_detalle, hc.id_juego, hc.precio, hc.estado AS estado_detalle, hc.comentario AS comentario_detalle,
							j.nombre AS nombre_juego
						FROM historial h
						LEFT JOIN historial_compras hc ON hc.id_historial = h.id
						LEFT JOIN juegos j ON hc.id_juego = j.id
						WHERE h.id_usuario = :id_usuario
						ORDER BY h.creado_en DESC, hc.id ASC
					"); /* Preparo consulta para obtener el historial del usuario con datos de juegos */
					$consulta_historial->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT); /* Vinculo el ID del usuario */
					$consulta_historial->execute(); /* Ejecuto la consulta */
					$historiales = $consulta_historial->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los registros del historial */

					$columnas_historial = ['tipo', 'estado', 'metodo_pago', 'comentario', 'estado_detalle', 'comentario_detalle', 'nombre_juego']; /* Columnas a buscar en historial */
				} else if($pagina_actual === 'panel_administrador.php' && $modo_edicion === 'pedidos') { /* Si estamos en panel administrador en modo edición de pedidos */
					$consulta_historial = $conexion->prepare("
						SELECT 
							h.id AS id_historial,
							h.id_usuario,
							h.tipo,
							h.estado,
							h.total,
							h.metodo_pago,
							h.comentario,
							h.creado_en,
							hc.id AS id_detalle,
							hc.id_juego,
							hc.precio,
							hc.estado AS estado_detalle,
							hc.comentario AS comentario_detalle,
							u.nombre AS usuario_nombre,
							u.apellidos AS usuario_apellidos,
							u.acronimo AS usuario_acronimo,
							j.nombre AS nombre_juego
						FROM historial h
						LEFT JOIN historial_compras hc ON hc.id_historial = h.id
						LEFT JOIN usuarios u ON u.id = h.id_usuario
						LEFT JOIN juegos j ON hc.id_juego = j.id
						ORDER BY h.creado_en DESC, hc.id ASC
						"); /* Preparo consulta para obtener el historial de compras general, incluyendo datos de usuario */
					$consulta_historial->execute(); /* Ejecuto la consulta */
					$historiales = $consulta_historial->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los registros del historial */

					$columnas_historial = ['tipo', 'estado', 'metodo_pago', 'comentario', 'estado_detalle', 'comentario_detalle', 'usuario_nombre', 'usuario_apellidos', 'usuario_acronimo', 'nombre_juego']; /* Columnas a buscar en historial */
				
					$filtro_tipo_detectado = 'null'; /* Inicializo filtro de tipo como 'null' */
					$filtro_estado_detectado = 'null'; /* Inicializo filtro de estado como 'null' */
					$filtros_estado_detalle_detectado = 'null'; /* Inicializo filtro de estado detalle como 'null' */
                    $filtros_acronimo_detectado = 'null'; /* Inicializo filtro de acrónimo como 'null' */
                    $filtros_nombre_detectado = 'null'; /* Inicializo filtro de nombre como 'null' */
                    $filtros_apellidos_detectado = 'null'; /* Inicializo filtro de apellidos como 'null' */
                    $filtros_metodo_pago_detectado = 'null'; /* Inicializo filtro de método de pago como 'null' */
				}

				// Recorro el historial buscando el texto
				foreach($historiales as $historial) {
					foreach($columnas_historial as $col_hist) { /* Recorro las columnas del historial */
						if($_POST['accion'] === 'realizar_busqueda') { /* Si es para realizar búsqueda */
							if(isset($historial[$col_hist]) && $historial[$col_hist] !== '' && contienePalabras($texto, $historial[$col_hist])) { /* Si la columna contiene el texto */
								if(!isset($_SESSION['datos_busqueda']['historiales_encontrados'])) { /* Si no está inicializado el array de historiales encontrados */
									$_SESSION['datos_busqueda']['historiales_encontrados'] = []; /* Inicializo el array de historiales encontrados */
								}
								if(!in_array((int)$historial['id_historial'], $_SESSION['datos_busqueda']['historiales_encontrados'])) { /* Si no está ya agregado */
									$_SESSION['datos_busqueda']['historiales_encontrados'][] = (int)$historial['id_historial']; /* Guardo el ID del historial encontrado */
								}

								if($pagina_actual === 'panel_administrador.php' && $modo_edicion === 'pedidos') { /* Si estamos en panel administrador en modo edición de pedidos */
									// Comprobar si es coincidencia exacta (normalizada) para activar filtro
									$texto_normalizado = normalizarTexto($texto); /* Normalizo el texto de búsqueda */
									$valor_normalizado = normalizarTexto($historial[$col_hist]); /* Normalizo el valor de la columna */
									
									if($texto_normalizado === $valor_normalizado) { /* Si hay coincidencia exacta */
										// Asignar el valor original (no normalizado) al filtro correspondiente
										switch($col_hist) {
											case 'tipo': /* Si la columna es tipo */
												$filtro_tipo_detectado = $historial['tipo']; /* Guardo el tipo */
												break;
											case 'estado': /* Si la columna es estado */
												$filtro_estado_detectado = $historial['estado']; /* Guardo el estado */
												break;
											case 'estado_detalle': /* Si la columna es estado_detalle */
												$filtros_estado_detalle_detectado = $historial['estado_detalle']; /* Guardo el estado detalle */
												break;
											case 'usuario_acronimo': /* Si la columna es usuario_acronimo */
												$filtros_acronimo_detectado = $historial['usuario_acronimo']; /* Guardo el acrónimo */
												break;
											case 'usuario_nombre': /* Si la columna es usuario_nombre */
												$filtros_nombre_detectado = $historial['usuario_nombre']; /* Guardo el nombre */
												break;
											case 'usuario_apellidos': /* Si la columna es usuario_apellidos */
												$filtros_apellidos_detectado = $historial['usuario_apellidos']; /* Guardo los apellidos */
												break;
											case 'metodo_pago': /* Si la columna es metodo_pago */
												$filtros_metodo_pago_detectado = $historial['metodo_pago']; /* Guardo el método de pago */
												break;
										}
									}
								}

								break; /* Salgo del bucle de columnas para pasar al siguiente historial */
							}
						} else if($_POST['accion'] === 'coincidencias') { /* Si es para autocompletar */
							if(isset($historial[$col_hist]) && $historial[$col_hist] !== '' && contienePalabras($texto, $historial[$col_hist]) && !in_array($historial[$col_hist], $agregados)) { /* Si la columna contiene el texto y no está ya agregado */
								$sugerencias[] = ['texto' => $historial[$col_hist], 'tipo' => 'historial']; /* Agrego la sugerencia */
								$agregados[] = $historial[$col_hist]; /* Marco como agregado */
								if(count($sugerencias) >= 8) break; /* Si ya hay 8 sugerencias, salgo */
							}
						}
					}
					if($_POST['accion'] === 'coincidencias' && count($sugerencias) >= 8) break; /* Si ya hay 8 sugerencias, salgo */
				}

			// Si se encontró algún filtro, lo guardo en sesión (solo para panel_administrador)
			if($_POST['accion'] === 'realizar_busqueda' && $pagina_actual === 'panel_administrador.php' && $modo_edicion === 'pedidos' && (
					$filtro_tipo_detectado !== 'null' || 
					$filtro_estado_detectado !== 'null' ||
					$filtros_estado_detalle_detectado !== 'null' ||
					$filtros_acronimo_detectado !== 'null' ||
					$filtros_nombre_detectado !== 'null' ||
					$filtros_apellidos_detectado !== 'null' ||
					$filtros_metodo_pago_detectado !== 'null'
				)) {
				// Preservar filtros existentes si los hay, completando claves faltantes de forma explícita
				$filtros_actuales = isset($_SESSION['filtros_pedidos']) ? $_SESSION['filtros_pedidos'] : []; /* Obtengo los filtros actuales o un array vacío */
				$filtros_actuales['tipo'] = $filtros_actuales['tipo'] ?? 'null'; /* Completo tipo si falta */
				$filtros_actuales['estado'] = $filtros_actuales['estado'] ?? 'null'; /* Completo estado si falta */
				$filtros_actuales['estado_detalle'] = $filtros_actuales['estado_detalle'] ?? 'null'; /* Completo estado detalle si falta */
				$filtros_actuales['acronimo'] = $filtros_actuales['acronimo'] ?? 'null'; /* Completo acrónimo si falta */
				$filtros_actuales['nombre'] = $filtros_actuales['nombre'] ?? 'null'; /* Completo nombre si falta */
				$filtros_actuales['apellidos'] = $filtros_actuales['apellidos'] ?? 'null'; /* Completo apellidos si falta */
				$filtros_actuales['metodo_pago'] = $filtros_actuales['metodo_pago'] ?? 'null'; /* Completo método de pago si falta */
				$filtros_actuales['total_min'] = $filtros_actuales['total_min'] ?? null; /* Completo total mínimo si falta */
				$filtros_actuales['total_max'] = $filtros_actuales['total_max'] ?? null; /* Completo total máximo si falta */
				$filtros_actuales['creado_desde'] = $filtros_actuales['creado_desde'] ?? null; /* Completo creado desde si falta */
				$filtros_actuales['creado_hasta'] = $filtros_actuales['creado_hasta'] ?? null; /* Completo creado hasta si falta */
				$filtros_actuales['actualizado_desde'] = $filtros_actuales['actualizado_desde'] ?? null; /* Completo actualizado desde si falta */
				$filtros_actuales['actualizado_hasta'] = $filtros_actuales['actualizado_hasta'] ?? null; /* Completo actualizado hasta si falta */

					// Actualizar solo los filtros que se detectaron
					$_SESSION['filtros_pedidos'] = [
						'tipo' => $filtro_tipo_detectado !== 'null' ? $filtro_tipo_detectado : $filtros_actuales['tipo'], /* Preservo o actualizo filtro de tipo */
						'estado' => $filtro_estado_detectado !== 'null' ? $filtro_estado_detectado : $filtros_actuales['estado'], /* Preservo o actualizo filtro de estado */
						'estado_detalle' => $filtros_estado_detalle_detectado !== 'null' ? $filtros_estado_detalle_detectado : $filtros_actuales['estado_detalle'], /* Preservo o actualizo filtro de estado detalle */
						'acronimo' => $filtros_acronimo_detectado !== 'null' ? $filtros_acronimo_detectado : $filtros_actuales['acronimo'], /* Preservo o actualizo filtro de acrónimo */
						'nombre' => $filtros_nombre_detectado !== 'null' ? $filtros_nombre_detectado : $filtros_actuales['nombre'], /* Preservo o actualizo filtro de nombre */
						'apellidos' => $filtros_apellidos_detectado !== 'null' ? $filtros_apellidos_detectado : $filtros_actuales['apellidos'], /* Preservo o actualizo filtro de apellidos */
						'metodo_pago' => $filtros_metodo_pago_detectado !== 'null' ? $filtros_metodo_pago_detectado : $filtros_actuales['metodo_pago'], /* Preservo o actualizo filtro de método de pago */
						'total_min' => $filtros_actuales['total_min'], /* Preservo filtro de total mínimo */
						'total_max' => $filtros_actuales['total_max'], /* Preservo filtro de total máximo */
						'creado_desde' => $filtros_actuales['creado_desde'], /* Preservo filtro de creado desde */
						'creado_hasta' => $filtros_actuales['creado_hasta'], /* Preservo filtro de creado hasta */
						'actualizado_desde' => $filtros_actuales['actualizado_desde'], /* Preservo filtro de actualizado desde */
						'actualizado_hasta' => $filtros_actuales['actualizado_hasta'] /* Preservo filtro de actualizado hasta */
					];
				}
			// Si es búsqueda real y no se encontraron historiales, asigno [-1] para evitar que se muestren todos
			if($_POST['accion'] === 'realizar_busqueda' && empty($_SESSION['datos_busqueda']['historiales_encontrados'])) {
				$_SESSION['datos_busqueda']['historiales_encontrados'] = [-1]; /* Asigno -1 para que no devuelva resultados */
			}
			// Buscar en usuarios si estamos en el panel de administrador en modo edición de usuarios
			} else if($pagina_actual === 'panel_administrador.php' && isset($_SESSION['modo_edicion']) && $_SESSION['modo_edicion'] === 'usuarios') {
				$consulta = $conexion->query("
					SELECT u.id, u.acronimo, u.nombre, u.apellidos, u.dni, u.email, r.id_rol, r.nombre AS rol_nombre
					FROM usuarios u
					INNER JOIN roles r ON u.id_rol = r.id_rol"); /* Preparo consulta para obtener usuarios */
				
				$usuarios = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo todos los usuarios */
				$columnas = ['acronimo','nombre','apellidos','dni','email','rol_nombre']; /* Columnas a buscar */

				// Variables para detectar coincidencias exactas y configurar filtros de usuarios
				$filtro_rol_detectado = 'null'; /* Inicializo filtro de rol como 'null' */
				$filtro_acronimo_detectado = 'null'; /* Inicializo filtro de acronimo como 'null' */
				$filtro_correo_detectado = 'null'; /* Inicializo filtro de correo como 'null' */
				$filtro_dni_detectado = 'null'; /* Inicializo filtro de dni como 'null' */
				$filtro_nombre_detectado = 'null'; /* Inicializo filtro de nombre como 'null' */
				$filtro_apellidos_detectado = 'null'; /* Inicializo filtro de apellidos como 'null' */

				// Recorro los usuarios buscando el texto
				foreach($usuarios as $usuario) {
					foreach($columnas as $col) { /* Recorro las columnas */
						if($_POST['accion'] === 'realizar_busqueda') { /* Si es para realizar búsqueda */
							if(isset($usuario[$col]) && $usuario[$col] !== '' && contienePalabras($texto, $usuario[$col])) { /* Si la columna contiene el texto */
								$_SESSION['datos_busqueda']['usuarios_encontrados'][] = (int)$usuario['id']; /* Guardo el ID del usuario encontrado */
								
								// Comprobar si es coincidencia exacta (normalizada) para activar filtro
								$texto_normalizado = normalizarTexto($texto); /* Normalizo el texto de búsqueda */
								$valor_normalizado = normalizarTexto($usuario[$col]); /* Normalizo el valor de la columna */
								
								if($texto_normalizado === $valor_normalizado) { /* Si hay coincidencia exacta */
									// Asignar el valor original (no normalizado) al filtro correspondiente
									switch($col) {
										case 'rol_nombre': /* Si la columna es rol_nombre */
											$filtro_rol_detectado = $usuario['id_rol']; /* Guardo el ID del rol */
											break;
										case 'acronimo': /* Si la columna es acronimo */
											$filtro_acronimo_detectado = $usuario[$col]; /* Guardo el acronimo */
											break;
										case 'email': /* Si la columna es email */
											$filtro_correo_detectado = $usuario[$col]; /* Guardo el correo */
											break;
										case 'dni': /* Si la columna es dni */
											$filtro_dni_detectado = $usuario[$col]; /* Guardo el dni */
											break;
										case 'nombre': /* Si la columna es nombre */
											$filtro_nombre_detectado = $usuario[$col]; /* Guardo el nombre */
											break;
										case 'apellidos': /* Si la columna es apellidos */
											$filtro_apellidos_detectado = $usuario[$col]; /* Guardo los apellidos */
											break;
									}
								}
								
								break; /* Salgo del bucle de columnas para pasar al siguiente usuario */
							}
						} else if($_POST['accion'] === 'coincidencias') { /* Si es para autocompletar */
							if(isset($usuario[$col]) && contienePalabras($texto, $usuario[$col]) && !in_array($usuario[$col], $agregados)) { /* Si la columna contiene el texto y no está ya agregado */
								$sugerencias[] = ['texto' => $usuario[$col], 'tipo' => 'usuario']; /* Agrego la sugerencia */
								$agregados[] = $usuario[$col]; /* Marco como agregado */
								if(count($sugerencias) >= 5) break; /* Si ya hay 5 sugerencias, salgo */
							}
						}
					}
				}

				// Si se encontró algún filtro, lo guardo en sesión
				if($_POST['accion'] === 'realizar_busqueda' && (
					$filtro_rol_detectado !== 'null' || 
					$filtro_acronimo_detectado !== 'null' || 
					$filtro_correo_detectado !== 'null' || 
					$filtro_dni_detectado !== 'null' || 
					$filtro_nombre_detectado !== 'null' || 
					$filtro_apellidos_detectado !== 'null'
				)) {
					// Preservar filtros existentes si los hay
					$filtros_actuales = isset($_SESSION['filtros_usuarios']) ? $_SESSION['filtros_usuarios'] : [
						'rol' => 'null', /* Inicializo filtro de rol como 'null' */
						'acronimo' => 'null', /* Inicializo filtro de acronimo como 'null' */
						'email' => 'null', /* Inicializo filtro de email como 'null' */
						'dni' => 'null', /* Inicializo filtro de dni como 'null' */
						'nombre' => 'null', /* Inicializo filtro de nombre como 'null' */
						'apellidos' => 'null', /* Inicializo filtro de apellidos como 'null' */
						'fecha_creacion_desde' => null, /* Inicializo fecha creación desde como null */
						'fecha_creacion_hasta' => null, /* Inicializo fecha creación hasta como null */
						'fecha_actualizacion_desde' => null, /* Inicializo fecha actualización desde como null */
						'fecha_actualizacion_hasta' => null, /* Inicializo fecha actualización hasta como null */
						'fecha_acceso_desde' => null, /* Inicializo fecha acceso desde como null */
						'fecha_acceso_hasta' => null /* Inicializo fecha acceso hasta como null */
					];

					// Actualizar solo los filtros que se detectaron
					$_SESSION['filtros_usuarios'] = [
						'rol' => $filtro_rol_detectado !== 'null' ? $filtro_rol_detectado : $filtros_actuales['rol'], /* Preservo o actualizo filtro de rol */
						'acronimo' => $filtro_acronimo_detectado !== 'null' ? $filtro_acronimo_detectado : $filtros_actuales['acronimo'], /* Preservo o actualizo filtro de acronimo */
						'email' => $filtro_correo_detectado !== 'null' ? $filtro_correo_detectado : $filtros_actuales['email'], /* Preservo o actualizo filtro de email */
						'dni' => $filtro_dni_detectado !== 'null' ? $filtro_dni_detectado : $filtros_actuales['dni'], /* Preservo o actualizo filtro de dni */
						'nombre' => $filtro_nombre_detectado !== 'null' ? $filtro_nombre_detectado : $filtros_actuales['nombre'], /* Preservo o actualizo filtro de nombre */
						'apellidos' => $filtro_apellidos_detectado !== 'null' ? $filtro_apellidos_detectado : $filtros_actuales['apellidos'], /* Preservo o actualizo filtro de apellidos */
						'fecha_creacion_desde' => $filtros_actuales['fecha_creacion_desde'], /* Preservo fecha creación desde */
						'fecha_creacion_hasta' => $filtros_actuales['fecha_creacion_hasta'], /* Preservo fecha creación hasta */
						'fecha_actualizacion_desde' => $filtros_actuales['fecha_actualizacion_desde'], /* Preservo fecha actualización desde */
						'fecha_actualizacion_hasta' => $filtros_actuales['fecha_actualizacion_hasta'], /* Preservo fecha actualización hasta */
						'fecha_acceso_desde' => $filtros_actuales['fecha_acceso_desde'], /* Preservo fecha acceso desde */
						'fecha_acceso_hasta' => $filtros_actuales['fecha_acceso_hasta'] /* Preservo fecha acceso hasta */
					];
				}

				// Si es búsqueda real y no se encontraron usuarios, asigno [-1] para evitar que se muestren todos
				if($_POST['accion'] === 'realizar_busqueda' && empty($_SESSION['datos_busqueda']['usuarios_encontrados'])) {
					$_SESSION['datos_busqueda']['usuarios_encontrados'] = [-1]; /* Asigno -1 para que no devuelva resultados */
				}
			}
			
		} catch (PDOException $e) {
			// Si hay error, dejo datos_busqueda como un array vacío y no interrumpo el proceso
		}

		// Procesar la acción solicitada
		if($_POST['accion'] === 'realizar_busqueda') {
			if($es_filtro) { /* Si se encontró un filtro, actualizo los filtros de juegos en sesión */
				if(isset($_SESSION['filtros_elegidos'])) { /* Si ya hay filtros elegidos */
					// Actualizo los filtros de juegos en la sesión
					$_SESSION['filtros_elegidos'] = [
						'tipo' => $_SESSION['datos_busqueda']['tipo_filtro'] == 'tipos' ? (int)$_SESSION['datos_busqueda']['id_filtro'] : $_SESSION['filtros_elegidos']['tipo'], /* Actualizo el tipo elegido */
						'genero' => $_SESSION['datos_busqueda']['tipo_filtro'] == 'generos' ? (int)$_SESSION['datos_busqueda']['id_filtro'] : $_SESSION['filtros_elegidos']['genero'], /* Actualizo el género elegido */
						'categoria' => $_SESSION['datos_busqueda']['tipo_filtro'] == 'categorias' ? (int)$_SESSION['datos_busqueda']['id_filtro'] : $_SESSION['filtros_elegidos']['categoria'], /* Actualizo la categoría elegida */
						'modo' => $_SESSION['datos_busqueda']['tipo_filtro'] == 'modos' ? (int)$_SESSION['datos_busqueda']['id_filtro'] : $_SESSION['filtros_elegidos']['modo'], /* Actualizo el modo elegido */
						'pegi' => $_SESSION['datos_busqueda']['tipo_filtro'] == 'clasificacionPEGI' ? (int)$_SESSION['datos_busqueda']['id_filtro'] : $_SESSION['filtros_elegidos']['pegi'], /* Actualizo la clasificación PEGI elegida */
						'precio_min' => $_SESSION['filtros_elegidos']['precio_min'], /* Actualizo el precio mínimo */
						'precio_max' => $_SESSION['filtros_elegidos']['precio_max'] /* Actualizo el precio máximo */
					]; /* Actualizo el array con todos los filtros elegidos */
				} else { /* Si no hay filtros elegidos aún */
					$_SESSION['filtros_elegidos'] = [
						'tipo' => $_SESSION['datos_busqueda']['tipo_filtro'] == 'tipos' ? (int)$_SESSION['datos_busqueda']['id_filtro'] : 0, /* Creo el tipo elegido */
						'genero' => $_SESSION['datos_busqueda']['tipo_filtro'] == 'generos' ? (int)$_SESSION['datos_busqueda']['id_filtro'] : 0, /* Creo el género elegido */
						'categoria' => $_SESSION['datos_busqueda']['tipo_filtro'] == 'categorias' ? (int)$_SESSION['datos_busqueda']['id_filtro'] : 0, /* Creo la categoría elegida */
						'modo' => $_SESSION['datos_busqueda']['tipo_filtro'] == 'modos' ? (int)$_SESSION['datos_busqueda']['id_filtro'] : 0, /* Creo el modo elegido */
						'pegi' => $_SESSION['datos_busqueda']['tipo_filtro'] == 'clasificacionPEGI' ? (int)$_SESSION['datos_busqueda']['id_filtro'] : 0, /* Creo la clasificación PEGI elegida */
						'precio_min' => 0, /* Creo el precio mínimo */
						'precio_max' => 100 /* Creo el precio máximo */
					]; /* Creo un array con todos los filtros elegidos */
				}
			}

			echo 'OK'; /* Devuelvo OK al finalizar el proceso */

		// Si la acción es para obtener coincidencias para autocompletar
		} else if($_POST['accion'] === 'coincidencias') {
			echo json_encode($sugerencias); /* Devuelvo las sugerencias en formato JSON */
			exit; /* Termino la ejecución */
		}
	}

?>
