<?php

    /* Crear orden de pago en PayPal (Sandbox o Live según config) */

    declare(strict_types=1); /* Tipado estricto */

    // Arranco sesión si no está iniciada
    if (session_status() !== PHP_SESSION_ACTIVE) { 
        session_start(); 
    }

    // Indicamos que vamos a devolver JSON
    header('Content-Type: application/json; charset=UTF-8');

    // Cargo conexión BD
    require_once __DIR__ . '/../../config/conexion.php';

    // Cargo las claves y modo de PayPal (sandbox/live)
    require_once __DIR__ . '/../../config/paypal.php';


    // 1) Recibir importe total de sesion

    // Si viene el total del carrito lo uso, si no pongo 1€ por defecto
    $entradaRaw = file_get_contents('php://input'); /* Datos JSON de entrada */
    $entrada    = json_decode($entradaRaw, true); /* Decodifico JSON */

    $importe_total = 1.00; // respaldo
    if (isset($entrada['total']) && is_numeric($entrada['total'])) { /* Si viene total en entrada */
        $importe_total = (float) $entrada['total']; /* guardo el total del carrito */
    } elseif (isset($_SESSION['total_pago']) && is_numeric($_SESSION['total_pago'])) { /* Si viene total en sesión */
        $importe_total = (float) $_SESSION['total_pago']; /* guardo el total del carrito */
}

    // Lo formateo a dos decimales para PayPal
    $importe_total = number_format($importe_total, 2, '.', ''); 


    // 2) Seleccionar URL de PayPal (según modo configurado)

    $base_url = ($PAYPAL_ENV === 'live')
        ? 'https://api-m.paypal.com'
        : 'https://api-m.sandbox.paypal.com';


    // 3) Función para conseguir token de acceso de PayPal

    function obtenerAccessToken(string $clientId, string $secret, string $baseUrl): string {

        // Llamo a PayPal para pedir token
        $ch = curl_init("{$baseUrl}/v1/oauth2/token"); /* URL de token */
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $clientId . ':' . $secret,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Accept-Language: es_ES',
            ],
        ]); /* Configuro CURL */

        $respuesta = curl_exec($ch); /* Ejecuto petición */
        if ($respuesta === false) { /* Si no hay respuesta */
            http_response_code(500); /* Error interno del servidor */
            echo json_encode(['error' => 'Error obteniendo token de PayPal.']); /* Mensaje de error */
            exit;
        }

        $data = json_decode($respuesta, true); /* Decodifico JSON */
        curl_close($ch); /* Cierro CURL */

        if (!isset($data['access_token'])) { /* Si no viene token */
            http_response_code(500); /* Error interno del servidor */
            echo json_encode(['error' => 'PayPal no devolvió token', 'detalles' => $data]); /* Mensaje de error */
            exit; /* Salgo */
        }

        return $data['access_token']; /* Devuelvo el token */
    }

    // Cojo credenciales del archivo de config
    $clientId = $PAYPAL_CLIENT_ID_SANDBOX;
    $secret   = $PAYPAL_SECRET_SANDBOX;

    // Consigo el token de acceso
    $access_token = obtenerAccessToken($clientId, $secret, $base_url);


    // 4) Crear orden de PayPal con el importe real del carrito

    $payload = [
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'amount' => [
                'currency_code' => 'EUR',
                'value' => $importe_total
            ],
            'description' => 'Compra en CLC Games'
        ]]
    ]; /* Datos de la orden */

    // Petición a PayPal para crear la orden
    $ch = curl_init("{$base_url}/v2/checkout/orders");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    ]); /* Configuro CURL */
    $respuesta = curl_exec($ch); /* Ejecuto petición */

    if ($respuesta === false) { /* Si no hay respuesta */
        http_response_code(500); /* Error interno del servidor */
        echo json_encode(['error' => 'No se pudo crear la orden en PayPal.']); /* Mensaje de error */
        exit; /* Salgo */
    }

    $datos = json_decode($respuesta, true); /* Decodifico JSON */
    curl_close($ch);/* Cierro CURL */


    // 5) Devolver ID de la orden a JavaScript (pago.php)

    if (!isset($datos['id'])) { /* Si no viene ID de orden */
        http_response_code(500); /* Error interno del servidor */
        echo json_encode(['error' => 'PayPal no devolvió un ID de orden.', 'detalles' => $datos]); /* Mensaje de error */
        exit; /* Salgo */
    }

    // Devuelvo ID de la orden creada
    echo json_encode([
        'id' => $datos['id'],
        'status' => $datos['status'] ?? null
    ]);
    
?>