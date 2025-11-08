// Variables globales para controlar el estado del carrusel
let datos = [];               // Array que almacenará los datos del JSON del carrusel
let indice = 0;              // Índice actual de la diapositiva mostrada
let intervaloCarrusel;       // Referencia al timer para el cambio automático de diapositivas
const tiempoIntervalo = 5000;  // Intervalo de tiempo entre cambios automáticos (5 segundos)

// Función principal que carga los datos del carrusel desde el archivo JSON
function cargarCarrusel() {
  // Creo una nueva petición AJAX para obtener los datos
  const xhttp = new XMLHttpRequest(); /* Creo objeto XMLHttpRequest para hacer la petición */
  
  xhttp.onreadystatechange = function() { /* Función que se ejecuta cuando cambia el estado de la petición */
    if (this.readyState == 4 && this.status == 200) { /* Solo procesar cuando la petición esté completada y sea exitosa */
      const json = JSON.parse(this.responseText); /* Convierto la respuesta en objeto JavaScript */
      datos = json.carrusel; /* Extraigo el array de diapositivas del carrusel */
      
      if(window.datosUsuario.autenticado) datos.splice(datos.length - 2, 1); /* Si está logueado, elimino la diapositiva de registro */
      else datos.splice(datos.length - 1, 1); /* Si no está logueado, elimino la diapositiva específica para usuarios logueados */

      mostrar(); /* Llamo a la función para mostrar la primera diapositiva */
      iniciarCarruselAutomatico(); /* Activo el timer automático del carrusel para el cambio automático de diapositivas una vez cargados los datos */
    }
  };
  
  // Configuro y envío la petición GET al archivo JSON
  xhttp.open("GET", "../publico/api/carrusel.json"); /* Configuro la petición GET al archivo JSON */
  xhttp.send(); /* Envío la petición al servidor */
}

// Función que muestra la diapositiva actual en el DOM
function mostrar() {
  // Verifico que haya datos disponibles antes de proceder
  if (datos.length === 0) return; /* Si no hay datos, salgo de la función */
  
  const elemento = datos[indice]; /* Obtengo la diapositiva que debo mostrar */
  const cont = document.getElementById("carrusel-contenido"); /* Obtengo el div donde voy a insertar el HTML */
  
  // Genero dinámicamente el HTML de la diapositiva
  cont.innerHTML = ` <!-- Inserto el HTML generado dinámicamente -->
    <article> <!-- Contenedor principal de la diapositiva -->
      <img src="${elemento.imagen}" alt="${elemento.titulo}"> <!-- Imagen principal de la diapositiva -->
      <h2>${elemento.titulo}</h2> <!-- Título principal -->
      <p>${elemento.subtitulo}</p> <!-- Subtítulo descriptivo -->
      <div class='botones-accion'> <!-- Contenedor para los botones de acción -->
        <a href="${elemento.boton1_url}" id="boton1">${elemento.boton1_texto}</a> <!-- Primer botón de acción -->
        <a href="${elemento.boton2_url}" id="boton2" class='boton-secundario'>${elemento.boton2_texto}</a> <!-- Segundo botón de acción -->
      </div>
    </article>
  `;
  if(elemento.datos_filtro1) filtrarJuegos(elemento.datos_filtro1, "boton1"); /* Si hay filtro para el primer botón, lo configuro */
  if(elemento.datos_filtro2) filtrarJuegos(elemento.datos_filtro2, "boton2"); /* Si hay filtro para el segundo botón, lo configuro */
}

