// Función para mostrar/ocultar el submenú de edición
function submenuEdicion() {
    const submenu = document.getElementById('submenu-edicion'); /* Obtengo referencia al submenú */
    const flecha = document.getElementById('flecha-submenu-icono'); /* Obtengo la imagen de la flecha indicadora */
    
    if (submenu) { /* Si existe el submenú */
        if (submenu.style.display === 'none' || submenu.style.display === '') { /* Si está oculto */
            submenu.style.display = 'block'; /* Lo muestro */
            if (flecha) { /* Si existe la flecha */
                flecha.src = '../recursos/imagenes/flecha_arriba.png'; /* Cambio a flecha arriba */
                flecha.alt = 'Contraer'; /* Cambio el texto alternativo */
            }
        } else { /* Si está visible */
            submenu.style.display = 'none'; /* Lo oculto */
            if (flecha) { /* Si existe la flecha */
                flecha.src = '../recursos/imagenes/flecha_abajo.png'; /* Cambio a flecha abajo */
                flecha.alt = 'Desplegar'; /* Cambio el texto alternativo */
            }
        }
    }
}

// Función para volver al panel de administrador con el modo de edición guardado
function volverAlPanel(modoEdicion) {
    const modo = modoEdicion || 'juegos'; /* Uso el modo recibido o 'juegos' por defecto */
    window.location.href = '../vistas/panel_administrador.php?modo=' + modo; /* Redirijo al panel con el parámetro del modo */
}

// Variable global para detectar si es carga inicial
let esCargaInicial = true;

// Función para manejar la selección del modo de edición
document.addEventListener('DOMContentLoaded', function() { /* Cuando el DOM esté completamente cargado */
    const enlacesSubmenu = document.querySelectorAll('.enlace-submenu'); /* Obtengo todos los enlaces del submenú */
    const submenu = document.getElementById('submenu-edicion'); /* Submenú de edición */
    const flecha = document.getElementById('flecha-submenu-icono'); /* Icono de flecha del submenú */
    
    enlacesSubmenu.forEach(enlace => { /* Recorro cada enlace */
        enlace.addEventListener('click', function(e) { /* Añado evento click */
            e.preventDefault(); /* Evito la navegación por defecto */
            const modo = this.getAttribute('data-modo'); /* Obtengo el modo seleccionado */
            
            // Verificar si estamos en el panel de administrador
            const contenedor = document.getElementById('contenedor-panel-administrador');
            
            if (!contenedor) { /* Si NO estamos en panel_administrador.php */
                // Guardar el modo en sessionStorage y redirigir al panel
                sessionStorage.setItem('modo_edicion_pendiente', modo); /* Guardo el modo para cargarlo después */
                window.location.href = '../vistas/panel_administrador.php'; /* Redirijo al panel */
                return; /* Salgo de la función */
            }
            
            esCargaInicial = false; /* Marco que ya no es carga inicial */
            
            enlacesSubmenu.forEach(enlace => enlace.classList.remove('activo')); /* Quito la clase activo de todos los enlaces */
            this.classList.add('activo'); /* Activo solo el clicado */

            if (modo === 'juegos') { /* Si es edición de juegos */
                mostrarEdicionJuegos(); /* Llamo a la función de edición de juegos */
            } else if (modo === 'usuarios') { /* Si es edición de usuarios */
                mostrarEdicionUsuarios(); /* Llamo a la función de edición de usuarios */
            } else if (modo === 'pedidos') { /* Si es edición de pedidos */
                mostrarEdicionPedidos(); /* Llamo a la función de edición de pedidos */
            }
        });
    });

    if (submenu) { /* Si existe el submenú */
        // Verificar si estamos en panel_administrador.php
        const contenedor = document.getElementById('contenedor-panel-administrador');
        
        if (!contenedor) { /* Si NO estamos en panel_administrador.php */
            // No marcar ningún modo como activo
            enlacesSubmenu.forEach(enlace => enlace.classList.remove('activo')); /* Quito la clase activo de todos */
            esCargaInicial = false; /* Marco que ya no es carga inicial */
            return; /* Salgo sin cargar ningún modo */
        }
        
        // Verificar si hay un modo pendiente desde sessionStorage (redirección desde notificaciones o estadísticas)
        const modoPendiente = sessionStorage.getItem('modo_edicion_pendiente');
        
        // Determinar qué modo usar
        let modoGuardado; /* Variable para el modo guardado */
        if (modoPendiente) { /* Si hay un modo pendiente */
            modoGuardado = modoPendiente; /* Guardo el modo pendiente */
        } else if (window.modoEdicion) { /* Si no, compruebo si hay un modo guardado en la variable global */
            modoGuardado = window.modoEdicion; /* Guardo el modo guardado en la sesión */
        } else { /* Si no hay modo guardado */
            modoGuardado = 'juegos'; /* Mostrar juegos */
        }
        
        // Limpiar el modo pendiente del sessionStorage
        if (modoPendiente) { /* Si había un modo pendiente */
            sessionStorage.removeItem('modo_edicion_pendiente'); /* Lo elimino después de usarlo */
        }
        
        enlacesSubmenu.forEach(enlace => enlace.classList.remove('activo')); /* Quito la clase activo de todos los enlaces */
        
        let opcionGuardada; /* Variable para almacenar la opción a activar */
        if (modoGuardado === 'usuarios') { /* Si el modo guardado es usuarios */
            opcionGuardada = document.querySelector('.enlace-submenu[data-modo="usuarios"]'); /* Selecciono usuarios */
            if (opcionGuardada) { /* Si encontré la opción */
                opcionGuardada.classList.add('activo'); /* La marco como activa */
            }
            mostrarEdicionUsuarios(); /* Cargo directamente la vista de usuarios */
        } else if (modoGuardado === 'pedidos') { /* Si el modo guardado es pedidos */
            opcionGuardada = document.querySelector('.enlace-submenu[data-modo="pedidos"]'); /* Selecciono pedidos */
            if (opcionGuardada) { /* Si encontré la opción */
                opcionGuardada.classList.add('activo'); /* La marco como activa */
            }
            mostrarEdicionPedidos(); /* Cargo directamente la vista de pedidos */
        } else { /* Si no hay modo guardado o es juegos */
            opcionGuardada = document.querySelector('.enlace-submenu[data-modo="juegos"]'); /* Selecciono juegos por defecto */
            if (opcionGuardada) { /* Si encontré la opción */
                opcionGuardada.classList.add('activo'); /* La marco como activa */
            }
            mostrarEdicionJuegos(); /* Cargo directamente la vista de juegos */
        }
        
        esCargaInicial = false; /* Después de la carga inicial, marco como false para futuras llamadas */
    }
});

