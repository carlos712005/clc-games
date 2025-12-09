<?php
    
    /* Reembolsar juegos comprados con PayPal mediante la API de Refund */

    declare(strict_types=1); /* Tipado estricto */

    // Arranco sesión si hace falta
    if (session_status() !== PHP_SESSION_ACTIVE) { 
        session_start();
    }

    // Indicamos que vamos a devolver JSON
    header('Content-Type: application/json; charset=UTF-8');

    // Cargo conexión BD
    require_once __DIR__ . '/../../config/conexion.php';

    // Cargo las claves y modo de PayPal (sandbox/live)
    require_once __DIR__ . '/../../config/paypal.php';


    // 0) Verificar que el método de reembolso es PayPal

    if ( empty($_SESSION['metodo_reembolso']) || $_SESSION['metodo_reembolso'] !== 'paypal') {
        echo json_encode(['ok' => false, 'error' => 'Método de reembolso no válido (se esperaba PayPal)']); /* Mensaje de error */
        exit; /* Salir si no es PayPal */
    }

    $info = $_SESSION['paypal_info_reembolso'] ?? null; /* Datos necesarios para reembolso */
    if (!$info || !is_array($info)) { /* Verifico que hay datos */
        echo json_encode(['ok' => false, 'error' => 'Faltan datos de PayPal en la sesión']); /* Mensaje de error */
        exit; /* Salir si no hay datos */
    }

    $capture_id = $info['paypal_capture_id'] ?? ''; /* ID de captura de PayPal */
    $precio_lin = $info['precio'] ?? null; /* precio del juego a reembolsar */

    if (!$capture_id) { /* Verifico capture_id */
        echo json_encode(['ok' => false, 'error' => 'No hay paypal_capture_id para reembolsar']); /* Mensaje de error */
        exit; /* Salir si no hay capture_id */
    }

    if (!is_numeric($precio_lin) || (float)$precio_lin <= 0) { /* Verifico precio válido */
        echo json_encode(['ok' => false, 'error' => 'Precio de línea no válido para reembolso parcial']); /* Mensaje de error */
        exit; /* Salir si precio no válido */
    }


    // 1) Seleccionar URL de PayPal (según modo configurado)

    $base_url = ($PAYPAL_ENV === 'live')
        ? 'https://api-m.paypal.com'
        : 'https://api-m.sandbox.paypal.com';


    // 2) Función para obtener access_token de PayPal

    function obtenerTokenPayPal(string $clientId, string $secret, string $baseUrl): string {
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
        $resp = curl_exec($ch); /* Ejecuto petición */
        curl_close($ch); /* Cierro CURL */

        if ($resp === false) { /* Si falla la petición */
            throw new Exception('No se pudo obtener access_token de PayPal'); /* Excepción con mensaje de error */
        }
        $data = json_decode($resp, true); /* Decodifico JSON */
        if (empty($data['access_token'])) { /* Si no hay token */
            throw new Exception('PayPal no devolvió access_token'); /* Excepción con mensaje de error */
        }
        return $data['access_token']; /* Devuelvo el token */
    }

    try { // Inicio bloque try para capturar posibles excepciones
        $token = obtenerTokenPayPal($PAYPAL_CLIENT_ID_SANDBOX, $PAYPAL_SECRET_SANDBOX, $base_url); /* Consigo token */


        // 3) Construir payload de reembolso PARCIAL

        $importe = number_format((float)$precio_lin, 2, '.', ''); // Formateo importe con dos decimales
        $payload = [
            'amount' => [
                'value'         => $importe,
                'currency_code' => 'EUR',
            ],
        ]; /* Payload para reembolso parcial */


        // 4) Llamar a la API de Refund

        $ch = curl_init("{$base_url}/v2/payments/captures/{$capture_id}/refund"); /* URL de reembolso */
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]); /* Configuro CURL */
        $resp = curl_exec($ch); /* Ejecuto petición */
        curl_close($ch); /* Cierro CURL */

        if ($resp === false) { /* Si falla la petición */
            throw new Exception('No se pudo crear el reembolso en PayPal'); /* Excepción con mensaje de error */
        }

        $datos = json_decode($resp, true); /* Decodifico JSON */
        $status     = $datos['status'] ?? ''; /* Estado del reembolso */
        $refund_id  = $datos['id'] ?? null; /* ID del reembolso */
        $amount_val = $datos['amount']['value'] ?? null; /* Valor del importe reembolsado */
        $amount_ccy = $datos['amount']['currency_code'] ?? 'EUR'; /* Moneda del importe */

        if ($status !== 'COMPLETED') { /* Si no se completó */
            echo json_encode([
                'ok'       => false,
                'error'    => 'PayPal no completó el reembolso',
                'respuesta'=> $datos,
            ]); /* Mensaje de error */
            exit; /* Salir si no se completó */
        }


        // 5) Responder OK al front

        echo json_encode([
            'ok'       => true,
            'refund_id'=> $refund_id,
            'status'   => $status,
            'amount'   => $amount_val ?: $importe, // respaldo: lo que pedimos
            'currency' => $amount_ccy,
        ]); /* Respuesta OK */

    } catch (Throwable $e) { // Capturo cualquier excepción
        http_response_code(500); /* Error interno del servidor */
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]); /* Mensaje de error con detalle */
    }

?>
