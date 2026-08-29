<?php

require_once __DIR__ . '/../../vendor/autoload.php';
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

    // 🔐 Validar JWT
    $usuario = validarJWT();

    // 🔑 Obtener usuario_id del JWT
    if (!isset($usuario['usuario_id'])) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "error" => "El token no contiene usuario_id"
        ]);

        exit;
    }

    $usuario_id = (int) $usuario['usuario_id'];

    $db = (new Database())->connect();

    $stmt = $db->prepare("
        SELECT
            c.id,
            c.numero_cuota,
            c.fecha_vencimiento,
            c.importe,
            c.estado,
            c.producto_bancario_id
        FROM cuotas_clientes c

        INNER JOIN productos_bancarios p
            ON c.producto_bancario_id = p.id

        INNER JOIN datos_cliente d
            ON p.datos_cliente_id = d.id

        WHERE d.usuario_id = ?

        ORDER BY c.id DESC
        LIMIT 100
    ");

    $stmt->execute([$usuario_id]);

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