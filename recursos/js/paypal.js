
// Verifico si es flujo de reembolso o compra normal
const esReembolso = window.esReembolso;

// Función para obtener carrito de compra única desde sessionStorage
function obtenerCompraUnica() {
    try { /*Inicio bloque try para captuar excepciones*/
        const datos = sessionStorage.getItem('carritoCompraUnica'); // leo datos guardados
        return datos ? JSON.parse(datos) : []; // devuelvo array o vacío
    } catch(error) { /* si hay error */
        return []; // si hay error devuelvo vacío
    }
}

// Función para limpiar carrito de compra única
function limpiarCompraUnica() {
    try { /*Inicio bloque try para captuar excepciones*/
        sessionStorage.removeItem('carritoCompraUnica'); // borro del almacenamiento
    } catch(error) { /* si hay error */
        console.error('Error limpiando compra única:', error); // muestro error
    }
}

// Función para contar juegos en carrito del servidor
function contarJuegosEnCarrito() {
    try { /*Inicio bloque try para captuar excepciones*/
        if (typeof recibir === 'function') { // verifico que existe la función
            const carritoServidor = recibir(); // obtengo carrito
            if (Array.isArray(carritoServidor)) return carritoServidor.length; // cuento elementos
            return carritoServidor ? 1 : 0; // si es objeto cuento 1, si no 0
        }
    } catch(error) { /* si hay error */
        console.error('Error contando juegos en carrito:', error); // muestro error
    }
    return 0; // valor por defecto
}

// Función para esperar a que el carrito tenga cierta cantidad de juegos
async function esperarCarritoCargado(cantidadMinima, tiempoMaximo = 5000, intervalo = 150) {
    const tiempoInicio = Date.now(); // guardo hora de inicio
    return new Promise(resolver => { /* creo promesa */
        const verificar = () => { /* función para verificar */
            try { /*Inicio bloque try para captuar excepciones*/
                if (typeof recibir === 'function') { // verifico función
                    const carritoServidor = recibir(); // obtengo carrito
                    const cantidad = Array.isArray(carritoServidor) ? carritoServidor.length : (carritoServidor ? 1 : 0); // cuento
                    if (cantidad >= cantidadMinima) return resolver(true); // si llega a la cantidad resuelvo
                }
            } catch(error) { /* si hay error */
                console.error('Error esperando carrito cargado:', error); // muestro error
            }
            if (Date.now() - tiempoInicio >= tiempoMaximo) return resolver(false); // si se pasa el tiempo resuelvo false
            setTimeout(verificar, intervalo); // vuelvo a verificar después de intervalo
        };
        verificar(); // inicio verificación
    });
}

// Función para calcular el total del carrito
function calcularTotal() {
    const compraUnica = obtenerCompraUnica(); // obtengo compra única
    const tieneCompraUnica = Array.isArray(compraUnica) && compraUnica.length > 0; // verifico si hay
    const juegosEnServidor = contarJuegosEnCarrito(); // cuento juegos en servidor

    // Priorizar compra única: si existe, siempre uso ese total y no el carrito general
    if (tieneCompraUnica) {
        let totalCompra = 0; // inicio en 0
        for (const juego of compraUnica) totalCompra += parseFloat(juego?.precio || 0); // sumo precios
        return Math.max(0.01, Math.round((totalCompra + Number.EPSILON) * 100) / 100); // redondeo y devuelvo
    }

    // Si no hay compra única, uso el carrito del servidor si existe
    if (juegosEnServidor > 0) {
        try { /*Inicio bloque try para captuar excepciones*/
            if (typeof calcularTotalCarrito === 'function') { // verifico función
                const totalCarrito = calcularTotalCarrito(recibir()); // calculo total
                if (typeof totalCarrito === 'string') { // si es string
                    const numero = parseFloat(totalCarrito.replace(/\./g, '').replace(',', '.')); // convierto a número
                    if (!isNaN(numero)) return Math.max(0.01, Math.round((numero + Number.EPSILON) * 100) / 100); // redondeo
                }
                if (typeof totalCarrito === 'number') { // si es número
                    return Math.max(0.01, Math.round((totalCarrito + Number.EPSILON) * 100) / 100); // redondeo
                }
            }
        } catch (error) { /* si hay error */
            console.error('Error calculando total:', error); // muestro error
        }
    }

    return 1.00; // valor por defecto
}

