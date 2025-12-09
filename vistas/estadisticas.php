    <!-- Encabezado -->
    <?php include __DIR__ . '/comunes/encabezado.php'; ?> <!-- Incluyo el encabezado con menú y estilos -->

    <?php

        // Verificar sesión y rol de administrador
        if(!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
            echo '<script>window.location.href = "../publico/index.php";</script>'; /* Redirijo si no es administrador */
            exit; /* Termino la ejecución */
        }

        // BLOQUE 1: RESUMEN GLOBAL (desde siempre)
        
        // Ventas totales (€) - incluye compras pagadas y reservas completadas
        $consulta = $conexion->query("SELECT SUM(total) as ventas_totales 
                                    FROM historial 
                                    WHERE (tipo = 'COMPRA' AND estado = 'PAGADA') OR (tipo = 'RESERVA' AND estado = 'RESERVADA')"); /* Consulto la suma de todas las compras pagadas y reservas completadas */
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el resultado */
        $ventas_totales = $resultado['ventas_totales'] ? $resultado['ventas_totales'] : 0; /* Si es NULL, lo pongo a 0 */

        // Número de pedidos completados
        $consulta = $conexion->query("SELECT COUNT(*) as pedidos_completados 
                                    FROM historial 
                                    WHERE tipo = 'COMPRA' AND estado = 'PAGADA'"); /* Consulto la cantidad de pedidos completados */
        $pedidos_completados = $consulta->fetch(PDO::FETCH_ASSOC)['pedidos_completados']; /* Obtengo el resultado */

        // Número de reservas solicitadas
        $consulta = $conexion->query("SELECT COUNT(*) as reservas_solicitadas 
                                    FROM historial 
                                    WHERE tipo = 'RESERVA' AND estado = 'PENDIENTE'"); /* Consulto la cantidad de reservas solicitadas */
        $reservas_solicitadas = $consulta->fetch(PDO::FETCH_ASSOC)['reservas_solicitadas']; /* Obtengo el resultado */

        // Número de devoluciones aprobadas
        $consulta = $conexion->query("SELECT COUNT(*) as devoluciones_aprobadas 
                                    FROM historial 
                                    WHERE tipo = 'SOLICITUD_DEVOLUCION' AND estado = 'APROBADA'"); /* Consulto la cantidad de devoluciones aprobadas */
        $devoluciones_aprobadas = $consulta->fetch(PDO::FETCH_ASSOC)['devoluciones_aprobadas']; /* Obtengo el resultado */

        // Porcentaje de devoluciones sobre compras
        $porcentaje_devoluciones = 0;
        if ($pedidos_completados > 0) { /* Evito división por cero */
            $porcentaje_devoluciones = ($devoluciones_aprobadas / $pedidos_completados) * 100; /* Calculo el porcentaje */
        }

        // Pedidos cancelados
        $consulta = $conexion->query("SELECT COUNT(*) as pedidos_cancelados 
                                    FROM historial 
                                    WHERE tipo = 'COMPRA' AND estado = 'CANCELADA'"); /* Consulto la cantidad de pedidos cancelados */
        $pedidos_cancelados = $consulta->fetch(PDO::FETCH_ASSOC)['pedidos_cancelados']; /* Obtengo el resultado */

        // Reservas aprobadas
        $consulta = $conexion->query("SELECT COUNT(*) as reservas_aprobadas 
                                    FROM historial 
                                    WHERE tipo = 'RESERVA' AND estado = 'APROBADA'"); /* Consulto la cantidad de reservas aprobadas */
        $reservas_aprobadas = $consulta->fetch(PDO::FETCH_ASSOC)['reservas_aprobadas']; /* Obtengo el resultado */

        // Reservas rechazadas
        $consulta = $conexion->query("SELECT COUNT(*) as reservas_rechazadas 
                                    FROM historial 
                                    WHERE tipo = 'RESERVA' AND estado = 'RECHAZADA'"); /* Consulto la cantidad de reservas rechazadas */
        $reservas_rechazadas = $consulta->fetch(PDO::FETCH_ASSOC)['reservas_rechazadas']; /* Obtengo el resultado */

        // Reservas completadas
        $consulta = $conexion->query("SELECT COUNT(*) as reservas_completadas 
                                    FROM historial 
                                    WHERE tipo = 'RESERVA' AND estado = 'RESERVADA'"); /* Consulto la cantidad de reservas completadas */
        $reservas_completadas = $consulta->fetch(PDO::FETCH_ASSOC)['reservas_completadas']; /* Obtengo el resultado */

        // Devoluciones rechazadas
        $consulta = $conexion->query("SELECT COUNT(*) as devoluciones_rechazadas 
                                    FROM historial 
                                    WHERE tipo = 'SOLICITUD_DEVOLUCION' AND estado = 'RECHAZADA'"); /* Consulto la cantidad de devoluciones rechazadas */
        $devoluciones_rechazadas = $consulta->fetch(PDO::FETCH_ASSOC)['devoluciones_rechazadas']; /* Obtengo el resultado */

        // Devoluciones completadas
        $consulta = $conexion->query("SELECT COUNT(*) as devoluciones_completadas 
                                    FROM historial 
                                    WHERE tipo = 'DEVOLUCION' AND estado = 'COMPLETADA'"); /* Consulto la cantidad de devoluciones completadas */
        $devoluciones_completadas = $consulta->fetch(PDO::FETCH_ASSOC)['devoluciones_completadas']; /* Obtengo el resultado */

        // Devoluciones solicitadas
        $consulta = $conexion->query("SELECT COUNT(*) as devoluciones_solicitadas 
                                    FROM historial 
                                    WHERE tipo = 'SOLICITUD_DEVOLUCION' AND estado = 'PENDIENTE_REVISION'"); /* Consulto la cantidad de devoluciones solicitadas */
        $devoluciones_solicitadas = $consulta->fetch(PDO::FETCH_ASSOC)['devoluciones_solicitadas']; /* Obtengo el resultado */

        // Reservas canceladas
        $consulta = $conexion->query("SELECT COUNT(*) as reservas_canceladas 
                                    FROM historial 
                                    WHERE tipo = 'RESERVA' AND estado = 'CANCELADA'"); /* Consulto la cantidad de reservas canceladas */
        $reservas_canceladas = $consulta->fetch(PDO::FETCH_ASSOC)['reservas_canceladas']; /* Obtengo el resultado */

        // Devoluciones canceladas
        $consulta = $conexion->query("SELECT COUNT(*) as devoluciones_canceladas 
                                    FROM historial 
                                    WHERE tipo = 'SOLICITUD_DEVOLUCION' AND estado = 'CANCELADA'"); /* Consulto la cantidad de devoluciones canceladas */
        $devoluciones_canceladas = $consulta->fetch(PDO::FETCH_ASSOC)['devoluciones_canceladas']; /* Obtengo el resultado */

        // Ventas perdidas (devoluciones completadas)
        $consulta = $conexion->query("SELECT SUM(total) as ventas_perdidas 
                                    FROM historial 
                                    WHERE tipo = 'DEVOLUCION' AND estado = 'COMPLETADA'"); /* Consulto la suma de las devoluciones completadas */
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el resultado */
        $ventas_perdidas = $resultado['ventas_perdidas'] ? $resultado['ventas_perdidas'] : 0; /* Si es NULL, lo pongo a 0 */

        // Ganancias netas (ventas totales - ventas perdidas)
        $ganancias_netas = $ventas_totales - $ventas_perdidas;

        // Porcentaje de ganancias
        $porcentaje_ganancias = 0;
        if ($ventas_totales > 0) { /* Evito división por cero */
            $porcentaje_ganancias = ($ganancias_netas / $ventas_totales) * 100; /* Calculo el porcentaje */
        }

        // Porcentaje de pérdidas
        $porcentaje_perdidas = 0;
        if ($ventas_totales > 0) { /* Evito división por cero */
            $porcentaje_perdidas = ($ventas_perdidas / $ventas_totales) * 100; /* Calculo el porcentaje */
        }

        // BLOQUE 2: RESUMEN POR PERIODOS FIJOS

        // Calcular fechas de referencia
        $fecha_hace_7dias = date('Y-m-d', strtotime('-7 days')); /* Fecha hace 7 días */
        $fecha_hace_30dias = date('Y-m-d', strtotime('-30 days')); /* Fecha hace 30 días */
        $fecha_hace_12meses = date('Y-m-d', strtotime('-12 months')); /* Fecha hace 12 meses */

        // Últimos 7 días - Ventas (compras + reservas)
            $consulta = $conexion->query("SELECT SUM(total) as total 
                                            FROM historial 
                                            WHERE ((tipo = 'COMPRA' AND estado = 'PAGADA') OR (tipo = 'RESERVA' AND estado = 'RESERVADA'))
                                                AND actualizado_en >= '$fecha_hace_7dias'"); /* Consulto la suma de las compras pagadas y reservas completadas en los últimos 7 días */
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el resultado */
        $ventas_7dias = $resultado['total'] ? $resultado['total'] : 0; /* Si es NULL, lo pongo a 0 */

        // Últimos 7 días - Pedidos
        $consulta = $conexion->query("SELECT COUNT(*) as total 
                                        FROM historial 
                                        WHERE tipo = 'COMPRA' AND estado = 'PAGADA' 
                                            AND actualizado_en >= '$fecha_hace_7dias'"); /* Consulto la cantidad de pedidos completados en los últimos 7 días */
        $pedidos_7dias = $consulta->fetch(PDO::FETCH_ASSOC)['total']; /* Obtengo el resultado */

        // Últimos 7 días - Reservas
        $consulta = $conexion->query("SELECT COUNT(*) as total 
                                        FROM historial 
                                        WHERE tipo = 'RESERVA' AND estado = 'RESERVADA' 
                                            AND actualizado_en >= '$fecha_hace_7dias'"); /* Consulto la cantidad de reservas completadas en los últimos 7 días */
        $reservas_7dias = $consulta->fetch(PDO::FETCH_ASSOC)['total']; /* Obtengo el resultado */

        // Últimos 7 días - Devoluciones
        $consulta = $conexion->query("SELECT COUNT(*) as total 
                                        FROM historial 
                                        WHERE tipo = 'DEVOLUCION' AND estado = 'COMPLETADA' 
                                            AND actualizado_en >= '$fecha_hace_7dias'"); /* Consulto la cantidad de devoluciones completadas en los últimos 7 días */
        $devoluciones_7dias = $consulta->fetch(PDO::FETCH_ASSOC)['total']; /* Obtengo el resultado */

        // Agrupar datos de 7 días
        $datos_7dias = [
            'ventas_7dias' => $ventas_7dias,
            'pedidos_7dias' => $pedidos_7dias,
            'reservas_7dias' => $reservas_7dias,
            'devoluciones_7dias' => $devoluciones_7dias
        ];

        // Últimos 30 días - Ventas (compras + reservas)
            $consulta = $conexion->query("SELECT SUM(total) as total 
                                            FROM historial 
                                            WHERE ((tipo = 'COMPRA' AND estado = 'PAGADA') OR (tipo = 'RESERVA' AND estado = 'RESERVADA'))
                                                AND actualizado_en >= '$fecha_hace_30dias'"); /* Consulto la suma de las compras pagadas y reservas completadas en los últimos 30 días */
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el resultado */
        $ventas_30dias = $resultado['total'] ? $resultado['total'] : 0; /* Si es NULL, lo pongo a 0 */

        // Últimos 30 días - Pedidos
        $consulta = $conexion->query("SELECT COUNT(*) as total 
                                        FROM historial 
                                        WHERE tipo = 'COMPRA' AND estado = 'PAGADA' 
                                            AND actualizado_en >= '$fecha_hace_30dias'"); /* Consulto la cantidad de compras pagadas en los últimos 30 días */
        $pedidos_30dias = $consulta->fetch(PDO::FETCH_ASSOC)['total']; /* Obtengo el resultado */

        // Últimos 30 días - Reservas
        $consulta = $conexion->query("SELECT COUNT(*) as total 
                                        FROM historial 
                                        WHERE tipo = 'RESERVA' AND estado = 'RESERVADA' 
                                            AND actualizado_en >= '$fecha_hace_30dias'"); /* Consulto la cantidad de reservas completadas en los últimos 30 días */
        $reservas_30dias = $consulta->fetch(PDO::FETCH_ASSOC)['total']; /* Obtengo el resultado */

        // Últimos 30 días - Devoluciones
        $consulta = $conexion->query("SELECT COUNT(*) as total 
                                        FROM historial 
                                        WHERE tipo = 'DEVOLUCION' AND estado = 'COMPLETADA' 
                                            AND actualizado_en >= '$fecha_hace_30dias'"); /* Consulto la cantidad de devoluciones completadas en los últimos 30 días */
        $devoluciones_30dias = $consulta->fetch(PDO::FETCH_ASSOC)['total']; /* Obtengo el resultado */

        // Agrupar datos de 30 días
        $datos_30dias = [
            'ventas_30dias' => $ventas_30dias,
            'pedidos_30dias' => $pedidos_30dias,
            'reservas_30dias' => $reservas_30dias,
            'devoluciones_30dias' => $devoluciones_30dias
        ];

        // Últimos 12 meses - Ventas (compras + reservas)
            $consulta = $conexion->query("SELECT SUM(total) as total 
                                            FROM historial 
                                            WHERE ((tipo = 'COMPRA' AND estado = 'PAGADA') OR (tipo = 'RESERVA' AND estado = 'RESERVADA'))
                                                AND actualizado_en >= '$fecha_hace_12meses'");
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC); /* Obtengo el resultado */
        $ventas_12meses = $resultado['total'] ? $resultado['total'] : 0; /* Si es NULL, lo pongo a 0 */

        // Últimos 12 meses - Pedidos
        $consulta = $conexion->query("SELECT COUNT(*) as total 
                                        FROM historial 
                                        WHERE tipo = 'COMPRA' AND estado = 'PAGADA' 
                                            AND actualizado_en >= '$fecha_hace_12meses'"); /* Consulto la cantidad de compras pagadas en los últimos 12 meses */
        $pedidos_12meses = $consulta->fetch(PDO::FETCH_ASSOC)['total']; /* Obtengo el resultado */

        // Últimos 12 meses - Reservas
        $consulta = $conexion->query("SELECT COUNT(*) as total 
                                        FROM historial 
                                        WHERE tipo = 'RESERVA' AND estado = 'RESERVADA' 
                                            AND actualizado_en >= '$fecha_hace_12meses'"); /* Consulto la cantidad de reservas completadas en los últimos 12 meses */
        $reservas_12meses = $consulta->fetch(PDO::FETCH_ASSOC)['total']; /* Obtengo el resultado */

        // Últimos 12 meses - Devoluciones
        $consulta = $conexion->query("SELECT COUNT(*) as total 
                                        FROM historial 
                                        WHERE tipo = 'DEVOLUCION' AND estado = 'COMPLETADA' 
                                            AND actualizado_en >= '$fecha_hace_12meses'"); /* Consulto la cantidad de devoluciones completadas en los últimos 12 meses */
        $devoluciones_12meses = $consulta->fetch(PDO::FETCH_ASSOC)['total']; /* Obtengo el resultado */

        // Agrupar datos de 12 meses
        $datos_12meses = [
            'ventas_12meses' => $ventas_12meses,
            'pedidos_12meses' => $pedidos_12meses,
            'reservas_12meses' => $reservas_12meses,
            'devoluciones_12meses' => $devoluciones_12meses
        ];

        // BLOQUE 3: GRÁFICO DE EVOLUCIÓN DE VENTAS POR MES

        // Buscar ventas de los últimos 12 meses agrupadas por año y mes (compras + reservas)
        $consulta = $conexion->query("SELECT 
                                        YEAR(actualizado_en) as anio,
                                        MONTH(actualizado_en) as mes,
                                        SUM(total) as total_mes
                                        FROM historial 
                                        WHERE ((tipo = 'COMPRA' AND estado = 'PAGADA') OR (tipo = 'RESERVA' AND estado = 'RESERVADA'))
                                            AND actualizado_en >= '$fecha_hace_12meses'
                                        GROUP BY YEAR(actualizado_en), MONTH(actualizado_en)
                                        ORDER BY anio, mes DESC"); /* Preparo la consulta para obtener las ventas agrupadas por año y mes */
        $datos_grafico = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Obtengo los datos agrupados por año y mes */

        // Preparar arrays para gráfico de barras
        $etiquetas_meses = []; /* Etiquetas de los meses */
        $valores_ventas = []; /* Valores de ventas por mes */
        $anios = []; /* Años */
        $meses = []; /* Meses */
        $nombres_meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']; /* Nombres de los meses */

        foreach ($datos_grafico as $resultado) { /* Recorro los resultados */
            // Guardar año y mes por separado
            $anios[] = $resultado['anio']; /* Guardo el año */
            $mes_numero = intval($resultado['mes']); /* Obtengo el número del mes como entero */
            $meses[] = $nombres_meses[$mes_numero - 1]; /* Convierto el número a nombre (01 -> Enero, 02 -> Febrero, etc.) */
            $valores_ventas[] = $resultado['total_mes'] ? $resultado['total_mes'] : 0; /* Guardo el valor de ventas (o 0 si es NULL) */
        }

    ?>

    <link rel="stylesheet" href="../recursos/css/estilos_estadisticas.css" type="text/css"> <!-- Estilos específicos para estadísticas -->

    <main> <!-- Cuerpo del panel de estadísticas -->
        <div class="contenedor-estadisticas"> <!-- Contenedor principal de estadísticas -->
            <h1>Estadísticas Generales</h1> <!-- Título principal -->
            <hr> <!-- Línea divisoria -->

            <!-- BLOQUE 1: RESUMEN GLOBAL -->
            <section class="bloque-resumen-global"> <!-- Sección de resumen global -->
                <h2>Resumen Global (Desde siempre)</h2> <!-- Título de la sección -->
                
                <div class="categoria-estadisticas"> <!-- Categoría de estadísticas: Ganancias y Pérdidas -->
                    <h3 class="titulo-categoria">Ganancias y Pérdidas</h3> <!-- Título de la categoría -->
                    <div class="grupo-indicadores"> <!-- Grupo de indicadores -->
                        <div class="indicador"> <!-- Indicador de ventas totales -->
                            <span class="indicador-texto">Ganancias Netas:</span> <!-- Texto del indicador -->
                            <span class="indicador-valor"><?php echo number_format($ganancias_netas, 2, ',', '.'); ?> €</span> <!-- Valor del indicador -->
                        </div>
                        <div class="indicador"> <!-- Indicador de porcentaje de ganancias -->
                            <span class="indicador-texto">% Ganancias:</span> <!-- Texto del indicador -->
                            <span class="indicador-valor"><?php echo number_format($porcentaje_ganancias, 2, ',', '.'); ?>%</span> <!-- Valor del indicador -->
                        </div>
                        <div class="indicador"> <!-- Indicador de pérdidas netas -->
                            <span class="indicador-texto">Pérdidas Netas:</span> <!-- Texto del indicador -->
                            <span class="indicador-valor"><?php echo number_format($ventas_perdidas, 2, ',', '.'); ?> €</span> <!-- Valor del indicador -->
                        </div>
                        <div class="indicador"> <!-- Indicador de porcentaje de pérdidas -->
                            <span class="indicador-texto">% Pérdidas:</span> <!-- Texto del indicador -->
                            <span class="indicador-valor"><?php echo number_format($porcentaje_perdidas, 2, ',', '.'); ?>%</span> <!-- Valor del indicador -->
                        </div>
                    </div>
                </div>

                <div class="categoria-estadisticas"> <!-- Categoría de estadísticas: Pedidos -->
                    <h3 class="titulo-categoria">Pedidos</h3> <!-- Título de la categoría -->
                    <div class="grupo-indicadores"> <!-- Grupo de indicadores -->
                        <div class="indicador"> <!-- Indicador de pedidos completados -->
                            <span class="indicador-texto">Completados:</span> <!-- Texto del indicador -->
                            <span class="indicador-valor"><?php echo $pedidos_completados; ?></span> <!-- Valor del indicador -->
                        </div>
                        <div class="indicador"> <!-- Indicador de pedidos cancelados -->
                            <span class="indicador-texto">Cancelados:</span> <!-- Texto del indicador -->
                            <span class="indicador-valor"><?php echo $pedidos_cancelados; ?></span> <!-- Valor del indicador -->
                        </div>
                    </div>
                </div>

                <div class="categoria-estadisticas"> <!-- Categoría de estadísticas: Reservas -->
                    <h3 class="titulo-categoria">Reservas</h3> <!-- Título de la categoría -->
                    <div class="grupo-indicadores"> <!-- Grupo de indicadores -->
                        <div class="indicador"> <!-- Indicador de reservas solicitadas -->
                            <span class="indicador-texto">Solicitadas:</span> <!-- Texto del indicador -->
                            <span class="indicador-valor"><?php echo $reservas_solicitadas; ?></span> <!-- Valor del indicador -->
                        </div>
                        <div class="indicador"> <!-- Indicador de reservas aprobadas -->
                            <span class="indicador-texto">Aprobadas:</span> <!-- Texto del indicador -->
                            <span class="indicador-valor"><?php echo $reservas_aprobadas; ?></span> <!-- Valor del indicador -->
                        </div>
                        <div class="indicador"> <!-- Indicador de reservas rechazadas -->
                            <span class="indicador-texto">Rechazadas:</span> <!-- Texto del indicador -->
                            <span class="indicador-valor"><?php echo $reservas_rechazadas; ?></span> <!-- Valor del indicador -->
                        </div>
                        <div class="indicador"> <!-- Indicador de reservas completadas -->
                            <span class="indicador-texto">Completadas:</span> <!-- Texto del indicador -->
                            <span class="indicador-valor"><?php echo $reservas_completadas; ?></span> <!-- Valor del indicador -->
                        </div>
                        <div class="indicador"> <!-- Indicador de reservas canceladas -->
                            <span class="indicador-texto">Canceladas:</span> <!-- Texto del indicador -->
                            <span class="indicador-valor"><?php echo $reservas_canceladas; ?></span> <!-- Valor del indicador -->
                        </div>
                    </div>
                </div>

                <div class="categoria-estadisticas"> <!-- Categoría de estadísticas: Devoluciones -->
                    <h3 class="titulo-categoria">Devoluciones</h3> <!-- Título de la categoría -->
                    <div class="grupo-indicadores"> <!-- Grupo de indicadores -->
                        <div class="indicador"> <!-- Indicador de devoluciones solicitadas -->
                            <span class="indicador-texto">Solicitadas:</span> <!-- Texto del indicador -->
                            <span class="indicador-valor"><?php echo $devoluciones_solicitadas; ?></span> <!-- Valor del indicador -->
                        </div>
                        <div class="indicador"> <!-- Indicador de devoluciones aprobadas -->
                            <span class="indicador-texto">Aprobadas:</span> <!-- Texto del indicador -->
                            <span class="indicador-valor"><?php echo $devoluciones_aprobadas; ?></span> <!-- Valor del indicador -->
                        </div>
                        <div class="indicador"> <!-- Indicador de devoluciones rechazadas -->
                            <span class="indicador-texto">Rechazadas:</span> <!-- Texto del indicador -->
                            <span class="indicador-valor"><?php echo $devoluciones_rechazadas; ?></span> <!-- Valor del indicador -->
                        </div>
                        <div class="indicador"> <!-- Indicador de devoluciones completadas -->
                            <span class="indicador-texto">Completadas:</span> <!-- Texto del indicador -->
                            <span class="indicador-valor"><?php echo $devoluciones_completadas; ?></span> <!-- Valor del indicador -->
                        </div>
                        <div class="indicador"> <!-- Indicador de devoluciones canceladas -->
                            <span class="indicador-texto">Canceladas:</span> <!-- Texto del indicador -->
                            <span class="indicador-valor"><?php echo $devoluciones_canceladas; ?></span> <!-- Valor del indicador -->
                        </div>
                    </div>
                </div>
            </section>

            <!-- BLOQUE 2: RESUMEN POR PERIODOS -->
            <section class="bloque-periodos"> <!-- Sección de resumen por periodos -->
                <h2>Resumen por Periodos</h2> <!-- Título de la sección -->
                <div class="contenedor-periodos"> <!-- Contenedor de periodos -->
                    <!-- Últimos 7 días -->
                    <div class="periodo"> <!-- Periodo de últimos 7 días -->
                        <h3>Últimos 7 días</h3> <!-- Título del periodo -->
                        <div class="datos-periodo"> <!-- Datos del periodo -->
                            <div class="dato-periodo"> <!-- Dato de ventas -->
                                <span class="texto">Ventas:</span> <!-- Texto del dato -->
                                <span class="valor"><?php echo number_format($datos_7dias['ventas_7dias'], 2, ',', '.'); ?> €</span> <!-- Valor del dato -->
                            </div>
                            <div class="dato-periodo"> <!-- Dato de pedidos -->
                                <span class="texto">Pedidos:</span> <!-- Texto del dato -->
                                <span class="valor"><?php echo $datos_7dias['pedidos_7dias']; ?></span> <!-- Valor del dato -->
                            </div>
                            <div class="dato-periodo"> <!-- Dato de reservas -->
                                <span class="texto">Reservas:</span> <!-- Texto del dato -->
                                <span class="valor"><?php echo $datos_7dias['reservas_7dias']; ?></span> <!-- Valor del dato -->
                            </div>
                            <div class="dato-periodo"> <!-- Dato de devoluciones -->
                                <span class="texto">Devoluciones:</span> <!-- Texto del dato -->
                                <span class="valor"><?php echo $datos_7dias['devoluciones_7dias']; ?></span> <!-- Valor del dato -->
                            </div>
                        </div>
                    </div>

                    <!-- Últimos 30 días -->
                    <div class="periodo"> <!-- Periodo de últimos 30 días -->
                        <h3>Últimos 30 días</h3> <!-- Título del periodo -->
                        <div class="datos-periodo"> <!-- Datos del periodo -->
                            <div class="dato-periodo"> <!-- Dato de ventas -->
                                <span class="texto">Ventas:</span> <!-- Texto del dato -->
                                <span class="valor"><?php echo number_format($datos_30dias['ventas_30dias'], 2, ',', '.'); ?> €</span> <!-- Valor del dato -->
                            </div>
                            <div class="dato-periodo"> <!-- Dato de pedidos -->
                                <span class="texto">Pedidos:</span> <!-- Texto del dato -->
                                <span class="valor"><?php echo $datos_30dias['pedidos_30dias']; ?></span> <!-- Valor del dato -->
                            </div>
                            <div class="dato-periodo"> <!-- Dato de reservas -->
                                <span class="texto">Reservas:</span> <!-- Texto del dato -->
                                <span class="valor"><?php echo $datos_30dias['reservas_30dias']; ?></span> <!-- Valor del dato -->
                            </div>
                            <div class="dato-periodo"> <!-- Dato de devoluciones -->
                                <span class="texto">Devoluciones:</span> <!-- Texto del dato -->
                                <span class="valor"><?php echo $datos_30dias['devoluciones_30dias']; ?></span> <!-- Valor del dato -->
                            </div>
                        </div>
                    </div>

                    <!-- Últimos 12 meses -->
                    <div class="periodo"> <!-- Periodo de últimos 12 meses -->
                        <h3>Últimos 12 meses</h3> <!-- Título del periodo -->
                        <div class="datos-periodo"> <!-- Datos del periodo -->
                            <div class="dato-periodo"> <!-- Dato de ventas -->
                                <span class="texto">Ventas:</span> <!-- Texto del dato -->
                                <span class="valor"><?php echo number_format($datos_12meses['ventas_12meses'], 2, ',', '.'); ?> €</span> <!-- Valor del dato -->
                            </div>
                            <div class="dato-periodo"> <!-- Dato de pedidos -->
                                <span class="texto">Pedidos:</span> <!-- Texto del dato -->
                                <span class="valor"><?php echo $datos_12meses['pedidos_12meses']; ?></span> <!-- Valor del dato -->
                            </div>
                            <div class="dato-periodo"> <!-- Dato de reservas -->
                                <span class="texto">Reservas:</span> <!-- Texto del dato -->
                                <span class="valor"><?php echo $datos_12meses['reservas_12meses']; ?></span> <!-- Valor del dato -->
                            </div>
                            <div class="dato-periodo"> <!-- Dato de devoluciones -->
                                <span class="texto">Devoluciones:</span> <!-- Texto del dato -->
                                <span class="valor"><?php echo $datos_12meses['devoluciones_12meses']; ?></span> <!-- Valor del dato -->
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- BLOQUE 3: TABLA CON BARRAS -->
            <section class="bloque-tabla-barras"> <!-- Sección de tabla con barras -->
                <h2>Evolución de Ventas (Últimos 12 meses)</h2> <!-- Título de la sección -->
                <div class="contenedor-tabla"> <!-- Contenedor de la tabla -->
                    <table class="tabla-estadisticas"> <!-- Tabla de estadísticas -->
                        <thead> <!-- Encabezado de la tabla -->
                            <tr> <!-- Fila del encabezado -->
                                <th>Año</th> <!-- Columna de año -->
                                <th>Mes</th> <!-- Columna de mes -->
                                <th>Ventas (€)</th> <!-- Columna de ventas -->
                            </tr>
                        </thead>
                        <tbody> <!-- Cuerpo de la tabla -->
                            <?php 
                            $max_tabla = max($valores_ventas) > 0 ? max($valores_ventas) : 1; /* Valor máximo para calcular el porcentaje de la barra */
                            
                            foreach ($anios as $index => $anio) { /* Recorro los años */
                                $mes = $meses[$index]; /* Obtengo el mes correspondiente */
                                $valor = $valores_ventas[$index]; /* Obtengo el valor de ventas correspondiente */
                                $porcentaje = ($valor / $max_tabla) * 100; /* Calculo el porcentaje para la barra */
                            ?>
                            <tr> <!-- Fila de datos -->
                                <td class="columna-anio"><?php echo htmlspecialchars($anio); ?></td> <!-- Columna de año -->
                                <td class="columna-mes"><?php echo htmlspecialchars($mes); ?></td> <!-- Columna de mes -->
                                <td class="columna-ventas"> <!-- Columna de ventas -->
                                    <div class="barra"> <!-- Barra de progreso -->
                                        <div class="barra-relleno" style="width: <?php echo $porcentaje; ?>%"> <!-- Relleno de la barra con ancho según el porcentaje -->
                                            <span class="valor-barra"><?php echo number_format($valor, 2, ',', '.'); ?> €</span> <!-- Valor dentro de la barra -->
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <!-- Pie de página -->
    <?php include __DIR__ . '/comunes/pie.php'; ?> <!-- Incluyo el pie de página -->
