// Función principal que crea y muestra un modal dinámicamente
function modal(nombre, contenido, cancelar) {
    // Crear el contenedor principal del modal
    const modal = document.createElement("div"); /* Creo el elemento div que servirá como contenedor principal del modal */
    modal.id = nombre; /* Asigno el ID específico pasado como parámetro */
    modal.className = "modal"; /* Asigno la clase CSS para el estilo del modal */
    
    // Crear la ventana interna del modal
    const ventana = document.createElement("div"); /* Creo el div que contendrá el contenido visible del modal */
    ventana.id = "ventana"; /* Asigno el ID fijo para la ventana interna */

    // Crear el área de mensaje/contenido
    const mensaje = document.createElement("div"); /* Creo el div que contendrá el mensaje o contenido principal */
    mensaje.id = "mensaje"; /* Asigno el ID fijo para el área de mensaje */
    mensaje.innerHTML = contenido; /* Inserto el contenido HTML pasado como parámetro */
    ventana.appendChild(mensaje); /* Añado el área de mensaje a la ventana */

    // Crear el div de botones por separado para evitar sobreescribir el contenido
    const divBotones = document.createElement("div"); /* Creo un contenedor separado para los botones de acción */
    divBotones.id = "botones"; /* Asigno el ID fijo para el área de botones */

    // Configurar botones según el tipo de modal solicitado
    if(cancelar) { /* Si se requiere un modal con opción de cancelar */
        divBotones.innerHTML = `<hr>
                                <button id="aceptar-${nombre}" class="aceptar">Aceptar</button>
                                <button id="cancelar-${nombre}" class="cancelar">Cancelar</button>`; /* Creo botones de Aceptar y Cancelar con separador */
    } else { /* Si solo se requiere un modal informativo */
        divBotones.innerHTML = `<hr>
                                <button id="cerrar-${nombre}" class="cerrar">Cerrar</button>`; /* Creo solo botón de Cerrar con separador */
    }

    // Agregar el div de botones a la ventana
    ventana.appendChild(divBotones); /* Añado el área de botones a la ventana */
    modal.appendChild(ventana); /* Añado la ventana completa al modal principal */
    document.body.appendChild(modal); /* Añado todo el modal al cuerpo del documento */

    // Configurar eventos de los botones según el tipo de modal
    if(cancelar) { /* Si es un modal con opción de cancelar */
        document.getElementById(`cancelar-${nombre}`).addEventListener('click', () => { /* Añado evento click al botón cancelar */
            document.body.removeChild(document.getElementById(nombre)); /* Elimino completamente el modal del DOM */
        });
    } else { /* Si es un modal solo informativo */
        document.getElementById(`cerrar-${nombre}`).addEventListener('click', () => { /* Añado evento click al botón cerrar */
            document.body.removeChild(document.getElementById(nombre)); /* Elimino completamente el modal del DOM */
        });
    }

    // Solo añadir el evento de cierre por click fuera si NO hay botón con id 'confirmar-pago', 'motivo-devolucion' o 'inicio-devolucion'
    setTimeout(() => {
        // Verifico si el modal no contiene esos botones específicos
        if (!modal.querySelector('#confirmar-pago') && !modal.querySelector('#motivo-devolucion') && !modal.querySelector('#inicio-devolucion')
                && !modal.querySelector('#modal-procesando') && !modal.querySelector('#modal-tarjeta')) {
            modal.addEventListener('click', (evento) => { /* Añado evento click al fondo del modal */
                if (evento.target === modal) { /* Si el click fue fuera de la ventana (en el fondo del modal) */
                    const modalElemento = document.getElementById(nombre); /* Obtengo el elemento modal por su ID */
                    if (modalElemento) { /* Si el modal existe */
                        document.body.removeChild(modalElemento); /* Elimino completamente el modal del DOM */
                    }
                }
            });
        }
    }, 0);
}