// Función para sincronizar compra única al servidor
async function sincronizarCompraUnica() {
    const compraUnica = obtenerCompraUnica(); // obtengo compra única
    if (!Array.isArray(compraUnica) || compraUnica.length === 0) return; // si no hay salgo

    // Si el servidor no tiene juegos y hay función mandar
    if (contarJuegosEnCarrito() === 0 && typeof mandar === 'function') { /* verifico función */
        for (const juego of compraUnica) { // recorro cada juego
            try { /*Inicio bloque try para captuar excepciones*/
                mandar('agregar', juego.id); // añado al carrito
            } catch(error) { /* si hay error */
                console.error('Error agregando juego:', error); // muestro error
            }
            await new Promise(esperar => setTimeout(esperar, 200)); // espero 200ms entre cada uno
        }
        await esperarCarritoCargado(compraUnica.length, 5000, 150); // espero a que se carguen todos
    }
}

// Función para esperar a que se guarde el pedido (carrito se vacíe)
function esperarPedidoGuardado(tiempoMaximo = 8000, intervalo = 250) {
    return new Promise((resolver) => { /* creo promesa */
        const tiempoInicio = Date.now(); // guardo hora de inicio
        const verificar = () => { /* función para verificar */
            try { /*Inicio bloque try para captuar excepciones*/
                if (typeof recibir === 'function') { // verifico función
                    const carritoServidor = recibir(); // obtengo carrito
                    const estaVacio = Array.isArray(carritoServidor) ? carritoServidor.length === 0 : !carritoServidor; // verifico si vacío
                    if (estaVacio) return resolver(true); // si está vacío resuelvo
                }
            } catch (error) { /* si hay error */
                console.error('Error verificando pedido guardado:', error); // muestro error
            }
            if (Date.now() - tiempoInicio >= tiempoMaximo) return resolver(false); // si pasa el tiempo resuelvo false
            setTimeout(verificar, intervalo); // vuelvo a verificar
        };
        verificar(); // inicio verificación
    });
}

// Función para formatear precio en euros
function formatearEuros(cantidad) {
    const numero = Number(cantidad || 0); // convierto a número
    return numero.toFixed(2).replace('.', ',') + ' €'; // formato con coma y símbolo
}

