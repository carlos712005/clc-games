// Función que cambia del menú principal al menú de filtros
function mostrarMenuFiltros() {
    // Obtener elementos del DOM
    const menuPrincipal = document.getElementById('menu-principal'); /* Obtengo referencia al menú principal */
    const menuFiltros = document.getElementById('menu-filtros'); /* Obtengo referencia al menú de filtros */
    const tituloMenu = document.getElementById('titulo-menu'); /* Obtengo referencia al título del menú */
    const botonCerrar = document.getElementById('boton-cerrar'); /* Obtengo referencia al botón de cerrar */
    const enlaceModoVision = document.querySelector('.enlace-modo-vision'); /* Obtengo referencia al botón de modo visión */

    // Ocultar menú principal
    if (menuPrincipal) { /* Si existe el menú principal */
        menuPrincipal.style.display = 'none'; /* Lo oculto completamente */
    }
    
    // Mostrar menú de filtros
    if (menuFiltros) { /* Si existe el menú de filtros */
        menuFiltros.style.display = 'block'; /* Lo muestro */
    }
    
    // Ocultar botón de modo visión
    if (enlaceModoVision) { /* Si existe el botón de modo visión */
        enlaceModoVision.style.display = 'none'; /* Lo oculto */
    }
    
    // Cambiar título
    if (tituloMenu) { /* Si existe el título del menú */
        tituloMenu.textContent = 'Filtros de Búsqueda'; /* Cambio el texto del título */
    }

    // Configurar el botón de cerrar
    if (botonCerrar) { /* Si existe el botón de cerrar */
        botonCerrar.onclick = function() { /* Configuro qué pasa cuando se hace clic */
            mostrarMenuPrincipal(); /* Vuelvo al menú principal */
            restaurarFiltrosDeSesion(); /* Restauro los filtros guardados en sesión */
            // Después cerrar el menú lateral
            setTimeout(function() { /* Espero un poco antes de cerrar */
                document.getElementById('abrir-menu').checked = false; /* Desmarco el checkbox para cerrar el menú */
            }, 200); /* Espero 200ms */
        };
    }

    // Controlar visibilidad de los filtros según el modo de edición
    const filtrosUsuarios = document.getElementById('parte-filtros-usuarios'); /* Obtengo el contenedor de filtros de usuarios */
    const filtrosJuegos = document.getElementById('parte-filtros-juegos'); /* Obtengo el contenedor de filtros de juegos */
    const filtrosPedidos = document.getElementById('parte-filtros-pedidos'); /* Obtengo el contenedor de filtros de pedidos */
    
    if (filtrosUsuarios && filtrosJuegos && filtrosPedidos) { /* Si todos los contenedores existen */
        if (window.modoEdicion === 'usuarios') { /* Si el modo de edición es usuarios */
            filtrosUsuarios.style.display = 'block'; /* Muestro los filtros de usuarios */
            filtrosJuegos.style.display = 'none'; /* Oculto los filtros de juegos */
            filtrosPedidos.style.display = 'none'; /* Oculto los filtros de pedidos */
        } else if (window.modoEdicion === 'pedidos') { /* Si el modo de edición es pedidos */
            filtrosUsuarios.style.display = 'none'; /* Oculto los filtros de usuarios */
            filtrosJuegos.style.display = 'none'; /* Oculto los filtros de juegos */
            filtrosPedidos.style.display = 'block'; /* Muestro los filtros de pedidos */
        } else { /* Si el modo de edición es juegos o cualquier otro */
            filtrosUsuarios.style.display = 'none'; /* Oculto los filtros de usuarios */
            filtrosJuegos.style.display = 'block'; /* Muestro los filtros de juegos */
            filtrosPedidos.style.display = 'none'; /* Oculto los filtros de pedidos */
        }
    }

    // Configurar los rangos de precios
    configurarRangesPrecios(); /* Llamo a la función que configura los campos de precio */

    // Configurar los rangos de totales de pedidos
    configurarRangesTotalesPedidos(); /* Llamo a la función que configura los campos de totales de pedidos */

    // Configurar los rangos de fechas
    configurarRangesFechas(); /* Llamo a la función que configura los campos de fecha */
    
    // Configurar los rangos de fechas para pedidos
    configurarRangesFechasPedidos(); /* Llamo a la función que configura los campos de fecha de pedidos */
    
    // Configurar evento para cerrar menú lateral al hacer click en el fondo (cortina)
    document.querySelectorAll('.cortina').forEach(cortinaElemento => { /* Busco todos los elementos con clase 'cortina' que actúan como fondo del menú lateral */
        cortinaElemento.addEventListener('click', () => { /* Añado evento click para cerrar el menú cuando se hace click en el fondo */
            mostrarMenuPrincipal(); /* Vuelvo al menú principal antes de cerrar */
            restaurarFiltrosDeSesion(); /* Restauro los filtros guardados en sesión sin aplicar los cambios temporales */
            // Después cerrar el menú lateral completamente
            setTimeout(function() { /* Espero un poco antes de cerrar para que se vea la transición */
                document.getElementById('abrir-menu').checked = false; /* Desmarco el checkbox que controla la visibilidad del menú lateral */
            }, 200); /* Espero 200ms para una transición suave */
        });
    });
    
}

