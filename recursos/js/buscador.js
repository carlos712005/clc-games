// Función para eliminar la búsqueda activa
function eliminarBusqueda() {
	/* Redirigir a la acción de limpiar búsqueda según la página actual */
	if (window.paginaActual === 'panel_administrador.php') { /* Si estoy en el panel de administrador */
		window.location.href = '../acciones/limpiar_busqueda.php?ir_a=../vistas/' + window.paginaActual + ''; /* Redirijo al panel de administrador */
	} else if(window.modoAdmin) { /* Si estoy en modo administrador */
		window.location.href = '../acciones/limpiar_busqueda.php?ir_a=../publico/' + window.paginaActual + '?id=' + window.idUsuarioBuscado + ''; /* Redirijo a alguna página pública con ID de usuario buscado */
	} else { /* Si estoy en modo usuario normal */
		window.location.href = '../acciones/limpiar_busqueda.php?ir_a=../publico/' + window.paginaActual + ''; /* Redirijo a alguna página pública normal */
	}
}

// Función única para enviar peticiones al buscador
function mandarBusqueda(accion, texto, despuesPeticion) {
	const datos = new FormData(); /* Creo el formulario de datos */
	datos.append('accion', accion); /* Agrego la acción a enviar */
	datos.append('texto_busqueda', texto); /* Agrego el texto de búsqueda */
	datos.append('pagina_actual', window.paginaActual || ''); /* Envio la página actual */
	datos.append('modo_edicion', window.modoEdicion || 'juegos'); /* Envio el modo de edición actual */

	// Envío la solicitud AJAX
	const solicitud = new XMLHttpRequest(); /* Creo objeto para la solicitud AJAX */
	solicitud.onreadystatechange = function() { /* Defino qué hacer cuando cambie el estado */
		if (this.readyState === 4 && this.status === 200) { /* Si la solicitud se completó exitosamente */
			if (despuesPeticion) { /* Si hay una función para después de la petición */
				despuesPeticion(this.responseText); /* Llamo a la función con la respuesta */
			}
		}
	};
	solicitud.open('POST', '../acciones/acciones_buscador.php', true); /* Configuro la solicitud POST */
	solicitud.send(datos); /* Envío los datos */
}

