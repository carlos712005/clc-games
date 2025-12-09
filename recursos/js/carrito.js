// Función que obtiene el contenido actual del carrito desde el servidor
function recibir() {
    // Preparar datos para la petición
    const datos = new FormData(); /* Creo objeto FormData para enviar datos al servidor */
    datos.append('accion', 'obtener'); /* Especifico que quiero obtener el contenido del carrito */
    const xhttp = new XMLHttpRequest(); /* Creo objeto para realizar petición AJAX */

    // Realizar llamada síncrona al servidor
    xhttp.open("POST", "../acciones/acciones_carrito.php", false); /* Configuro petición POST síncrona */
    xhttp.send(datos); /* Envío la petición con los datos */

    // Procesar respuesta del servidor
    if (xhttp.status == 200) { /* Si la petición fue exitosa */
        const respuesta = JSON.parse(xhttp.responseText); /* Convierto respuesta JSON en objeto */
        return respuesta; /* Devuelvo el contenido del carrito */
    } else { /* Si hubo error en la petición */
        console.error("Respuesta del servidor:", xhttp.responseText); /* Log de la respuesta */
        return null; /* Devuelvo null para indicar error */
    }
}

// Función principal que maneja acciones del carrito y de pedidos (agregar, eliminar, vaciar, realizar pedido, cancelar pedido, devoluciones)
function mandar(accion, id_juego, nombreModal, mensaje, cancelar, total = null, boton = null, carrito_unico = null, motivo = null) {
    let carrito = []; /* Inicializo variable para el carrito */
    if(carrito_unico) { /* Si se pasa un carrito único como parámetro */
        carrito = carrito_unico; /* Uso el carrito pasado como parámetro */
    } else { /* Si no se pasa un carrito único */
        carrito = recibir(); /* Obtengo el contenido actual del carrito */
        if (!carrito) { /* Si no se pudo obtener el carrito */
            console.error("No se pudo obtener el carrito"); /* Log del error */
            return; /* Salgo de la función */
        }
    }
    
    // Guardar IDs de juegos en el carrito para actualizar botones al vaciar
    const idsCarrito = []; /* Array para almacenar los IDs de los juegos en el carrito */
    if (accion === 'vaciar') { /* Si la acción es vaciar el carrito */
        for (let i = 0; i < carrito.length; i++) { /* Recorro todos los juegos del carrito */
            idsCarrito.push(carrito[i].id); /* Almaceno el ID del juego en el array */
        }
    }

    // Verificar si el juego ya existe en el carrito
    let existe = false; /* Variable para controlar si el juego ya está en el carrito */
    for (let i = 0; i < carrito.length; i++) { /* Recorro todos los juegos del carrito */
        if (carrito[i].id == id_juego) { /* Si encuentro el juego */
            existe = true; /* Marco que ya existe */
        }
    }

    // Prevenir duplicados al agregar
    if (accion === "agregar" && existe) { /* Si intento agregar un juego que ya existe */
        modal("modal1", "<h1>El juego ya está en el carrito</h1>", false); /* Muestro mensaje de error */
        return; /* Salgo de la función sin hacer nada */
    }

    // Preparar datos para enviar al servidor
    const datos = new FormData(); /* Creo objeto para enviar datos */
    datos.append('accion', accion); /* Envío la acción */

    if(accion === "agregar" || accion === "eliminar") { /* Si es agregar o eliminar específico */
        datos.append('id_juego', id_juego); /* Envío el ID del juego */
    } else if(accion === "cancelar_pedido" || accion === "realizar_pedido") { /* Si es cancelar pedido o realizar pago */
        datos.append('carrito', JSON.stringify(carrito)); /* Envío el carrito codificado en JSON */
        datos.append('total', total); /* Envío el total */
        if (carrito_unico && accion === "realizar_pedido") { /* Si es un carrito único, indico que es así */
            datos.append('compra_unica', "si"); /* Indico que es una compra única */
        }        
    } else if(accion === "realizar_reserva") { /* Si es realizar reserva */
        if(id_juego == null && carrito.length > 0) { /* Si no se pasó ID pero hay un juego en el carrito */
            datos.append('id_juego', carrito[0].id); /* Envío el ID del juego */
        } else { /* Si se pasó el ID del juego */
            datos.append('id_juego', id_juego); /* Envío el ID del juego */
        }
        datos.append('total', total); /* Envío el total */
        if (carrito_unico && accion === "realizar_reserva") { /* Si es un carrito único, indico que es así */
            datos.append('compra_unica', "si"); /* Indico que es una compra única */
        }
    } else if(accion === "cancelar_devolucion") { /* Si es cancelar devolución */
        datos.append('id_juego', id_juego); /* Envío el ID del juego */
        datos.append('total', total); /* Envío el total */
    } else if(accion === "realizar_devolucion") { /* Si es realizar devolución */
        datos.append('id_juego', id_juego); /* Envío el ID del juego */
        datos.append('total', total); /* Envío el total */
    } else if(accion === "solicitar_reserva") { /* Si es solicitar reserva */
        datos.append('id_juego', id_juego); /* Envío el ID del juego */
        datos.append('total', total); /* Envío el total */
    } else if(accion === "solicitar_devolucion") { /* Si es solicitar devolución */
        datos.append('id_juego', id_juego); /* Envío el ID del juego */
        datos.append('total', total); /* Envío el total */
        datos.append('motivo', motivo); /* Envío el motivo */
    } else if(accion === "cancelar_solicitud_reserva" || accion === "cancelar_solicitud_devolucion") { /* Si es cancelar solicitud de reserva o devolución */
        datos.append('id_juego', id_juego); /* Envío el ID del juego */
        datos.append('total', total); /* Envío el total */
        if(motivo != null && motivo !== "") { /* Si se pasó un motivo */
            datos.append('motivo', motivo); /* Envío el motivo */
        }
    }

    // Realizar petición asíncrona al servidor
    const xhttp = new XMLHttpRequest(); /* Creo objeto para petición AJAX */
    xhttp.onreadystatechange = function() { /* Defino qué hacer cuando cambie el estado */
        if (this.readyState == 4 && this.status == 200) { /* Si la petición se completó exitosamente */
            const respuesta = JSON.parse(this.responseText); /* Convierto respuesta JSON en objeto */
            if(respuesta.total_notificaciones_no_leidas !== undefined) { /* Si se envió el total de notificaciones no leídas */
                const total = respuesta.total_notificaciones_no_leidas; /* Guardo el total */
                
                // Actualizar botones del menú (usando clase ya que hay IDs duplicados)
                const botonesMenu = document.querySelectorAll('.boton-menu'); /* Obtengo ambos botones */
                botonesMenu.forEach(boton => { /* Recorro cada botón */
                    if(boton.id === 'menu-con-notificaciones') { /* Si es el botón CON notificaciones */
                        const contador = boton.querySelector('.cantidad-notificaciones'); /* Busco el contador */
                        if(contador) contador.textContent = total; /* Actualizo el contador */
                        boton.style.display = total > 0 ? 'inline-flex' : 'none'; /* Muestro u oculto */
                    } else if(boton.id === 'menu-sin-notificaciones') { /* Si es el botón SIN notificaciones */
                        boton.style.display = total > 0 ? 'none' : 'inline-flex'; /* Muestro u oculto */
                    }
                });
                
                // Actualizar enlaces de notificaciones
                const enlacesNotif = document.querySelectorAll('.enlace-notificaciones'); /* Obtengo ambos enlaces */
                enlacesNotif.forEach(enlace => { /* Recorro cada enlace */
                    if(enlace.id === 'enlace-con-notificaciones') { /* Si es el enlace CON notificaciones */
                        const contador = enlace.querySelector('.cantidad-notificaciones'); /* Busco el contador */
                        if(contador) contador.textContent = total; /* Actualizo el contador */
                        enlace.style.display = total > 0 ? 'flex' : 'none'; /* Muestro u oculto */
                    } else if(enlace.id === 'enlace-sin-notificaciones') { /* Si es el enlace SIN notificaciones */
                        enlace.style.display = total > 0 ? 'none' : 'flex'; /* Muestro u oculto */
                    }
                });
            }
            
            if(nombreModal == null && mensaje == null && cancelar == null) return; /* Si no hay modal que mostrar, salgo */
            
            if(cancelar) modal(nombreModal, mensaje, true); /* Si requiere confirmación, muestro modal con botones */
            else modal(nombreModal, mensaje, false); /* Si no, muestro modal solo informativo */

            document.getElementById("cantidad-carrito").textContent = recibir().length; /* Actualizo el contador del carrito en la interfaz */
            // Actualizar el botón de la tarjeta si se elimina desde el modal del carrito
            if(accion === 'eliminar') { /* Si la acción es eliminar un juego */
                // Busca el botón de la tarjeta en la lista de juegos
                let botonTarjeta = document.getElementById('tarjeta-eliminar' + id_juego); /* Obtengo el botón de la tarjeta */
                // Solo actualiza si es distinto al botón pasado como parámetro
                if(botonTarjeta && botonTarjeta !== boton) {
                    botonTarjeta.onclick = function() { /* Actualizo el evento onclick del botón */
                        mandar('agregar', id_juego, nombreModal, '<h1>Juego añadido al carrito</h1>', false, null, botonTarjeta); /* Cambio a función de agregar al carrito */
                    };
                    let spanBoton = botonTarjeta.querySelector('span'); /* Selecciono el texto del botón */
                    let imgBoton = botonTarjeta.querySelector('img'); /* Selecciono la imagen del botón */
                    if(spanBoton) spanBoton.textContent = 'Añadir al carrito'; /* Cambio el texto a Añadir */
                    if(imgBoton) imgBoton.src = '../recursos/imagenes/carrito2.png'; /* Cambio la imagen a carrito vacío */
                    botonTarjeta.id = botonTarjeta.id.replace('tarjeta-eliminar', 'tarjeta-anadir'); /* Cambio el ID del botón */
                }
            } else if(accion === 'vaciar') { /* Si la acción es vaciar el carrito */
                // Actualiza todos los botones de las tarjetas a "Añadir al carrito"
                idsCarrito.forEach(idJuego => { /* Recorro todos los IDs de juegos que estaban en el carrito */
                    let botonTarjeta = document.getElementById('tarjeta-eliminar' + idJuego); /* Busca el botón de la tarjeta en la lista de juegos */
                    if(botonTarjeta) { /* Si encuentra el botón */
                        botonTarjeta.onclick = function() { /* Actualizo el evento onclick del botón */
                            mandar('agregar', idJuego, 'modal1', '<h1>Juego añadido al carrito</h1>', false, null, botonTarjeta); /* Cambio a función de agregar al carrito */
                        };
                        let spanBoton = botonTarjeta.querySelector('span'); /* Selecciono el texto del botón */
                        let imgBoton = botonTarjeta.querySelector('img'); /* Selecciono la imagen del botón */
                        if(spanBoton) spanBoton.textContent = 'Añadir al carrito'; /* Cambio el texto a Añadir */
                        if(imgBoton) imgBoton.src = '../recursos/imagenes/carrito2.png'; /* Cambio la imagen a carrito vacío */
                        botonTarjeta.id = botonTarjeta.id.replace('tarjeta-eliminar', 'tarjeta-anadir'); /* Cambio el ID del botón */
                    }
                });
            }
            // Actualizar el botón pulsado si existe
            if(boton) { /* Si se pasó un botón como parámetro */
                // Actualizo el botón según la acción realizada
                if(boton.id.startsWith('tarjeta-anadir')) { /* Si el botón es de añadir */
                    const carritoActualizado = recibir(); /* Obtengo el carrito actualizado */
                    let nombreJuegoActualizado = ""; /* Variable para almacenar el nombre del juego */
                    if(carritoActualizado) { /* Si se pudo obtener el carrito actualizado */
                        for(let i = 0; i < carritoActualizado.length; i++) { /* Recorro los juegos del carrito */
                            if(carritoActualizado[i].id == id_juego) {  /* Si encuentro el juego agregado */
                                nombreJuegoActualizado = carritoActualizado[i].nombre; /* Almaceno el nombre del juego */
                                break; /* Salgo del bucle */
                            }
                        }
                    }
                    const nombreCapturado = nombreJuegoActualizado; /* Capturo el nombre del juego para usar en el cierre del modal */
                    boton.onclick = function() { /* Actualizo el evento onclick del botón */
                        eliminarDelCarrito(id_juego, nombreCapturado, boton); /* Cambio a función de eliminar del carrito */
                    };
                    boton.querySelector('span').textContent = 'Quitar del carrito'; /* Cambio el texto a Quitar del carrito */
                    boton.querySelector('img').src = '../recursos/imagenes/en_carrito2.png'; /* Cambio la imagen a carrito lleno */
                    boton.id = boton.id.replace('tarjeta-anadir', 'tarjeta-eliminar'); /* Cambio el ID del botón */
                } else if(boton.id.startsWith('tarjeta-eliminar')) { /* Si el botón es de eliminar */
                    boton.onclick = function() { /* Actualizo el evento onclick del botón */
                        mandar('agregar', id_juego, nombreModal, '<h1>Juego añadido al carrito</h1>', false, null, boton); /* Cambio a función de agregar al carrito */
                    };
                    boton.querySelector('span').textContent = 'Añadir al carrito'; /* Cambio el texto a Añadir al carrito */
                    boton.querySelector('img').src = '../recursos/imagenes/carrito2.png'; /* Cambio la imagen a carrito vacío */
                    boton.id = boton.id.replace('tarjeta-eliminar', 'tarjeta-anadir'); /* Cambio el ID del botón */
                } else if(boton.id.startsWith('reserva-pedir')) { /* Si el botón es de pedir reserva */
                    const nombreJuego = carrito[0].nombre; /* Capturo el nombre del juego */
                    const precioJuego = carrito[0].precio; /* Capturo el precio del juego */
                    boton.onclick = function() { /* Actualizo el evento onclick del botón */
                        cancelarSolicitud("cancelar_solicitud_reserva", id_juego, nombreJuego, precioJuego, boton); /* Cambio a función de cancelar solicitud de reserva */
                    };
                    boton.querySelector('span').textContent = 'Cancelar solicitud'; /* Cambio el texto a Cancelar solicitud */
                    boton.querySelector('img').src = '../recursos/imagenes/cancelar_solicitud.png'; /* Cambio la imagen a cancelar solicitud */
                    boton.id = boton.id.replace('reserva-pedir', 'reserva-cancelar'); /* Cambio el ID del botón */
                } else if(boton.id.startsWith('reserva-cancelar')) { /* Si el botón es de cancelar reserva */
                    const hiddenReserva = document.getElementById('reserva-json' + id_juego); /* Obtengo el input oculto con el JSON original */
                    const reservaJson = hiddenReserva ? hiddenReserva.value : null; /* Uso su valor si existe */
                    boton.onclick = function() { /* Actualizo el evento onclick del botón */
                        if(reservaJson) { /* Si existe el JSON original */
                            mostrarResumenPedido(reservaJson, true, 'reserva', boton); /* Muestro el resumen con el JSON original */
                        } else { /* Si no existe el JSON original */
                            mostrarResumenPedido(JSON.stringify(carrito), true, 'reserva', boton); /* Uso el carrito actual si falta el oculto */
                        }
                    };
                    boton.querySelector('span').textContent = 'Solicitar reserva'; /* Cambio el texto a Solicitar reserva */
                    boton.querySelector('img').src = '../recursos/imagenes/reservable.png'; /* Cambio la imagen a reservable */
                    boton.id = boton.id.replace('reserva-cancelar', 'reserva-pedir'); /* Cambio el ID del botón */
                } else if(boton.id.startsWith('devolucion-pedir')) { /* Si el botón es de pedir devolución */
                    const hiddenDevolucion = document.getElementById('devolucion-json' + id_juego); /* Obtengo el input oculto con el JSON original */
                    const devolucionJson = hiddenDevolucion ? hiddenDevolucion.value : null; /* Uso su valor si existe */
                    boton.onclick = function() { /* Actualizo el evento onclick del botón */
                        if(devolucionJson) { /* Si existe el JSON original */
                            // Decodificar entidades HTML antes de parsear
                            const textarea = document.createElement('textarea'); /* Creo un textarea temporal */
                            textarea.innerHTML = devolucionJson; /* Asigno el JSON escapado al innerHTML */
                            const devolucionDecodificado = textarea.value; /* Obtengo el valor decodificado */
                            const devolucionData = JSON.parse(devolucionDecodificado); /* Parseo el JSON decodificado */
                            cancelarSolicitud("cancelar_solicitud_devolucion", devolucionData[0].id, devolucionData[0].nombre, devolucionData[0].precio, boton); /* Cambio a función de cancelar solicitud de devolución */
                        } else { /* Si no existe el JSON original */
                            modal("modal1", "<h1>Error: No se encontraron los datos del juego</h1>", false); /* Muestro mensaje de error */
                        }
                    };
                    boton.querySelector('span').textContent = 'Cancelar devolución'; /* Cambio el texto a Cancelar solicitud */
                    boton.querySelector('img').src = '../recursos/imagenes/rechazar_devolucion.png'; /* Cambio la imagen a rechazar devolución */
                    boton.id = boton.id.replace('devolucion-pedir', 'devolucion-cancelar'); /* Cambio el ID del botón */
                } else if(boton.id.startsWith('devolucion-cancelar')) { /* Si el botón es de cancelar devolución */
                    const hiddenDevolucion = document.getElementById('devolucion-json' + id_juego); /* Obtengo el input oculto con el JSON original */
                    const devolucionJson = hiddenDevolucion ? hiddenDevolucion.value : null; /* Uso su valor si existe */
                    boton.onclick = function() { /* Actualizo el evento onclick del botón */
                        if(devolucionJson) { /* Si existe el JSON original */
                            // Decodificar entidades HTML antes de parsear
                            const textarea = document.createElement('textarea'); /* Creo un textarea temporal */
                            textarea.innerHTML = devolucionJson; /* Asigno el JSON escapado al innerHTML */
                            const devolucionDecodificado = textarea.value; /* Obtengo el valor decodificado */
                            const devolucionData = JSON.parse(devolucionDecodificado); /* Parseo el JSON decodificado */
                            descambiarJuego(devolucionData[0].id, devolucionData[0].precio, devolucionData[0].nombre, boton); /* Llamo a descambiarJuego con el botón */
                        } else { /* Si no existe el JSON original */
                            descambiarJuego(carrito[0].id, carrito[0].precio, carrito[0].nombre, boton); /* Uso el carrito actual si falta el oculto */
                        }
                    };
                    boton.querySelector('span').textContent = 'Solicitar devolución'; /* Cambio el texto a Solicitar devolución */
                    boton.querySelector('img').src = '../recursos/imagenes/descambiar.png'; /* Cambio la imagen a descambiar */
                    boton.id = boton.id.replace('devolucion-cancelar', 'devolucion-pedir'); /* Cambio el ID del botón */
                }
            }
        }
    };
    xhttp.open("POST", "../acciones/acciones_carrito.php", true); /* Configuro petición POST asíncrona */
    xhttp.send(datos); /* Envío los datos al servidor */
}