// Función para cargar estilos CSS dinámicamente según el modo de edición
function cargarEstilosPanelAdmin(modo) {
    const estiloAnterior = document.getElementById('estilos-panel-dinamicos'); /* Busco estilos previos en el panel */
    if (estiloAnterior) { /* Si existen */
        estiloAnterior.remove(); /* Los elimino */
    }
    
    // Crear nuevo link de estilos
    const link = document.createElement('link'); /* Creo elemento link */
    link.id = 'estilos-panel-dinamicos'; /* Le asigno ID para poder eliminarlo después */
    link.rel = 'stylesheet'; /* Tipo stylesheet */
    link.type = 'text/css'; /* Tipo CSS */
    
    // Asignar ruta según el modo
    if (modo === 'juegos') { /* Si es modo juegos */
        link.href = '../recursos/css/estilos_index.css'; /* Cargo estilos de index */
    } else if (modo === 'usuarios') { /* Si es modo usuarios */
        link.href = '../recursos/css/estilos_edicion_usuarios.css'; /* Cargo estilos de usuarios */
    } else if (modo === 'pedidos') { /* Si es modo pedidos */
        link.href = '../recursos/css/estilos_historial.css'; /* Cargo estilos de historial/pedidos */
    }
    
    document.head.appendChild(link); /* Añado el link al head del documento */
}

// Función genérica para cargar contenido mediante AJAX
function cargarContenidoAjax(url, contenedor, contenedorHTML = null, mensajeCarga = 'Cargando...', mensajeExito = 'Contenido cargado') {
    contenedor.innerHTML = `<div class="cargando"><h2>${mensajeCarga}</h2></div>`; /* Muestro un mensaje temporal de carga */
    
    // Realizar petición AJAX
    const xhttp = new XMLHttpRequest(); /* Creo objeto para petición AJAX */
    xhttp.onreadystatechange = function() { /* Defino qué hacer cuando cambie el estado */
        if (this.readyState == 4 && this.status == 200) { /* Si la petición se completó exitosamente */
            if (contenedorHTML) { /* Si se especificó un contenedor HTML */
                contenedor.innerHTML = contenedorHTML.replace('{contenido}', this.responseText); /* Uso el contenedor con el contenido */
            } else { /* Si no hay contenedor */
                contenedor.innerHTML = this.responseText; /* Cargo directamente el contenido */
            }
            
            // Ejecutar scripts inline que estén en el contenido cargado
            const scripts = contenedor.querySelectorAll('script:not([src])'); /* Obtengo todos los scripts inline */
            scripts.forEach(script => { /* Recorro cada script */
                const nuevoScript = document.createElement('script'); /* Creo un nuevo script */
                nuevoScript.textContent = script.textContent; /* Copio el contenido del script */
                document.body.appendChild(nuevoScript); /* Lo añado al body para ejecutarlo */
                nuevoScript.remove(); /* Lo elimino después de ejecutar */
            });
        } else if (this.readyState == 4) { /* Si la petición terminó pero con error */
            console.error('Error al cargar contenido: ' + this.status); /* Muestro error en consola */
            contenedor.innerHTML = '<div class="error"><h2>Error al cargar el contenido. Por favor, inténtelo de nuevo.</h2></div>'; /* Muestro mensaje de error */
        }
    };
    xhttp.open("GET", url, true); /* Configuro petición GET */
    xhttp.send(); /* Envío la petición */
}