// Función para inicializar el buscador (se llama después de cargar contenido por AJAX)
function inicializarBuscador() {
	const formularioBusqueda = document.querySelector('.formulario-busqueda'); /* Obtengo el formulario de búsqueda */
	if (!formularioBusqueda) return; /* Si no existe, no hago nada */
	const cuadroBusqueda = formularioBusqueda.querySelector('#cuadro-busqueda'); /* Obtengo el input del buscador */
	const botonBuscar = formularioBusqueda.querySelector('.boton-lupa'); /* Obtengo el botón de búsqueda */
	
	const botonBuscarBtn = document.getElementById('boton-buscar'); /* Botón de buscar principal */
	const botonLimpiarBusqueda = document.getElementById('boton-limpiar-busqueda'); /* Botón de limpiar búsqueda */
	
	// Si el botón de buscar, limpiar y el cuadro de búsqueda existen
	if (botonBuscarBtn && botonLimpiarBusqueda && cuadroBusqueda) {
		// Si el input tiene valor, mostrar botón de limpiar
		if (cuadroBusqueda.value && cuadroBusqueda.value.trim() !== '') {
			botonBuscarBtn.style.display = 'none'; /* Oculto el botón de buscar */
			botonLimpiarBusqueda.style.display = 'block'; /* Muestro el botón de limpiar */
		} else { /* Si no tiene valor, mostrar botón de buscar */
			botonBuscarBtn.style.display = 'block'; /* Muestro el botón de buscar */
			botonLimpiarBusqueda.style.display = 'none'; /* Oculto el botón de limpiar */
		}
	}
	
	// Deshabilitar el buscador en páginas específicas
	if(window.paginaActual === 'detalles_juego.php' || window.paginaActual === 'mapa.php'
		|| window.paginaActual === 'editar_datos.php' || window.paginaActual === 'editar_juego.php'
		|| window.paginaActual === 'detalles_usuario.php' || window.paginaActual === 'notificaciones.php'
		|| window.paginaActual === 'estadisticas.php') {
		cuadroBusqueda.disabled = true; /* Deshabilito el buscador */
		cuadroBusqueda.placeholder = 'Buscador no disponible aquí'; /* Muestro mensaje */
		cuadroBusqueda.style.pointerEvents = 'none'; /* Desactivo hover */
		cuadroBusqueda.style.cursor = 'not-allowed'; /* Cambio cursor al símbolo de prohibido */
		// Forzar cursor sobre el contenedor padre
		cuadroBusqueda.parentElement.style.cursor = 'not-allowed'; /* Cambio cursor del padre al símbolo de prohibido */
		
		// Deshabilitar el botón de búsqueda
		if (botonBuscar) {
			botonBuscar.disabled = true; /* Deshabilito el botón */
			botonBuscar.style.pointerEvents = 'none'; /* Desactivo hover */
			botonBuscar.style.cursor = 'not-allowed'; /* Cambio cursor al símbolo de prohibido */
			// Forzar cursor sobre el contenedor padre
			botonBuscar.parentElement.style.cursor = 'not-allowed'; /* Cambio cursor del padre al símbolo de prohibido */
		}
		return; // No activar el buscador en estas páginas
	}

	// Contenedor de sugerencias
	const contenedorSugerencias = document.createElement('div'); /* Creo el contenedor de sugerencias */
	contenedorSugerencias.className = 'sugerencias-busqueda'; /* Asigno clase al contenedor */
	contenedorSugerencias.style.display = 'none'; /* Inicialmente oculto el contenedor */
	cuadroBusqueda.parentElement.appendChild(contenedorSugerencias); /* Agrego el contenedor al DOM */

	// Autocompletado con retardo simple
	let temporizador = null; /* Inicializo el temporizador */
	cuadroBusqueda.addEventListener('input', function() { /* Al escribir en el cuadro de búsqueda */
		clearTimeout(temporizador); /* Limpio el temporizador anterior */
		const textoActual = this.value.trim(); /* Obtengo el texto actual */

		if (textoActual.length < 1) { /* Si no hay texto, oculto sugerencias */
			contenedorSugerencias.style.display = 'none'; /* Oculto el contenedor de sugerencias */
			return; /* Salgo de la función */
		}

		temporizador = setTimeout(() => { /* Espero un breve momento antes de enviar la solicitud */
			const textoBuscado = cuadroBusqueda.value.trim(); /* Obtengo el texto buscado */
			
			// Envío la solicitud para obtener sugerencias
			mandarBusqueda('coincidencias', textoBuscado, function(respuesta) {
				let sugerencias = []; /* Inicializo el array de sugerencias */
				try { /* Intento parsear la respuesta JSON */
					sugerencias = JSON.parse(respuesta); /* Parseo la respuesta */
				} catch (e) { /* Si hay error */
					sugerencias = []; /* Dejo el array vacío */
				}

				// Si el usuario cambió el texto mientras llegaba la respuesta, la ignoro
				if (cuadroBusqueda.value.trim() !== textoBuscado) return;

				// Si no hay sugerencias, oculto el contenedor
				if (!sugerencias || sugerencias.length === 0) {
					contenedorSugerencias.style.display = 'none'; /* Oculto el contenedor de sugerencias */
					return; /* Salgo de la función */
				}

				contenedorSugerencias.innerHTML = ''; /* Limpio sugerencias previas */
				for (let i = 0; i < sugerencias.length; i++) { /* Recorro las sugerencias */
					const sug = sugerencias[i]; /* Obtengo la sugerencia actual */
					const item = document.createElement('div'); /* Creo el elemento de sugerencia */
					item.className = 'sugerencia-item'; /* Asigno clase al ítem (o elemento) */
					
					// Resaltar texto buscado
					const textoMinusculas = sug.texto.toLowerCase(); /* Texto en minúsculas */
					const busquedaMinusculas = textoBuscado.toLowerCase(); /* Búsqueda en minúsculas */
					const posicion = textoMinusculas.indexOf(busquedaMinusculas); /* Posición de la búsqueda */
					
					let textoMostrar = sug.texto; /* Texto a mostrar */
					if (posicion !== -1) { /* Si se encontró la búsqueda */
						const antes = sug.texto.substring(0, posicion); /* Texto antes de la coincidencia */
						const coincide = sug.texto.substring(posicion, posicion + textoBuscado.length); /* Texto que coincide */
						const despues = sug.texto.substring(posicion + textoBuscado.length); /* Texto después de la coincidencia */
						textoMostrar = antes + '<strong>' + coincide + '</strong>' + despues; /* Resalto la coincidencia */
					}
					
					// Asigno el contenido HTML al ítem
					item.innerHTML = '<span>' + textoMostrar + '</span>' +
									 '<small>' + (sug.tipo || '') + '</small>';
					item.onclick = function() { /* Al hacer clic en la sugerencia */
						const textoSeleccionado = sug.texto; /* Obtengo el texto seleccionado */
						cuadroBusqueda.value = textoSeleccionado; /* Actualizo el cuadro de búsqueda */
						contenedorSugerencias.style.display = 'none'; /* Oculto las sugerencias */

						// Realizo la búsqueda con el texto seleccionado
						mandarBusqueda('realizar_busqueda', textoSeleccionado, function(respuesta) {
							if (respuesta === 'OK') { /* Si la búsqueda fue exitosa */
								window.location.reload(); /* Recargo la página */
							}
						});
					};
					contenedorSugerencias.appendChild(item); /* Agrego el ítem al contenedor */
				}
				contenedorSugerencias.style.display = 'block'; /* Muestro el contenedor de sugerencias */
			});
		}, 300);
	});

	// Cerrar sugerencias al hacer clic fuera
	document.addEventListener('click', function(e) {
		const clicFuera = !cuadroBusqueda.contains(e.target) && !contenedorSugerencias.contains(e.target); /* Verifico si el clic fue fuera */
		if (clicFuera) contenedorSugerencias.style.display = 'none'; /* Oculto las sugerencias */
	});

	// Envío del formulario para búsqueda completa
	formularioBusqueda.addEventListener('submit', function(e) {
		e.preventDefault(); /* Evito el envío por defecto */
		contenedorSugerencias.style.display = 'none'; /* Oculto las sugerencias */

		let texto = ''; /* Inicializo el texto */
		if (cuadroBusqueda && cuadroBusqueda.value) { /* Si hay texto en el cuadro */
			texto = cuadroBusqueda.value.trim(); /* Obtengo el texto */
		}

		// Realizo la búsqueda completa
		mandarBusqueda('realizar_busqueda', texto, function(respuesta) {
			if (respuesta === 'OK' || respuesta === 'VACIO') { /* Si la búsqueda fue exitosa */
				window.location.reload(); /* Recargo la página */
			}
		});
	});
}

// Cuando la página haya cargado completamente
document.addEventListener('DOMContentLoaded', () => {
	inicializarBuscador(); /* Inicializo el buscador */
});
