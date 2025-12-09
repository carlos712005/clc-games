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
    
    <?php if(isset($_SESSION['metodo_reembolso'])) { /* Si es flujo de reembolso */
      
      if($_SESSION['metodo_reembolso'] === 'paypal') { /* Si es reembolso con PayPal */ ?>
        
        <section class="tarjeta-pago solo-paypal"> <!-- Sección de la tarjeta de PayPal -->
            <h2>Realizar reembolso con PayPal</h2> <!-- Título de la sección de PayPal -->

            <div id="boton-paypal"></div> <!-- Contenedor del botón de PayPal -->
          </form>
        </section>
      
      <?php } else { /* Si es reembolso con tarjeta */ ?>
        
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
            
          </form>
        </section>

      <?php } ?>

    <?php } else { /* Si no es flujo de reembolso */ ?>
      
      <section class="tarjeta-pago"> <!-- Sección de la tarjeta de pago -->
        <h2>Seleccione un método de pago</h2> <!-- Título de la sección -->
        <button type="button" id="boton-tarjeta" class="boton-metodo-pago"> <!-- Botón para pago con tarjeta -->
          <img src="../recursos/imagenes/tarjeta.png" alt="Ícono de tarjeta de crédito/débito" class="icono-tarjeta"/> <!-- Ícono de tarjeta de crédito/débito -->
        <span>Tarjeta de débito o crédito</span> <!-- Texto del botón -->
        </button> <!-- Botón para pago con tarjeta -->
        <br> <!-- Salto de línea -->
        <hr> <!-- Línea horizontal -->
        <br> <!-- Salto de línea -->
        <div id="boton-paypal"></div> <!-- Contenedor del botón de PayPal -->
      </section>
      
    <?php } ?>

  </main>

  <script src="../recursos/js/modal.js" defer></script> <!-- Script para modales -->
  <script src="../recursos/js/carrito.js" defer></script> <!-- Script del carrito de compras -->


  <!-- SDK de PayPal (SANDBOX) -->
  <script src="https://www.paypal.com/sdk/js?client-id=AcJvFq6jmpqy09uwlRXM3nDaDOMwfl0Py1SOS089-6-vUm1JpbBJqidkZVzGwJNwrtBqdsH-h5lK6mEY&currency=EUR"></script> <!-- SDK de PayPal con Client ID y moneda EUR -->
  <script>
    window.esReembolso = <?php echo json_encode(isset($_SESSION['metodo_reembolso']) && $_SESSION['metodo_reembolso'] === 'paypal'); ?>; // Variable global para indicar si es reembolso con PayPal
  </script>
  <script src="../recursos/js/paypal.js" defer></script> <!-- Script de la página de pago -->

</body>
</html>
