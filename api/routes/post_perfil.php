<?php 

require_once __DIR__ . '/../config/database.php';

header("Content-Type: application/json");



try {

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Método no permitido");
    }

    if (!isset($_FILES["archivo"])) {
        throw new Exception("No se recibió archivo");
    }

    $empresa_id = $_POST["empresa_id"];


    $tipo_certificacion = $_POST["tipo_certificacion"] ?? null;


    $carpeta = __DIR__ . "/../uploads/perfiles/";

    if (!file_exists($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    $nombreArchivo =
        time() . "_" .
        basename($_FILES["archivo"]["name"]);

    $rutaFisica =
        $carpeta . $nombreArchivo;

    if (
        !move_uploaded_file(
            $_FILES["archivo"]["tmp_name"],
            $rutaFisica
        )
    ) {
        throw new Exception(
            "Error al guardar archivo"
        );
    }

    $url_pdf =
        "/uploads/perfiles/" .
        $nombreArchivo;

    $db = (new Database())->connect();

    $stmt = $db->prepare("
        INSERT INTO perfiles
        (
            perfil_id,
            tipo_certificacion,
            url_pdf
        )
        VALUES
        (
            :empresa_id,
            :tipo_certificacion,
            :url_pdf
        )
    ");

    $stmt->execute([
       
        ":tipo_certificacion" => $tipo_certificacion,
        ":url_pdf" => $url_pdf,
        ":empresa_id" => $empresa_id
    ]);

    echo json_encode([
        "success" => true,
        "url" => $url_pdf
    ]);

} catch (Exception $e) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}




?>