// Función para mostrar u ocultar la contraseña
function mostrarOcultarContrasena(idCampo) {
    let campoContrasena = document.getElementById(idCampo); /* Obtengo el campo de contraseña por su ID */
    let botonMostrar = document.getElementById('boton-' + idCampo); /* Obtengo el botón de mostrar/ocultar por su ID */
    
    // Si está oculta, mostrarla
    if (campoContrasena.type === 'password') { /* Verifico si el campo está oculto */
        campoContrasena.type = 'text'; /* Cambio el tipo a texto para mostrar la contraseña */
        botonMostrar.classList.add('hidden'); /* Oculto el botón de mostrar */
        botonMostrar.title = 'Ocultar contraseña'; /* Cambio el título del botón */
    } 
    // Si está visible, ocultarla
    else { /* Si el campo está visible */
        campoContrasena.type = 'password'; /* Cambio el tipo a password para ocultar la contraseña */
        botonMostrar.classList.remove('hidden'); /* Muestro el botón de mostrar */
        botonMostrar.title = 'Mostrar contraseña'; /* Cambio el título del botón */
    }
    
    campoContrasena.focus(); /* Pongo el foco nuevamente en el campo de contraseña */
}

// Eliminar mensajes de error y de éxito después de 5 segundos */
document.addEventListener('DOMContentLoaded', function() { /* Espero a que el DOM esté cargado */
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