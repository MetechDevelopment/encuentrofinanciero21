<?php

use GuzzleHttp\Client;
use function GuzzleHttp\json_encode;

// Errores en archivo log o en pantalla si estamos en desarrollo
error_reporting(E_ALL);
// Errores en archivo log o en pantalla si estamos en desarrollo
ini_set('ignore_repeated_source', 0);
ini_set('ignore_repeated_errors', 1); // do not log repeating errors
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/storage/logs/' . date('Y-m-d') . '_error.log');

require __DIR__ . '/vendor/autoload.php';

// source of error plays role in determining if errors are different
if ($GLOBALS['env']['debug']) {
    ini_set('display_errors', 1); // Mostramos los errores en pantalla
    ini_set('display_startup_errors', 1);
}

// Language Initial Settings
$locale = array_keys($GLOBALS['env']['locales'])[0];

date_default_timezone_set('Europe/Madrid');
setlocale(LC_TIME, $GLOBALS['env']['locales'][$locale]);
setlocale(LC_ALL, $GLOBALS['env']['locales'][$locale]);

header('Content-type: application/json; charset=utf-8');

if (empty($_POST['product_id'])) {
    http_response_code(400);

    echo json_encode([
        'response' => [
            'status' => 'error',
            'message' => 'Jornada no reconocida.'
        ]
    ]);
    exit();
}

if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) || empty($_POST['phone']) || empty($_POST['company']) || empty($_POST['position']) || empty($_POST['mayor_de_edad'])) {
    http_response_code(400);

    echo json_encode([
        'response' => [
            'status' => 'error',
            'message' => 'Por favor, introduce todos los datos requeridos.'
        ]
    ]);
    exit();
}

if (empty($_POST['legal']) || (int)$_POST['legal'] !== 1) {
    http_response_code(400);

    echo json_encode([
        'response' => [
            'status' => 'error',
            'message' => 'Por favor, acepta las condiciones legales.'
        ]
    ]);
    exit();
}

if (empty($_POST['mayor_de_edad']) || (int)$_POST['mayor_de_edad'] !== 1) {
    http_response_code(400);

    echo json_encode([
        'response' => [
            'status' => 'error',
            'message' => 'Al evento solo pueden asistir mayores de edad.'
        ]
    ]);
    exit();
}

if (!filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);

    echo json_encode([
        'response' => [
            'status' => 'error',
            'message' => 'El email introducido no es válido.'
        ]
    ]);
    exit();
}

try {
    $client = new Client($GLOBALS['env']['api']);

    foreach ($_POST['product_id'] as $product) {
        $res = $client->request('POST', 'registrations', [
            'headers' => [
                'Authorization' => "Bearer {$GLOBALS['env']['api_token']}",
                'Content-Language' => $locale,
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Referer' => 'https://x-encuentro-sector-financiero.com/'
            ],
            'form_params' => [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'company' => $_POST['company'],
                'position' => $_POST['position'],
                'product_id' => $product,
                'info' => $_POST['info'],
                'registration_type' => 'inscrito',
                'transition' => 'approve',
            ]
        ]);
    }

    $response = json_decode($res->getBody());

    echo json_encode([
        'response' => [
            'status' => 'success'
        ]
    ]);
    exit();
} catch (\Throwable $th) {
    error_log('Fallo en request a API: ' . $th->getMessage());

    http_response_code(500);

    echo json_encode([
        'response' => [
            'status' => 'error',
            'message' => 'Error al enviar la inscripción, inténtalo de nuevo más tarde.'
        ]
    ]);
    exit();
}
