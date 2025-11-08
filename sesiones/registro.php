<?php

    session_start(); /* Inicio la sesión para manejar mensajes de error */
    require_once __DIR__ . "/../config/conexion.php"; /* Incluyo la conexión a la base de datos */

    // Obtener categorías para el formulario de registro
    try { /* Inicio bloque try para capturar errores al obtener filtros */
        $consulta = $conexion->query("SELECT id, id_fijo, nombre, tipo_filtro, clave FROM filtros WHERE id > 0"); /* Obtengo todos los filtros de la base de datos */
        $filtros = $consulta->fetchAll(PDO::FETCH_ASSOC); /* Guardo todos los filtros en un array */
    } catch (PDOException $e) { /* Si hay error al obtener los filtros */
        echo "Error al obtener las categorías: " . $e->getMessage(); /* Muestro el error */
        exit; /* Termino la ejecución */
    }

?>

<!DOCTYPE html>
<html lang="es"> <!-- Documento HTML en español -->

<head>
    <meta charset="UTF-8"> <!-- Codificación de caracteres UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Viewport responsive -->
    <title>CLC Games</title> <!-- Título de la página -->
    <link rel="icon" type="image/x-icon" href="../recursos/imagenes/favicon.ico"> <!-- Favicon del sitio -->
    <link rel="stylesheet" href="../recursos/css/estilos_registrarse.css"> <!-- Estilos específicos del registro -->
</head>

