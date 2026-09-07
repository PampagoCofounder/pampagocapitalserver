<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "error" => "Método no permitido"
    ]);

    exit;
}

try {

    $usuario = validarJWT();

    $usuario_id = (int)$usuario['usuario_id'];

    $db = (new Database())->connect();

    $stmt = $db->prepare("
        SELECT
            c.id,
            c.estado,
            c.updated_at,

            c.usuario_cliente_id,
            cliente.nombre AS cliente_nombre,

            c.usuario_admin_id,
            admin.nombre AS admin_nombre

        FROM conversaciones c

        INNER JOIN usuarios_pampamind cliente
            ON c.usuario_cliente_id = cliente.id

        INNER JOIN usuarios_pampamind admin
            ON c.usuario_admin_id = admin.id

        WHERE
            c.usuario_cliente_id = ?
            OR c.usuario_admin_id = ?

        ORDER BY c.updated_at DESC
    ");

    $stmt->execute([
        $usuario_id,
        $usuario_id
    ]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>