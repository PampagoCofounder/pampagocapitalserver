<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../middleware/auth.php";

try {

    // ==============================
    // JWT
    // ==============================

    $usuario = validarJWT();

    $admin_id = (int) ($usuario["usuario_id"] ?? 0);

    if (!$admin_id) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "error" => "Usuario no válido en el token"
        ]);

        exit();
    }


    // ==============================
    // MÉTODO
    // ==============================

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {

        http_response_code(405);

        echo json_encode([
            "success" => false,
            "error" => "Método no permitido",
            "hint" => "Usar POST"
        ]);

        exit();
    }


    // ==============================
    // DATOS
    // ==============================

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    if (!$data) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "error" => "JSON inválido"
        ]);

        exit();
    }


    $cliente_id = isset($data["cliente_id"])
        ? (int) $data["cliente_id"]
        : 0;

    $mensaje = trim(
        $data["mensaje"] ?? ""
    );


    if (!$cliente_id || !$mensaje) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "error" => "cliente_id y mensaje son obligatorios"
        ]);

        exit();
    }


    // ==============================
    // BASE DE DATOS
    // ==============================

    $db = (new Database())->connect();


    // ==============================
    // BUSCAR CONVERSACIÓN
    // ==============================

    $stmt = $db->prepare("
        SELECT id, usuario_cliente_id, usuario_admin_id, estado
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
            INSERT INTO conversaciones
            (
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

        $conversacion_id = (int) $db->lastInsertId();

    } else {

        $conversacion_id =
            (int) $conversacion["id"];


        // Si estaba cerrada, la abrimos

        if ($conversacion["estado"] === "cerrada") {

            $stmt = $db->prepare("
                UPDATE conversaciones
                SET estado = 'abierta'
                WHERE id = ?
            ");

            $stmt->execute([
                $conversacion_id
            ]);
        }
    }


    // ==============================
    // INSERTAR MENSAJE
    // ==============================

    $stmt = $db->prepare("
        INSERT INTO mensajes
        (
            conversacion_id,
            usuario_id,
            mensaje,
            leido
        )
        VALUES (?, ?, ?, 0)
    ");

    $stmt->execute([
        $conversacion_id,
        $admin_id,
        $mensaje
    ]);


    $mensaje_id =
        (int) $db->lastInsertId();


    // ==============================
    // ACTUALIZAR CONVERSACIÓN
    // ==============================

    $stmt = $db->prepare("
        UPDATE conversaciones
        SET updated_at = NOW()
        WHERE id = ?
    ");

    $stmt->execute([
        $conversacion_id
    ]);


    // ==============================
    // RESPUESTA
    // ==============================

    echo json_encode([
        "success" => true,
        "mensaje" => [
            "id" => $mensaje_id,
            "conversacion_id" => $conversacion_id,
            "usuario_id" => $admin_id,
            "mensaje" => $mensaje
        ]
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "error" => "Error interno del servidor",
        "detalle" => $e->getMessage()
    ]);

}
?>