// Función para limpiar el buscador en el panel de administrador
function limpiarBuscadorAdmin() {
    // Solo limpiar si NO es la carga inicial
    if (esCargaInicial) {
        return; /* No hacer nada en la carga inicial */
    }
    
	const cuadroBusqueda = document.getElementById('cuadro-busqueda'); /* Obtengo el input del buscador */
	const botonBuscarBtn = document.getElementById('boton-buscar'); /* Obtengo el botón de buscar */
	const botonLimpiarBusqueda = document.getElementById('boton-limpiar-busqueda'); /* Obtengo el botón de limpiar búsqueda */
	
    // Si existen los elementos
	if (botonBuscarBtn && botonLimpiarBusqueda && cuadroBusqueda) {
        cuadroBusqueda.value = ''; /* Limpio el valor del buscador */
        botonBuscarBtn.style.display = 'block'; /* Muestro el botón de buscar */
        botonLimpiarBusqueda.style.display = 'none'; /* Oculto el botón de limpiar búsqueda */
	}
}

// Función que carga y muestra la vista de edición de juegos
function mostrarEdicionJuegos() {
    const contenedor = document.getElementById('contenedor-panel-administrador'); /* Obtengo el contenedor principal */
    
    if (!contenedor) { /* Si no existe el contenedor */
        console.error('No se encontró el contenedor del panel'); /* Muestro error en consola */
        return; /* Salgo de la función */
    }
    
    const botonAgregarJuego = document.getElementById('boton-anadir-juego'); /* Obtengo el botón de agregar juego */
    const botonAgregarUsuario = document.getElementById('boton-anadir-usuario'); /* Obtengo el botón de agregar usuario */
    const botonDescargarPDF = document.getElementById('boton-descargar-pdf'); /* Obtengo el botón de descargar PDF */

    if(botonAgregarJuego) { /* Si existe el botón de agregar juego */
        botonAgregarJuego.style.display = 'flex'; /* Lo muestro */
    }

    if(botonAgregarUsuario) { /* Si existe el botón de agregar usuario */
        botonAgregarUsuario.style.display = 'none'; /* Lo oculto */
    }

    if(botonDescargarPDF) { /* Si existe el botón de descargar PDF */
        botonDescargarPDF.style.display = 'none'; /* Lo oculto */
    }

    const url = '../acciones/cargar_juegos.php'; /* URL del archivo PHP */
    const contenedorHTML = '<div class="juegos">{contenido}</div>'; /* Contenedor HTML */
    const mensajeCarga = 'Cargando juegos...'; /* Mensaje de carga */
    const mensajeExito = 'Vista de edición de juegos cargada'; /* Mensaje de éxito */
    
    // Actualizar el modo de edición global
    window.modoEdicion = 'juegos'; /* Actualizo la variable global para que los filtros sepan qué mostrar */
    
    // Limpiar el buscador al cambiar de modo
    limpiarBuscadorAdmin(); /* Limpio el buscador y restauro botones */
    
    // Actualizar el placeholder del buscador
    const cuadroBusqueda = document.getElementById('cuadro-busqueda'); /* Obtengo el input del buscador */
    if (cuadroBusqueda) { /* Si existe el buscador */
        cuadroBusqueda.placeholder = 'Buscar juegos, categorías...'; /* Cambio el placeholder a juegos */
    }
    
    cargarEstilosPanelAdmin('juegos'); /* Cargo los estilos correspondientes */
    
    // Cargar contenido de juegos usando función genérica
    cargarContenidoAjax(url, contenedor, contenedorHTML, mensajeCarga, mensajeExito);
}

// Función que carga y muestra la vista de edición de usuarios
function mostrarEdicionUsuarios() {
    const contenedor = document.getElementById('contenedor-panel-administrador'); /* Obtengo el contenedor principal */
    
    if (!contenedor) { /* Si no existe el contenedor */
        console.error('No se encontró el contenedor del panel'); /* Muestro error en consola */
        return; /* Salgo de la función */
    }

    const botonAgregarJuego = document.getElementById('boton-anadir-juego'); /* Obtengo el botón de agregar juego */
    const botonAgregarUsuario = document.getElementById('boton-anadir-usuario'); /* Obtengo el botón de agregar usuario */
    const botonDescargarPDF = document.getElementById('boton-descargar-pdf'); /* Obtengo el botón de descargar PDF */

    if(botonAgregarJuego) { /* Si existe el botón de agregar juego */
        botonAgregarJuego.style.display = 'none'; /* Lo oculto */
    }

    if(botonAgregarUsuario) { /* Si existe el botón de agregar usuario */
        botonAgregarUsuario.style.display = 'flex'; /* Lo muestro */
    }

    if(botonDescargarPDF) { /* Si existe el botón de descargar PDF */
        botonDescargarPDF.style.display = 'none'; /* Lo oculto */
    }
    
    const url = '../acciones/cargar_usuarios.php'; /* URL del archivo PHP */
    const contenedorHTML = null; /* Sin contenedor HTML */
    const mensajeCarga = 'Cargando usuarios...'; /* Mensaje de carga */
    const mensajeExito = 'Vista de edición de usuarios cargada'; /* Mensaje de éxito */

    // Actualizar el modo de edición global
    window.modoEdicion = 'usuarios'; /* Actualizo la variable global para que los filtros sepan qué mostrar */

    // Limpiar el buscador al cambiar de modo
    limpiarBuscadorAdmin(); /* Limpio el buscador y restauro botones */

    // Actualizar el placeholder del buscador
    const cuadroBusqueda = document.getElementById('cuadro-busqueda'); /* Obtengo el input del buscador */
    if (cuadroBusqueda) { /* Si existe el buscador */
        cuadroBusqueda.placeholder = 'Buscar usuarios, acrónimos...'; /* Cambio el placeholder a usuarios */
    }

    cargarEstilosPanelAdmin('usuarios'); /* Cargo los estilos correspondientes */
    
    // Cargar contenido de usuarios usando función genérica
    cargarContenidoAjax(url, contenedor, contenedorHTML, mensajeCarga, mensajeExito);
}