// Función que vuelve del menú de filtros al menú principal
function mostrarMenuPrincipal() {
    // Obtener elementos del DOM
    const menuPrincipal = document.getElementById('menu-principal'); /* Obtengo referencia al menú principal */
    const menuFiltros = document.getElementById('menu-filtros'); /* Obtengo referencia al menú de filtros */
    const tituloMenu = document.getElementById('titulo-menu'); /* Obtengo referencia al título del menú */
    const botonCerrar = document.getElementById('boton-cerrar'); /* Obtengo referencia al botón de cerrar */
    const enlaceModoVision = document.querySelector('.enlace-modo-vision'); /* Obtengo referencia al botón de modo visión */

    // Mostrar menú principal
    if (menuPrincipal) { /* Si existe el menú principal */
        menuPrincipal.style.display = 'block'; /* Lo muestro */
    }
    
    // Ocultar menú de filtros
    if (menuFiltros) { /* Si existe el menú de filtros */
        menuFiltros.style.display = 'none'; /* Lo oculto */
    }
    
    // Mostrar botón de modo visión si el usuario es administrador
    if (enlaceModoVision) { /* Si existe el botón de modo visión */
        enlaceModoVision.style.display = 'flex'; /* Lo muestro */
    }
    
    // Restaurar título
    if (tituloMenu) { /* Si existe el título del menú */
        tituloMenu.textContent = 'Todas las secciones'; /* Restauro el título original */
    }

}

// Función que configura el comportamiento de los campos de precio
function configurarRangesPrecios() {
    // Busco los elementos de precio mínimo
    const campoMin = document.getElementById('precio-min'); /* Obtengo el campos de precio mínimo */
    const numeroMin = document.getElementById('output-min'); /* Obtengo el campo numérico del precio mínimo */
    
    // Busco los elementos de precio máximo  
    const campoMax = document.getElementById('precio-max'); /* Obtengo el campo de precio máximo */
    const numeroMax = document.getElementById('output-max'); /* Obtengo el campo numérico del precio máximo */
    
    // Si existen los elementos del precio mínimo
    if (campoMin && numeroMin) { /* Si ambos elementos existen */
        // Cuando muevo el campo mínimo
        campoMin.addEventListener('input', function() { /* Escucho cuando se mueve el campo mínimo */
            // Actualizo el número que se muestra con formato español (coma decimal)
            numeroMin.value = campoMin.value.replace('.', ','); /* Sincronizo el valor numérico con el campo usando coma */

            // Si el mínimo es mayor que el máximo, corrijo el máximo
            if (parseInt(campoMin.value) > parseInt(campoMax.value)) { /* Si el mínimo supera al máximo */
                campoMax.value = campoMin.value;  /* Ajusto el campo máximo */
                numeroMax.value = campoMin.value.replace('.', ',');  /* Ajusto el valor numérico máximo con coma */
            }
        });
    }
    
    // Si existen los elementos del precio máximo
    if (campoMax && numeroMax) { /* Si ambos elementos existen */
        // Cuando muevo el campo máximo
        campoMax.addEventListener('input', function() { /* Escucho cuando se mueve el campo máximo */
            // Actualizo el número que se muestra con formato español (coma decimal)
            numeroMax.value = campoMax.value.replace('.', ','); /* Sincronizo el valor numérico con el campo usando coma */

            // Si el máximo es menor que el mínimo, corrijo el mínimo
            if (parseInt(campoMax.value) < parseInt(campoMin.value)) { /* Si el máximo es menor al mínimo */
                campoMin.value = campoMax.value;  /* Ajusto el campo mínimo */
                numeroMin.value = campoMax.value.replace('.', ',');  /* Ajusto el valor numérico mínimo con coma */
            }
        });
    }
}

// Función que configura el comportamiento de los campos de totales para pedidos
function configurarRangesTotalesPedidos() {
    // Busco los elementos de total mínimo
    const campoMin = document.getElementById('filtro-pedido-total-min'); /* Obtengo el campo de total mínimo */
    const numeroMin = document.getElementById('output-pedido-total-min'); /* Obtengo el campo numérico del total mínimo */
    
    // Busco los elementos de total máximo  
    const campoMax = document.getElementById('filtro-pedido-total-max'); /* Obtengo el campo de total máximo */
    const numeroMax = document.getElementById('output-pedido-total-max'); /* Obtengo el campo numérico del total máximo */
    
    // Si existen los elementos del total mínimo
    if (campoMin && numeroMin) { /* Si ambos elementos existen */
        // Cuando muevo el campo mínimo
        campoMin.addEventListener('input', function() { /* Escucho cuando se mueve el campo mínimo */
            // Actualizo el número que se muestra con formato español (coma decimal)
            numeroMin.value = parseFloat(campoMin.value).toFixed(2).replace('.', ','); /* Sincronizo el valor numérico con el campo usando coma */

            // Si el mínimo es mayor que el máximo, corrijo el máximo
            if (parseFloat(campoMin.value) > parseFloat(campoMax.value)) { /* Si el mínimo supera al máximo */
                campoMax.value = campoMin.value;  /* Ajusto el campo máximo */
                numeroMax.value = parseFloat(campoMin.value).toFixed(2).replace('.', ',');  /* Ajusto el valor numérico máximo con coma */
            }
        });
    }
    
    // Si existen los elementos del total máximo
    if (campoMax && numeroMax) { /* Si ambos elementos existen */
        // Cuando muevo el campo máximo
        campoMax.addEventListener('input', function() { /* Escucho cuando se mueve el campo máximo */
            // Actualizo el número que se muestra con formato español (coma decimal)
            numeroMax.value = parseFloat(campoMax.value).toFixed(2).replace('.', ','); /* Sincronizo el valor numérico con el campo usando coma */

            // Si el máximo es menor que el mínimo, corrijo el mínimo
            if (parseFloat(campoMax.value) < parseFloat(campoMin.value)) { /* Si el máximo es menor al mínimo */
                campoMin.value = campoMax.value;  /* Ajusto el campo mínimo */
                numeroMin.value = parseFloat(campoMax.value).toFixed(2).replace('.', ',');  /* Ajusto el valor numérico mínimo con coma */
            }
        });
    }
}