// Función que calcula el precio total de todos los juegos en el carrito
function calcularTotalCarrito(carrito) {
    let total = 0; /* Inicializo contador del total */
    for(let i = 0; i < carrito.length; i++) { /* Recorro todos los juegos del carrito */
        total += parseFloat(carrito[i].precio); /* Sumo el precio de cada juego al total */
    }
    return total.toFixed(2).replace('.', ','); /* Devuelvo el total formateado en español con coma decimal */
}

// Función que elimina un juego específico del carrito con confirmación
function eliminarDelCarrito(id_juego, nombre, boton) {
    let mensaje = `<h1>¿Está seguro de eliminar el artículo "${nombre}" del carrito?</h1>`; /* Creo mensaje de confirmación */
    modal("modal2", mensaje, true); /* Muestro modal de confirmación */

    document.getElementById(`aceptar-modal2`).addEventListener('click', function() { /* Si confirma la eliminación */
        // Cerrar modal de confirmación verificando que existe
        const modal2 = document.getElementById("modal2"); /* Busco modal de confirmación */
        if (modal2) document.body.removeChild(modal2); /* Cierro modal de confirmación solo si existe */

        // Ejecutar eliminación del producto
        mandar("eliminar", id_juego, "modal3", "<h1>Juego eliminado correctamente</h1>", false, null, boton); /* Elimino el juego del carrito */
    });
}