// Función que carga y muestra la vista de edición de pedidos
function mostrarEdicionPedidos() {
    const contenedor = document.getElementById('contenedor-panel-administrador'); /* Obtengo el contenedor principal */
    
    if (!contenedor) { /* Si no existe el contenedor */
        console.error('No se encontró el contenedor del panel'); /* Muestro error en consola */
        return; /* Salgo de la función */
    }

    const botonAgregarJuego = document.getElementById('boton-anadir-juego'); /* Botón agregar juego */
    const botonAgregarUsuario = document.getElementById('boton-anadir-usuario'); /* Botón agregar usuario */
    const botonDescargarPDF = document.getElementById('boton-descargar-pdf'); /* Obtengo el botón de descargar PDF */

    if(botonAgregarJuego) { /* Si existe el botón de agregar juego */
        botonAgregarJuego.style.display = 'none'; /* Lo oculto */
    }

    if(botonAgregarUsuario) { /* Si existe el botón de agregar usuario */
        botonAgregarUsuario.style.display = 'none'; /* Lo oculto */
    }

    if(botonDescargarPDF) { /* Si existe el botón de descargar PDF */
        botonDescargarPDF.style.display = 'flex'; /* Lo muestro */

        botonDescargarPDF.onclick = function() { /* Asigno función onclick para descargar PDF */
            // Mostrar mensaje de carga
            modal('modal-generando-pdf', '<h2>Generando PDF...</h2><p>Por favor, espere un momento.</p>', false);
            
            // Obtener datos del servidor usando XMLHttpRequest
            const xhttp = new XMLHttpRequest(); /* Creo objeto para petición AJAX */
            xhttp.onreadystatechange = function() { /* Defino qué hacer cuando cambie el estado */
                if (this.readyState == 4) { /* Si la petición terminó */
                    // Cerrar modal de carga
                    const modalCarga = document.getElementById('modal-generando-pdf'); /* Obtengo el modal de carga */
                    if (modalCarga) document.body.removeChild(modalCarga); /* Lo elimino */
                    
                    if (this.status == 200) { /* Si fue exitosa */
                        try { /* Inicio bloque try para capturar errores */
                            const data = JSON.parse(this.responseText); /* Parseo la respuesta JSON */
                            
                            if (data.error) { /* Si hay error en los datos */
                                modal('modal-error-pdf', '<h2>Error</h2><p>' + data.error + '</p>', false); /* Muestro el error */
                                return; /* Salgo de la función */
                            }
                            
                            // Generar el HTML del PDF
                            generarPDFEnNavegador(data);
                        } catch (error) { /* Si hay error al parsear JSON */
                            modal('modal-error-pdf', '<h2>Error</h2><p>Error al procesar la respuesta del servidor</p>', false); /* Muestro el error */
                        }
                    } else { /* Si la petición falló */
                        modal('modal-error-pdf', '<h2>Error</h2><p>Error al conectar con el servidor: ' + this.status + '</p>', false); /* Muestro el error */
                    }
                }
            };
            xhttp.open("GET", '../acciones/generar_pdf_pedidos.php', true); /* Configuro petición GET */
            xhttp.send(); /* Envío la petición */
        };
    }

    const url = '../acciones/cargar_pedidos.php'; /* URL del archivo PHP */
    const contenedorHTML = '<div id="contenedor-historial" class="contenedor-historial">{contenido}</div>'; /* Contenedor HTML */
    const mensajeCarga = 'Cargando pedidos...'; /* Mensaje de carga */
    const mensajeExito = 'Vista de edición de pedidos cargada'; /* Mensaje de éxito */

    // Actualizar el modo de edición global
    window.modoEdicion = 'pedidos'; /* Modo actual */

    // Limpiar el buscador al cambiar de modo
    limpiarBuscadorAdmin(); /* Limpio buscador y restauro botones */

    // Actualizar el placeholder del buscador
    const cuadroBusqueda = document.getElementById('cuadro-busqueda'); /* Obtengo el input del buscador */
    if (cuadroBusqueda) { /* Si existe el buscador */
        cuadroBusqueda.placeholder = 'Buscar pedidos, estados, método de pago...'; /* Cambio el placeholder para pedidos */
    }

    cargarEstilosPanelAdmin('pedidos'); /* Cargo estilos de pedidos */

    // Cargar contenido de pedidos usando función genérica
    cargarContenidoAjax(url, contenedor, contenedorHTML, mensajeCarga, mensajeExito);
}

