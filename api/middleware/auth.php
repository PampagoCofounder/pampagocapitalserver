<?php

require_once __DIR__ . "/../../vendor/autoload.php";
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function validarJWT()
{
    $headers = getallheaders();

    if (!isset($headers["Authorization"])) {
        http_response_code(401);
        echo json_encode([
            "success"=> false,
            "error" => "Token requerido"
        ]);
        exit();
    }

    //Obtener token
    $token = str_replace("Bearer ", "", $headers["Authorization"]);

    try {
        $decoded = JWT::decode(
            $token,
            new Key("mi_clave_super_secreta_de_32_caracteres_minimo_2026", "HS256")
        );

        return (array) $decoded;
    } catch (ExpiredException $e) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "expired" => true,
            "error" => "La sesion expiro"
        ]);
        exit();
    }

    //token invalido
    catch (Exception $e){
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "expired" => false,
            "error" => "Token inválido"
        ]);

        exit();
    }
}
