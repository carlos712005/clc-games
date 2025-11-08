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

// Función para manejar la selección del modo de edición
document.addEventListener('DOMContentLoaded', function() { /* Cuando el DOM esté completamente cargado */
    const enlacesSubmenu = document.querySelectorAll('.enlace-submenu'); /* Obtengo todos los enlaces del submenú */
    const submenu = document.getElementById('submenu-edicion'); /* Submenú de edición */
    const flecha = document.getElementById('flecha-submenu-icono'); /* Icono de flecha del submenú */
    
    enlacesSubmenu.forEach(enlace => { /* Recorro cada enlace */
        enlace.addEventListener('click', function(e) { /* Añado evento click */
            e.preventDefault(); /* Evito la navegación por defecto */
            const modo = this.getAttribute('data-modo'); /* Obtengo el modo seleccionado */
            
            enlacesSubmenu.forEach(enlace => enlace.classList.remove('activo')); /* Quito la clase activo de todos los enlaces */
            this.classList.add('activo'); /* Activo solo el clicado */

            if (modo === 'juegos') { /* Si es edición de juegos */
                mostrarEdicionJuegos(); /* Llamo a la función de edición de juegos */
            } else if (modo === 'usuarios') { /* Si es edición de usuarios */
                mostrarEdicionUsuarios(); /* Llamo a la función de edición de usuarios */
            }
        });
    });

    if (submenu) { /* Si existe el submenú */
        // Usar el modo guardado en la variable global window.modoEdicion
        const modoGuardado = window.modoEdicion || 'juegos'; /* Obtengo el modo guardado o 'juegos' por defecto */
        
        enlacesSubmenu.forEach(enlace => enlace.classList.remove('activo')); /* Quito la clase activo de todos los enlaces */
        
        let opcionGuardada; /* Variable para almacenar la opción a activar */
        if (modoGuardado === 'usuarios') { /* Si el modo guardado es usuarios */
            opcionGuardada = document.querySelector('.enlace-submenu[data-modo="usuarios"]'); /* Selecciono usuarios */
            if (opcionGuardada) { /* Si encontré la opción */
                opcionGuardada.classList.add('activo'); /* La marco como activa */
            }
            mostrarEdicionUsuarios(); /* Cargo directamente la vista de usuarios */
        } else { /* Si no hay modo guardado o es juegos */
            opcionGuardada = document.querySelector('.enlace-submenu[data-modo="juegos"]'); /* Selecciono juegos por defecto */
            if (opcionGuardada) { /* Si encontré la opción */
                opcionGuardada.classList.add('activo'); /* La marco como activa */
            }
            mostrarEdicionJuegos(); /* Cargo directamente la vista de juegos */
        }
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
        } else if (this.readyState == 4) { /* Si la petición terminó pero con error */
            console.error('Error al cargar contenido: ' + this.status); /* Muestro error en consola */
            contenedor.innerHTML = '<div class="error"><h2>Error al cargar el contenido. Por favor, inténtelo de nuevo.</h2></div>'; /* Muestro mensaje de error */
        }
    };
    xhttp.open("GET", url, true); /* Configuro petición GET */
    xhttp.send(); /* Envío la petición */
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

    if(botonAgregarJuego) { /* Si existe el botón de agregar juego */
        botonAgregarJuego.style.display = 'flex'; /* Lo muestro */
    }

    if(botonAgregarUsuario) { /* Si existe el botón de agregar usuario */
        botonAgregarUsuario.style.display = 'none'; /* Lo oculto */
    }

    const url = '../acciones/cargar_juegos.php'; /* URL del archivo PHP */
    const contenedorHTML = '<div class="juegos">{contenido}</div>'; /* Contenedor HTML */
    const mensajeCarga = 'Cargando juegos...'; /* Mensaje de carga */
    const mensajeExito = 'Vista de edición de juegos cargada'; /* Mensaje de éxito */
    
    // Actualizar el modo de edición global
    window.modoEdicion = 'juegos'; /* Actualizo la variable global para que los filtros sepan qué mostrar */
    
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

    if(botonAgregarJuego) { /* Si existe el botón de agregar juego */
        botonAgregarJuego.style.display = 'none'; /* Lo oculto */
    }

    if(botonAgregarUsuario) { /* Si existe el botón de agregar usuario */
        botonAgregarUsuario.style.display = 'flex'; /* Lo muestro */
    }
    
    const url = '../acciones/cargar_usuarios.php'; /* URL del archivo PHP */
    const contenedorHTML = null; /* Sin contenedor HTML */
    const mensajeCarga = 'Cargando usuarios...'; /* Mensaje de carga */
    const mensajeExito = 'Vista de edición de usuarios cargada'; /* Mensaje de éxito */

    // Actualizar el modo de edición global
    window.modoEdicion = 'usuarios'; /* Actualizo la variable global para que los filtros sepan qué mostrar */

    cargarEstilosPanelAdmin('usuarios'); /* Cargo los estilos correspondientes */
    
    // Cargar contenido de usuarios usando función genérica
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