// Función que confirma y elimina un usuario
function eliminarUsuario(id, acronimo) {
    const contenido = `
        <h2>¿Estás seguro?</h2>
        <p>Vas a eliminar al usuario <strong>${acronimo}</strong> (ID: ${id}).</p>
        <p>Esta acción no se puede deshacer y eliminará:</p>
        <ul>
            <li> Sus datos personales</li>
            <li> Su biblioteca de juegos</li>
            <li> Su carrito de compras</li>
            <li> Sus favoritos</li>
            <li> Su historial de compras</li>
        </ul>
        <form action="../acciones/eliminar_usuario.php" method="post" id="form-eliminar-usuario">
            <input type="hidden" name="id_usuario" value="${id}">
        </form>
    `; /* Contenido del modal */
    modal('modal-eliminar-usuario', contenido, true); /* Muestro el modal */
    
    // Agregar evento al botón aceptar
    document.getElementById('aceptar-modal-eliminar-usuario').onclick = function() {
        document.getElementById('form-eliminar-usuario').requestSubmit(); /* Envío el formulario para eliminar el usuario */
    };
}

// Función que confirma y elimina un juego
function eliminarJuego(id, nombre) {
    const contenido = `
        <h2>¿Estás seguro?</h2>
        <p>Vas a descatalogar el juego <strong>${nombre}</strong> (ID: ${id}).</p>
        <p>Esta acción marcará el juego como inactivo:</p>
        <ul>
            <li> El juego no se mostrará en el catálogo público</li>
            <li> Seguirá visible en la biblioteca de usuarios que lo compraron</li>
            <li> Los datos del juego se conservarán</li>
            <li> Podrás reactivarlo más tarde si es necesario</li>
        </ul>
        <form action="../acciones/eliminar_juego.php" method="post" id="form-eliminar-juego">
            <input type="hidden" name="id_juego" value="${id}">
        </form>
    `; /* Contenido del modal */
    modal('modal-eliminar-juego', contenido, true); /* Muestro el modal */
    
    // Agregar evento al botón aceptar
    document.getElementById('aceptar-modal-eliminar-juego').onclick = function() {
        document.getElementById('form-eliminar-juego').requestSubmit(); /* Envío el formulario para eliminar el juego */
    };
}

// Función que confirma y reactivar juego
function reactivarJuego(id, nombre) {
    const contenido = `
        <h2>¿Reactivar juego?</h2>
        <p>Vas a reactivar el juego <strong>${nombre}</strong> (ID: ${id}).</p>
        <p>Esta acción marcará el juego como activo:</p>
        <ul>
            <li> El juego volverá a mostrarse en el catálogo público</li>
            <li> Los usuarios podrán comprarlo y añadirlo a favoritos</li>
            <li> Aparecerá en las búsquedas y filtros</li>
        </ul>
        <form action="../acciones/reactivar_juego.php" method="post" id="form-reactivar-juego">
            <input type="hidden" name="id_juego" value="${id}">
        </form>
    `; /* Contenido del modal */
    modal('modal-reactivar-juego', contenido, true); /* Muestro el modal */
    
    // Agregar evento al botón aceptar
    document.getElementById('aceptar-modal-reactivar-juego').onclick = function() {
        document.getElementById('form-reactivar-juego').requestSubmit(); /* Envío el formulario para reactivar el juego */
    };
}

