// Función que muestra los juegos de la biblioteca
function mostrarBiblioteca(idUsuario) {
    const contenedor = document.getElementById('contenedor-juegos'); /* Obtengo el contenedor de la biblioteca */
    contenedor.innerHTML = '<p>Cargando biblioteca...</p>'; /* Muestro mensaje de carga */
        
    // Preparar datos para enviar al servidor
    const datos = new FormData(); /* Creo objeto para enviar datos */
    datos.append('accion', 'mostrar'); /* Envío la acción mostrar */

    // Verifico si se proporcionó un ID de usuario
    if(idUsuario) {
        datos.append('id_usuario', idUsuario); /* Envío el ID del usuario si existe */
    }
    
    // Hacer petición AJAX POST para obtener los juegos de la biblioteca
    const xhttp = new XMLHttpRequest(); /* Creo objeto para petición AJAX */
    xhttp.onreadystatechange = function() { /* Defino qué hacer cuando cambie el estado */
        if (this.readyState == 4 && this.status == 200) { /* Si la petición se completó exitosamente */
            contenedor.innerHTML = `<div id="advertencia-biblioteca">
                                        <p>Advertencia: Si tienes filtros aplicados, es posible que no se muestren todos los juegos. </p>
                                    </div>
                                    <div class="juegos">
                                        ${this.responseText}
                                    </div>`; /* Cargo el HTML de la biblioteca en el contenedor, envuelto en el div con clase juegos */
        }
    };
    xhttp.open("POST", "../acciones/acciones_biblioteca.php", true); /* Configuro petición POST para mostrar la biblioteca */
    xhttp.send(datos); /* Envío los datos al servidor */
}

// Función para eliminar la advertencia de la biblioteca
function eliminarAdvertencia() {
    setTimeout(function() { /* Espero 5 segundos antes de eliminar */
        const advertencia = document.getElementById('advertencia-biblioteca'); /* Obtengo el elemento de advertencia */
        if(advertencia) { /* Si existe la advertencia */
            advertencia.style.transition = 'opacity 0.5s ease'; /* Aplico transición de opacidad */
            advertencia.style.opacity = '0'; /* Inicio la animación de desvanecimiento */

            setTimeout(function() { /* Después de la animación */
                advertencia.remove(); /* Elimino el mensaje del DOM */
            }, 500); // Espera 0.5s para que termine la animación
        }
    }, 5000);
}