// Función para procesar reembolso con PayPal
async function procesarReembolsoPayPal() {
    // Leo datos de la devolución
    const idJuego = JSON.parse(sessionStorage.getItem('id_juego_reembolso') || 'null'); // id del juego
    const importeTotal = JSON.parse(sessionStorage.getItem('total_reembolso') || 'null'); // precio
    
    // Verifico que tengo todos los datos
    if (!idJuego || importeTotal === null) {
        modal('modal1', '<h1>Error</h1><p>Faltan datos de la devolución. Vuelve a iniciar el proceso.</p>', false); // muestro error
        return; // salgo
    }

    // Muestro modal de procesando sin botones
    modal('modal-procesando', '<h1>Procesando reembolso...</h1><p>Por favor, espera un momento.</p>', false); // muestro modal

    // Oculto el botón de cerrar del modal de procesando
    setTimeout(() => {
        const botonCerrar = document.getElementById('cerrar-modal-procesando'); // obtengo botón cerrar
        if (botonCerrar) botonCerrar.style.display = 'none'; // oculto botón
        const hr = document.querySelector('#modal-procesando hr'); // obtengo línea separadora
        if (hr) hr.style.display = 'none'; // oculto línea
    }, 0);

    try { /*Inicio bloque try para captuar excepciones*/
        // Llamo al servidor para hacer refund en PayPal
        const respuesta = await fetch('../acciones/paypal/reembolsar_paypal.php', {
            method: 'POST', // método POST
            headers: { 'Content-Type': 'application/json' }, // cabeceras
            body: JSON.stringify({}) // envío vacío, usa datos de sesión
        });
        const datosRespuesta = await respuesta.json(); // leo respuesta

        // Cierro modal de procesando
        const modalProcesando = document.getElementById('modal-procesando'); // obtengo modal
        if (modalProcesando) document.body.removeChild(modalProcesando); // elimino modal

        // Verifico si el reembolso fue exitoso
        if (!datosRespuesta || datosRespuesta.ok !== true) {
            console.error('Error en reembolso:', datosRespuesta); // muestro error en consola
            modal('modal1', '<h1>Error</h1><p>No se pudo completar el reembolso.<br>' + (datosRespuesta?.error || 'Error desconocido.') + '</p>', false); // muestro modal
            return; // salgo
        }

        // Registro la devolución en mi base de datos
        try { /*Inicio bloque try para captuar excepciones*/
            if (typeof mandar === 'function') { // verifico función
                mandar('realizar_devolucion', idJuego, null, null, null, importeTotal, null, null, null); // registro
            } else { /* si no existe */
                console.warn('Función mandar() no encontrada'); // aviso
            }
        } catch (error) { /* si hay error */
            console.error('Error registrando devolución:', error); // muestro error
        }

        // Muestro mensaje de éxito
        modal('modal1', 
        '<h1>Reembolso completado</h1>' +
        '<p>El reembolso se realizó correctamente</p>' +
        '<p><strong>ID Reembolso:</strong> ' + (datosRespuesta.refund_id || '—') + '</p>' +
        '<p><strong>Importe:</strong> ' + (datosRespuesta.amount || importeTotal) + ' ' + (datosRespuesta.currency || 'EUR') + '</p>',
        false
        ); // muestro modal de éxito

        // Oculto el botón de cerrar del modal de éxito
        setTimeout(() => {
            const botonCerrar = document.getElementById('cerrar-modal1'); // obtengo botón cerrar
            if (botonCerrar) botonCerrar.style.display = 'none'; // oculto botón
            const hr = document.querySelector('#modal1 hr'); // obtengo línea separadora
            if (hr) hr.style.display = 'none'; // oculto línea
        }, 0);

        // Limpio datos del sessionStorage
        sessionStorage.removeItem('devolucion'); // borro marcador
        sessionStorage.removeItem('id_juego_reembolso'); // borro id
        sessionStorage.removeItem('total_reembolso'); // borro total

        // Redirijo al index después de 2 segundos
        setTimeout(() => {
            window.location.href = '../publico/index.php'; // redirijo
        }, 1000);

    } catch (error) { /* si hay error */
        // Cierro modal de procesando si hay error
        const modalProcesando = document.getElementById('modal-procesando'); // obtengo modal
        if (modalProcesando) document.body.removeChild(modalProcesando); // elimino modal

        console.error(error); // muestro error en consola
        modal('modal1', '<h1>Error de conexión</h1><p>No se pudo conectar con el servidor.</p>', false); // muestro modal
    } finally { /* si hay error o no */
        // Restauro botón
        const botonPayPal = document.querySelector('#boton-paypal button'); // obtengo botón
        if (botonPayPal) { /* si existe */
            botonPayPal.disabled = false; // habilito
            botonPayPal.innerText = botonPayPal.dataset.textoOriginal || 'Reembolsar con PayPal'; // restauro texto
        }
    }
}