// Función que muestra el carrito completo en un modal
function mostrarCarrito() {
    const carrito = recibir(); /* Obtengo los datos del carrito desde el servidor */
    if (!carrito) { /* Si no se pudo obtener el carrito */        
        // Mostrar mensaje de error según el tipo
        if (carrito && carrito.error) { /* Si hay un error específico del servidor */
            modal("modal1", `<h1>Error: ${carrito.error}</h1>`, false); /* Muestro el error específico */
        } else { /* Si es un error genérico */
            modal("modal1", "<h1>Error al cargar el carrito</h1>", false); /* Muestro error genérico */
        }
        return; /* Salgo de la función */
    }

    // Construir HTML del carrito con todos los productos
    let contenedor = `<div class="contenido-carrito">
                        <h1>Carrito</h1>
                        <div id="opciones-carrito">                            
                            <button id="tramitar-pedido">Tramitar pedido - Total: ${calcularTotalCarrito(carrito) == "0,00" ? "Gratis" : calcularTotalCarrito(carrito) + " €"} (${carrito.length} productos)</button>
                            <hr>
                        </div>
                        <div id="productos">`; /* Inicio estructura HTML del carrito con título, botones integrados y productos */

    // Generar HTML para cada producto del carrito
    for(let i = 0; i < carrito.length; i++) { /* Recorro todos los productos del carrito */
        contenedor += `<div class="producto">
                            <div class="producto-imagen">
                                <img src="../${carrito[i].portada}" alt="${carrito[i].nombre}">
                            </div>
                            <div class="producto-info">
                                <h3>${carrito[i].nombre}</h3>
                                <p>Tipo: ${carrito[i].tipo}</p>
                                <p>Precio: ${carrito[i].precio == 0.00 ? "Gratis" : `${parseFloat(carrito[i].precio).toFixed(2).replace('.', ',')} €`}</p>
                                <p class="producto-resumen">${carrito[i].resumen}</p>
                                <button id="eliminar${carrito[i].id}" class="boton-eliminar-juego">Eliminar del carrito</button>
                            </div>
                        </div>`; /* Añado HTML de cada producto con imagen, info y botón eliminar */
    }

    // Finalizar HTML del carrito
    contenedor += `     </div>
                        <div id="opciones-carrito">
                            <hr>
                            <button id="vaciar-carrito">Vaciar carrito</button>
                        </div>
                    </div>`; /* Cierro estructura HTML del carrito */

    // Mostrar carrito si tiene productos
    if(carrito.length > 0) { /* Si el carrito no está vacío */
        modal("modal1", contenedor, false); /* Muestro el modal con todo el contenido del carrito */

        // Configurar eventos para botones de eliminar productos individuales
        for(let i = 0; i < carrito.length; i++) { /* Recorro todos los productos para añadir eventos */
            let juego = carrito[i]; /* Obtengo referencia al juego actual */
            
            document.getElementById(`eliminar${juego.id}`).addEventListener('click', function() { /* Añado evento click al botón eliminar */
                let mensaje = `<h1>¿Está seguro de eliminar el artículo "${juego.nombre}" del carrito?</h1>`; /* Creo mensaje de confirmación */
                modal("modal2", mensaje, true); /* Muestro modal de confirmación */

                document.getElementById(`aceptar-modal2`).addEventListener('click', function() { /* Si confirma la eliminación */
                    // Cerrar modal de confirmación verificando que existe
                    const modal2 = document.getElementById("modal2"); /* Busco modal de confirmación */
                    if (modal2) document.body.removeChild(modal2); /* Cierro modal de confirmación solo si existe */
                    
                    mandar("eliminar", juego.id, "modal3", "<h1>Juego eliminado correctamente</h1>", false, null, null); /* Elimino el juego del carrito */

                    setTimeout(function() { /* Espero un momento para que se procese la eliminación */
                        // Actualizar interfaz después de eliminar
                        const nuevoCarrito = recibir(); /* Obtengo el carrito actualizado */
                        const cantidadElemento = document.getElementById("cantidad-carrito"); /* Busco elemento contador */
                        if (cantidadElemento && nuevoCarrito) { /* Si existen ambos elementos */
                            cantidadElemento.textContent = nuevoCarrito.length; /* Actualizo el contador */
                        }
                        
                        // Cerrar modales verificando que existen
                        const modal3 = document.getElementById("modal3"); /* Busco modal de confirmación */
                        if (modal3) document.body.removeChild(modal3); /* Cierro modal de confirmación solo si existe */

                        const modal1 = document.getElementById("modal1"); /* Busco modal del carrito */
                        if (modal1) document.body.removeChild(modal1); /* Cierro carrito actual solo si existe */
                        mostrarCarrito(); /* Muestro carrito actualizado */
                    }, 1000); /* Espero 1 segundo */
                });
            });
        }

        // Configurar evento para botón de vaciar carrito completo
        document.getElementById("vaciar-carrito").addEventListener('click', () => { /* Añado evento al botón vaciar */
            let mensaje = `<h1>¿Esta seguro de vaciar el carrito?</h1>`; /* Creo mensaje de confirmación */
            modal("modal2", mensaje, true); /* Muestro modal de confirmación */

            document.getElementById(`aceptar-modal2`).addEventListener('click', () => { /* Si confirma vaciar */
                // Cerrar modal de confirmación verificando que existe
                const modal2_vaciar = document.getElementById("modal2"); /* Busco modal de confirmación */
                if (modal2_vaciar) document.body.removeChild(modal2_vaciar); /* Cierro modal de confirmación solo si existe */

                // Ejecutar vaciado completo del carrito
                mandar("vaciar", null, "modal3", "<h1>Carrito vaciado correctamente</h1>", false); /* Vacío todo el carrito */
                
                setTimeout( () => { /* Espero un momento para que se procese */
                    // Actualizar interfaz después de vaciar
                    const cantidadElemento = document.getElementById("cantidad-carrito"); /* Busco contador del carrito */
                    if (cantidadElemento) { /* Si existe el elemento */
                        cantidadElemento.textContent = "0"; /* Lo pongo a cero */
                    }
                    const modal3 = document.getElementById("modal3"); /* Busco modal de confirmación */
                    if (modal3) document.body.removeChild(modal3); /* Cierro modal de confirmación solo si existe */
                    // Cerrar carrito actual verificando que existe
                    const modal1_vaciar = document.getElementById("modal1"); /* Busco modal del carrito */
                    if (modal1_vaciar) document.body.removeChild(modal1_vaciar); /* Cierro el carrito actual solo si existe */
                    mostrarCarrito(); /* Muestro el carrito actualizado (vacío) */
                }, 1000); /* Espero 1 segundo */
            });
        });

        // Configurar evento para botón de tramitar pedido
        document.getElementById("tramitar-pedido").addEventListener('click', () => { /* Añado evento al botón tramitar pedido */
            // Cerrar modal del carrito verificando que existe
            const modal1_tramitar = document.getElementById("modal1"); /* Busco modal del carrito */
            if (modal1_tramitar) document.body.removeChild(modal1_tramitar); /* Cierro el carrito actual solo si existe */
            
            // Mostrar modal de resumen del pedido antes del método de pago
            mostrarResumenPedido(carrito); /* Llamo función que muestra el resumen del pedido */
        });


    } else { /* Si el carrito está vacío */
        modal("modal1", "<h1>El carrito está vacío</h1>", false); /* Muestro mensaje de carrito vacío */
    }
}