<body>
    <h1>Registro de Usuario</h1> <!-- Título principal de la página -->

    <?php if (isset($_SESSION['error_acronimo_existente'])) { ?> <!-- Si hay error de usuario existente -->
        <div class="mensaje-error"> <?php echo htmlspecialchars($_SESSION['error_acronimo_existente']); ?> </div> <!-- Muestro el error -->
        <?php unset($_SESSION['error_acronimo_existente']); ?> <!-- Limpio el error de la sesión -->
    <?php } ?> <!-- Fin del condicional -->

    <?php if (isset($_SESSION['error_dni_existente'])) { ?> <!-- Si hay error de DNI existente -->
        <div class="mensaje-error"> <?php echo htmlspecialchars($_SESSION['error_dni_existente']); ?> </div> <!-- Muestro el error -->
        <?php unset($_SESSION['error_dni_existente']); ?> <!-- Limpio el error de la sesión -->
    <?php } ?> <!-- Fin del condicional -->

    <?php if (isset($_SESSION['error_email_existente'])) { ?> <!-- Si hay error de email existente -->
        <div class="mensaje-error"> <?php echo htmlspecialchars($_SESSION['error_email_existente']); ?> </div> <!-- Muestro el error -->
        <?php unset($_SESSION['error_email_existente']); ?> <!-- Limpio el error de la sesión -->
    <?php } ?> <!-- Fin del condicional -->

    <?php if (isset($_SESSION['error_contrasena_no_coincide'])) { ?> <!-- Si hay error de contraseñas que no coinciden -->
        <div class="mensaje-error"> <?php echo htmlspecialchars($_SESSION['error_contrasena_no_coincide']); ?> </div> <!-- Muestro el error -->
        <?php unset($_SESSION['error_contrasena_no_coincide']); ?> <!-- Limpio el error de la sesión -->
    <?php } ?> <!-- Fin del condicional -->

    <?php if (isset($_SESSION['error_general'])) { ?> <!-- Si hay error general -->
        <div class="mensaje-error"> <?php echo htmlspecialchars($_SESSION['error_general']); ?> </div> <!-- Muestro el error -->
        <?php unset($_SESSION['error_general']); ?> <!-- Limpio el error de la sesión -->
    <?php } ?> <!-- Fin del condicional -->

    <form action="procesar_registro.php" method="post"> <!-- Formulario que envía a procesar_registro.php por POST -->

        <h2>Información Personal</h2> <!-- Subtítulo para la sección de datos personales -->

        <label for="nombre">Nombre real:</label> <!-- Etiqueta para el campo de nombre -->
        <input type="text" id="nombre" name="nombre" placeholder="Introduce tu nombre real" 
               minlength="2" maxlength="50" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñÜü\s]+" 
               title="Solo se permiten letras y espacios, mínimo 2 caracteres" 
               value="<?php echo isset($_SESSION['datos_formulario_registro']['nombre']) ? htmlspecialchars($_SESSION['datos_formulario_registro']['nombre']) : ''; ?>"
               tabindex="1" required> <!-- Campo de nombre con restricciones y primero en orden de navegación -->

        <label for="acronimo">Nombre de usuario:</label> <!-- Etiqueta para el campo de usuario -->
        <input type="text" id="acronimo" name="acronimo" placeholder="Introduce tu nombre de usuario" 
               minlength="3" maxlength="20" pattern="[A-Za-z0-9_]+" 
               title="Solo letras, números y guiones bajos, entre 3 y 20 caracteres" 
               value="<?php echo isset($_SESSION['datos_formulario_registro']['acronimo']) ? htmlspecialchars($_SESSION['datos_formulario_registro']['acronimo']) : ''; ?>"
               tabindex="2" required> <!-- Campo de usuario con restricciones y segundo en orden -->

        <label for="apellidos">Apellidos:</label> <!-- Etiqueta para el campo de apellidos -->
        <input type="text" id="apellidos" name="apellidos" placeholder="Introduce tus apellidos" 
               minlength="2" maxlength="100" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñÜü\s]+" 
               title="Solo se permiten letras y espacios, mínimo 2 caracteres" 
               value="<?php echo isset($_SESSION['datos_formulario_registro']['apellidos']) ? htmlspecialchars($_SESSION['datos_formulario_registro']['apellidos']) : ''; ?>"
               tabindex="3" required> <!-- Campo de apellidos con restricciones y tercero en orden -->

        <label for="dni">DNI:</label> <!-- Etiqueta para el campo de DNI -->
        <input type="text" id="dni" name="dni" pattern="[0-9]{8}[A-Za-z]{1}" 
               maxlength="9" minlength="9"
               title="Formato: 12345678A (8 números seguidos de 1 letra)" 
               placeholder="12345678A" 
               value="<?php echo isset($_SESSION['datos_formulario_registro']['dni']) ? htmlspecialchars($_SESSION['datos_formulario_registro']['dni']) : ''; ?>"
               tabindex="4" required> <!-- Campo de DNI con formato específico y cuarto en orden -->

        <label for="email">Correo Electrónico:</label> <!-- Etiqueta para el campo de email -->
        <input type="email" id="email" name="email" placeholder="Introduce tu correo electrónico" 
               maxlength="255" 
               title="Introduce un correo electrónico válido" 
               value="<?php echo isset($_SESSION['datos_formulario_registro']['email']) ? htmlspecialchars($_SESSION['datos_formulario_registro']['email']) : ''; ?>"
               tabindex="5" required> <!-- Campo de email con validación y quinto en orden -->

        <label for="contrasena">Contraseña:</label> <!-- Etiqueta para el campo de contraseña -->
        <div class="contenedor-contrasena"> <!-- Contenedor para el campo de contraseña -->
            <input type="password" id="contrasena" name="contrasena" placeholder="Introduce tu contraseña" 
                   minlength="8" maxlength="255" 
                   pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}"
                   title="Mínimo 8 caracteres, debe contener al menos: 1 minúscula, 1 mayúscula, 1 número y 1 carácter especial (@$!%*?&)" 
                   value="<?php echo isset($_SESSION['datos_formulario_registro']['contrasena']) ? htmlspecialchars($_SESSION['datos_formulario_registro']['contrasena']) : ''; ?>"
                   tabindex="6" required> <!-- Campo de contraseña con restricciones fuertes y sexto en orden -->
            <button type="button" id="boton-contrasena" class="mostrar-ocultar-contrasena" onclick="mostrarOcultarContrasena('contrasena')" tabindex="-1" title = 'Mostrar contraseña'></button> <!-- Botón para mostrar/ocultar contraseña -->
        </div>

        <label for="contrasena-confirmar">Confirmar Contraseña:</label> <!-- Etiqueta para el campo de confirmar contraseña -->
        <div class="contenedor-contrasena"> <!-- Contenedor para el campo de confirmar contraseña -->
            <input type="password" id="contrasena-confirmar" name="contrasena-confirmar" placeholder="Confirma tu contraseña" 
                   minlength="8" maxlength="255" 
                   pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}"
                   title="Mínimo 8 caracteres, debe contener al menos: 1 minúscula, 1 mayúscula, 1 número y 1 carácter especial (@$!%*?&)" 
                   value="<?php echo isset($_SESSION['datos_formulario_registro']['contrasena-confirmar']) ? htmlspecialchars($_SESSION['datos_formulario_registro']['contrasena-confirmar']) : ''; ?>"
                   tabindex="7" required> <!-- Campo de confirmar contraseña con restricciones fuertes y séptimo en orden -->
            <button type="button" id="boton-contrasena-confirmar" class="mostrar-ocultar-contrasena" onclick="mostrarOcultarContrasena('contrasena-confirmar')" tabindex="-1" title = 'Mostrar contraseña'></button> <!-- Botón para mostrar/ocultar confirmar contraseña -->
        </div>

        <h2>Preferencias de Juegos</h2> <!-- Subtítulo para la sección de preferencias -->

        <label for="id_preferencia_genero">Géneros:</label> <!-- Etiqueta para el select de géneros -->
        <select id="id_preferencia_genero" name="id_preferencia_genero" tabindex="8" required> <!-- Select de géneros, octavo en orden -->
            <option value="0"<?php echo (!isset($_SESSION['datos_formulario_registro']['genero']) || $_SESSION['datos_formulario_registro']['genero'] == '0') ? 'selected' : ''; ?>>Todos los géneros</option> <!-- Opción por defecto -->
            <?php foreach ($filtros as $filtro) { /* Recorro todos los filtros */
                if($filtro['tipo_filtro'] === 'generos'){ /* Si el filtro es un género */
            ?>
                <option value="<?php echo $filtro['id_fijo']; ?>" <?php echo (isset($_SESSION['datos_formulario_registro']['genero']) && $_SESSION['datos_formulario_registro']['genero'] == $filtro['id_fijo']) ? 'selected' : ''; ?>> <!-- Opción con el ID del género -->
                    <?php echo htmlspecialchars($filtro['nombre']); ?> <!-- Nombre del género escapado -->
                </option>
            <?php } }?> <!-- Fin del foreach y condicional -->
        </select>

        <label for="id_preferencia_categoria">Categorías:</label> <!-- Etiqueta para el select de categorías -->
        <select id="id_preferencia_categoria" name="id_preferencia_categoria" tabindex="9" required> <!-- Select de categorías, noveno en orden -->
            <option value="0"<?php echo (!isset($_SESSION['datos_formulario_registro']['categoria']) || $_SESSION['datos_formulario_registro']['categoria'] == '0') ? 'selected' : ''; ?>>Todas las categorías</option> <!-- Opción por defecto -->
            <?php foreach ($filtros as $filtro) { /* Recorro todos los filtros */
                if($filtro['tipo_filtro'] === 'categorias'){ /* Si el filtro es del tipo categorías */
            ?>
                <option value="<?php echo $filtro['id_fijo']; ?>" <?php echo (isset($_SESSION['datos_formulario_registro']['categoria']) && $_SESSION['datos_formulario_registro']['categoria'] == $filtro['id_fijo']) ? 'selected' : ''; ?>> <!-- Opción con el ID de la categoría -->
                    <?php echo htmlspecialchars($filtro['nombre']); ?> <!-- Nombre de la categoría escapado -->
                </option>
            <?php } }?> <!-- Fin del foreach y condicional -->
        </select>

        <label for="id_preferencia_modo">Modos de juego:</label> <!-- Etiqueta para el select de modos -->
        <select id="id_preferencia_modo" name="id_preferencia_modo" tabindex="10" required> <!-- Select de modos, décimo en orden -->
            <option value="0"<?php echo (!isset($_SESSION['datos_formulario_registro']['modo']) || $_SESSION['datos_formulario_registro']['modo'] == '0') ? 'selected' : ''; ?>>Todos los modos</option> <!-- Opción por defecto -->
            <?php foreach ($filtros as $filtro) { /* Recorro todos los filtros */
                if($filtro['tipo_filtro'] === 'modos'){ /* Si el filtro es un modo de juego */
            ?>
                <option value="<?php echo $filtro['id_fijo']; ?>" <?php echo (isset($_SESSION['datos_formulario_registro']['modo']) && $_SESSION['datos_formulario_registro']['modo'] == $filtro['id_fijo']) ? 'selected' : ''; ?>> <!-- Opción con el ID del modo -->
                    <?php echo htmlspecialchars($filtro['nombre']); ?> <!-- Nombre del modo escapado -->
                </option>
            <?php } }?> <!-- Fin del foreach y condicional -->
        </select>

        <label for="id_preferencia_pegi">Clasificaciones PEGI:</label> <!-- Etiqueta para el select de PEGI -->
        <select id="id_preferencia_pegi" name="id_preferencia_pegi" tabindex="11" required> <!-- Select de PEGI, décimo primero en orden -->
            <option value="0"<?php echo (!isset($_SESSION['datos_formulario_registro']['pegi']) || $_SESSION['datos_formulario_registro']['pegi'] == '0') ? 'selected' : ''; ?>>Todas las clasificaciones PEGI</option> <!-- Opción por defecto -->
            <?php foreach ($filtros as $filtro) { /* Recorro todos los filtros */
                if($filtro['tipo_filtro'] === 'clasificacionPEGI'){ /* Si el filtro es una clasificación PEGI */
            ?>
                <option value="<?php echo $filtro['id_fijo']; ?>" <?php echo (isset($_SESSION['datos_formulario_registro']['pegi']) && $_SESSION['datos_formulario_registro']['pegi'] == $filtro['id_fijo']) ? 'selected' : ''; ?>> <!-- Opción con el ID de la clasificación -->
                    <?php echo htmlspecialchars($filtro['nombre']); ?> <!-- Nombre de la clasificación escapado -->
                </option>
            <?php } }?> <!-- Fin del foreach y condicional -->
        </select>

        <button type="submit" tabindex="12"><?php echo isset($_SESSION['modo_admin']) && $_SESSION['modo_admin'] === true ? 'Registrar usuario' : 'Registrarse'; ?></button> <!-- Botón de envío, décimo segundo en orden -->
    </form> <!-- Fin del formulario -->

    <br> <!-- Espacio adicional -->
    <a href="<?php echo isset($_SESSION['modo_admin']) && $_SESSION['modo_admin'] === true ? '../vistas/panel_administrador.php' : '../publico/index.php'; ?>" tabindex="13"><?php echo isset($_SESSION['modo_admin']) && $_SESSION['modo_admin'] === true ? 'Volver al panel' : 'Volver al inicio'; ?></a> <!-- Enlace de vuelta al index, décimo tercero en orden -->

    <?php 
    // Limpiar los datos del formulario después de mostrarlos
    if (isset($_SESSION['datos_formulario_registro'])) { /* Si hay datos del formulario en sesión */
        unset($_SESSION['datos_formulario_registro']); /* Los limpio después de usarlos */
        /* Vacío los datos del formulario en sesión por si ya existían
            y los dejo así para usarlos solo en caso de error, por lo que
            si no hay errores los posibles datos sensibles del usuario 
            (como la contraseña), guardados aquí, desaparecerán*/
    }
    ?>

    <script src="../recursos/js/mostrar_ocultar_contrasena.js" defer></script> <!-- Script para funcionalidad de mostrar/ocultar contraseña -->

</body>

</html>
