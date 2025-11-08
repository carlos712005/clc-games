// Función que ejecuta acciones relacionadas con favoritos (agregar, eliminar)
function mandarFavoritos(accion, id_juego, nombreModal, mensaje, cancelar) {
    
    // Preparar datos para enviar al servidor
    const datos = new FormData(); /* Creo objeto para enviar datos */
    datos.append('accion', accion); /* Envío la acción */
    datos.append('id_juego', id_juego); /* Y el ID del juego específico */

    // Realizar petición asíncrona al servidor
    const xhttp = new XMLHttpRequest(); /* Creo objeto para petición AJAX */
    xhttp.onreadystatechange = function() { /* Defino qué hacer cuando cambie el estado */
        if (this.readyState == 4 && this.status == 200) { /* Si la petición se completó exitosamente */
            if(nombreModal == null && mensaje == null && cancelar == null) return; /* Si no hay modal que mostrar, salgo */
            
            if(cancelar) modal(nombreModal, mensaje, true); /* Si requiere confirmación, muestro modal con botones */
            else modal(nombreModal, mensaje, false); /* Si no, muestro modal solo informativo */
        
            // Recargar página después de 1.5 segundos para que se vean los cambios
            setTimeout(function() {
                location.reload(); /* Recargo la página actual */
            }, 1500);
        }
    };
    xhttp.open("POST", "../acciones/acciones_favoritos.php", true); /* Configuro petición POST asíncrona */
    xhttp.send(datos); /* Envío los datos al servidor */
}

// Función que muestra los juegos favoritos en el contenedor principal
function mostrarFavoritos(idUsuario = null) {
    const contenedor = document.getElementById('contenedor-juegos'); /* Obtengo el contenedor de favoritos */
    contenedor.innerHTML = '<p>Cargando favoritos...</p>'; /* Muestro mensaje de carga */
        
    // Preparar datos para enviar al servidor
    const datos = new FormData(); /* Creo objeto para enviar datos */
    datos.append('accion', 'mostrar'); /* Envío la acción mostrar */
    if(idUsuario) { /* Verifico si se proporcionó un ID de usuario */
        datos.append('id_usuario', idUsuario); /* Envío el ID del usuario si existe */
    }
    
    // Hacer petición AJAX POST para obtener los favoritos
    const xhttp = new XMLHttpRequest(); /* Creo objeto para petición AJAX */
    xhttp.onreadystatechange = function() { /* Defino qué hacer cuando cambie el estado */
        if (this.readyState == 4 && this.status == 200) { /* Si la petición se completó exitosamente */
            contenedor.innerHTML = `<div id="advertencia-favoritos">
                                        <p>Advertencia: Si tienes filtros aplicados, es posible que no se muestren todos los favoritos. </p>
                                    </div>
                                    <div class="juegos">
                                        ${this.responseText}
                                    </div>`; /* Cargo el HTML de favoritos en el contenedor, envuelto en el div con clase juegos */
        }
    };
    xhttp.open("POST", "../acciones/acciones_favoritos.php", true); /* Configuro petición POST para mostrar favoritos */
    xhttp.send(datos); /* Envío los datos al servidor */
}

// Función para eliminar la advertencia de favoritos
function eliminarAdvertencia() {
    setTimeout(function() { /* Espero 5 segundos antes de eliminar */
        const advertencia = document.getElementById('advertencia-favoritos'); /* Obtengo el elemento de advertencia */
        if(advertencia) { /* Si existe la advertencia */
            advertencia.style.transition = 'opacity 0.5s ease'; /* Aplico transición de opacidad */
            advertencia.style.opacity = '0'; /* Inicio la animación de desvanecimiento */

            setTimeout(function() { /* Después de la animación */
                advertencia.remove(); /* Elimino el mensaje del DOM */
            }, 500); // Espera 0.5s para que termine la animación
        }
    }, 5000);
}