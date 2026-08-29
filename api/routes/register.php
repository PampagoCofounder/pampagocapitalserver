<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../../vendor/autoload.php";

use Firebase\JWT\JWT;

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"));

if (
    !$data ||
    !isset($data->nombre) ||
    !isset($data->email) ||
    !isset($data->password)
) {
    http_response_code(400);
    echo json_encode([
        "error" => "Datos incompletos"
    ]);
    exit();
}

$db = (new Database())->connect();

$nombre = trim($data->nombre);
$email = trim($data->email);
$password = trim($data->password);

/* Validaciones */

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "error" => "Email inválido"
    ]);
    exit();
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode([
        "error" => "La contraseña debe tener al menos 8 caracteres"
    ]);
    exit();
}

/* Verificar email */

$stmt = $db->prepare("
    SELECT id
    FROM usuarios_pampamind
    WHERE email = ?
");

$stmt->execute([$email]);

if ($stmt->fetch()) {

    http_response_code(409);

    echo json_encode([
        "error" => "El email ya se encuentra registrado"
    ]);

    exit();
}

/* Crear usuario */

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("
    INSERT INTO usuarios_pampamind
    (
        nombre,
        email,
        password
    )
    VALUES
    (
        ?,
        ?,
        ?
    )
");

$stmt->execute([
    $nombre,
    $email,
    $passwordHash
]);

$userId = $db->lastInsertId();

/* Rol por defecto */

$stmt = $db->prepare("
    INSERT INTO usuarios_roles
    (
        usuario_id,
        rol_id
    )
    VALUES
    (
        ?,
        ?
    )
");

$stmt->execute([
    $userId,
    2
]);

/* Obtener roles */

$stmt = $db->prepare("
    SELECT r.nombre
    FROM roles r
    INNER JOIN usuarios_roles ur
        ON ur.rol_id = r.id
    WHERE ur.usuario_id = ?
");

$stmt->execute([$userId]);

$roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

/* JWT */

$key = "mi_clave_super_secreta_de_32_caracteres_minimo_2026";

$payload = [

    "iat" => time(),
    "exp" => time() + (60 * 60),

    "id" => $userId,
    "nombre" => $nombre,
    "email" => $email,
    "roles" => $roles

];

$token = JWT::encode($payload, $key, "HS256");

/* Respuesta */

http_response_code(201);

echo json_encode([

    "token" => $token,

    "id" => $userId,

    "nombre" => $nombre,

    "email" => $email,

    "roles" => $roles,

    "message" => "Usuario registrado correctamente"

]);
?>