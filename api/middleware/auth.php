<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function validarJWT()
{
    $authorization = null;

    // Apache / XAMPP
    if (function_exists("getallheaders")) {

        $headers = getallheaders();

        $authorization =
            $headers["Authorization"]
            ?? $headers["authorization"]
            ?? null;
    }

    // Fallback
    if (!$authorization && isset($_SERVER["HTTP_AUTHORIZATION"])) {
        $authorization = $_SERVER["HTTP_AUTHORIZATION"];
    }

    // Otro fallback posible en Apache
    if (!$authorization && isset($_SERVER["REDIRECT_HTTP_AUTHORIZATION"])) {
        $authorization = $_SERVER["REDIRECT_HTTP_AUTHORIZATION"];
    }

    if (!$authorization) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "error" => "Token requerido"
        ]);

        exit();
    }

    // Verificar Bearer
    if (!preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "error" => "Formato de Authorization inválido"
        ]);

        exit();
    }

    $token = trim($matches[1]);

    try {

        $decoded = JWT::decode(
            $token,
            new Key(
                "mi_clave_super_secreta_de_32_caracteres_minimo_2026",
                "HS256"
            )
        );

        return (array) $decoded;

    } catch (ExpiredException $e) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "expired" => true,
            "error" => "La sesión expiró"
        ]);

        exit();

    } catch (Exception $e) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "expired" => false,
            "error" => "Token inválido"
        ]);

        exit();
    }
}
?>