// Función que aprueba una solicitud de devolución o reserva
function aprobarSolicitud(id_historial_formateado, id_historial, id_historial_compras, tipo, nombre_usuario) {
    if (tipo === 'SOLICITUD_DEVOLUCION') { /* Si es una solicitud de devolución */
        let contenido = `
            <h2>¿Aprobar devolución?</h2>
            <p>Vas a aprobar la devolución del pedido <strong>Nº ${id_historial_formateado}</strong> del usuario <strong>${nombre_usuario}</strong>.</p>
            <p>Esta acción realizará lo siguiente:</p>
            <ul>
                <li> Al usuario se le mostrará un botón para acceder a la página de reembolso</li>
                <li> El juego se eliminará de su biblioteca cuando complete el reembolso</li>
                <li> El estado de la solicitud cambiará a APROBADO</li>
                <li> Esta acción no se puede deshacer</li>
            </ul>
            <form action="../acciones/aprobar_rechazar_solicitudes.php" method="post" id="form-aprobar-devolucion">
                <input type="hidden" name="id_historial" value="${id_historial}">
                <input type="hidden" name="id_detalle" value="${id_historial_compras}">
                <input type="hidden" name="tipo" value="${tipo}">
                <input type="hidden" name="accion" value="aprobar">
            </form>
        `; /* Contenido del modal */

        modal('modal-aprobar-devolucion', contenido, true); /* Muestro el modal */
        
        // Agregar evento al botón aceptar
        document.getElementById('aceptar-modal-aprobar-devolucion').onclick = function() {
            document.getElementById('form-aprobar-devolucion').requestSubmit(); /* Envío el formulario para aprobar la devolución */
        };
    } else if (tipo === 'RESERVA') { /* Si es una reserva */
        let contenido = `
            <h2>¿Aprobar reserva?</h2>
            <p>Vas a aprobar la reserva del pedido <strong>Nº ${id_historial_formateado}</strong> del usuario <strong>${nombre_usuario}</strong>.</p>
            <p>Esta acción realizará lo siguiente:</p>
            <ul>
                <li> Al usuario se le mostrará un botón para acceder a la página de pago</li>
                <li> El juego se añadirá a su biblioteca cuando complete el pago</li>
                <li> El estado de la solicitud cambiará a APROBADO</li>
                <li> Esta acción no se puede deshacer</li>
            </ul>
            <form action="../acciones/aprobar_rechazar_solicitudes.php" method="post" id="form-aprobar-reserva">
                <input type="hidden" name="id_historial" value="${id_historial}">
                <input type="hidden" name="id_detalle" value="${id_historial_compras}">
                <input type="hidden" name="tipo" value="${tipo}">
                <input type="hidden" name="accion" value="aprobar">
            </form>
        `; /* Contenido del modal */

        modal('modal-aprobar-reserva', contenido, true); /* Muestro el modal */
        
        // Agregar evento al botón aceptar
        document.getElementById('aceptar-modal-aprobar-reserva').onclick = function() {
            document.getElementById('form-aprobar-reserva').requestSubmit(); /* Envío el formulario para aprobar la reserva */
        };
    }
}

// Función que rechaza una solicitud de devolución o reserva
function rechazarSolicitud(id_historial_formateado, id_historial, id_historial_compras, tipo, nombre_usuario) {
    if (tipo === 'SOLICITUD_DEVOLUCION') { /* Si es una solicitud de devolución */
        let contenido = `
            <h2>¿Rechazar devolución?</h2>
            <p>Vas a rechazar la devolución del pedido <strong>Nº ${id_historial_formateado}</strong> del usuario <strong>${nombre_usuario}</strong>.</p>
            <p>Esta acción realizará lo siguiente:</p>
            <ul>
                <li> El usuario no podrá solicitar la devolución de este pedido nuevamente</li>
                <li> El estado de la solicitud cambiará a RECHAZADO</li>
                <li> Esta acción no se puede deshacer</li>
            </ul>
        `; /* Contenido del modal */

        modal('modal-rechazar', contenido, true); /* Muestro el modal */
        
    } else if (tipo === 'RESERVA') { /* Si es una reserva */
        let contenido = `
            <h2>¿Rechazar reserva?</h2>
            <p>Vas a rechazar la reserva del pedido <strong>Nº ${id_historial_formateado}</strong> del usuario <strong>${nombre_usuario}</strong>.</p>
            <p>Esta acción realizará lo siguiente:</p>
            <ul>
                <li> El usuario no podrá completar el pago de este pedido</li>
                <li> El estado de la solicitud cambiará a RECHAZADO</li>
                <li> Esta acción no se puede deshacer</li>
            </ul>
        `; /* Contenido del modal */

        modal('modal-rechazar', contenido, true); /* Muestro el modal */
        
    }

    // Agregar evento al botón aceptar
    document.getElementById('aceptar-modal-rechazar').onclick = function() {
        const modal1 = document.getElementById("modal-rechazar"); /* Obtengo el modal 1 */
        if (modal1) document.body.removeChild(modal1); /* Cierro el modal 1 */
        // Mostrar modal para indicar el motivo del rechazo
        let mensaje = `<h1>Indique a continuación el motivo del rechazo: </h1>
                    <form action="../acciones/aprobar_rechazar_solicitudes.php" method="post" id="form-rechazar">
                        <textarea id='motivo-rechazo' name="motivo" rows='4' cols='50' placeholder='Escriba aquí el motivo del rechazo...' maxlength='500'></textarea>
                        <input type="hidden" name="id_historial" value="${id_historial}">
                        <input type="hidden" name="id_detalle" value="${id_historial_compras}">
                        <input type="hidden" name="tipo" value="${tipo}">
                        <input type="hidden" name="accion" value="rechazar">
                    </form>
                    <br>
                    <div id="advertencia-rechazo">
                        <p>Una vez rechace la solicitud, la acción le será comunicada a <strong>${nombre_usuario}</strong>.</p>
                    </div>`; /* Mensaje con textarea para el motivo */
        modal("modal2", mensaje, false); /* Muestro modal para indicar el motivo */
        
        // Usar setTimeout para asegurar que el DOM se actualice
        setTimeout(function() { /* Espero un momento para que se cree completamente el modal */
            const botonCerrar = document.getElementById('cerrar-modal2'); /* Obtengo el botón de cerrar */

            if (botonCerrar) { /* Si el botón existe */
                // Primero eliminar el evento anterior de cerrar
                const nuevoBoton = botonCerrar.cloneNode(true); /* Clono el botón para eliminar eventos */
                botonCerrar.parentNode.replaceChild(nuevoBoton, botonCerrar); /* Reemplazo el botón antiguo por el nuevo sin eventos */
                
                nuevoBoton.textContent = "Rechazar Solicitud"; /* Cambio el texto del botón */
                nuevoBoton.id = 'confirmar-rechazo'; /* Cambio el ID del botón */
                
                // Añadir evento al nuevo botón
                nuevoBoton.addEventListener('click', function(e) { /* Evento para confirmar la cancelación */
                    e.preventDefault(); /* Prevengo acción por defecto */
                    e.stopPropagation(); /* Detengo propagación del evento */
                    
                    const textareaMotivo = document.getElementById('motivo-rechazo'); /* Obtengo el textarea del motivo */
                    
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
                    
                    document.getElementById('form-rechazar').requestSubmit(); /* Envío el formulario para rechazar la solicitud */
                });
            }
        }, 50);
    };
}