// Función que muestra el resumen del pedido antes del método de pago
function mostrarResumenPedido(carrito, compra_unica = null, solicitud = null, boton = null) {
    if(compra_unica) { /* Si es compra única, convierto el carrito de JSON escapado a objeto */
        // Decodificar entidades HTML
        const textarea = document.createElement('textarea'); /* Creo un textarea temporal */
        textarea.innerHTML = carrito; /* Asigno el carrito codificado al innerHTML */
        const carritoDecodificado = textarea.value; /* Obtengo el valor decodificado */
        carrito = JSON.parse(carritoDecodificado); /* Convierto el JSON decodificado a objeto */
    }

    // Calcular estadísticas del pedido
    const totalPedido = calcularTotalCarrito(carrito); /* Obtengo el total del carrito */
    const numeroProductos = carrito.length; /* Obtengo la cantidad de productos */

    // Construir HTML del resumen del pedido
    let contenidoResumen = `<div class="contenido-resumen-pedido">
                                <h1>Resumen del Pedido</h1>
                                <div class="estadisticas-pedido">
                                    <p><strong>Total de productos:</strong> ${numeroProductos}</p>
                                </div>
                                <div class="estadisticas-pedido">
                                    <p><strong>Total a pagar:</strong> ${totalPedido == "0,00" ? "Gratis" : `${totalPedido} €`}</p>
                                </div>
                                <hr>
                                <div class="lista-productos-resumen">
                                    <h2>Productos en tu pedido:</h2>`; /* Inicio estructura HTML del resumen */

    // Generar lista resumida de productos
    for (let i = 0; i < carrito.length; i++) { /* Recorro todos los productos del carrito */
        contenidoResumen += `<div class="producto">
                                <div class="producto-imagen">
                                    <img src="../${carrito[i].portada}" alt="${carrito[i].nombre}">
                                </div>
                                <div class="producto-info">
                                    <h3>${carrito[i].nombre}</h3>
                                    <p>Tipo: ${carrito[i].tipo}</p>
                                    <p>Precio: ${carrito[i].precio == 0.00 ? "Gratis" : `${parseFloat(carrito[i].precio).toFixed(2).replace('.', ',')} €`}</p>
                                    <p class="producto-resumen">${carrito[i].resumen}</p>
                                </div>
                            </div>`; /* Añado cada producto al resumen con imagen, nombre, tipo y precio */
    }

    if(solicitud == "reserva") { /* Si la solicitud es una reserva */
        // Finalizar HTML del resumen y añadir botones
        contenidoResumen += `   </div>
                                    <hr>
                                    <div class="botones-resumen-pedido">
                                        <button id="solicitar-reserva" class="boton-pagar">Solicitar Reserva</button>
                                        <button id="cancelar-reserva" class="boton-cancelar">Cancelar</button>
                                    </div>
                                </div>`; /* Cierro estructura y añado botones de acción */
    } else { /* Si es un pedido normal */
        // Finalizar HTML del resumen y añadir botones
        contenidoResumen += `   </div>
                                    <hr>
                                    <div class="botones-resumen-pedido">
                                        <button id="confirmar-pago" class="boton-pagar">Proceder al Pago</button>
                                        <button id="cancelar-pedido" class="boton-cancelar">Cancelar</button>
                                    </div>
                                </div>`; /* Cierro estructura y añado botones de acción */
    }

    // Mostrar modal con el resumen del pedido SIN botones por defecto
    modal("modal1", contenidoResumen, false); /* Muestro el modal con el resumen completo */
    
    // Eliminar los botones por defecto del modal si existen
    setTimeout(function() { /* Espero un momento para que se cree completamente el modal */
        const modalElemento = document.getElementById("modal1"); /* Obtengo el elemento del modal */
        if (modalElemento) { /* Si el modal existe */
            const botonesModal = modalElemento.querySelector("#botones"); /* Busco el div de botones por defecto */
            if (botonesModal) { /* Si existe el div de botones */
                botonesModal.remove(); /* Lo elimino completamente del DOM */
            }
        }
    }, 10); /* Timeout muy corto para que no se vea el parpadeo */

    const botonSolicitarReserva = document.getElementById("solicitar-reserva"); /* Busco el botón de solicitar reserva */
    const botonConfirmarPago = document.getElementById("confirmar-pago"); /* Busco el botón de confirmar pago */

    if(botonSolicitarReserva) { /* Si el botón de solicitar reserva existe */
        botonSolicitarReserva.addEventListener('click', () => { /* Evento para solicitar reserva */
            // Cerrar modal de resumen
            const modalResumen = document.getElementById("modal1"); /* Busco el modal de resumen */
            if (modalResumen) document.body.removeChild(modalResumen); /* Cierro el modal de resumen */
            mandar("solicitar_reserva", carrito[0].id, "modal2", "<h1>Reserva solicitada correctamente</h1>", false, carrito[0].precio, boton, carrito, null); /* Realizo la solicitud de reserva */
        });

        document.getElementById("cancelar-reserva").addEventListener('click', () => { /* Evento para cancelar la reserva */
            modal("modal2", "<h1>¿Está seguro de cancelar la solicitud de reserva?</h1>", true); /* Muestro modal de confirmación */
            document.getElementById("aceptar-modal2").addEventListener('click', () => { /* Si confirma la cancelación */
                const modal2 = document.getElementById("modal2"); /* Busco el modal de confirmación */
                if (modal2) document.body.removeChild(modal2); /* Cierro el modal de confirmación */
                // Cerrar modal de resumen
                const modalResumen = document.getElementById("modal1"); /* Busco el modal de resumen */
                if (modalResumen) document.body.removeChild(modalResumen); /* Cierro el modal de resumen */
                // Ejecutar cancelación de la solicitud de reserva
                mandar("cancelar_solicitud_reserva", carrito[0].id, "modal2", "<h1>Solicitud de reserva cancelada correctamente</h1>", false, totalPedido); /* Cancelo la solicitud de reserva */
                setTimeout(() => { /* Espero un momento para que se procese */
                    const modal2 = document.getElementById("modal2"); /* Busco el modal de éxito */
                    if (modal2) document.body.removeChild(modal2); /* Cierro el modal de éxito solo si existe */
                }, 1500);
            });
        });
    }    
    
    if(botonConfirmarPago) { /* Si el botón de confirmar pago existe */
        document.getElementById("confirmar-pago").addEventListener('click', () => { /* Evento para proceder al pago */
            if(compra_unica) { /* Si es compra única, guardo el carrito en sessionStorage para que enviar() lo use */
                sessionStorage.setItem('carritoCompraUnica', JSON.stringify(carrito)); /* Guardo el carrito en sessionStorage */
            }
            
            // Cerrar modal de resumen
            const modalResumen = document.getElementById("modal1"); /* Busco el modal de resumen */
            if (modalResumen) document.body.removeChild(modalResumen); /* Cierro el modal de resumen */
            if(totalPedido != "0,00") { /* Si el total es distinto de 0.00, redirijo a la página de pago */
                window.location.href = "../vistas/pago.php"; /* Redirijo a la página de pago */
            } else { /* Si el total es 0.00 (juegos gratuitos), realizo el pedido directamente */
                if(compra_unica) mandar("realizar_pedido", null, "modal2", "<h1>Pedido realizado correctamente</h1>", false, totalPedido, null, carrito); /* Realizo el pedido pasando el carrito único si es compra única */
                else mandar("realizar_pedido", null, "modal2", "<h1>Pedido realizado correctamente</h1>", false, totalPedido); /* Si no es compra única, realizo el pedido sin pasar el carrito */
                setTimeout(() => { /* Espero un momento para que se procese */
                    const modal2 = document.getElementById("modal2"); /* Busco el modal de éxito */
                    if (modal2) document.body.removeChild(modal2); /* Cierro el modal de éxito */
                    window.location.href = "../publico/index.php"; /* Redirijo a la página principal */
                }, 1000); /* Cierro el modal de éxito después de un momento */
            }
        });

        document.getElementById("cancelar-pedido").addEventListener('click', () => { /* Evento para cancelar el pedido */
            modal("modal2", "<h1>¿Está seguro de cancelar el pedido?</h1>", true); /* Muestro modal de confirmación */
            document.getElementById("aceptar-modal2").addEventListener('click', () => { /* Si confirma la cancelación */
                const modal2 = document.getElementById("modal2"); /* Busco el modal de confirmación */
                if (modal2) document.body.removeChild(modal2); /* Cierro el modal de confirmación */
                // Cerrar modal de resumen y volver al carrito
                const modalResumen = document.getElementById("modal1"); /* Busco el modal de resumen */
                if (modalResumen) document.body.removeChild(modalResumen); /* Cierro el modal de resumen */
                // Ejecutar cancelación del pedido señalando si es compra única o no
                if(compra_unica) mandar("cancelar_pedido", null, "modal2", "<h1>Pedido cancelado correctamente</h1>", false, totalPedido, null, carrito); /* Cancelo el pedido pasando el carrito único */
                else mandar("cancelar_pedido", null, "modal2", "<h1>Pedido cancelado correctamente</h1>", false, totalPedido); /* Cancelo el pedido */
                setTimeout(() => { /* Espero un momento para que se procese */
                    const modal2 = document.getElementById("modal2"); /* Busco el modal de éxito */
                    if (modal2) document.body.removeChild(modal2); /* Cierro el modal de éxito solo si existe */
                    if(!compra_unica) mostrarCarrito(); /* Vuelvo a mostrar el carrito normal */
                }, 1500);
            });
        });
    }
}

