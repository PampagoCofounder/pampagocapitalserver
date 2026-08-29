<?php

require_once __DIR__ . '/../config/database.php';

header("Content-Type: application/json");

try {

    // =====================================
    // MÉTODO
    // =====================================

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {

        throw new Exception(
            "Método no permitido"
        );

    }


    // =====================================
    // ARCHIVO
    // =====================================

    if (!isset($_FILES["archivo"])) {

        throw new Exception(
            "No se recibió el archivo"
        );

    }


    // =====================================
    // DATOS
    // =====================================

    $usuario_id =
        $_POST["usuario_id"] ?? null;


    $tipo_documento =
        $_POST["tipo_documento"] ?? null;


    if (!$usuario_id) {

        throw new Exception(
            "No se recibió usuario_id"
        );

    }


    if (!$tipo_documento) {

        throw new Exception(
            "No se recibió tipo_documento"
        );

    }


    // =====================================
    // ARCHIVO
    // =====================================

    $archivo =
        $_FILES["archivo"];


    if (
        $archivo["error"] !==
        UPLOAD_ERR_OK
    ) {

        throw new Exception(
            "Error al subir archivo"
        );

    }


    // =====================================
    // VALIDAR PDF
    // =====================================

    $extension =
        strtolower(
            pathinfo(
                $archivo["name"],
                PATHINFO_EXTENSION
            )
        );


    if ($extension !== "pdf") {

        throw new Exception(
            "Solo se permiten archivos PDF"
        );

    }


    // =====================================
    // CONEXIÓN
    // =====================================

    $db =
        (new Database())->connect();


    $db->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );


    // =====================================
    // BUSCAR CLIENTE
    // =====================================

    $stmtCliente =
        $db->prepare("
            SELECT id
            FROM clientes
            WHERE usuario_id = :usuario_id
            LIMIT 1
        ");


    $stmtCliente->execute([

        ":usuario_id" =>
            $usuario_id

    ]);


    $cliente =
        $stmtCliente->fetch(
            PDO::FETCH_ASSOC
        );


    if (!$cliente) {

        throw new Exception(
            "No se encontró un cliente asociado al usuario"
        );

    }


    $cliente_id =
        $cliente["id"];


    // =====================================
    // CARPETA
    // =====================================

    $carpeta =
        __DIR__ .
        "/../uploads/documentos_clientes/";


    if (!is_dir($carpeta)) {

        if (
            !mkdir(
                $carpeta,
                0777,
                true
            )
        ) {

            throw new Exception(
                "No se pudo crear la carpeta"
            );

        }

    }


    // =====================================
    // NOMBRE DEL ARCHIVO
    // =====================================

    $nombreOriginal =
        basename(
            $archivo["name"]
        );


    $nombreArchivo =
        time() .
        "_" .
        $nombreOriginal;


    $rutaFisica =
        $carpeta .
        $nombreArchivo;


    // =====================================
    // GUARDAR ARCHIVO
    // =====================================

    if (
        !move_uploaded_file(
            $archivo["tmp_name"],
            $rutaFisica
        )
    ) {

        throw new Exception(
            "No se pudo guardar el archivo"
        );

    }


    // =====================================
    // RUTA BD
    // =====================================

    $rutaArchivo =
        "/uploads/documentos_clientes/" .
        $nombreArchivo;


    // =====================================
    // INSERT
    // =====================================

    $stmt =
        $db->prepare("
            INSERT INTO documentos_clientes
            (
                cliente_id,
                tipo_documento,
                nombre_archivo,
                ruta_archivo,
                fecha_subida,
                estado
            )
            VALUES
            (
                :cliente_id,
                :tipo_documento,
                :nombre_archivo,
                :ruta_archivo,
                NOW(),
                'activo'
            )
        ");


    $stmt->execute([

        ":cliente_id" =>
            $cliente_id,

        ":tipo_documento" =>
            $tipo_documento,

        ":nombre_archivo" =>
            $nombreOriginal,

        ":ruta_archivo" =>
            $rutaArchivo

    ]);


    // =====================================
    // RESPUESTA
    // =====================================

    echo json_encode([

        "success" => true,

        "message" =>
            "Documento guardado correctamente",

        "cliente_id" =>
            $cliente_id,

        "documento_id" =>
            $db->lastInsertId(),

        "nombre_archivo" =>
            $nombreOriginal,

        "ruta_archivo" =>
            $rutaArchivo

    ]);

    exit;


} catch (Exception $e) {

    http_response_code(400);


    echo json_encode([

        "success" => false,

        "error" =>
            $e->getMessage()

    ]);

    exit;

}

?>