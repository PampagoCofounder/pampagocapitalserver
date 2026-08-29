<?php

header("Content-Type: application/json; charset=UTF-8");

$cuit = $_GET['identificacion'] ?? '';

if (empty($cuit)) {
    http_response_code(400);

    echo json_encode([
        "error" => "Debe ingresar un CUIT"
    ]);

    exit;
}

if (!preg_match('/^\d{11}$/', $cuit)) {
    http_response_code(400);

    echo json_encode([
        "error" => "El CUIT debe contener 11 números"
    ]);

    exit;
}

$url =
    "https://api.bcra.gob.ar/centraldedeudores/v1.0/Deudas/"
    . $cuit;

$certificado =
    "C:\\xampp\\php\\extras\\ssl\\cacert-2026-08-13.pem";

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CAINFO => $certificado,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_TIMEOUT => 20,
]);

$respuesta = curl_exec($ch);

if ($respuesta === false) {

    http_response_code(500);

    echo json_encode([
        "error" => curl_error($ch)
    ]);

    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);

curl_close($ch);

if ($httpCode !== 200) {

    http_response_code($httpCode);

    echo json_encode([
        "error" => "Error al consultar BCRA",
        "http_code" => $httpCode
    ]);

    exit;
}

echo $respuesta;

?>