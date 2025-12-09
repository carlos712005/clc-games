<?php

  /* Capturar una orden de PayPal (Sandbox/Live según config) */

  declare(strict_types=1); /* Tipado estricto */

  // Arranco sesión si hace falta
  if (session_status() !== PHP_SESSION_ACTIVE) { 
    session_start(); 
  }

  // Vamos a devolver JSON
  header('Content-Type: application/json; charset=UTF-8');

  // Carga de conexión
  require_once __DIR__ . '/../../config/conexion.php';

  // Claves y entorno de PayPal
  require_once __DIR__ . '/../../config/paypal.php';


  // 1) Recibir el orderID que llega desde pago.php

  $input = json_decode(file_get_contents('php://input'), true); /* Datos JSON de entrada */
  $order_id = isset($input['orderID']) ? trim($input['orderID']) : ''; /* orderID recibido */

  // Si no viene orderID, error
  if ($order_id === '') {
    http_response_code(400); /* Error de solicitud incorrecta */
    echo json_encode(['error' => 'Falta el orderID']); /* Mensaje de error */
    exit; /* Salir si falta orderID */
  }


  // 2) Seleccionar URL base según entorno

  $base_url = ($PAYPAL_ENV === 'live')
    ? 'https://api-m.paypal.com'
    : 'https://api-m.sandbox.paypal.com';


  // 3) Conseguir token de acceso

  function obtenerAccessToken(string $clientId, string $secret, string $baseUrl): string {
      $ch = curl_init("{$baseUrl}/v1/oauth2/token"); /* URL de token */
          curl_setopt_array($ch, [
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_POST => true,
          CURLOPT_USERPWD => $clientId . ':' . $secret,
          CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
          CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: es_ES'],
          ]); /* Configuro CURL */
      $respuesta = curl_exec($ch); /* Ejecuto petición */

      // Si falla la petición, lanzo excepción
      if ($respuesta === false) { 
        throw new Exception('No se pudo obtener token'); /* Excepción con mensaje de error */
      }

      $data = json_decode($respuesta, true); /* Decodifico JSON */
      curl_close($ch); /* Cierro CURL */

      // Si no hay token, lanzo excepción
      if (!isset($data['access_token'])) { 
        throw new Exception('Sin access_token'); /* Excepción con mensaje de error */
      }

      return $data['access_token']; /* Devuelvo token */
  }

  // Cojo credenciales del archivo de config
  $clientId = $PAYPAL_CLIENT_ID_SANDBOX;
  $secret   = $PAYPAL_SECRET_SANDBOX;

  try { // Inicio bloque try para capturar posibles excepciones
    $access_token = obtenerAccessToken($clientId, $secret, $base_url); /* Consigo token */


    // 4) Capturar la orden en PayPal

    $ch = curl_init("{$base_url}/v2/checkout/orders/{$order_id}/capture"); /* URL de captura */
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token,
      ],
    ]); /* Configuro CURL */
    $respuesta = curl_exec($ch); /* Ejecuto petición */

    // Si falla la petición, lanzo excepción
    if ($respuesta === false) { 
      throw new Exception('Error capturando la orden'); /* Excepción con mensaje de error */
    }

    $datos = json_decode($respuesta, true); /* Decodifico JSON */
    curl_close($ch); /* Cierro CURL */


    // 5) Validar estado

    $estado = $datos['status'] ?? ''; /* Estado de la orden */
    if ($estado !== 'COMPLETED') { /* Si no está completada */
      http_response_code(400); /* Error de solicitud incorrecta */
      echo json_encode(['error' => 'Pago no completado', 'estado' => $estado, 'respuesta' => $datos]); /* Mensaje de error */
      exit; /* Salir si no está completada */
    }

    // 6) Guardar info útil en sesión
          
    $_SESSION['pago_paypal'] = [
        'metodo_pago' => 'paypal',
        'order_id'    => $order_id,
        'capture_id'  => $datos['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
        'payer_email' => $datos['payer']['email_address'] ?? null,
        'importe'     => $datos['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? null,
        'moneda'      => $datos['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'] ?? 'EUR',
        'estado'      => 'pagada'
    ]; /* Datos del pago */


    // 7) Responder OK al front

    echo json_encode([
      'ok' => true,
      'order_id'   => $order_id,
      'capture_id' => $datos['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
      'payer_email'=> $datos['payer']['email_address'] ?? null,
      'amount'     => $datos['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? null,
      'currency'   => $datos['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'] ?? 'EUR',
    ]); /* Respuesta OK */

  } catch (Throwable $e) { /* Capturo cualquier excepción */
    http_response_code(500); /* Error interno del servidor */
    echo json_encode(['error' => 'Excepción capturando pago', 'detalle' => $e->getMessage()]); /* Mensaje de error con detalle */
  }

?>