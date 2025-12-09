// Función genérica para mandar datos vía AJAX (comentarios y valoraciones)
function mandarComentarioValoracion(datos, reidrigir) {
    const xhttp = new XMLHttpRequest(); /* Creo objeto para petición AJAX */
    xhttp.onreadystatechange = function() { /* Defino función para cuando cambie el estado */
        if (this.readyState === 4 && this.status == 200) { /* Si la petición ha terminado y ha ido bien */
            if (typeof reidrigir === 'function') reidrigir(); /* Llamo a la función pasada como parámetro para redirigir o recargar */
        }
    };
    xhttp.open('POST', '../acciones/comentarios_valoraciones.php', true); /* Configuro la petición AJAX */
    xhttp.send(datos); /* Mando los datos */
}


// Función para eliminar comentario del usuario
function eliminarComentario() {
    const formulario_comentario = document.getElementById('form-comentario'); /* Obtengo el formulario de comentario */
    if (!formulario_comentario) return; /* Si no existe el formulario, salgo */
    const campo_id_juego = formulario_comentario.querySelector('input[name="id_juego"]'); /* Obtengo el campo id_juego */
    if (!campo_id_juego) return; /* Si no existe el campo, salgo */

    const nombre_modal = 'modal-eliminar-comentario'; /* Nombre del modal */
    const contenido_modal = '<h1>Eliminar comentario</h1><p>¿Seguro que quieres eliminar tu comentario?</p>'; /* Contenido del modal */
    
    modal(nombre_modal, contenido_modal, true); /* Muestro el modal */
    const boton_aceptar = document.getElementById('aceptar-' + nombre_modal); /* Obtengo el botón aceptar del modal */
    if (boton_aceptar) { /* Si existe el botón */
        boton_aceptar.addEventListener('click', function() { /* Añado evento click al botón */
            const datos = new FormData(); /* Creo objeto FormData */
            datos.append('accion', 'eliminar_comentario'); /* Añado acción */
            datos.append('id_juego', campo_id_juego.value); /* Añado id_juego */
            const modal_creado = document.getElementById(nombre_modal); /* Obtengo el modal */
            if (modal_creado) document.body.removeChild(modal_creado); /* Cierro el modal */
            mandarComentarioValoracion(datos, function() { /* Mando los datos vía AJAX */
                window.location.href = 'detalles_juego.php?id=' + campo_id_juego.value; /* Redirijo al detalle del juego */
            });
        });
    }
}


// Función para eliminar valoración del usuario
function eliminarValoracion() {
    const formulario_valoracion = document.getElementById('form-valoracion'); /* Obtengo el formulario de valoración */
    if (!formulario_valoracion) return; /* Si no existe el formulario, salgo */
    const campo_id_juego = formulario_valoracion.querySelector('input[name="id_juego"]'); /* Obtengo el campo id_juego */
    if (!campo_id_juego) return; /* Si no existe el campo, salgo */
    const nombre_modal = 'modal-eliminar-valoracion'; /* Nombre del modal */
    const contenido_modal = '<h1>Eliminar valoración</h1><p>¿Seguro que quieres eliminar tu valoración?</p>'; /* Contenido del modal */
    
    modal(nombre_modal, contenido_modal, true); /* Muestro el modal */
    const boton_aceptar = document.getElementById('aceptar-' + nombre_modal); /* Obtengo el botón aceptar del modal */
    if (boton_aceptar) { /* Si existe el botón */
        boton_aceptar.addEventListener('click', function() { /* Añado evento click al botón */
            const datos = new FormData(); /* Creo objeto FormData */
            datos.append('accion', 'eliminar_valoracion'); /* Añado acción */
            datos.append('id_juego', campo_id_juego.value); /* Añado id_juego */
            const modal_creado = document.getElementById(nombre_modal); /* Obtengo el modal */
            if (modal_creado) document.body.removeChild(modal_creado); /* Cierro el modal */
            mandarComentarioValoracion(datos, function() { /* Mando los datos vía AJAX */
                window.location.href = 'detalles_juego.php?id=' + campo_id_juego.value; /* Redirijo al detalle del juego */
            });
        });
    }
}

// Función para que administrador pueda eliminar comentario concreto
function eliminarComentarioAdmin(id_comentario) {
    const nombre_modal = 'modal-eliminar-comentario-admin'; /* Nombre del modal */
    const contenido_modal = '<h1>Eliminar comentario</h1><p>¿Seguro que quieres eliminar este comentario?</p>'; /* Contenido del modal */
    
    modal(nombre_modal, contenido_modal, true); /* Muestro el modal */
    const boton_aceptar = document.getElementById('aceptar-' + nombre_modal); /* Obtengo el botón aceptar del modal */
    if (boton_aceptar) { /* Si existe el botón */
        boton_aceptar.addEventListener('click', function() { /* Añado evento click al botón */
            const datos = new FormData(); /* Creo objeto FormData */
            datos.append('accion', 'eliminar_comentario_admin'); /* Añado acción */
            datos.append('id_comentario', id_comentario); /* Añado id_comentario */
            const modal_creado = document.getElementById(nombre_modal); /* Obtengo el modal */
            if (modal_creado) document.body.removeChild(modal_creado); /* Cierro el modal */
            mandarComentarioValoracion(datos, function() { /* Mando los datos vía AJAX */
                window.location.reload(); /* Recargo la página */
            });
        });
    }
}