// Función que configura el comportamiento de los campos de fecha para usuarios
function configurarRangesFechas() {
    // Pares de campos fecha desde/hasta
    const paresFechas = [
        { desde: 'filtro_fecha_creacion_desde', hasta: 'filtro_fecha_creacion_hasta' },
        { desde: 'filtro_fecha_actualizacion_desde', hasta: 'filtro_fecha_actualizacion_hasta' },
        { desde: 'filtro_fecha_acceso_desde', hasta: 'filtro_fecha_acceso_hasta' }
    ];

    paresFechas.forEach(par => { /* Recorro cada par de fechas */
        const campoDesde = document.getElementById(par.desde); /* Obtengo el campo desde */
        const campoHasta = document.getElementById(par.hasta); /* Obtengo el campo hasta */

        // Si no existen los campos, saltar este par
        if (!campoDesde || !campoHasta) { /* Si alguno no existe */
            return; /* Continúo con el siguiente */
        }

        // Establecer la fecha máxima permitida (hoy) en ambos campos
        campoDesde.max = new Date().toLocaleDateString('en-CA'); /* No permito seleccionar fechas futuras en el campo desde */
        campoHasta.max = new Date().toLocaleDateString('en-CA'); /* No permito seleccionar fechas futuras en el campo hasta */

        // Cuando cambia la fecha desde
        campoDesde.addEventListener('change', function() { /* Escucho cuando cambia el campo desde */
            // Si hay fecha desde y fecha hasta
            if (campoDesde.value && campoHasta.value) { /* Si ambos tienen valor */
                // Si la fecha desde es posterior a la fecha hasta
                if (campoDesde.value > campoHasta.value) { /* Si desde es mayor que hasta */
                    campoHasta.value = campoDesde.value; /* Ajusto el hasta al valor del desde */
                }
            }
        });

        // Cuando cambia la fecha hasta
        campoHasta.addEventListener('change', function() { /* Escucho cuando cambia el campo hasta */
            // Si hay fecha desde y fecha hasta
            if (campoDesde.value && campoHasta.value) { /* Si ambos tienen valor */
                // Si la fecha hasta es anterior a la fecha desde
                if (campoHasta.value < campoDesde.value) { /* Si hasta es menor que desde */
                    campoDesde.value = campoHasta.value; /* Ajusto el desde al valor del hasta */
                }
            }
        });
    });
}

// Función que configura el comportamiento de los campos de fecha para pedidos
function configurarRangesFechasPedidos() {
    // Pares de campos fecha desde/hasta para pedidos
    const paresFechasPedidos = [
        { desde: 'filtro_pedido_creado_desde', hasta: 'filtro_pedido_creado_hasta' },
        { desde: 'filtro_pedido_actualizado_desde', hasta: 'filtro_pedido_actualizado_hasta' }
    ];

    paresFechasPedidos.forEach(par => { /* Recorro cada par de fechas */
        const campoDesde = document.getElementById(par.desde); /* Obtengo el campo desde */
        const campoHasta = document.getElementById(par.hasta); /* Obtengo el campo hasta */

        // Si no existen los campos, saltar este par
        if (!campoDesde || !campoHasta) { /* Si alguno no existe */
            return; /* Continúo con el siguiente */
        }

        // Establecer la fecha máxima permitida (hoy) en ambos campos
        campoDesde.max = new Date().toLocaleDateString('en-CA'); /* No permito seleccionar fechas futuras en el campo desde */
        campoHasta.max = new Date().toLocaleDateString('en-CA'); /* No permito seleccionar fechas futuras en el campo hasta */

        // Cuando cambia la fecha desde
        campoDesde.addEventListener('change', function() { /* Escucho cuando cambia el campo desde */
            // Si hay fecha desde y fecha hasta
            if (campoDesde.value && campoHasta.value) { /* Si ambos tienen valor */
                // Si la fecha desde es posterior a la fecha hasta
                if (campoDesde.value > campoHasta.value) { /* Si desde es mayor que hasta */
                    campoHasta.value = campoDesde.value; /* Ajusto el hasta al valor del desde */
                }
            }
        });

        // Cuando cambia la fecha hasta
        campoHasta.addEventListener('change', function() { /* Escucho cuando cambia el campo hasta */
            // Si hay fecha desde y fecha hasta
            if (campoDesde.value && campoHasta.value) { /* Si ambos tienen valor */
                // Si la fecha hasta es anterior a la fecha desde
                if (campoHasta.value < campoDesde.value) { /* Si hasta es menor que desde */
                    campoDesde.value = campoHasta.value; /* Ajusto el desde al valor del hasta */
                }
            }
        });
    });
}