// Función para generar PDF desde el navegador y descargarlo automáticamente
function generarPDFEnNavegador(data) {
    const { jsPDF } = window.jspdf; /* Obtengo jsPDF desde el objeto window */
    const doc = new jsPDF('p', 'mm', 'a4'); /* PDF en orientación vertical */
    
    // Título del documento
    doc.setFontSize(18); /* Tamaño de fuente para el título */
    doc.setFont(undefined, 'bold'); /* Fuente en negrita */
    doc.text('Lista de Pedidos', doc.internal.pageSize.getWidth() / 2, 15, { align: 'center' }); /* Título centrado */
    
    // Fecha de generación
    doc.setFontSize(10); /* Tamaño de fuente para la fecha */
    doc.setFont(undefined, 'normal'); /* Fuente normal */
    doc.text('Generado el ' + data.fecha_generacion, doc.internal.pageSize.getWidth() / 2, 22, { align: 'center' }); /* Fecha centrada */
    
    let yPos = 30; /* Posición Y inicial para el contenido */
    
    // Mostrar búsqueda aplicada si existe
    if (data.busqueda) {
        doc.setFontSize(11); /* Tamaño de fuente para el título de búsqueda */
        doc.setFont(undefined, 'bold'); /* Fuente en negrita para el título de búsqueda */
        doc.text('Búsqueda Aplicada:', 14, yPos); /* Título de búsqueda */
        yPos += 6; /* Espacio después del título */
        
        doc.setFontSize(9); /* Tamaño de fuente para el texto de búsqueda */
        doc.setFont(undefined, 'normal'); /* Fuente normal para el texto de búsqueda */
        doc.text('• ' + data.busqueda, 14, yPos); /* Texto de búsqueda */
        yPos += 5; /* Espacio después del texto */
        
        yPos += 3; /* Espacio extra después de la búsqueda */
    }
    
    // Mostrar filtros aplicados si existen
    if (data.filtros && data.filtros.length > 0) {
        doc.setFontSize(11); /* Tamaño de fuente para el título de filtros */
        doc.setFont(undefined, 'bold'); /* Fuente en negrita para el título de filtros */
        doc.text('Filtros Aplicados:', 14, yPos); /* Título de filtros */
        yPos += 6; /* Espacio después del título */
        
        doc.setFontSize(9); /* Tamaño de fuente para el texto de filtros */
        doc.setFont(undefined, 'normal'); /* Fuente normal para el texto de filtros */
        
        if (data.filtros && data.filtros.length > 0) { /* Si hay filtros */
            data.filtros.forEach(filtro => { /* Recorro cada filtro */
                doc.text('• ' + filtro, 14, yPos); /* Texto de filtro */
                yPos += 5; /* Espacio después de cada filtro */
            });
        }
        
        yPos += 3; /* Espacio extra después de los filtros */
    }
    
    // Generar tabla de pedidos con detalles
    if (data.pedidos && data.pedidos.length > 0) { /* Si hay pedidos */
        data.pedidos.forEach((pedido, index) => { /* Recorro cada pedido */
            // Verificar si necesitamos una nueva página (adaptado a altura vertical ~297mm)
            if (yPos > 260) { /* Si la posición Y supera 260mm */
                doc.addPage(); /* Agrego nueva página */
                yPos = 20; /* Reseteo posición Y */
            }
            
            // Encabezado del pedido
            doc.setFillColor(230, 230, 230); /* Color gris claro para el fondo */
            doc.rect(14, yPos, doc.internal.pageSize.getWidth() - 28, 8, 'F'); /* Dibujo rectángulo de fondo */
            doc.setFontSize(10); /* Tamaño de fuente para el encabezado */
            doc.setFont(undefined, 'bold'); /* Fuente en negrita */
            doc.text('Pedido ' + pedido.id, 16, yPos + 5); /* Texto del encabezado */
            yPos += 10; /* Espacio después del encabezado */
            
            // Información general del pedido
            doc.setFontSize(8); /* Tamaño de fuente para la información */
            doc.setFont(undefined, 'normal'); /* Fuente normal */
            const infoGeneral = [
                ['Cliente:', pedido.cliente],
                ['Tipo:', pedido.tipo],
                ['Estado:', pedido.estado],
                ['Método Pago:', pedido.metodo_pago],
                ['Total:', pedido.total],
                ['Fecha Creación:', pedido.creado_en],
                ['Última Actualización:', pedido.actualizado_en]
            ]; /* Datos de información general */
            
            doc.autoTable({
                startY: yPos, /* Posición Y inicial para la tabla */
                body: infoGeneral, /* Cuerpo de la tabla */
                theme: 'plain', /* Tema plano sin bordes */
                styles: {
                    fontSize: 8, /* Tamaño de fuente para las celdas */
                    cellPadding: 1 /* Relleno de celda */
                }, /* Estilos generales */
                columnStyles: {
                    0: { fontStyle: 'bold', cellWidth: 40 }, /* Estilo para la primera columna */
                    1: { cellWidth: 'auto' } /* Estilo para la segunda columna */
                }, /* Estilos para las columnas */
                margin: { left: 16, right: 14 } /* Márgenes izquierdo y derecho */
            }); /* Genero la tabla de información general */
            
            yPos = doc.lastAutoTable.finalY + 3; /* Actualizo posición Y después de la tabla */
            
            // Detalles de juegos si existen
            if (pedido.detalles && pedido.detalles.length > 0) { /* Si hay detalles de juegos */
                doc.setFontSize(9); /* Tamaño de fuente para el título */
                doc.setFont(undefined, 'bold'); /* Fuente en negrita */
                doc.text('Juegos del pedido:', 16, yPos); /* Texto del título */
                yPos += 5; /* Espacio después del título */
                
                const detallesData = pedido.detalles.map(detalle => [
                    detalle.nombre,
                    detalle.tipo,
                    detalle.estado_detalle,
                    detalle.precio
                ]); /* Datos de detalles de juegos */
                
                doc.autoTable({
                    startY: yPos, /* Posición Y inicial para la tabla */
                    head: [['Nombre Juego', 'Tipo', 'Estado', 'Precio']], /* Encabezados de la tabla */
                    body: detallesData, /* Cuerpo de la tabla */
                    styles: {
                        fontSize: 7, /* Tamaño de fuente para las celdas */
                        cellPadding: 2 /* Relleno de celda */
                    }, /* Estilos generales */
                    headStyles: {
                        fillColor: [200, 200, 200], /* Color de fondo gris para el encabezado */
                        textColor: [0, 0, 0], /* Color de texto negro para el encabezado */
                        fontStyle: 'bold', /* Fuente en negrita para el encabezado */
                        halign: 'center' /* Alineación centrada para el encabezado */
                    }, /* Estilos para el encabezado */
                    columnStyles: {
                        0: { halign: 'left', cellWidth: 'auto' },
                        1: { halign: 'center', cellWidth: 30 },
                        2: { halign: 'center', cellWidth: 30 },
                        3: { halign: 'right', cellWidth: 25 }
                    }, /* Estilos para las columnas */
                    margin: { left: 16, right: 14 } /* Márgenes izquierdo y derecho */
                }); /* Genero la tabla de detalles de juegos */
                
                yPos = doc.lastAutoTable.finalY + 5; /* Actualizo posición Y después de la tabla */
            } else { /* Si no hay detalles de juegos */
                yPos += 5; /* Espacio si no hay detalles */
            }
            
            // Línea separadora entre pedidos
            if (index < data.pedidos.length - 1) { /* Si no es el último pedido */
                doc.setDrawColor(150, 150, 150); /* Color gris para la línea */
                doc.line(14, yPos, doc.internal.pageSize.getWidth() - 14, yPos); /* Dibujo la línea */
                yPos += 8; /* Espacio después de la línea */
            }
        });
        
        // Añadir resumen al final
        if (yPos > 260) { /* Si la posición Y supera 260mm */
            doc.addPage(); /* Agrego nueva página */
            yPos = 20; /* Reseteo posición Y */
        }
        yPos += 5; /* Espacio antes del resumen */
        doc.setFontSize(10); /* Tamaño de fuente para el resumen */
        doc.setFont(undefined, 'bold'); /* Fuente en negrita */
        doc.text('Total de pedidos: ' + data.total, doc.internal.pageSize.getWidth() - 14, yPos, { align: 'right' }); /* Resumen alineado a la derecha */
    } else { /* Si no hay pedidos */
        doc.setFontSize(12); /* Tamaño de fuente para el mensaje */
        doc.setFont(undefined, 'italic'); /* Fuente en cursiva */
        doc.text('No hay pedidos que coincidan con los criterios seleccionados.', doc.internal.pageSize.getWidth() / 2, yPos + 20, { align: 'center' }); /* Mensaje centrado */
    }
    
    // Descargar el PDF automáticamente
    const nombreArchivo = 'pedidos_' + new Date().toISOString().slice(0, 10).replace(/-/g, '') + '_' + 
                          new Date().toTimeString().slice(0, 8).replace(/:/g, '') + '.pdf'; /* Formato: pedidos_YYYYMMDD_HHMMSS.pdf */
    doc.save(nombreArchivo); /* Descargo el archivo PDF */
}