// Función para que administrador pueda eliminar todas las valoraciones de un juego
function eliminarTodasValoraciones(id_juego) {
    const nombre_modal = 'modal-eliminar-valoraciones-admin'; /* Nombre del modal */
    const contenido_modal = '<h1>Eliminar valoraciones</h1><p>¿Seguro que quieres eliminar TODAS las valoraciones de este juego?</p>'; /* Contenido del modal */
    
    window.modal(nombre_modal, contenido_modal, true); /* Muestro el modal */
    const boton_aceptar = document.getElementById('aceptar-' + nombre_modal); /* Obtengo el botón aceptar del modal */
    if (boton_aceptar) { /* Si existe el botón */
        boton_aceptar.addEventListener('click', function() { /* Añado evento click al botón */
            const datos = new FormData(); /* Creo objeto FormData */
            datos.append('accion', 'eliminar_todas_valoraciones_admin'); /* Añado acción */
            datos.append('id_juego', id_juego); /* Añado id_juego */
            const modal_creado = document.getElementById(nombre_modal); /* Obtengo el modal */
            if (modal_creado) document.body.removeChild(modal_creado); /* Cierro el modal */
            mandarComentarioValoracion(datos, function() { /* Mando los datos vía AJAX */
                window.location.reload(); /* Recargo la página */
            });
        });
    }
}

// Cuando el DOM esté cargado
document.addEventListener('DOMContentLoaded', () => {
    // Bloque VALORACIÓN (estrellas)
    const lista_estrellas = document.querySelectorAll('.estrella-valoracion'); /* Obtengo todas las estrellas */
    const campo_valoracion = document.getElementById('input-valoracion'); /* Obtengo el campo oculto de valoración */
    if (lista_estrellas.length && campo_valoracion) { /* Si existen las estrellas y el campo */
        let valor_actual = parseInt(campo_valoracion.value, 10) || 0; /* Valor actual */
        function actualizarEstrellas() { /* Función para actualizar las estrellas según el valor actual */
            lista_estrellas.forEach(estrella => { /* Recorro todas las estrellas */
                const valor_estrella = parseInt(estrella.dataset.valor, 10); /* Valor de la estrella */
                if (valor_estrella <= valor_actual) { /* Si el valor de la estrella es menor o igual al valor actual */
                    estrella.src = '../recursos/imagenes/valorado.png'; /* Estrella llena */
                } else { /* Si no */
                    estrella.src = '../recursos/imagenes/sin_valorar.png'; /* Estrella vacía */
                }
            });
        }
        lista_estrellas.forEach(estrella => { /* Recorro todas las estrellas */
            estrella.addEventListener('mouseover', () => { /* Evento para cuando se pasa el ratón por encima */
                const valor_hover = parseInt(estrella.dataset.valor, 10); /* Valor de la estrella */
                lista_estrellas.forEach(e => { /* Recorro todas las estrellas */
                    const valor_e = parseInt(e.dataset.valor, 10); /* Valor de la estrella */
                    if(valor_e <= valor_hover) { /* Si el valor de la estrella es menor o igual al valor hover */
                        e.src = '../recursos/imagenes/valorado.png'; /* Estrella llena */
                    } else { /* Si no */
                        e.src = '../recursos/imagenes/sin_valorar.png'; /* Estrella vacía */
                    }
                });
            });
            estrella.addEventListener('mouseout', actualizarEstrellas); /* Evento para cuando se quita el ratón */
            estrella.addEventListener('click', () => { /* Evento para cuando se hace click */
                valor_actual = parseInt(estrella.dataset.valor, 10); /* Actualizo el valor actual */
                campo_valoracion.value = valor_actual; /* Actualizo el campo oculto */
                actualizarEstrellas(); /* Actualizo las estrellas */
            });
            estrella.addEventListener('keydown', (e) => { /* Evento para accesibilidad con teclado */
                if (e.key === 'Enter' || e.key === ' ') { /* Si se presiona Enter o Espacio */
                    e.preventDefault(); /* Evito el comportamiento por defecto */
                    valor_actual = parseInt(estrella.dataset.valor, 10); /* Actualizo el valor actual */
                    campo_valoracion.value = valor_actual; /* Actualizo el campo oculto */
                    actualizarEstrellas(); /* Actualizo las estrellas */
                }
            });
            estrella.setAttribute('tabindex', '0'); /* Hago que la estrella sea enfocables */
            estrella.setAttribute('role', 'button'); /* Defino el rol como botón para accesibilidad */
            estrella.setAttribute('aria-label', 'Valor ' + estrella.dataset.valor); /* Etiqueta accesible */
        });
        actualizarEstrellas(); /* Inicializo las estrellas */
    }

    // Bloque para eliminar mensajes de error y de éxito después de 5 segundos */
    const mensajesError = document.querySelectorAll('.mensaje-error'); /* Selecciono todos los mensajes de error */
    const mensajesExito = document.querySelectorAll('.mensaje-exito'); /* Selecciono todos los mensajes de éxito */

    mensajesError.forEach(function(mensaje) { /* Recorro cada mensaje de error */
        setTimeout(function() { /* Después de 5 segundos */
            mensaje.style.transition = 'opacity 0.5s ease'; /* Aplico transición de opacidad */
            mensaje.style.opacity = '0'; /* Inicio la animación de desvanecimiento */
            
            setTimeout(function() { /* Después de la animación */
                mensaje.remove(); /* Elimino el mensaje del DOM */
            }, 500); // Espera 0.5s para que termine la animación
        }, 5000); // 5000ms = 5 segundos
    });

    mensajesExito.forEach(function(mensaje) { /* Recorro cada mensaje de éxito */
        setTimeout(function() { /* Después de 5 segundos */
            mensaje.style.transition = 'opacity 0.5s ease'; /* Aplico transición de opacidad */
            mensaje.style.opacity = '0'; /* Inicio la animación de desvanecimiento */

            setTimeout(function() { /* Después de la animación */
                mensaje.remove(); /* Elimino el mensaje del DOM */
            }, 500); // Espera 0.5s para que termine la animación
        }, 5000); // 5000ms = 5 segundos
    });
});