// Función que configura los filtros automáticos en los botones del carrusel
function filtrarJuegos(datos_filtro, boton) {
  document.getElementById(boton).addEventListener("click", function(event) { /* Añado listener de click al botón específico */
    event.preventDefault(); /* Elimino la acción por defecto del enlace */
    
    const filtrosUsuario = window.preferenciasUsuario || []; /* Obtengo las preferencias del usuario si existen */
    
    // Crear objeto con las preferencias organizadas por tipo
    let preferenciasOriginales = {}; /* Creo objeto para organizar los filtros que voy a aplicar */
    if (datos_filtro.tipo_filtro === 'tipos') { /* Si el filtro es de tipo de juego */
      preferenciasOriginales = { /* Configuro para filtrar por tipo */
        'tipo': datos_filtro.id_fijo, /* Establezco el tipo específico */
        'genero': null, /* Sin filtro de género */
        'categoria': null,  /* Sin filtro de categoría */
        'modo': null, /* Sin filtro de modo */
        'pegi': null /* Sin filtro de PEGI */
      };
    } else if(datos_filtro.tipo_filtro === 'generos') { /* Si el filtro es de género */
      preferenciasOriginales = { /* Configuro para filtrar por género */
        'tipo': null, /* Sin filtro de tipo */
        'genero': datos_filtro.id_fijo, /* Establezco el género específico */
        'categoria': null,  /* Sin filtro de categoría */
        'modo': null, /* Sin filtro de modo */
        'pegi': null /* Sin filtro de PEGI */
      };
    } else if(datos_filtro.tipo_filtro === 'categorias') { /* Si el filtro es de categoría */
      preferenciasOriginales = { /* Configuro para filtrar por categoría */
        'tipo': null, /* Sin filtro de tipo */
        'genero': null, /* Sin filtro de género */
        'categoria': datos_filtro.id_fijo,  /* Establezco la categoría específica */
        'modo': null, /* Sin filtro de modo */
        'pegi': null /* Sin filtro de PEGI */
      };
    } else if(datos_filtro.tipo_filtro === 'modos') { /* Si el filtro es de modo de juego */
      preferenciasOriginales = { /* Configuro para filtrar por modo */
        'tipo': null, /* Sin filtro de tipo */
        'genero': null, /* Sin filtro de género */
        'categoria': null, /* Sin filtro de categoría */
        'modo': datos_filtro.id_fijo, /* Establezco el modo específico */
        'pegi': null /* Sin filtro de PEGI */
      };
    } else if(datos_filtro.tipo_filtro === 'clasificacionPEGI') { /* Si el filtro es de clasificación PEGI */
      preferenciasOriginales = { /* Configuro para filtrar por PEGI */
        'tipo': null, /* Sin filtro de tipo */
        'genero': null, /* Sin filtro de género */
        'categoria': null, /* Sin filtro de categoría */
        'modo': null, /* Sin filtro de modo */
        'pegi': elemento.datos_filtro1.id_fijo /* Establezco la clasificación PEGI específica */
      };
    }

    // Hacer completamente invisible todo el menú lateral
    const menuLateral = document.querySelector('.menu-lateral'); /* Obtengo referencia al menú lateral */
    const cortina = document.querySelector('.cortina'); /* Obtengo referencia a la cortina del menú */
    
    if (menuLateral) { /* Si existe el menú lateral */
        menuLateral.style.visibility = 'hidden'; /* Lo hago invisible */
        menuLateral.style.opacity = '0'; /* Y transparente */
    }
    if (cortina) { /* Si existe la cortina */
        cortina.style.visibility = 'hidden'; /* La hago invisible */
        cortina.style.opacity = '0'; /* Y transparente */
    }

    // Abrir el menú de filtros de forma invisible
    document.getElementById('abrir-menu').checked = true; /* Marco el checkbox para abrir el menú */
    mostrarMenuFiltros(); /* Llamo a la función que muestra el menú de filtros */

    // Tipos
    const selectTipo = document.getElementById('id_preferencia_tipo'); /* Obtengo el select de tipos */
    if (selectTipo) { /* Si existe el select */
        selectTipo.value = preferenciasOriginales.tipo || 'null'; /* Establezco el valor del filtro de tipo */
    }
    
    // Géneros
    const selectGenero = document.getElementById('id_preferencia_genero'); /* Obtengo el select de géneros */
    if (selectGenero) { /* Si existe el select */
        selectGenero.value = preferenciasOriginales.genero || 'null'; /* Establezco el valor del filtro de género */
    }
    
    // Categorías
    const selectCategoria = document.getElementById('id_preferencia_categoria'); /* Obtengo el select de categorías */
    if (selectCategoria) { /* Si existe el select */
        selectCategoria.value = preferenciasOriginales.categoria || 'null'; /* Establezco el valor del filtro de categoría */
    }
    
    // Modos
    const selectModo = document.getElementById('id_preferencia_modo'); /* Obtengo el select de modos */
    if (selectModo) { /* Si existe el select */
        selectModo.value = preferenciasOriginales.modo || 'null'; /* Establezco el valor del filtro de modo */
    }
    
    // PEGI
    const selectPegi = document.getElementById('id_preferencia_pegi'); /* Obtengo el select de PEGI */
    if (selectPegi) { /* Si existe el select */
        selectPegi.value = preferenciasOriginales.pegi || 'null'; /* Establezco el valor del filtro de PEGI */
    }

    // Precios - Min
    const precioMin = document.getElementById('precio-min'); /* Obtengo el slider de precio mínimo */
    const outputMin = document.getElementById('output-min'); /* Obtengo el output del precio mínimo */
    if (precioMin && outputMin) { /* Si existen ambos elementos */
        precioMin.value = 0; // Valor mínimo por defecto /* Establezco el precio mínimo en 0 */
        outputMin.value = 0; // Actualizar el número que se muestra /* Actualizo el texto mostrado */
    }

    // Precios - Max
    const precioMax = document.getElementById('precio-max'); /* Obtengo el slider de precio máximo */
    const outputMax = document.getElementById('output-max'); /* Obtengo el output del precio máximo */
    if (precioMax && outputMax) { /* Si existen ambos elementos */
        precioMax.value = 100; // Valor máximo por defecto /* Establezco el precio máximo en 100 */
        outputMax.value = 100; // Actualizar el número que se muestra /* Actualizo el texto mostrado */
    }

    // Simular clic en el botón de aplicar filtros
    const botonAplicarFiltros = document.getElementById('aplicar-filtros'); /* Obtengo el botón de aplicar filtros */
    if (botonAplicarFiltros) { /* Si existe el botón */
        botonAplicarFiltros.click(); /* Simulo un clic para aplicar los filtros */
    }

    // Usar setTimeout para ejecutar después de que se apliquen los filtros
    setTimeout(() => { /* Espero un momento para que se apliquen los filtros */
        // Cerrar el menú de filtros de forma invisible
        document.getElementById('abrir-menu').checked = false; /* Desmarco el checkbox para cerrar el menú */
        mostrarMenuFiltros(); /* Llamo a la función que oculta el menú de filtros */

        // Restaurar la visibilidad después de un breve momento
        setTimeout(() => { /* Espero un poco más para restaurar la visibilidad */
            if (menuLateral) { /* Si existe el menú lateral */
                menuLateral.style.visibility = ''; /* Restauro su visibilidad */
                menuLateral.style.opacity = ''; /* Restauro su opacidad */
            }
            if (cortina) { /* Si existe la cortina */
                cortina.style.visibility = ''; /* Restauro su visibilidad */
                cortina.style.opacity = ''; /* Restauro su opacidad */
            }
        }, 50); /* Espero 50ms para la restauración */
    }, 100); /* Espero 100ms para cerrar el menú */
  });
}

