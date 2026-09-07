<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../middleware/auth.php";

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "error" => "Método no permitido"
    ]);

    exit;
}

try {

    // Validar JWT
    $usuario = validarJWT();

    if (!isset($usuario['usuario_id'])) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "error" => "El token no contiene usuario_id"
        ]);

        exit;
    }

    // Verificar que sea ADMIN
    $esAdmin = false;

    if (
        isset($usuario['roles']) &&
        is_array($usuario['roles'])
    ) {

        foreach ($usuario['roles'] as $rol) {

            if (
                is_string($rol) &&
                strtolower(trim($rol)) === "admin"
            ) {
                $esAdmin = true;
                break;
            }
        }
    }

    if (!$esAdmin) {

        http_response_code(403);

        echo json_encode([
            "success" => false,
            "error" => "No tiene permisos de administrador"
        ]);

        exit;
    }

    // Conexión
    $db = (new Database())->connect();

    // Obtener clientes
    $stmt = $db->prepare("
        SELECT
            d.id,
            d.usuario_id,
            d.nombre_cliente,
            d.apellido_cliente,
            d.dni_cliente,
            d.telefono_cliente,
            d.tipo_cliente,
            u.email
        FROM datos_cliente d
        INNER JOIN usuarios_pampamind u
            ON d.usuario_id = u.id
        ORDER BY d.id DESC
    ");

    $stmt->execute();

    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $clientes
    ]);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>