// Función que restaura los filtros que están guardados en la sesión
function restaurarFiltrosDeSesion() {
    // Obtener el modo de edición guardado en la variable global
    let modoEdicion = window.modoEdicion || 'juegos'; /* Obtengo el modo de edición de la variable global o 'juegos' por defecto */

    if(modoEdicion === 'juegos') { /* Si el modo de edición es 'juegos' */
        // Obtener los filtros guardados en la variable global (definida en encabezado.php)
        const filtros = window.filtrosElegidos || {}; /* Obtengo los filtros de la variable global o un objeto vacío */
        
        // Tipos
        const selectTipo = document.getElementById('id_preferencia_tipo'); /* Obtengo el select de tipos */
        if (selectTipo) { /* Si el elemento existe */
            if(filtros.tipo !== undefined && filtros.tipo !== 0) { /* Si existe el filtro de tipo y no es 0 */
                selectTipo.value = filtros.tipo; /* Aplico el filtro de tipo */
            } else { /* Si no hay filtro o es 0 */
                selectTipo.value = 'null'; /* Lo pongo a null */
            }
        }
        
        // Géneros
        const selectGenero = document.getElementById('id_preferencia_genero'); /* Obtengo el select de géneros */
        if (selectGenero) { /* Si el elemento existe */
            if(filtros.genero !== undefined && filtros.genero !== 0) { /* Si existe el filtro de género y no es 0 */
                selectGenero.value = filtros.genero; /* Aplico el filtro de género */
            } else { /* Si no hay filtro o es 0 */
                selectGenero.value = 'null'; /* Lo pongo a null */
            }
        }
        
        // Categorías
        const selectCategoria = document.getElementById('id_preferencia_categoria'); /* Obtengo el select de categorías */
        if (selectCategoria) { /* Si el elemento existe */
            if(filtros.categoria !== undefined && filtros.categoria !== 0) { /* Si existe el filtro de categoría y no es 0 */
                selectCategoria.value = filtros.categoria; /* Aplico el filtro de categoría */
            } else { /* Si no hay filtro o es 0 */
                selectCategoria.value = 'null'; /* Lo pongo a null */
            }
        }
        
        // Modos
        const selectModo = document.getElementById('id_preferencia_modo'); /* Obtengo el select de modos */
        if (selectModo) { /* Si el elemento existe */
            if(filtros.modo !== undefined && filtros.modo !== 0) { /* Si existe el filtro de modo y no es 0 */
                selectModo.value = filtros.modo; /* Aplico el filtro de modo */
            } else { /* Si no hay filtro o es 0 */
                selectModo.value = 'null'; /* Lo pongo a null */
            }
        }
        
        // PEGI
        const selectPegi = document.getElementById('id_preferencia_pegi'); /* Obtengo el select de PEGI */
        if (selectPegi) { /* Si el elemento existe */
            if(filtros.pegi !== undefined && filtros.pegi !== 0) { /* Si existe el filtro de PEGI y no es 0 */
                selectPegi.value = filtros.pegi; /* Aplico el filtro de PEGI */
            } else { /* Si no hay filtro o es 0 */
                selectPegi.value = 'null'; /* Lo pongo a null */
            }
        }
        
        // Precio mínimo
        const precioMin = document.getElementById('precio-min'); /* Obtengo el campo de precio mínimo */
        const outputMin = document.getElementById('output-min'); /* Obtengo el output del precio mínimo */
        if (precioMin && outputMin) { /* Si ambos elementos existen */
            const valorMin = (filtros.precio_min !== undefined) ? filtros.precio_min : 0; /* Uso el filtro guardado o 0 por defecto */
            precioMin.value = valorMin; /* Establezco el valor del campo */
            outputMin.value = String(valorMin).replace('.', ','); /* Actualizo el número mostrado con coma */
        }
        
        // Precio máximo
        const precioMax = document.getElementById('precio-max'); /* Obtengo el campo de precio máximo */
        const outputMax = document.getElementById('output-max'); /* Obtengo el output del precio máximo */
        if (precioMax && outputMax) { /* Si ambos elementos existen */
            const valorMax = (filtros.precio_max !== undefined) ? filtros.precio_max : 100; /* Uso el filtro guardado o 100 por defecto */
            precioMax.value = valorMax; /* Establezco el valor del campo */
            outputMax.value = String(valorMax).replace('.', ','); /* Actualizo el número mostrado con coma */
        }

    } else if(modoEdicion === 'usuarios') {
        // Obtener los filtros guardados en la variable global (definida en encabezado.php)
        const filtrosUsuarios = window.filtrosUsuarios || {}; /* Obtengo los filtros de usuarios de la variable global o un objeto vacío */

        // Rol
        const selectRol = document.getElementById('filtro_rol'); /* Obtengo el select de rol */
        if (selectRol) { /* Si el elemento existe */
            if(filtrosUsuarios.rol !== undefined && filtrosUsuarios.rol !== 'null') { /* Si existe el filtro de rol y no es null */
                selectRol.value = filtrosUsuarios.rol; /* Aplico el filtro de rol */
            } else { /* Si no hay filtro o es null */
                selectRol.value = 'null'; /* Lo pongo a null */
            }
        }

        // Acrónimo
        const selectAcronimo = document.getElementById('filtro_acronimo'); /* Obtengo el select de acrónimo */
        if (selectAcronimo) { /* Si el elemento existe */
            if(filtrosUsuarios.acronimo !== undefined && filtrosUsuarios.acronimo !== 'null') { /* Si existe el filtro de acrónimo y no es null */
                selectAcronimo.value = filtrosUsuarios.acronimo; /* Aplico el filtro de acrónimo */
            } else { /* Si no hay filtro o es null */
                selectAcronimo.value = 'null'; /* Lo pongo a null */
            }
        }

        // Correo
        const selectCorreo = document.getElementById('filtro_correo'); /* Obtengo el select de correo */
        if (selectCorreo) { /* Si el elemento existe */
            if(filtrosUsuarios.email !== undefined && filtrosUsuarios.email !== 'null') { /* Si existe el filtro de email y no es null */
                selectCorreo.value = filtrosUsuarios.email; /* Aplico el filtro de email */
            } else { /* Si no hay filtro o es null */
                selectCorreo.value = 'null'; /* Lo pongo a null */
            }
        }

        // DNI
        const selectDni = document.getElementById('filtro_dni'); /* Obtengo el select de DNI */
        if (selectDni) { /* Si el elemento existe */
            if(filtrosUsuarios.dni !== undefined && filtrosUsuarios.dni !== 'null') { /* Si existe el filtro de DNI y no es null */
                selectDni.value = filtrosUsuarios.dni; /* Aplico el filtro de DNI */
            } else { /* Si no hay filtro o es null */
                selectDni.value = 'null'; /* Lo pongo a null */
            }
        }

        // Nombre
        const selectNombre = document.getElementById('filtro_nombre'); /* Obtengo el select de nombre */
        if (selectNombre) { /* Si el elemento existe */
            if(filtrosUsuarios.nombre !== undefined && filtrosUsuarios.nombre !== 'null') { /* Si existe el filtro de nombre y no es null */
                selectNombre.value = filtrosUsuarios.nombre; /* Aplico el filtro de nombre */
            } else { /* Si no hay filtro o es null */
                selectNombre.value = 'null'; /* Lo pongo a null */
            }
        }

        // Apellidos
        const selectApellidos = document.getElementById('filtro_apellidos'); /* Obtengo el select de apellidos */
        if (selectApellidos) { /* Si el elemento existe */
            if(filtrosUsuarios.apellidos !== undefined && filtrosUsuarios.apellidos !== 'null') { /* Si existe el filtro de apellidos y no es null */
                selectApellidos.value = filtrosUsuarios.apellidos; /* Aplico el filtro de apellidos */
            } else { /* Si no hay filtro o es null */
                selectApellidos.value = 'null'; /* Lo pongo a null */
            }
        }

        // Fecha de creación desde
        const fechaCreacionDesde = document.getElementById('filtro_fecha_creacion_desde'); /* Obtengo el input de fecha de creación desde */
        if (fechaCreacionDesde) { /* Si el elemento existe */
            fechaCreacionDesde.value = filtrosUsuarios.fecha_creacion_desde || ''; /* Aplico el filtro o vacío */
        }

        // Fecha de creación hasta
        const fechaCreacionHasta = document.getElementById('filtro_fecha_creacion_hasta'); /* Obtengo el input de fecha de creación hasta */
        if (fechaCreacionHasta) { /* Si el elemento existe */
            fechaCreacionHasta.value = filtrosUsuarios.fecha_creacion_hasta || ''; /* Aplico el filtro o vacío */
        }

        // Fecha de actualización desde
        const fechaActualizacionDesde = document.getElementById('filtro_fecha_actualizacion_desde'); /* Obtengo el input de fecha de actualización desde */
        if (fechaActualizacionDesde) { /* Si el elemento existe */
            fechaActualizacionDesde.value = filtrosUsuarios.fecha_actualizacion_desde || ''; /* Aplico el filtro o vacío */
        }

        // Fecha de actualización hasta
        const fechaActualizacionHasta = document.getElementById('filtro_fecha_actualizacion_hasta'); /* Obtengo el input de fecha de actualización hasta */
        if (fechaActualizacionHasta) { /* Si el elemento existe */
            fechaActualizacionHasta.value = filtrosUsuarios.fecha_actualizacion_hasta || ''; /* Aplico el filtro o vacío */
        }

        // Fecha de acceso desde
        const fechaAccesoDesde = document.getElementById('filtro_fecha_acceso_desde'); /* Obtengo el input de fecha de acceso desde */
        if (fechaAccesoDesde) { /* Si el elemento existe */
            fechaAccesoDesde.value = filtrosUsuarios.fecha_acceso_desde || ''; /* Aplico el filtro o vacío */
        }

        // Fecha de acceso hasta
        const fechaAccesoHasta = document.getElementById('filtro_fecha_acceso_hasta'); /* Obtengo el input de fecha de acceso hasta */
        if (fechaAccesoHasta) { /* Si el elemento existe */
            fechaAccesoHasta.value = filtrosUsuarios.fecha_acceso_hasta || ''; /* Aplico el filtro o vacío */
        }
    } else if(modoEdicion === 'pedidos') { /* Si el modo de edición es 'pedidos' */
        // Obtener los filtros guardados en la variable global (definida en encabezado.php)
        const filtrosPedidos = window.filtrosPedidos || {}; /* Obtengo los filtros de pedidos de la variable global o un objeto vacío */

        // Tipo de operación
        const selectTipo = document.getElementById('filtro_pedido_tipo'); /* Obtengo el select de tipo */
        if (selectTipo) { /* Si el elemento existe */
            selectTipo.value = filtrosPedidos.tipo || 'null'; /* Aplico el filtro o null */
        }

        // Estado del pedido
        const selectEstado = document.getElementById('filtro_pedido_estado'); /* Obtengo el select de estado */
        if (selectEstado) { /* Si el elemento existe */
            selectEstado.value = filtrosPedidos.estado || 'null'; /* Aplico el filtro o null */
        }

        // Estado del detalle
        const selectEstadoDetalle = document.getElementById('filtro_pedido_estado_detalle'); /* Obtengo el select de estado detalle */
        if (selectEstadoDetalle) { /* Si el elemento existe */
            selectEstadoDetalle.value = filtrosPedidos.estado_detalle || 'null'; /* Aplico el filtro o null */
        }

        // Acrónimo
        const selectAcronimo = document.getElementById('filtro_pedido_acronimo'); /* Obtengo el select de acrónimo */
        if (selectAcronimo) { /* Si el elemento existe */
            selectAcronimo.value = filtrosPedidos.acronimo || 'null'; /* Aplico el filtro o null */
        }

        // Nombre
        const selectNombre = document.getElementById('filtro_pedido_nombre'); /* Obtengo el select de nombre */
        if (selectNombre) { /* Si el elemento existe */
            selectNombre.value = filtrosPedidos.nombre || 'null'; /* Aplico el filtro o null */
        }

        // Apellidos
        const selectApellidos = document.getElementById('filtro_pedido_apellidos'); /* Obtengo el select de apellidos */
        if (selectApellidos) { /* Si el elemento existe */
            selectApellidos.value = filtrosPedidos.apellidos || 'null'; /* Aplico el filtro o null */
        }

        // Método de pago
        const selectMetodoPago = document.getElementById('filtro_pedido_metodo_pago'); /* Obtengo el select de método de pago */
        if (selectMetodoPago) { /* Si el elemento existe */
            selectMetodoPago.value = filtrosPedidos.metodo_pago || 'null'; /* Aplico el filtro o null */
        }

        // Total mínimo
        const totalMin = document.getElementById('filtro-pedido-total-min'); /* Obtengo el campo de total mínimo */
        const outputTotalMin = document.getElementById('output-pedido-total-min'); /* Obtengo el output del total mínimo */
        if (totalMin && outputTotalMin) { /* Si ambos elementos existen */
            const valorMin = (filtrosPedidos.total_min !== undefined) ? filtrosPedidos.total_min : window.totalMinHistorial; /* Uso el filtro guardado o el mínimo del historial */
            totalMin.value = valorMin; /* Establezco el valor del campo */
            outputTotalMin.value = parseFloat(valorMin).toFixed(2).replace('.', ','); /* Actualizo el número mostrado con coma */
        }

        // Total máximo
        const totalMax = document.getElementById('filtro-pedido-total-max'); /* Obtengo el campo de total máximo */
        const outputTotalMax = document.getElementById('output-pedido-total-max'); /* Obtengo el output del total máximo */
        if (totalMax && outputTotalMax) { /* Si ambos elementos existen */
            const valorMax = (filtrosPedidos.total_max !== undefined) ? filtrosPedidos.total_max : window.totalMaxHistorial; /* Uso el filtro guardado o el máximo del historial */
            totalMax.value = valorMax; /* Establezco el valor del campo */
            outputTotalMax.value = parseFloat(valorMax).toFixed(2).replace('.', ','); /* Actualizo el número mostrado con coma */
        }

        // Fecha de creación desde
        const fechaCreadoDesde = document.getElementById('filtro_pedido_creado_desde'); /* Obtengo el input de fecha creado desde */
        if (fechaCreadoDesde) { /* Si el elemento existe */
            fechaCreadoDesde.value = filtrosPedidos.creado_desde || ''; /* Aplico el filtro o vacío */
        }

        // Fecha de creación hasta
        const fechaCreadoHasta = document.getElementById('filtro_pedido_creado_hasta'); /* Obtengo el input de fecha creado hasta */
        if (fechaCreadoHasta) { /* Si el elemento existe */
            fechaCreadoHasta.value = filtrosPedidos.creado_hasta || ''; /* Aplico el filtro o vacío */
        }

        // Fecha de actualización desde
        const fechaActualizadoDesde = document.getElementById('filtro_pedido_actualizado_desde'); /* Obtengo el input de fecha actualizado desde */
        if (fechaActualizadoDesde) { /* Si el elemento existe */
            fechaActualizadoDesde.value = filtrosPedidos.actualizado_desde || ''; /* Aplico el filtro o vacío */
        }

        // Fecha de actualización hasta
        const fechaActualizadoHasta = document.getElementById('filtro_pedido_actualizado_hasta'); /* Obtengo el input de fecha actualizado hasta */
        if (fechaActualizadoHasta) { /* Si el elemento existe */
            fechaActualizadoHasta.value = filtrosPedidos.actualizado_hasta || ''; /* Aplico el filtro o vacío */
        }
    }
}

