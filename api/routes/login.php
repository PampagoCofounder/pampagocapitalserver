<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../../vendor/autoload.php";

use Firebase\JWT\JWT;

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

$db = (new Database())->connect();

$email = trim($data->email);
$password = trim($data->password);

/* 🔥 1. BUSCAR USUARIO */
/*
nueva tabla 
usuarios_pampamind

*/
$stmt = $db->prepare("SELECT id, nombre, email, password FROM usuarios_pampamind WHERE email = ?");
$stmt->execute([$email]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

/* 🔥 2. VALIDAR USER + PASSWORD */
if (!$user || !password_verify($password, $user["password"])) {
    http_response_code(401);
    echo json_encode(["error" => "Credenciales inválidas"]);
    exit();
}

/* 🔥 3. TRAER ROLES */
$stmt = $db->prepare("
    SELECT r.nombre
    FROM roles r
    INNER JOIN usuarios_roles ur ON ur.rol_id = r.id
    WHERE ur.usuario_id = ?
");
$stmt->execute([$user["id"]]);

$roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

/* 🔐 4. JWT */
$key = "mi_clave_super_secreta_de_32_caracteres_minimo_2026";

$payload = [
    "iat" => time(),
    "exp" => time() + (60*60),
    "usuario_id" => (int) $user["id"],
    "nombre"=>$user["nombre"],
    "email" => $user["email"],
    "roles" => $roles
];

$jwt = JWT::encode($payload, $key, "HS256");

/* ✔ RESPONSE */
echo json_encode([
    "token" => $jwt,
    "id"=> $user["id"],
    "nombre"=> $user["nombre"],
    "email" => $user["email"],
    "roles"=> $roles


]);

?>