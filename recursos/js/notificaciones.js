// Función para cargar todas las notificaciones del usuario
function recibirNotificaciones() {
    // Preparar datos para la petición
    const datos = new FormData(); /* Creo objeto FormData para enviar datos al servidor */
    datos.append('accion', 'obtener'); /* Especifico que quiero obtener las notificaciones */
    const xhttp = new XMLHttpRequest(); /* Creo objeto para realizar petición AJAX */

    // Realizar llamada síncrona al servidor
    xhttp.open("POST", "../acciones/acciones_notificaciones.php", false); /* Configuro petición POST síncrona */
    xhttp.send(datos); /* Envío la petición con los datos */

    // Procesar respuesta del servidor
    if (xhttp.status == 200) { /* Si la petición fue exitosa */
        const respuesta = JSON.parse(xhttp.responseText); /* Convierto respuesta JSON en objeto */
        return respuesta; /* Devuelvo las notificaciones */
    } else { /* Si hubo error en la petición */
        console.error("Respuesta del servidor:", xhttp.responseText); /* Log de la respuesta */
        return null; /* Devuelvo null para indicar error */
    }
}

// Función para mostrar las notificaciones en el contenedor
function mostrarNotificaciones() {
    const notificaciones = recibirNotificaciones(); /* Obtengo las notificaciones del servidor */
    
    // Validar que las notificaciones existen
    if(!notificaciones) { /* Si no se obtuvieron notificaciones (null o undefined) */
        modal('modal-error',"<h1>No se pudieron cargar las notificaciones.</h1>",false); /* Muestro modal de error */
        return; /* Salgo de la función */
    }

    const contenedor = document.getElementById('contenedor-notificaciones'); /* Obtengo el contenedor */

    // Verificar si el array está vacío
    if(notificaciones.length === 0) { /* Si no hay notificaciones */
        contenedor.innerHTML = '<div class="sin-notificaciones"><h2>No tienes notificaciones</h2></div>'; /* Muestro mensaje de sin notificaciones */
        return; /* Salgo de la función */
    }

    contenedor.innerHTML = ''; /* Limpio el contenedor */

    // Crear y agregar los botones globales
    const controlesGlobales = document.createElement('div'); /* Creo el div de botones globales */
    controlesGlobales.className = 'controles-globales'; /* Asigno la clase */
    controlesGlobales.innerHTML = `
        <button id="marcar-todas-vistas" class="boton-accion-global boton-vistas" title="Marcar todas como vistas">
            <img src="../recursos/imagenes/ojo_mostrar.png" alt="Marcar como vistas" id="icono-ojo-abierto">
            <span>Marcar todas como vistas</span>
        </button>
        <button id="eliminar-todas" class="boton-accion-global boton-eliminar-todas" title="Eliminar todas las notificaciones">
            <img src="../recursos/imagenes/eliminar_notificacion.png" id="icono-eliminar" alt="Eliminar todas">
            <span>Eliminar todas</span>
        </button>
    `; /* Genero el HTML de los botones globales */
    contenedor.appendChild(controlesGlobales); /* Los agrego al contenedor */

    // Agregar event listeners a los botones recién creados
    document.getElementById('marcar-todas-vistas').addEventListener('click', marcarTodasVistas); /* Evento para marcar todas como vistas */
    document.getElementById('eliminar-todas').addEventListener('click', eliminarTodas); /* Evento para eliminar todas */

    notificaciones.forEach(function(notificacion) { /* Recorro cada notificación */
        if(window.modoAdmin && notificacion.tipo != 'SISTEMA') {
            return; /* Si es admin, solo muestro notificaciones de sistema */
        } else if(!window.modoAdmin && notificacion.tipo == 'SISTEMA') {
            return; /* Si no es admin, no muestro notificaciones de sistema */
        }
        const divNotificacion = document.createElement('div'); /* Creo un div para la notificación */
        divNotificacion.className = `notificacion ${notificacion.leido == 1 ? 'leida' : 'no-leida'} tipo-${notificacion.tipo}`; /* Asigno clases según estado y tipo */
        divNotificacion.setAttribute('data-id', notificacion.id); /* Guardo el ID de la notificación */

        // Crear el contenido de la notificación
        let htmlNotificacion = '<div class="contenido-notificacion">'; /* Inicio el contenido */

        htmlNotificacion += `
            <div class="info-notificacion">
                <div class="encabezado-notificacion">
                    <span class="tipo-etiqueta">${notificacion.tipo.toUpperCase()}</span>
                    <span class="fecha-notificacion">${formatearFecha(notificacion.creado_en)}</span>
                </div>
                <div class="mensaje-notificacion">
                    <p>${notificacion.mensaje}</p>
                </div>
            </div>
        `; /* Agrego la información de la notificación */

        htmlNotificacion += '</div>'; /* Cierro el contenido */

        // Botones de acción
        htmlNotificacion += '<div class="acciones-notificacion">'; /* Inicio las acciones */
        
        if(notificacion.leido == 0) { /* Si no está leída */
            htmlNotificacion += `
                <button class="boton-marcar-leida" onclick="marcarComoVista(${notificacion.id})" title="Marcar como leída">
                    <img src="../recursos/imagenes/ojo_mostrar.png" id="icono-ojo-abierto" alt="Marcar como leída">
                    <span>Marcar como leída</span>
                </button>
            `; /* Agrego botón para marcar como leída */
        }

        htmlNotificacion += `
            <button class="boton-eliminar" onclick="eliminarNotificacion(${notificacion.id})" title="Eliminar notificación">
                <img src="../recursos/imagenes/eliminar_notificacion.png" id="icono-eliminar" alt="Eliminar">
                <span>Eliminar</span>
            </button>
        `; /* Agrego botón para eliminar */

        htmlNotificacion += '</div>'; /* Cierro las acciones */

        divNotificacion.innerHTML = htmlNotificacion; /* Inserto el HTML en el div */
        contenedor.appendChild(divNotificacion); /* Agrego el div al contenedor */
    });
}