// Cuando carga la página ejecuto esto
document.addEventListener('DOMContentLoaded', function () {
    const contenedorBoton = document.getElementById('boton-paypal'); // obtengo contenedor del botón
    if (!contenedorBoton) { // si no existe
        console.warn('No se encontró el contenedor del botón PayPal'); // aviso
        return; // salgo
    }

    // Si es flujo de reembolso
    if (esReembolso) {
        // Creo botón personalizado para reembolso
        contenedorBoton.innerHTML = `
        <button type="button" class="paypal-estilo">
            <img src="../recursos/imagenes/paypal.png" alt="PayPal" id="icono-paypal">
        </button>`; // inserto HTML del botón
        const botonReembolso = contenedorBoton.querySelector('button'); // obtengo botón
        botonReembolso.addEventListener('click', procesarReembolsoPayPal); // añado evento click
        return; // salgo
    }

    // Si es flujo de compra normal
    if (!window.paypal) { // verifico que esté cargado el SDK
        console.error('SDK de PayPal no cargado'); // muestro error
        return; // salgo
    }

    // Configuro botones de PayPal
    paypal.Buttons({
        fundingSource: paypal.FUNDING.PAYPAL, // solo muestro opción PayPal
        funding: {
            disallowed: [paypal.FUNDING.CARD] // oculto opción tarjeta
        },

        // Función que se ejecuta al crear orden
        createOrder: function () {
            let importeTotal = calcularTotal(); // calculo total del carrito
            importeTotal = Math.max(0.01, Math.round((importeTotal + Number.EPSILON) * 100) / 100); // redondeo mínimo 0.01
            // Llamo al servidor para crear orden en PayPal
            return fetch('../acciones/paypal/crear_orden.php', {
                method: 'POST', // método POST
                headers: { 'Content-Type': 'application/json' }, // cabeceras
                body: JSON.stringify({ total: importeTotal }) // envío total
            })
            .then(respuesta => respuesta.json()) // leo respuesta
            .then(datos => datos.id); // devuelvo id de orden
        },

        // Función que se ejecuta cuando usuario aprueba el pago
        onApprove: function (datosPayPal) {
            // Capturo el pago en PayPal
            return fetch('../acciones/paypal/capturar_orden.php', {
                method: 'POST', // método POST
                headers: { 'Content-Type': 'application/json' }, // cabeceras
                body: JSON.stringify({ orderID: datosPayPal.orderID }) // envío id de orden
            })
            .then(respuesta => respuesta.json()) // leo respuesta
            .then(async informacion => {
                // Verifico que se capturó correctamente
                if (!informacion || !informacion.ok) {
                    modal('modal1', '<h1>Error</h1><p>El pago no se pudo completar.</p>', false); // muestro error
                    console.error('Error en captura:', informacion); // muestro en consola
                    return; // salgo
                }

                // Obtengo compra única (si existe) y evito sincronizarla al carrito general
                const compraUnica = obtenerCompraUnica(); // obtengo compra única
                const tieneCompraUnica = Array.isArray(compraUnica) && compraUnica.length > 0; // verifico si hay
                if (!tieneCompraUnica) { // si NO es compra única
                    await sincronizarCompraUnica(); // sincronizo carrito al servidor
                }

                // Verifico si es una reserva desde sessionStorage
                const esReserva = sessionStorage.getItem('reserva') ? JSON.parse(sessionStorage.getItem('reserva')) : false; // leo flag de reserva
                const idJuegoReserva = esReserva ? JSON.parse(sessionStorage.getItem('id_juego_reserva') || 'null') : null; // id del juego de la reserva
                
                // Registro el pedido o reserva en mi base de datos
                try { /*Inicio bloque try para captuar excepciones*/
                    const importeTotal = calcularTotal(); // calculo total
                    if (typeof mandar === 'function') { // verifico función
                        if (esReserva && idJuegoReserva) { // si es una reserva
                            mandar('realizar_reserva', idJuegoReserva, null, null, null, importeTotal, null, tieneCompraUnica ? compraUnica : null); // registro reserva con id del juego y carrito único si aplica
                            sessionStorage.removeItem('reserva'); // limpio flag de reserva
                            sessionStorage.removeItem('id_juego_reserva'); // limpio id del juego
                            sessionStorage.removeItem('carritoCompraUnica'); // limpio carrito de compra única
                        } else { // si es un pedido normal o compra única
                            mandar('realizar_pedido', null, null, null, null, importeTotal, null, tieneCompraUnica ? compraUnica : null); // registro pedido usando carrito único si existe
                        }
                    } else { /* si no existe */
                        console.warn('Función mandar() no encontrada'); // aviso
                    }
                } catch (error) { /* si hay error */
                    console.error('Error registrando pedido/reserva:', error); // muestro error
                }

                await esperarPedidoGuardado(); // espero a que se guarde
                limpiarCompraUnica(); // limpio compra única

                // Muestro mensaje de éxito
                modal('modal1',
                '<h1>¡Pago completado!</h1>' +
                '<p>Tu compra se realizó correctamente</p>' +
                '<p><strong>ID Orden:</strong> ' + informacion.order_id + '</p>' +
                '<p><strong>ID Captura:</strong> ' + informacion.capture_id + '</p>',
                false
                ); // muestro modal

                // Redirijo al index después de 2 segundos
                setTimeout(() => {
                    window.location.href = '../publico/index.php'; // redirijo
                }, 2000);
            })
            .catch(error => { /* si hay error */
                console.error('Error en proceso de compra:', error); // muestro error
                modal('modal1', '<h1>Error</h1><p>Error procesando el pago.</p>', false); // muestro modal
            });
        },

        // Función que se ejecuta si hay error
        onError: function (error) {
            console.error('Error en PayPal:', error); // muestro error
            modal('modal1', '<h1>Error PayPal</h1><p>Ocurrió un error con el servicio de pago.</p>', false); // muestro modal
        }
    }).render('#boton-paypal'); // renderizo botones en el contenedor
});