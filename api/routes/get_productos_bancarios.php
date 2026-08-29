<?php

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../middleware/auth.php';

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
            pb.id,
            pb.tipo_producto,
            pb.montos,
            pb.cantidad_prestamos_disponibles,
            pb.cantidad_prestamos_vigentes,
            pb.cantidad_prestamos_finalizados,
            pb.cant_cuotas,
            pb.tasa,
            pb.fecha_otorgamiento,
            pb.estado,
            pb.datos_cliente_id

        FROM productos_bancarios pb

        INNER JOIN datos_cliente dc
            ON dc.id = pb.datos_cliente_id

        WHERE dc.usuario_id = ?

        ORDER BY pb.id DESC
        LIMIT 100
    ");

    $stmt->execute([$usuario_id]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$data) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "error" => "No se encontraron productos bancarios para este usuario"
        ]);

        exit;
    }

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