// Función que sincroniza los indicadores visuales (círculos) con la diapositiva actual
function actualizarIndicadores() {
  // Primero elimino la clase 'activo' de todos los círculos indicadores
  document.querySelectorAll(".circulo").forEach(circulo => { /* Recorro todos los círculos indicadores */
    circulo.classList.remove("activo"); /* Quito la clase activo de cada uno */
  });
  
  // Luego busco y activo específicamente el círculo que corresponde al índice actual
  // Uso un selector de atributo para encontrar el onclick que coincida con el índice
  const circuloActivo = document.querySelector(`.circulo[onclick="cambiarIndice(${indice})"]`); /* Busco el círculo que corresponde al índice actual */
  if (circuloActivo) { /* Si encontré el círculo */
    circuloActivo.classList.add("activo"); /* Le añado la clase activo */
  }
}

// Función que inicia el cambio automático de diapositivas
function iniciarCarruselAutomatico() {
  intervaloCarrusel = setInterval(function() { /* Creo un intervalo que se ejecuta cada 5 segundos */
    cambiar(1); /* Avanzo a la siguiente diapositiva */
  }, tiempoIntervalo); /* Uso el tiempo definido en la variable global */
}

// Función para detener el carrusel automático cuando sea necesario
function pausarCarruselAutomatico() {
  if (intervaloCarrusel) { /* Si hay un intervalo activo */
    clearInterval(intervaloCarrusel); /* Lo cancelo */
    intervaloCarrusel = null; /* Reseteo la referencia para control posterior */
  }
}

// Función que reinicia completamente el timer automático desde cero
function reiniciarCarruselAutomatico() {
  pausarCarruselAutomatico(); /* Detengo cualquier timer existente para evitar múltiples intervalos */
  iniciarCarruselAutomatico(); /* Inicio un nuevo timer desde cero */
}

// Función que cambia de diapositiva en la dirección indicada (usada tanto manual como automáticamente)
function cambiar(direccion) {
  // Actualizo el índice según la dirección especificada (+1 o -1)
  indice += direccion; /* Sumo o resto 1 al índice actual */
  
  // Manejo el caso de retroceder desde la primera diapositiva (ir al final)
  if (indice < 0) indice = datos.length - 1; /* Si voy antes del inicio, salto al final */
  
  // Manejo el caso de avanzar desde la última diapositiva (volver al inicio)
  if (indice >= datos.length) indice = 0; /* Si paso del final, vuelvo al inicio */
  
  // Sincronizo los indicadores visuales con el nuevo índice
  actualizarIndicadores(); /* Actualizo qué círculo está activo */
  
  mostrar(); /* Muestro la nueva diapositiva en pantalla */
}

// Función específica para cuando el usuario hace clic en un círculo indicador
function cambiarIndice(indiceNuevo) {
  if (indiceNuevo >= 0 && indiceNuevo < datos.length) { /* Valido que el índice solicitado esté dentro del rango válido */
    indice = indiceNuevo; /* Cambio al índice solicitado */

    actualizarIndicadores(); /* Actualizo los círculos indicadores para que coincidan con el nuevo índice */
    mostrar(); /* Muestro la diapositiva correspondiente */
    reiniciarCarruselAutomatico(); /* Reinicio el timer automático para que no interfiera con la interacción del usuario */
  
  } else { /* Si el índice no es válido */
    console.error("Índice fuera de rango"); /* Muestro error en consola si el índice es inválido */
  }
}

// Función para cuando se usan las flechas de navegación
function cambiarManual(direccion) {
  cambiar(direccion); /* Uso la función cambiar */
  
  // Reinicio el timer automático para evitar cambios inmediatos no deseados
  // Esto da al usuario tiempo para navegar manualmente sin interferencias
  reiniciarCarruselAutomatico();
}

// Evento que se ejecuta cuando la página termina de cargar completamente
window.onload = cargarCarrusel; /* Cuando se carga la página, inicio el carrusel */

