// Función para mostrar el modal de recuperación de contraseña
function mostrarModalRecuperacion(evento) {
    evento.preventDefault(); /* Prevengo el comportamiento por defecto del enlace */
    const contenido = `
        <div class="modal-recuperacion">
            <h1>Selecciona cómo deseas recuperar tu contraseña</h1>
            
            <div class="opciones-recuperacion">
                <div class="opcion-recuperacion" onclick="seleccionarOpcionRecuperacion(1)">
                    <h3>Contraseña temporal</h3>
                    <p>Recibirás una contraseña temporal en tu correo electrónico que podrás usar inmediatamente para iniciar sesión.</p>
                </div>
                
                <div class="opcion-recuperacion" onclick="seleccionarOpcionRecuperacion(2)">
                    <h3>Enlace de restablecimiento</h3>
                    <p>Recibirás un enlace seguro en tu correo electrónico para crear una nueva contraseña personalizada.</p>
                </div>
            </div>
        </div>
    `; /* Creo el contenido HTML del modal con las dos opciones */

    modal('modal-recuperacion', contenido, false); /* Muestro el modal con botón cerrar */
}

// Función que se ejecuta cuando el usuario selecciona una opción
function seleccionarOpcionRecuperacion(opcion) {
    // Cerrar el modal actual
    const modalActual = document.getElementById('modal-recuperacion'); /* Obtengo el modal actual */
    if (modalActual) { /* Si el modal existe */
        document.body.removeChild(modalActual); /* Elimino el modal del DOM */
    }

    // Mostrar formulario para introducir email
    const contenidoEmail = `
        <div class="modal-recuperacion modal-recuperacion-form">
            <h1>
                ${opcion === 1 ? 'Contraseña temporal' : 'Enlace de restablecimiento'}
            </h1>
            
            <form class="form-recuperacion" id="form-recuperar-contrasena">
                <input type="hidden" id="opcion-recuperacion" name="opcion" value="${opcion}">
                
                <label for="email-recuperacion">Introduce tu correo electrónico para continuar:</label>
                <input type="email" id="email-recuperacion" name="email" 
                       placeholder="tu@email.com" 
                       required 
                       autocomplete="email">
            </form>
        </div>
    `; /* Creo el formulario para solicitar el email */

    modal('modal-email-recuperacion', contenidoEmail, true); /* Muestro el modal con botones Aceptar y Cancelar */

    // Agregar evento al botón Aceptar
    setTimeout(() => { /* Pequeño timeout para asegurar que el modal está en el DOM */
        const botonAceptar = document.getElementById('aceptar-modal-email-recuperacion'); /* Obtengo el botón Aceptar */
        if (botonAceptar) { /* Si el botón Aceptar existe */
            botonAceptar.addEventListener('click', function(e) {
                e.preventDefault(); /* Prevenir envío normal */
                procesarRecuperacion(); /* Llamar a la función de procesamiento */
            });
        }
    }, 100);
}

// Función para procesar la recuperación de contraseña
function procesarRecuperacion() {
    const email = document.getElementById('email-recuperacion').value; /* Obtengo el email ingresado */
    const opcion = document.getElementById('opcion-recuperacion').value; /* Obtengo la opción seleccionada */

    // Validar email
    if (!email || !email.includes('@')) {
        modal('modal-error-email', '<h1>Error de validación</h1><p>Por favor, introduce un correo electrónico válido.</p>', false); /* Mensaje de error */
        return; /* Salir de la función */
    }

    const datos = new FormData(); /* Creo objeto para enviar datos */
    datos.append('email', email); /* Envío el email */
    datos.append('opcion', opcion); /* Envío la opción seleccionada */

    const xhttp = new XMLHttpRequest(); /* Creo objeto para petición AJAX */
    xhttp.onreadystatechange = function() { /* Defino qué hacer cuando cambie el estado */
        if (this.readyState == 4 && this.status == 200) { /* Si la petición se completó exitosamente */
            try {
                const respuesta = JSON.parse(this.responseText); /* Parseo la respuesta JSON */
                
                // Cerrar el modal actual
                const modalActual = document.getElementById('modal-email-recuperacion'); /* Obtengo el modal actual */
                if (modalActual) { /* Si el modal existe */
                    document.body.removeChild(modalActual); /* Elimino el modal del DOM */
                }

                if(respuesta.error) { /* Si hay error */
                    modal('modal-error', '<h1>Error: ' + respuesta.error + '</h1>', false); /* Muestro el error */
                } else if(respuesta.success) { /* Si hay éxito */
                    const contenidoExito = `
                        <div class="modal-recuperacion">
                            <h1>Solicitud enviada</h1>
                            <p>${respuesta.mensaje}</p>
                            <p class="texto-secundario">Revisa tu bandeja de entrada y la carpeta de spam.</p>
                        </div>
                    `; /* Creo el mensaje de éxito */
                    modal('modal-exito-recuperacion', contenidoExito, false); /* Muestro el modal de éxito */
                }
            } catch (e) { /* Si hay error al parsear JSON */
                console.error('Error al parsear respuesta:', e); /* Loguear error */
                modal('modal-error', '<h1>Error al procesar la respuesta del servidor.</h1>', false); /* Mostrar error */
            }
        }
    };
    xhttp.open("POST", "../acciones/procesar_recuperacion.php", true); /* Configuro petición POST asincrónica */
    xhttp.send(datos); /* Envío los datos */
}
