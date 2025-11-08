// Función para mostrar los detalles de una transacción del historial
function mostrarDetallesHistorial(idHistorial, idHistorialFormateado) {
    const filas = window.historial; /* Obtengo las filas del historial desde la variable global */
    if (!filas || filas.length === undefined) { /* Si no hay filas definidas */
        modal("modal1", "<h1>No se encontraron detalles de esta transacción</h1>", false); /* Muestro modal de error */
        return; /* Salgo de la función */
    }
    const seleccion = []; /* Array para almacenar los detalles de la transacción seleccionada */
    for (let i = 0; i < filas.length; i++) { /* Recorro todas las filas del historial */
        if (filas[i].id_historial == idHistorial) { /* Si el ID del historial coincide */
            seleccion[seleccion.length] = filas[i]; /* Añado la fila al array de selección */
        }
    }
    if (seleccion.length === 0) { /* Si no se encontraron detalles para el ID dado */
        modal("modal1", "<h1>No se encontraron detalles de esta transacción</h1>", false); /* Muestro modal de error */
        return; /* Salgo de la función */
    }

    // Construir el contenido HTML para el modal
    let contenido = '<div class="contenido-historial-detalles">' +
        '<h1>Detalles de la transacción</h1>' +
        '<div class="info-transaccion">' +
                '<p><strong>Nº:</strong> ' + idHistorialFormateado + '</p>' + /* Usar el ID formateado para mostrar */
        '</div>' +
        '<div class="info-transaccion">' +
                '<p><strong>Tipo:</strong> ' + seleccion[0].tipo + '</p>' +
        '</div>' +
        '<div class="info-transaccion">' +
                '<p><strong>Estado:</strong> ' + seleccion[0].estado + '</p>' +
        '</div>' +
        '<div class="info-transaccion">' +
                '<p><strong>Total:</strong> ' + (parseFloat(seleccion[0].total) === 0 ? "Gratis" : parseFloat(seleccion[0].total).toFixed(2).replace('.', ',') + ' €') + '</p>' +
        '</div>' +
        '<hr>' +
        '<div id="productos-historial">' +
            '<h2>Juegos en esta transacción:</h2>';

        // Recorrer cada detalle (cada juego en la transacción)
        for (let j = 0; j < seleccion.length; j++) {
            const fila = seleccion[j]; /* Obtengo la fila actual */
            const datosJuego = fila.juego; /* Obtengo los datos del juego (objeto) */
            const estadoJuego = fila.estado_detalle; /* Obtengo el estado específico del juego */
            const comentarioJuego = fila.comentario_detalle; /* Obtengo el comentario/motivo específico relacionado con el juego */

            if (datosJuego) { /* Si hay datos del juego */
                // Construir el HTML para el juego
                contenido += '<div class="producto-historial">' +
                    '<div class="producto-imagen">' +
                        '<img src="../' + datosJuego.portada + '" alt="' + datosJuego.nombre + '">' +
                    '</div>' +
                    '<div class="producto-info">' +
                        '<h3>' + datosJuego.nombre + '</h3>' +
                        '<p><strong>Tipo:</strong> ' + datosJuego.tipo + '</p>' +
                        '<p><strong>Precio:</strong> ' + (datosJuego.precio == 0.00 ? "Gratis" : `${parseFloat(datosJuego.precio).toFixed(2).replace('.', ',')} €`) + '</p>' +
                        '<p><strong>Estado:</strong> ' + (estadoJuego || '') + '</p>' +
                        (comentarioJuego && comentarioJuego.trim() !== '' ? '<p><strong>Motivo:</strong> <em>' + comentarioJuego + '</em></p>' : '') + /* Solo mostrar si hay un comentario */
                        '<p class="producto-resumen">' + (datosJuego.resumen || '') + '</p>' +
                        '<button class="boton-ver-ficha" onclick="window.location.href=\'detalles_juego.php?id=' + datosJuego.id + '\'">Ver ficha del juego</button>' +
                    '</div>' +
                '</div>';
            } else { /* Si no hay datos del juego */
                // Mostrar solo el ID del juego y su estado/comentario
                contenido += '<div class="producto-historial">' +
                    '<div class="producto-info">' +
                        '<h3>Juego #' + fila.id_juego + '</h3>' +
                        '<p><strong>Estado:</strong> ' + (estadoJuego || '') + '</p>' +
                        (comentarioJuego ? '<p><strong>Motivo:</strong> <em>' + comentarioJuego + '</em></p>' : '') + /* Solo mostrar si hay un comentario */
                    '</div>' +
                '</div>';
            }
        }

    contenido += '</div></div>'; /* Cierro los divs principales */
    modal("modal-detalles-historial", contenido, false); /* Muestro el modal con los detalles construidos */
}