/* Funciones los campos relacionados con el pago: número de tarjeta, fecha de expiración, CVC y titular */

// Función que formatea el número de tarjeta con espacios cada 4 dígitos
function formatearNumeroTarjeta() {
    const input = document.getElementById('numero-tarjeta'); /* Obtengo el campo de número de tarjeta */
    if (!input) return; /* Si no existe el campo, salgo */

    input.addEventListener('input', function(e) { /* Añado evento cuando el usuario escribe */
        let valor = e.target.value.replace(/\s/g, ''); /* Elimino todos los espacios */
        let nuevoValor = ''; /* Variable para el nuevo valor formateado */

        /* Solo permito números */
        valor = valor.replace(/\D/g, ''); /* Elimino todo lo que no sea número */

        /* Añado espacios cada 4 dígitos */
        for (let i = 0; i < valor.length; i++) { /* Recorro cada dígito */
            if (i > 0 && i % 4 === 0) { /* Cada 4 dígitos */
                nuevoValor += ' '; /* Añado un espacio */
            }
            nuevoValor += valor[i]; /* Añado el dígito */
        }

        e.target.value = nuevoValor; /* Actualizo el valor del campo */
    });
}

// Función que formatea la fecha de expiración
function formatearFechaExpiracion() {
    const input = document.getElementById('fecha-expiracion'); /* Obtengo el campo de fecha */
    if (!input) return; /* Si no existe el campo, salgo */

    input.addEventListener('input', function(e) { /* Añado evento cuando el usuario escribe */
        let valor = e.target.value.replace(/\s/g, '').replace(/\//g, ''); /* Elimino espacios y barras */
        
        /* Solo permito números */
        valor = valor.replace(/\D/g, ''); /* Elimino todo lo que no sea número */

        /* Limito a 4 dígitos máximo (MMAA) */
        if (valor.length > 4) { /* Si tiene más de 4 dígitos */
            valor = valor.substring(0, 4); /* Lo corto a 4 */
        }

        /* Formateo con barra después del mes */
        let nuevoValor = ''; /* Variable para el nuevo valor */
        
        if (valor.length === 1) { /* Si solo hay 1 dígito */
            let primerDigito = parseInt(valor); /* Obtengo el primer dígito */
            if (primerDigito > 1) { /* Si es mayor que 1 (2-9) */
                nuevoValor = '0' + valor + ' / '; /* Añado 0 delante y la barra */
            } else { /* Si es 0 o 1 */
                nuevoValor = valor; /* Mantengo el valor sin formato */
            }
        } else if (valor.length >= 2) { /* Si hay 2 o más dígitos */
            let mes = valor.substring(0, 2); /* Obtengo los dos primeros dígitos (mes) */
            
            /* Valido que el mes esté entre 01 y 12 */
            let mesNum = parseInt(mes); /* Convierto el mes a número */
            if (mesNum < 1) { /* Si es menor que 1 */
                mes = '01'; /* Lo pongo en 01 */
            } else if (mesNum > 12) { /* Si es mayor que 12 */
                mes = '12'; /* Lo pongo en 12 */
            }

            nuevoValor = mes + ' / ' + valor.substring(2); /* Formato: MM / AA */
        } else { /* Si no hay dígitos */
            nuevoValor = valor; /* Mantengo el valor vacío */
        }

        e.target.value = nuevoValor; /* Actualizo el valor del campo */
    });

    /* Validación cuando el usuario sale del campo */
    input.addEventListener('blur', function(e) { /* Evento cuando pierde el foco */
        let valor = e.target.value.replace(/\s/g, '').replace(/\//g, ''); /* Limpio el valor */
        
        if (valor.length === 4) { /* Si tiene los 4 dígitos completos */
            let mes = parseInt(valor.substring(0, 2)); /* Obtengo el mes */
            let anio = parseInt('20' + valor.substring(2, 4)); /* Obtengo el año completo (20XX) */
            
            /* Obtengo fecha actual */
            let fechaActual = new Date(); /* Fecha actual */
            let mesActual = fechaActual.getMonth() + 1; /* Mes actual (0-11, por eso sumo 1) */
            let anioActual = fechaActual.getFullYear(); /* Año actual completo */

            /* Valido que la fecha no sea anterior a la actual */
            if (anio < anioActual || (anio === anioActual && mes < mesActual)) { /* Si es fecha pasada */
                e.target.value = ''; /* Limpio el campo */
                modal("modal1", "<h1>La fecha de expiración no puede ser anterior a la fecha actual</h1>", false); /* Muestro mensaje de error */
            }
        }
    });
}

// Función que limita el CVC a solo dígitos
function limitarCVC() {
    const input = document.getElementById('cvc'); /* Obtengo el campo de CVC */
    if (!input) return; /* Si no existe el campo, salgo */

    input.addEventListener('input', function(e) { /* Añado evento cuando el usuario escribe */
        let valor = e.target.value; /* Obtengo el valor actual */
        
        /* Solo permito números */
        valor = valor.replace(/\D/g, ''); /* Elimino todo lo que no sea número */
        
        e.target.value = valor; /* Actualizo el valor del campo */
    });
}

// Función que limita el titular a solo letras y espacios
function limitarTitular() {
    const input = document.getElementById('titular'); /* Obtengo el campo del titular */
    if (!input) return; /* Si no existe el campo, salgo */

    input.addEventListener('input', function(e) { /* Añado evento cuando el usuario escribe */
        let valor = e.target.value; /* Obtengo el valor actual */
        
        /* Solo permito letras (mayúsculas y minúsculas) y espacios */
        valor = valor.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, ''); /* Elimino todo lo que no sea letra o espacio */
        
        e.target.value = valor; /* Actualizo el valor del campo */
    });
}


/* Validación de los campos del formulario de pago y envío de datos */

// Función que valida los campos del formulario de pago
function validarPago() {
    const inputTarjeta = document.getElementById('numero-tarjeta'); /* Obtengo el campo de número de tarjeta */
    const inputFecha = document.getElementById('fecha-expiracion'); /* Obtengo el campo de fecha de expiración */
    const inputCVC = document.getElementById('cvc'); /* Obtengo el campo de CVC */
    const inputTitular = document.getElementById('titular'); /* Obtengo el campo de titular */

    if (!inputTarjeta || !inputFecha || !inputCVC || !inputTitular) return false; /* Faltan campos en el DOM */

    /* Número de tarjeta: 16 dígitos */
    const tarjetaDigitos = (inputTarjeta.value || '').replace(/\D/g, ''); /* Solo dígitos */
    if (tarjetaDigitos.length < 16) { /* Si tiene menos de 16 dígitos */
        modal("modal1", "<h1>El número de tarjeta debe tener 16 dígitos</h1>", false); /* Muestro mensaje de error */
        inputTarjeta.focus(); /* Pongo el foco en el campo */
        return false; /* Salgo de la función */
    }

    /* Fecha de expiración: MM / AA, no pasada */
    const fechaCruda = (inputFecha.value || '').replace(/\s/g, '').replace(/\//g, ''); /* Solo dígitos */
    if (fechaCruda.length !== 4) { /* Si no tiene 4 dígitos */
        modal("modal1", "<h1>Completa la fecha de expiración (MM / AA)</h1>", false); /* Muestro mensaje de error */
        inputFecha.focus(); /* Pongo el foco en el campo */
        return false; /* Salgo de la función */
    }
    let mes = parseInt(fechaCruda.substring(0, 2)); /* Obtengo el mes */
    let aa = parseInt(fechaCruda.substring(2, 4)); /* Obtengo el año (AA) */
    if (isNaN(mes) || mes < 1 || mes > 12) { /* Si el mes no es válido */
        modal("modal1", "<h1>El mes debe estar entre 01 y 12</h1>", false); /* Muestro mensaje de error */
        inputFecha.focus(); /* Pongo el foco en el campo */
        return false; /* Salgo de la función */
    }
    const anio = 2000 + aa; /* Convertir AA a 20AA */
    const hoy = new Date(); /* Fecha actual */
    const mesActual = hoy.getMonth() + 1; /* Mes actual (0-11, por eso sumo 1) */
    const anioActual = hoy.getFullYear(); /* Año actual completo */
    if (anio < anioActual || (anio === anioActual && mes < mesActual)) { /* Si la fecha es pasada */
        modal("modal1", "<h1>La fecha de expiración no puede ser anterior a la actual</h1>", false); /* Muestro mensaje de error */
        inputFecha.focus(); /* Pongo el foco en el campo */
        return false; /* Salgo de la función */
    }

    /* CVC: mínimo 3 y máximo 4 dígitos */
    const cvc = (inputCVC.value || '').replace(/\D/g, ''); /* Solo dígitos */
    if (cvc.length < 3) { /* Si tiene menos de 3 dígitos */
        modal("modal1", "<h1>El CVC debe tener al menos 3 dígitos</h1>", false); /* Muestro mensaje de error */
        inputCVC.focus(); /* Pongo el foco en el campo */
        return false; /* Salgo de la función */
    }

    /* Titular: mínimo 6 caracteres y que parezca nombre y apellidos */
    const titular = (inputTitular.value || '').trim(); /* Obtengo el valor y elimino espacios al inicio y final */
    if (titular.length < 6 || !titular.includes(' ')) { /* Si tiene menos de 6 caracteres o no tiene espacio */
        modal("modal1", "<h1>Introduce el nombre del titular (nombre y apellidos)</h1>", false); /* Muestro mensaje de error */
        inputTitular.focus(); /* Pongo el foco en el campo */
        return false; /* Salgo de la función */
    }

    return true; /* Todo correcto */
}

// Función que envía los datos del pago tras validación
function enviar() {
    if (sessionStorage.getItem('devolucion')) { /* Si es una devolución, obtengo los datos desde sessionStorage */
        /* Mostrar un breve estado de procesamiento y luego confirmar */
        modal("modal1", "<h1>Procesando reembolso...</h1>", false);

        let contenedorBotones = document.getElementById("botones"); /* Obtengo el contenedor de botones del modal */
        if (contenedorBotones) { /* Si existe el contenedor */
            contenedorBotones.style.display = "none"; /* Oculto los botones del modal si existen */
            const hr = document.querySelector('#modal2 hr'); /* Obtengo línea separadora */
            if (hr) hr.style.display = 'none'; /* Oculto línea */
        }
        
        const id_juego = JSON.parse(sessionStorage.getItem('id_juego_reembolso')); /* Obtengo el ID del juego a devolver */
        const total = JSON.parse(sessionStorage.getItem('total_reembolso')); /* Obtengo el total a reembolsar */

        setTimeout(() => { /* Simulo tiempo de procesamiento */
            const m1 = document.getElementById("modal1"); /* Obtengo el modal de procesamiento */
            if (m1) document.body.removeChild(m1); /* Cierro el modal de procesamiento */
            modal("modal2", "<h1>Reembolso realizado correctamente</h1>", false); /* Muestro modal de éxito */
            
            // Oculto el botón de cerrar del modal de éxito
            setTimeout(() => { /* Timeout muy corto para que no se vea el parpadeo */
                const botonCerrar = document.getElementById('cerrar-modal2'); /* Obtengo botón cerrar */
                if (botonCerrar) botonCerrar.style.display = 'none'; /* Oculto botón */
                const hr = document.querySelector('#modal2 hr'); /* Obtengo línea separadora */
                if (hr) hr.style.display = 'none'; /* Oculto línea */
            }, 0);
            
            setTimeout(() => { /* Espero un momento */
                if (document.getElementById("modal2")) document.body.removeChild(document.getElementById("modal2")); /* Cierro el modal de éxito */
                mandar("realizar_devolucion", id_juego, null, null, null, total, null, null, null); /* Realizo la devolución */
                setTimeout(() => { /* Espero un momento para que se procese */
                    // Limpiar sessionStorage
                    sessionStorage.removeItem('devolucion'); /* Elimino marca de devolución */
                    sessionStorage.removeItem('id_juego_reembolso'); /* Elimino ID del juego */
                    sessionStorage.removeItem('total_reembolso'); /* Elimino total a reembolsar */
                    window.location.href = "../publico/index.php"; /* Redirijo a la página principal */
                }, 1000);
            }, 1000);
        }, 900);
    } else { /* Si es un pago normal */
        /* Mostrar un breve estado de procesamiento y luego confirmar */
        modal("modal1", "<h1>Procesando pago...</h1>", false);
        
        // Oculto el botón de cerrar del modal de éxito
        setTimeout(() => { /* Timeout muy corto para que no se vea el parpadeo */
            const botonCerrar = document.getElementById('cerrar-modal1'); /* Obtengo botón cerrar */
            if (botonCerrar) botonCerrar.style.display = 'none'; /* Oculto botón */
            const hr = document.querySelector('#modal1 hr'); /* Obtengo línea separadora */
            if (hr) hr.style.display = 'none'; /* Oculto línea */
        }, 0);
        
        // Verificar si hay un carrito de compra única en sessionStorage
        const carritoCompraUnica = sessionStorage.getItem('carritoCompraUnica'); /* Obtengo el carrito de compra única si existe */
        let carrito; /* Variable para el carrito a usar */
        
        if(carritoCompraUnica) { /* Si es compra única, uso el carrito guardado */
            carrito = JSON.parse(carritoCompraUnica); /* Convierto el JSON a objeto */
        } else { /* Si es compra normal, obtengo el carrito del servidor */
            carrito = recibir(); /* Obtengo el carrito actual */
        }
        
        setTimeout(() => { /* Simulo tiempo de procesamiento */
            const m1 = document.getElementById("modal1"); /* Obtengo el modal de procesamiento */
            if (m1) document.body.removeChild(m1); /* Cierro el modal de procesamiento */
            modal("modal2", "<h1>Pago realizado correctamente</h1>", false); /* Muestro modal de éxito */
            // Oculto el botón de cerrar del modal de éxito
            setTimeout(() => { /* Timeout muy corto para que no se vea el parpadeo */
                const botonCerrar = document.getElementById('cerrar-modal2'); /* Obtengo botón cerrar */
                if (botonCerrar) botonCerrar.style.display = 'none'; /* Oculto botón */
                const hr = document.querySelector('#modal2 hr'); /* Obtengo línea separadora */
                if (hr) hr.style.display = 'none'; /* Oculto línea */
            }, 0);
            setTimeout(() => { /* Espero un momento */
                if (document.getElementById("modal2")) document.body.removeChild(document.getElementById("modal2")); /* Cierro el modal de éxito */
                // Solo paso carrito como carrito_unico si realmente es compra única
                if(carritoCompraUnica) { /* Si es compra única */
                    if(sessionStorage.getItem('reserva')) { /* Si es una reserva */
                        mandar("realizar_reserva", null, null, null, null, calcularTotalCarrito(carrito), null, carrito); /* Paso el carrito como carrito_unico */
                        sessionStorage.removeItem('reserva'); /* Elimino marca de reserva */
                    } else { /* Si es un pedido normal */
                        mandar("realizar_pedido", null, null, null, null, calcularTotalCarrito(carrito), null, carrito); /* Paso el carrito como carrito_unico */
                    }
                } else { /* Si es compra normal */
                    if(sessionStorage.getItem('reserva')) { /* Si es una reserva */
                        mandar("realizar_reserva", null, null, null, null, calcularTotalCarrito(carrito), null, null); /* No paso carrito_unico */
                        sessionStorage.removeItem('reserva'); /* Elimino marca de reserva */
                    } else { /* Si es un pedido normal */
                        mandar("realizar_pedido", null, null, null, null, calcularTotalCarrito(carrito), null, null); /* No paso carrito_unico */
                    }
                }
                setTimeout(() => { /* Espero un momento para que se procese */
                    // Limpiar sessionStorage si era compra única
                    if(carritoCompraUnica) { /* Si era compra única */
                        sessionStorage.removeItem('carritoCompraUnica'); /* Elimino el carrito de compra única */
                    }
                    window.location.href = "../publico/index.php"; /* Redirijo a la página principal */
                }, 1000);
            }, 1500);
        }, 900);
    }
}

/* Inicializar funciones de formateo cuando cargue la página */
document.addEventListener('DOMContentLoaded', function() { /* Cuando el DOM esté listo */
    // Limpiar datos de devolución incompletos o antiguos
    const esDevolucion = sessionStorage.getItem('devolucion'); /* Compruebo si hay marca de devolución */
    const tieneIdJuego = sessionStorage.getItem('id_juego_reembolso'); /* Compruebo si hay ID del juego */
    const tieneTotal = sessionStorage.getItem('total_reembolso'); /* Compruebo si hay total a reembolsar */
    
    // Si hay marca de devolución pero faltan datos, limpiar todo
    if (esDevolucion && (!tieneIdJuego || !tieneTotal)) {
        sessionStorage.removeItem('devolucion'); /* Elimino marca de devolución */
        sessionStorage.removeItem('id_juego_reembolso'); /* Elimino ID del juego */
        sessionStorage.removeItem('total_reembolso'); /* Elimino total a reembolsar */
    }
    
    formatearNumeroTarjeta(); /* Activo formateo de número de tarjeta */
    formatearFechaExpiracion(); /* Activo formateo de fecha de expiración */
    limitarCVC(); /* Activo limitación de CVC */
    limitarTitular(); /* Activo limitación del titular a solo letras */
    
    /* Vincular botón pagar */
    const boton = document.querySelector('.boton-pagar'); /* Selecciono el botón pagar */
    if (boton) { /* Si el botón existe */
        boton.addEventListener('click', function() { /* Añado evento click */
            if (validarPago()) enviar(); /* Si la validación es correcta, envío los datos */
        });
        if(sessionStorage.getItem('devolucion')) { /* Si es una devolución, cambio el texto del botón */
            boton.textContent = "Confirmar Devolución"; /* Cambio el texto del botón */
        }
    }

    // Evento para botón de tarjeta
    const botonTarjeta = document.getElementById('boton-tarjeta'); // obtengo botón de tarjeta
    if (botonTarjeta) { // si existe
        botonTarjeta.addEventListener('click', function() { // añado evento click
            // Creo contenido del modal con formulario de tarjeta
            const contenidoModal = `
                <h2>Ingresa los datos de tu tarjeta</h2>
                <form class="formulario-pago" autocomplete="off">
                <label for="numero-tarjeta" class="campo-pago">Número de tarjeta:</label>
                <input type="text" id="numero-tarjeta" inputmode="numeric" placeholder="1234 5678 9012 3456" maxlength="19" class="input-pago" required/>
                
                <div class="fila-pago">
                    <div>
                    <label for="fecha-expiracion" class="campo-pago">MM / AA:</label>
                    <input type="text" id="fecha-expiracion" inputmode="numeric" placeholder="MM / AA" maxlength="7" class="input-pago" required/>
                    </div>
                    <div>
                    <label for="cvc" class="campo-pago">CVC:</label>
                    <input type="text" id="cvc" inputmode="numeric" placeholder="123" maxlength="4" class="input-pago" required/>
                    </div>
                </div>

                <label for="titular" class="campo-pago">Nombre del titular:</label>
                <input type="text" id="titular" placeholder="Nombre y apellidos" class="input-pago" required/>

                <button type="button" class="boton-pagar">Pagar</button>
                </form>
            `; // contenido HTML del formulario
            
            modal('modal-tarjeta', contenidoModal, false); // muestro modal
            
            // Oculto el botón de cerrar y el hr del modal de tarjeta
            setTimeout(() => { /* Timeout muy corto para que no se vea el parpadeo */
                const botonCerrar = document.getElementById('cerrar-modal-tarjeta'); // obtengo botón cerrar
                if (botonCerrar) botonCerrar.style.display = 'none'; // oculto botón
                const hr = document.querySelector('#modal-tarjeta hr'); // obtengo línea separadora
                if (hr) hr.style.display = 'none'; // oculto línea
            }, 0);
            
            // Inicializo funciones de formateo para los campos del formulario
            setTimeout(() => { // espero un momento para que se cree el formulario
                formatearNumeroTarjeta(); // formateo número de tarjeta
                formatearFechaExpiracion(); // formateo fecha de expiración
                limitarCVC(); // limito CVC a números
                limitarTitular(); // limito titular a letras
                
                // Añado evento al botón pagar del modal
                const botonPagar = document.querySelector('#modal-tarjeta .boton-pagar'); // obtengo botón pagar
                if (botonPagar) { // si existe
                    botonPagar.addEventListener('click', function() { // añado evento click
                        if (validarPago()) enviar(); // valido y envío si es correcto
                    });
                }
            }, 0);
        });
    }
});

// Función para iniciar el proceso de descambio/devolución de un juego
function descambiarJuego(id_juego, total, nombre, boton) {
    modal("modal1", "<h1>¿Estás seguro de que deseas descambiar el juego " + nombre + "?</h1>", true); /* Muestro modal de confirmación */
    document.getElementById('cancelar-modal1').addEventListener('click', function() { /* Si cancela la devolución */
        const modal1 = document.getElementById("modal1"); /* Obtengo el modal 1 */
        if (modal1) document.body.removeChild(modal1); /* Cierro el modal 1 */
        mandar("cancelar_devolucion", id_juego, "modal2", "<h1>Devolución cancelada</h1>", false, total); /* Cancelo la devolución */
    });
    document.getElementById('aceptar-modal1').addEventListener('click', function() { /* Si confirma la devolución */
        const modal1 = document.getElementById("modal1"); /* Obtengo el modal 1 */
        if (modal1) document.body.removeChild(modal1); /* Cierro el modal 1 */
        // Mostrar modal para indicar el motivo de la devolución
        let mensaje = `<h1>Indique a continuación el motivo de la devolución:</h1>
                    <textarea id='motivo-devolucion' rows='4' cols='50' placeholder='Escriba aquí el motivo de la devolución...' maxlength='500'></textarea>
                    <br>
                    <div id="advertencia-devolucion">
                        <p>Una vez confirme la devolución, será redirigido a la página de pago en donde deberá completar los datos de la tarjeta donde recibirá un reembolso de ${parseFloat(total).toFixed(2).replace('.', ',')} €.</p>
                    </div>`; /* Mensaje con textarea para el motivo */
        modal("modal2", mensaje, false); /* Muestro modal para indicar el motivo */
        
        // Usar setTimeout para asegurar que el DOM se actualice
        setTimeout(function() { /* Espero un momento para que se cree completamente el modal */
            const botonCerrar = document.getElementById('cerrar-modal2'); /* Obtengo el botón de cerrar */

            if (botonCerrar) { /* Si el botón existe */
                // Primero eliminar el evento anterior de cerrar
                const nuevoBoton = botonCerrar.cloneNode(true); /* Clono el botón para eliminar eventos */
                botonCerrar.parentNode.replaceChild(nuevoBoton, botonCerrar); /* Reemplazo el botón antiguo por el nuevo sin eventos */
                
                nuevoBoton.textContent = "Confirmar Devolución"; /* Cambio el texto del botón */
                nuevoBoton.id = 'confirmar-devolucion'; /* Cambio el ID del botón */
                
                // Añadir evento al nuevo botón
                nuevoBoton.addEventListener('click', function(e) { /* Evento para confirmar la devolución */
                    e.preventDefault(); /* Prevengo acción por defecto */
                    e.stopPropagation(); /* Detengo propagación del evento */
                    
                    const textareaMotivo = document.getElementById('motivo-devolucion'); /* Obtengo el textarea del motivo */
                    
                    if (!textareaMotivo) { /* Si no existe el textarea */
                        modal("modal3", "<h1>Error: No se encontró el campo de motivo</h1>", false); /* Muestro mensaje de error */
                        return; /* Salgo de la función */
                    }
                    
                    const motivo = textareaMotivo.value.trim(); /* Obtengo el motivo y elimino espacios al inicio y final */
                    
                    if (motivo === '') { /* Si el motivo está vacío */
                        modal("modal3", "<h1>Debe indicar el motivo de la devolución</h1>", false); /* Muestro mensaje de error */
                        return; /* Salgo de la función */
                    }
                    if (motivo.length < 10) { /* Si el motivo tiene menos de 10 caracteres */
                        modal("modal3", "<h1>El motivo debe tener al menos 10 caracteres</h1>", false); /* Muestro mensaje de error */
                        return; /* Salgo de la función */
                    }
                    
                    const modal2 = document.getElementById("modal2"); /* Obtengo el modal 2 */
                    if (modal2) document.body.removeChild(modal2); /* Cierro el modal 2 */
                    mandar("solicitar_devolucion", id_juego, "modal2", "<h1>Solicitud de devolución enviada correctamente</h1>", false, total, boton, null, motivo); /* Realizo la solicitud de devolución */
                    
                    setTimeout(() => { /* Espero un momento para que se procese */
                        const modal2 = document.getElementById("modal2"); /* Busco el modal de éxito */
                        if (modal2) document.body.removeChild(modal2); /* Cierro el modal de éxito */
                    }, 1000);
                });
            }
        }, 50);        
    });
}

// Función para cancelar una solicitud de reserva o devolución
function cancelarSolicitud(tipo_solicitud, id_juego, nombre, total, boton) {
    if(tipo_solicitud === "cancelar_solicitud_reserva") { /* Si es cancelación de reserva */
        modal("modal1", "<h1>¿Estás seguro de que deseas cancelar solicitud de reserva del juego " + nombre + "?</h1>", true); /* Muestro modal de confirmación */
    } else if(tipo_solicitud === "cancelar_solicitud_devolucion") { /* Si es cancelación de devolución */
        modal("modal1", "<h1>¿Estás seguro de que deseas cancelar solicitud de devolución del juego " + nombre + "?</h1>", true); /* Muestro modal de confirmación */
    }
    document.getElementById('cancelar-modal1').addEventListener('click', function() { /* Si cancela la cancelación */
        const modal1 = document.getElementById("modal1"); /* Obtengo el modal 1 */
        if (modal1) document.body.removeChild(modal1); /* Cierro el modal 1 */
        if(tipo_solicitud === "cancelar_solicitud_reserva") { /* Si es cancelación de reserva */
            mandar(tipo_solicitud, id_juego, "modal2", "<h1>Cancelación de solicitud de reserva abortada</h1>", false, total); /* Cancelo la cancelación */
        } else if(tipo_solicitud === "cancelar_solicitud_devolucion") { /* Si es cancelación de devolución */
            mandar(tipo_solicitud, id_juego, "modal2", "<h1>Cancelación de solicitud de devolución abortada</h1>", false, total); /* Cancelo la cancelación */
        }
    });
    document.getElementById('aceptar-modal1').addEventListener('click', function() { /* Si confirma la cancelación */
        const modal1 = document.getElementById("modal1"); /* Obtengo el modal 1 */
        if (modal1) document.body.removeChild(modal1); /* Cierro el modal 1 */
        // Mostrar modal para indicar el motivo de la cancelación
        let mensaje = `<h1>Indique a continuación el motivo de la cancelación: </h1>
                    <textarea id='motivo-cancelacion' rows='4' cols='50' placeholder='Escriba aquí el motivo de la cancelación...' maxlength='500'></textarea>
                    <br>
                    <div id="advertencia-cancelacion">
                        <p>Una vez confirme la cancelación, su solicitud será eliminada.</p>
                    </div>`; /* Mensaje con textarea para el motivo */
        modal("modal2", mensaje, false); /* Muestro modal para indicar el motivo */
        
        // Usar setTimeout para asegurar que el DOM se actualice
        setTimeout(function() { /* Espero un momento para que se cree completamente el modal */
            const botonCerrar = document.getElementById('cerrar-modal2'); /* Obtengo el botón de cerrar */

            if (botonCerrar) { /* Si el botón existe */
                // Primero eliminar el evento anterior de cerrar
                const nuevoBoton = botonCerrar.cloneNode(true); /* Clono el botón para eliminar eventos */
                botonCerrar.parentNode.replaceChild(nuevoBoton, botonCerrar); /* Reemplazo el botón antiguo por el nuevo sin eventos */
                
                nuevoBoton.textContent = "Confirmar Cancelación de Solicitud"; /* Cambio el texto del botón */
                nuevoBoton.id = 'confirmar-solicitud'; /* Cambio el ID del botón */
                
                // Añadir evento al nuevo botón
                nuevoBoton.addEventListener('click', function(e) { /* Evento para confirmar la cancelación */
                    e.preventDefault(); /* Prevengo acción por defecto */
                    e.stopPropagation(); /* Detengo propagación del evento */
                    
                    const textareaMotivo = document.getElementById('motivo-cancelacion'); /* Obtengo el textarea del motivo */
                    
                    if (!textareaMotivo) { /* Si no existe el textarea */
                        modal("modal3", "<h1>Error: No se encontró el campo de motivo</h1>", false); /* Muestro mensaje de error */
                        return; /* Salgo de la función */
                    }
                    
                    const motivo = textareaMotivo.value.trim(); /* Obtengo el motivo y elimino espacios al inicio y final */
                    
                    if (motivo === '') { /* Si el motivo está vacío */
                        modal("modal3", "<h1>Debe indicar el motivo de la cancelación</h1>", false); /* Muestro mensaje de error */
                        return; /* Salgo de la función */
                    }
                    if (motivo.length < 10) { /* Si el motivo tiene menos de 10 caracteres */
                        modal("modal3", "<h1>El motivo debe tener al menos 10 caracteres</h1>", false); /* Muestro mensaje de error */
                        return; /* Salgo de la función */
                    }
                    
                    const modal2 = document.getElementById("modal2"); /* Obtengo el modal 2 */
                    if (modal2) document.body.removeChild(modal2); /* Cierro el modal 2 */
                    if(tipo_solicitud === "cancelar_solicitud_reserva") { /* Si es cancelación de reserva */
                        mandar(tipo_solicitud, id_juego, "modal2", "<h1>Solicitud de reserva cancelada correctamente</h1>", false, total, boton, null, motivo); /* Realizo la cancelación */
                    } else if(tipo_solicitud === "cancelar_solicitud_devolucion") { /* Si es cancelación de devolución */
                        mandar(tipo_solicitud, id_juego, "modal2", "<h1>Solicitud de devolución cancelada correctamente</h1>", false, total, boton, null, motivo); /* Realizo la cancelación */
                    }

                    setTimeout(() => { /* Espero un momento para que se procese */
                        const modal2 = document.getElementById("modal2"); /* Busco el modal de éxito */
                        if (modal2) document.body.removeChild(modal2); /* Cierro el modal de éxito */
                    }, 1000);
                });
            }
        }, 50);        
    });
}

// Función para completar una solicitud de reserva o devolución
function completarSolicitud(tipo_solicitud, id_juego) {
    if(tipo_solicitud === "reserva") { /* Si es reserva */
        modal("modal1", "<h1>¿Estás seguro de que deseas completar la reserva?</h1>", true); /* Muestro modal de confirmación */
    } else if(tipo_solicitud === "devolucion") { /* Si es devolución */
        modal("modal1", "<h1>¿Estás seguro de que deseas completar la devolución?</h1>", true); /* Muestro modal de confirmación */
    }

    document.getElementById('cancelar-modal1').addEventListener('click', function() { /* Si cancela la acción */
        const modal1 = document.getElementById("modal1"); /* Obtengo el modal 1 */
        if (modal1) document.body.removeChild(modal1); /* Cierro el modal 1 */
    });
    
    document.getElementById('aceptar-modal1').addEventListener('click', function() { /* Si confirma la acción */
        if(tipo_solicitud === "reserva") { /* Si es reserva */
            const hiddenReserva = document.getElementById('reserva-json' + id_juego); /* Obtengo el input oculto con el JSON original */
            const reservaJson = hiddenReserva ? hiddenReserva.value : null; /* Uso su valor si existe */
        
            if(reservaJson) { /* Si existe el JSON original */
                // Decodificar entidades HTML antes de parsear
                const textarea = document.createElement('textarea'); /* Creo un textarea temporal */
                textarea.innerHTML = reservaJson; /* Asigno el JSON escapado al innerHTML */
                const reservaDecodificado = textarea.value; /* Obtengo el valor decodificado */
                const reservaData = JSON.parse(reservaDecodificado); /* Parseo el JSON decodificado */
                
                if(reservaData[0].precio != "0,00") { /* Si el total es distinto de 0.00, redirijo a la página de pago */
                    sessionStorage.setItem('carritoCompraUnica', JSON.stringify(reservaData)); /* Guardo el carrito en sessionStorage */
                    sessionStorage.setItem('reserva', JSON.stringify(true)); /* Marco que es una reserva */
                    sessionStorage.setItem('id_juego_reserva', JSON.stringify(reservaData[0].id)); /* Guardo el ID del juego de la reserva */
                    window.location.href = "../vistas/pago.php"; /* Redirijo a la página de pago */
                } else { /* Si el total es 0.00 (juegos gratuitos), realizo el completado de la reserva directamente */
                    mandar("realizar_reserva", null, "modal2", "<h1>Reserva completada correctamente</h1>", false, calcularTotalCarrito(reservaData), null, reservaData); /* Realizo el completado de la reserva */
                    setTimeout(() => { /* Espero un momento para que se procese */
                        const modal2 = document.getElementById("modal2"); /* Busco el modal de éxito */
                        if (modal2) document.body.removeChild(modal2); /* Cierro el modal de éxito */
                        window.location.href = "../publico/index.php"; /* Redirijo a la página principal */
                    }, 1000); /* Cierro el modal de éxito después de un momento */
                }
            }
        } else if(tipo_solicitud === "devolucion") { /* Si es devolución */
            const hiddenDevolucion = document.getElementById('devolucion-json' + id_juego); /* Obtengo el input oculto con el JSON original */
            const devolucionJson = hiddenDevolucion ? hiddenDevolucion.value : null; /* Uso su valor si existe */
        
            if(devolucionJson) { /* Si existe el JSON original */
                // Decodificar entidades HTML antes de parsear
                const textarea = document.createElement('textarea'); /* Creo un textarea temporal */
                textarea.innerHTML = devolucionJson; /* Asigno el JSON escapado al innerHTML */
                const devolucionDecodificado = textarea.value; /* Obtengo el valor decodificado */
                const devolucionData = JSON.parse(devolucionDecodificado); /* Parseo el JSON decodificado */
                
                if(devolucionData[0].precio != 0.00) { /* Si el total a reembolsar es distinto de 0.00, redirijo a la página de pago */
                    // Guardar datos de la devolución en sessionStorage para usarlos en la página de pago
                    sessionStorage.setItem('devolucion', JSON.stringify(true)); /* Marco que es una devolución */
                    sessionStorage.setItem('id_juego_reembolso', JSON.stringify(devolucionData[0].id)); /* Guardo el ID del juego */
                    sessionStorage.setItem('total_reembolso', JSON.stringify(devolucionData[0].precio)); /* Guardo el total a reembolsar */
                    window.location.href = "../vistas/pago.php"; /* Redirijo a la página de pago */
                } else { /* Si el total es 0.00, realizo la devolución directamente */
                    const modal1 = document.getElementById("modal1"); /* Obtengo el modal 1 */
                    if (modal1) document.body.removeChild(modal1); /* Cierro el modal 1 */
                    mandar("realizar_devolucion", devolucionData[0].id, "modal2", "<h1>Devolución realizada correctamente</h1>", false, devolucionData[0].precio, null, null, null); /* Realizo la devolución */
                    setTimeout(() => { /* Espero un momento para que se procese */
                        const modal2 = document.getElementById("modal2"); /* Busco el modal de éxito */
                        if (modal2) document.body.removeChild(modal2); /* Cierro el modal de éxito */
                        window.location.href = "../publico/index.php"; /* Redirijo a la página principal */
                    }, 1000);
                }
            }
        }
    });
}