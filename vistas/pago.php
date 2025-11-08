<?php

  session_start(); // Iniciar sesión
  // Verificar sesión y redirigir con JavaScript si es necesario
  if(!isset($_SESSION['id_usuario'])) {
      echo '<script>window.location.href = "../publico/index.php";</script>'; /* Redirijo con JavaScript si no está logueado */
      exit; /* Termino la ejecución del script */
  }

?>

<!doctype html>
<html lang="es"> <!-- Documento HTML en español -->

<head>
  <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" /> <!-- Codificación de caracteres UTF-8 y Viewport responsive-->
  <title>CLC Games</title> <!-- Título de la página -->
  <link rel="icon" type="image/x-icon" href="../recursos/imagenes/favicon.ico"> <!-- Favicon del sitio -->
  <link rel="stylesheet" href="../recursos/css/estilos_modal.css" type="text/css"/> <!-- Enlace a la hoja de estilos CSS de modal -->
  <link rel="stylesheet" href="../recursos/css/estilos_pago.css" type="text/css"/> <!-- Enlace a la hoja de estilos CSS de pago -->
</head>

<body>
  
  <header class="barra-superior"> <!-- Encabezado de la página de pago -->
    <h1>Pago seguro</h1> <!-- Título de la página de pago -->
  </header>

  <main class="zona-pago"> <!-- Contenedor principal de la página de pago -->
    <section class="tarjeta-pago"> <!-- Sección de la tarjeta de pago -->
      <h2>Ingresa los datos de tu tarjeta</h2> <!-- Título de la sección -->

      <form class="formulario-pago" autocomplete="off"> <!-- Formulario de pago -->
        <!-- Número de tarjeta -->
        <label for="numero-tarjeta" class="campo-pago">Número de tarjeta:</label> <!-- Etiqueta del campo -->
        <input type="text" id="numero-tarjeta" inputmode="numeric" placeholder="1234 5678 9012 3456" maxlength="19" class="input-pago" required/> <!-- Campo de entrada -->
        
        <!-- Fila MM/AA + CVC -->
        <div class="fila-pago"> <!-- Contenedor para los campos de fecha de expiración y CVC -->
          <div> <!-- Contenedor del campo de fecha de expiración -->
            <label for="fecha-expiracion" class="campo-pago">MM / AA:</label> <!-- Etiqueta del campo -->
            <input type="text" id="fecha-expiracion" inputmode="numeric" placeholder="MM / AA" maxlength="7" class="input-pago" required/> <!-- Campo de entrada -->
          </div>

          <div> <!-- Contenedor del campo CVC -->
            <label for="cvc" class="campo-pago">CVC:</label> <!-- Etiqueta del campo -->
            <input type="text" id="cvc" inputmode="numeric" placeholder="123" maxlength="4" class="input-pago" required/> <!-- Campo de entrada -->
          </div>
        </div>

        <!-- Titular -->
        <label for="titular" class="campo-pago">Nombre del titular:</label> <!-- Etiqueta del campo -->
        <input type="text" id="titular" placeholder="Nombre y apellidos" class="input-pago" required/> <!-- Campo de entrada -->

        <button type="button" class="boton-pagar">Pagar</button> <!-- Botón para realizar el pago -->

        <div class="logos-pago"> <!-- Contenedor de los logos de pago -->
          <img src="../recursos/imagenes/pago.png" alt="Métodos de pago: Visa, MasterCard, Maestro, American Express, Contactless, Apple Pay" /> <!-- Imagen de los logos de pago -->
        </div>

      </form>
    </section>

  </main>

  <script src="../recursos/js/modal.js" defer></script> <!-- Script para modales -->
  <script src="../recursos/js/carrito.js" defer></script> <!-- Script del carrito de compras -->
</body>
</html>