// Función para formatear la fecha de forma legible
function formatearFecha(fechaString) {
    const fecha = new Date(fechaString); /* Creo objeto Date con la fecha */
    const ahora = new Date(); /* Obtengo la fecha actual */
    const diferencia = ahora - fecha; /* Calculo la diferencia en milisegundos */
    
    // Convertir a minutos
    const minutos = Math.floor(diferencia / 60000); /* Convierto a minutos */
    
    if(minutos < 1) { /* Si fue hace menos de 1 minuto */
        return 'Hace un momento'; /* Retorno mensaje */
    } else if(minutos < 60) { /* Si fue hace menos de 1 hora */
        return `Hace ${minutos} minuto${minutos > 1 ? 's' : ''}`; /* Retorno minutos */
    } else if(minutos < 1440) { /* Si fue hace menos de 24 horas */
        const horas = Math.floor(minutos / 60); /* Calculo las horas */
        return `Hace ${horas} hora${horas > 1 ? 's' : ''}`; /* Retorno horas */
    } else { /* Si fue hace más de 24 horas */
        const dias = Math.floor(minutos / 1440); /* Calculo los días */
        if(dias < 7) { /* Si fue hace menos de una semana */
            return `Hace ${dias} día${dias > 1 ? 's' : ''}`; /* Retorno días */
        } else { /* Si fue hace más de una semana */
            // Mostrar fecha completa
            const opciones = {
                year: 'numeric',   // Año con 4 dígitos (2025)
                month: 'long',     // Mes en texto completo (diciembre)
                day: 'numeric',    // Día (6)
                hour: '2-digit',   // Hora con dos dígitos (09, 18…)
                minute: '2-digit'  // Minutos con dos dígitos (05, 42…)
            };
            return fecha.toLocaleDateString('es-ES', opciones); /* Retorno fecha formateada, como por ejemplo "6 de diciembre de 2025, 09:05" */
        }
    }
}

