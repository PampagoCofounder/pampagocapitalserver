<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../middleware/auth.php";

try {

    // Validar JWT
    $usuario = validarJWT();

    $cliente_id = (int) ($usuario["usuario_id"] ?? 0);

    if ($cliente_id <= 0) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "error" => "Usuario inválido"
        ]);

        exit();
    }

    $db = (new Database())->connect();

    /*
     * Buscar la conversación del cliente.
     *
     * usuario_cliente_id = usuario_id del JWT
     */
    $stmt = $db->prepare("
        SELECT
            id,
            usuario_cliente_id,
            usuario_admin_id,
            estado,
            created_at,
            updated_at
        FROM conversaciones
        WHERE usuario_cliente_id = ?
        ORDER BY id DESC
        LIMIT 1
    ");

    $stmt->execute([$cliente_id]);

    $conversacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$conversacion) {

        echo json_encode([
            "success" => true,
            "existe" => false,
            "data" => null
        ]);

        exit();
    }

    echo json_encode([
        "success" => true,
        "existe" => true,
        "data" => $conversacion
    ]);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}

?>