// Función que restaura todos los filtros a sus valores por defecto o preferencias del usuario
function restablecerFiltros(event) {
    event.preventDefault(); /* Cancelo la acción por defecto del botón */
    
    // Obtener el modo de edición guardado en la variable global
    let modoEdicion = window.modoEdicion || 'juegos'; /* Obtengo el modo de edición de la variable global o 'juegos' por defecto */
    
    if(modoEdicion === 'juegos') { /* Si el modo de edición es 'juegos' */
        // Obtener las preferencias del usuario
        const filtrosUsuario = window.preferenciasUsuario || []; /* Obtengo las preferencias del usuario o array vacío */
        
        // Crear objeto con las preferencias organizadas por tipo
        const preferenciasOriginales = { /* Creo objeto para organizar las preferencias */
            'tipo': null, /* Sin filtro de tipo por defecto */
            'genero': null, /* Sin filtro de género por defecto */
            'categoria': null, /* Sin filtro de categoría por defecto */
            'modo': null, /* Sin filtro de modo por defecto */
            'pegi': null /* Sin filtro de PEGI por defecto */
        };
        
        // Recorrer las preferencias y asignarlas según el tipo_filtro
        filtrosUsuario.forEach(preferencia => { /* Recorro cada preferencia del usuario */
            switch(preferencia.tipo_filtro) { /* Según el tipo de filtro */
                case 'generos': /* Si es un género */
                    preferenciasOriginales.genero = preferencia.id_fijo; /* Lo asigno al objeto */
                    break;
                case 'categorias': /* Si es una categoría */
                    preferenciasOriginales.categoria = preferencia.id_fijo; /* Lo asigno al objeto */
                    break;
                case 'modos': /* Si es un modo */
                    preferenciasOriginales.modo = preferencia.id_fijo; /* Lo asigno al objeto */
                    break;
                case 'clasificacionPEGI': /* Si es una clasificación PEGI */
                    preferenciasOriginales.pegi = preferencia.id_fijo; /* Lo asigno al objeto */
                    break;
            }
        });
        
        // Tipos
        const selectTipo = document.getElementById('id_preferencia_tipo'); /* Obtengo el select de tipos */
        if (selectTipo) { /* Si existe el elemento */
            selectTipo.value = preferenciasOriginales.tipo || 'null'; /* Establezco la preferencia o null */
        }
        
        // Géneros
        const selectGenero = document.getElementById('id_preferencia_genero'); /* Obtengo el select de géneros */
        if (selectGenero) { /* Si existe el elemento */
            selectGenero.value = preferenciasOriginales.genero || 'null'; /* Establezco la preferencia o null */
        }
        
        // Categorías
        const selectCategoria = document.getElementById('id_preferencia_categoria'); /* Obtengo el select de categorías */
        if (selectCategoria) { /* Si existe el elemento */
            selectCategoria.value = preferenciasOriginales.categoria || 'null'; /* Establezco la preferencia o null */
        }
        
        // Modos
        const selectModo = document.getElementById('id_preferencia_modo'); /* Obtengo el select de modos */
        if (selectModo) { /* Si existe el elemento */
            selectModo.value = preferenciasOriginales.modo || 'null'; /* Establezco la preferencia o null */
        }
        
        // PEGI
        const selectPegi = document.getElementById('id_preferencia_pegi'); /* Obtengo el select de PEGI */
        if (selectPegi) { /* Si existe el elemento */
            selectPegi.value = preferenciasOriginales.pegi || 'null'; /* Establezco la preferencia o null */
        }

        // Precios - Min
        const precioMin = document.getElementById('precio-min'); /* Obtengo el campo de precio mínimo */
        const outputMin = document.getElementById('output-min'); /* Obtengo el output del precio mínimo */
        if (precioMin && outputMin) { /* Si ambos elementos existen */
            precioMin.value = 0; /* Establezco 0 como valor mínimo */
            outputMin.value = '0'; /* Actualizo el número mostrado */
        }

        // Precios - Max
        const precioMax = document.getElementById('precio-max'); /* Obtengo el campo de precio máximo */
        const outputMax = document.getElementById('output-max'); /* Obtengo el output del precio máximo */
        if (precioMax && outputMax) { /* Si ambos elementos existen */
            precioMax.value = 100; /* Establezco 100 como valor máximo */
            outputMax.value = '100'; /* Actualizo el número mostrado */
        }
        
    } else if(modoEdicion === 'usuarios') { /* Si el modo de edición es 'usuarios' */
        // Restablecer filtros de usuarios a sus valores por defecto (null)
        
        // Rol
        const selectRol = document.getElementById('filtro_rol'); /* Obtengo el select de rol */
        if (selectRol) { /* Si el elemento existe */
            selectRol.value = 'null'; /* Lo pongo a null */
        }

        // Acrónimo
        const selectAcronimo = document.getElementById('filtro_acronimo'); /* Obtengo el select de acrónimo */
        if (selectAcronimo) { /* Si el elemento existe */
            selectAcronimo.value = 'null'; /* Lo pongo a null */
        }

        // Correo
        const selectCorreo = document.getElementById('filtro_correo'); /* Obtengo el select de correo */
        if (selectCorreo) { /* Si el elemento existe */
            selectCorreo.value = 'null'; /* Lo pongo a null */
        }

        // DNI
        const selectDni = document.getElementById('filtro_dni'); /* Obtengo el select de DNI */
        if (selectDni) { /* Si el elemento existe */
            selectDni.value = 'null'; /* Lo pongo a null */
        }

        // Nombre
        const selectNombre = document.getElementById('filtro_nombre'); /* Obtengo el select de nombre */
        if (selectNombre) { /* Si el elemento existe */
            selectNombre.value = 'null'; /* Lo pongo a null */
        }

        // Apellidos
        const selectApellidos = document.getElementById('filtro_apellidos'); /* Obtengo el select de apellidos */
        if (selectApellidos) { /* Si el elemento existe */
            selectApellidos.value = 'null'; /* Lo pongo a null */
        }

        // Fecha de creación desde
        const fechaCreacionDesde = document.getElementById('filtro_fecha_creacion_desde'); /* Obtengo el input de fecha de creación desde */
        if (fechaCreacionDesde) { /* Si el elemento existe */
            fechaCreacionDesde.value = ''; /* Lo limpio */
        }

        // Fecha de creación hasta
        const fechaCreacionHasta = document.getElementById('filtro_fecha_creacion_hasta'); /* Obtengo el input de fecha de creación hasta */
        if (fechaCreacionHasta) { /* Si el elemento existe */
            fechaCreacionHasta.value = ''; /* Lo limpio */
        }

        // Fecha de actualización desde
        const fechaActualizacionDesde = document.getElementById('filtro_fecha_actualizacion_desde'); /* Obtengo el input de fecha de actualización desde */
        if (fechaActualizacionDesde) { /* Si el elemento existe */
            fechaActualizacionDesde.value = ''; /* Lo limpio */
        }

        // Fecha de actualización hasta
        const fechaActualizacionHasta = document.getElementById('filtro_fecha_actualizacion_hasta'); /* Obtengo el input de fecha de actualización hasta */
        if (fechaActualizacionHasta) { /* Si el elemento existe */
            fechaActualizacionHasta.value = ''; /* Lo limpio */
        }

        // Fecha de acceso desde
        const fechaAccesoDesde = document.getElementById('filtro_fecha_acceso_desde'); /* Obtengo el input de fecha de acceso desde */
        if (fechaAccesoDesde) { /* Si el elemento existe */
            fechaAccesoDesde.value = ''; /* Lo limpio */
        }

        // Fecha de acceso hasta
        const fechaAccesoHasta = document.getElementById('filtro_fecha_acceso_hasta'); /* Obtengo el input de fecha de acceso hasta */
        if (fechaAccesoHasta) { /* Si el elemento existe */
            fechaAccesoHasta.value = ''; /* Lo limpio */
        }
    } else if(modoEdicion === 'pedidos') { /* Si el modo de edición es 'pedidos' */
        // Restablecer filtros de pedidos a sus valores por defecto
        
        // Tipo
        const selectTipo = document.getElementById('filtro_pedido_tipo'); /* Obtengo el select de tipo */
        if (selectTipo) { /* Si el elemento existe */
            selectTipo.value = 'null'; /* Lo pongo a null */
        }

        // Estado
        const selectEstado = document.getElementById('filtro_pedido_estado'); /* Obtengo el select de estado */
        if (selectEstado) { /* Si el elemento existe */
            selectEstado.value = 'null'; /* Lo pongo a null */
        }

        // Estado detalle
        const selectEstadoDetalle = document.getElementById('filtro_pedido_estado_detalle'); /* Obtengo el select de estado detalle */
        if (selectEstadoDetalle) { /* Si el elemento existe */
            selectEstadoDetalle.value = 'null'; /* Lo pongo a null */
        }

        // Acrónimo
        const selectAcronimo = document.getElementById('filtro_pedido_acronimo'); /* Obtengo el select de acrónimo */
        if (selectAcronimo) { /* Si el elemento existe */
            selectAcronimo.value = 'null'; /* Lo pongo a null */
        }

        // Nombre
        const selectNombre = document.getElementById('filtro_pedido_nombre'); /* Obtengo el select de nombre */
        if (selectNombre) { /* Si el elemento existe */
            selectNombre.value = 'null'; /* Lo pongo a null */
        }

        // Apellidos
        const selectApellidos = document.getElementById('filtro_pedido_apellidos'); /* Obtengo el select de apellidos */
        if (selectApellidos) { /* Si el elemento existe */
            selectApellidos.value = 'null'; /* Lo pongo a null */
        }

        // Método de pago
        const selectMetodoPago = document.getElementById('filtro_pedido_metodo_pago'); /* Obtengo el select de método de pago */
        if (selectMetodoPago) { /* Si el elemento existe */
            selectMetodoPago.value = 'null'; /* Lo pongo a null */
        }

        // Totales - Min
        const totalMin = document.getElementById('filtro-pedido-total-min'); /* Obtengo el campo de total mínimo */
        const outputTotalMin = document.getElementById('output-pedido-total-min'); /* Obtengo el output del total mínimo */
        if (totalMin && outputTotalMin) { /* Si ambos elementos existen */
            totalMin.value = window.totalMinHistorial; /* Establezco el mínimo del historial */
            outputTotalMin.value = parseFloat(window.totalMinHistorial).toFixed(2).replace('.', ','); /* Actualizo el número mostrado con coma */
        }

        // Totales - Max
        const totalMax = document.getElementById('filtro-pedido-total-max'); /* Obtengo el campo de total máximo */
        const outputTotalMax = document.getElementById('output-pedido-total-max'); /* Obtengo el output del total máximo */
        if (totalMax && outputTotalMax) { /* Si ambos elementos existen */
            totalMax.value = window.totalMaxHistorial; /* Establezco el máximo del historial */
            outputTotalMax.value = parseFloat(window.totalMaxHistorial).toFixed(2).replace('.', ','); /* Actualizo el número mostrado con coma */
        }

        // Fecha creado desde
        const fechaCreadoDesde = document.getElementById('filtro_pedido_creado_desde'); /* Obtengo el input de fecha creado desde */
        if (fechaCreadoDesde) { /* Si el elemento existe */
            fechaCreadoDesde.value = ''; /* Lo limpio */
        }

        // Fecha creado hasta
        const fechaCreadoHasta = document.getElementById('filtro_pedido_creado_hasta'); /* Obtengo el input de fecha creado hasta */
        if (fechaCreadoHasta) { /* Si el elemento existe */
            fechaCreadoHasta.value = ''; /* Lo limpio */
        }

        // Fecha actualizado desde
        const fechaActualizadoDesde = document.getElementById('filtro_pedido_actualizado_desde'); /* Obtengo el input de fecha actualizado desde */
        if (fechaActualizadoDesde) { /* Si el elemento existe */
            fechaActualizadoDesde.value = ''; /* Lo limpio */
        }

        // Fecha actualizado hasta
        const fechaActualizadoHasta = document.getElementById('filtro_pedido_actualizado_hasta'); /* Obtengo el input de fecha actualizado hasta */
        if (fechaActualizadoHasta) { /* Si el elemento existe */
            fechaActualizadoHasta.value = ''; /* Lo limpio */
        }
    }

    // Simular clic en el botón de aplicar filtros
    const botonAplicarFiltros = document.getElementById('aplicar-filtros'); /* Obtengo el botón de aplicar filtros */
    if (botonAplicarFiltros) { /* Si existe el botón */
        botonAplicarFiltros.click(); /* Simulo un clic para aplicar los filtros restablecidos */
    }
}