// Función para enviar peticiones de notificaciones al servidor
function enviarPeticionNotificacion(accion, id_notificacion = null) {
    const datos = new FormData(); /* Creo objeto para enviar datos */
    datos.append('accion', accion); /* Envío la acción */
    if(id_notificacion != null) datos.append('id_notificacion', id_notificacion); /* Si hay ID, lo añado */

    const xhttp = new XMLHttpRequest(); /* Creo objeto para petición AJAX */
    xhttp.onreadystatechange = function() { /* Defino qué hacer cuando cambie el estado */
        if (this.readyState == 4 && this.status == 200) { /* Si la petición se completó exitosamente */
            const respuesta = JSON.parse(this.responseText); /* Parseo la respuesta JSON */
            
            if(respuesta.error) { /* Si hay error */
                modal('modal-error', '<h1>Error: ' + respuesta.error + '</h1>', false); /* Muestro el error */
            } else if(respuesta.exito) { /* Si hay éxito */
                modal('modal-exito', '<h1>' + respuesta.exito + '</h1>', false); /* Muestro el mensaje de éxito del servidor */
                actualizarContadorNotificaciones(); /* Actualizo el contador */
                mostrarNotificaciones(); /* Recargo las notificaciones */
            }
        }
    };
    xhttp.open("POST", "../acciones/acciones_notificaciones.php", true); /* Configuro petición POST asincrónica */
    xhttp.send(datos); /* Envío los datos */
}

// Función para actualizar el contador de notificaciones en el encabezado
function actualizarContadorNotificaciones() {
    const notificaciones = recibirNotificaciones(); /* Obtengo todas las notificaciones */
    let cantidad_sin_leer = 0; /* Inicializo contador */
    
    if(notificaciones && Array.isArray(notificaciones)) { /* Si obtuve notificaciones */
        for(let i = 0; i < notificaciones.length; i++) { /* Recorro cada notificación */
            if(notificaciones[i].leido == 0) { /* Si no está leída */
                if(window.modoAdmin && notificaciones[i].tipo == 'SISTEMA') { /* Si es admin y es SISTEMA */
                    cantidad_sin_leer++; /* Cuento */
                } else if(!window.modoAdmin && notificaciones[i].tipo != 'SISTEMA') { /* Si no es admin y NO es SISTEMA */
                    cantidad_sin_leer++; /* Cuento */
                }
            }
        }
    }
    
    const contador = document.getElementById('cantidad-notificaciones'); /* Busco el elemento contador */
    if(contador) { /* Si existe el elemento */
        contador.textContent = cantidad_sin_leer; /* Actualizo el texto con la nueva cantidad */
    }
}

// Función para marcar una notificación como vista
function marcarComoVista(idNotificacion) {
    enviarPeticionNotificacion('marcar_vista', idNotificacion); /* Envío la petición */
}

// Función para eliminar una notificación
function eliminarNotificacion(idNotificacion) {
    modal('modal-confirmar-eliminar', '<h1>¿Estás seguro de que deseas eliminar esta notificación?</h1>', true); /* Creo modal de confirmación */
    
    document.getElementById('aceptar-modal-confirmar-eliminar').addEventListener('click', function() { /* Cuando se confirma */
        document.body.removeChild(document.getElementById('modal-confirmar-eliminar')); /* Cierro el modal */
        enviarPeticionNotificacion('eliminar', idNotificacion); /* Envío la petición */
    });
}

// Función para marcar todas las notificaciones como vistas
function marcarTodasVistas() {
    enviarPeticionNotificacion('marcar_todas_vistas'); /* Envío la petición */
}

// Función para eliminar todas las notificaciones
function eliminarTodas() {
    modal('modal-confirmar-eliminar-todas', '<h1>¿Estás seguro de que deseas eliminar <strong>TODAS</strong> las notificaciones?<br>Esta acción no se puede deshacer.</h1>', true); /* Creo modal de confirmación */
    
    document.getElementById('aceptar-modal-confirmar-eliminar-todas').addEventListener('click', function() { /* Cuando se confirma */
        document.body.removeChild(document.getElementById('modal-confirmar-eliminar-todas')); /* Cierro el modal */
        enviarPeticionNotificacion('eliminar_todas'); /* Envío la petición */
    });
}

// Cargar las notificaciones al cargar la página
document.addEventListener('DOMContentLoaded', function() { /* Cuando el DOM esté listo */
    mostrarNotificaciones(); /* Cargo las notificaciones */
    actualizarContadorNotificaciones(); /* Actualizo el contador de notificaciones */
});