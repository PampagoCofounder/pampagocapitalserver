<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../../vendor/autoload.php';

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

    // ==============================
    // JWT
    // ==============================

    $usuario = validarJWT();

    if (!isset($usuario['usuario_id'])) {

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "error" => "El token no contiene usuario_id"
        ]);

        exit;
    }

    $admin_id = (int) $usuario['usuario_id'];


    // ==============================
    // CLIENTE
    // ==============================

    if (
        !isset($_GET['cliente_id']) ||
        !is_numeric($_GET['cliente_id'])
    ) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "error" => "cliente_id es obligatorio"
        ]);

        exit;
    }

    $cliente_id = (int) $_GET['cliente_id'];


    // ==============================
    // DATABASE
    // ==============================

    $db = (new Database())->connect();


    // ==============================
    // BUSCAR CONVERSACIÓN
    // ==============================

    $stmt = $db->prepare("
        SELECT
            id,
            usuario_cliente_id,
            usuario_admin_id,
            estado
        FROM conversaciones
        WHERE usuario_cliente_id = ?
        AND usuario_admin_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $cliente_id,
        $admin_id
    ]);

    $conversacion = $stmt->fetch(PDO::FETCH_ASSOC);


    // ==============================
    // SI NO EXISTE → CREAR
    // ==============================

    if (!$conversacion) {

        $stmt = $db->prepare("
            INSERT INTO conversaciones (
                usuario_cliente_id,
                usuario_admin_id,
                estado
            )
            VALUES (?, ?, 'abierta')
        ");

        $stmt->execute([
            $cliente_id,
            $admin_id
        ]);

        $conversacion_id =
            (int) $db->lastInsertId();

        $conversacion = [
            "id" => $conversacion_id,
            "usuario_cliente_id" => $cliente_id,
            "usuario_admin_id" => $admin_id,
            "estado" => "abierta"
        ];

    } else {

        $conversacion_id =
            (int) $conversacion['id'];
    }


    // ==============================
    // OBTENER MENSAJES
    // ==============================

    $stmt = $db->prepare("
        SELECT
            id,
            conversacion_id,
            usuario_id,
            mensaje,
            leido,
            created_at
        FROM mensajes
        WHERE conversacion_id = ?
        ORDER BY created_at ASC, id ASC
    ");

    $stmt->execute([
        $conversacion_id
    ]);

    $mensajes =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


    // ==============================
    // RESPUESTA
    // ==============================

    echo json_encode([
        "success" => true,
        "conversacion" => $conversacion,
        "data" => $mensajes
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Error de base de datos",
        "detalle" => $e->getMessage()
    ]);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>