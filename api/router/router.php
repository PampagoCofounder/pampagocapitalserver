<?php

require_once __DIR__ . "/../config/cors.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];



//limpiar ruta correctamente
$path = trim($uri,"/");
$segments = explode("/",$path);

//validar que empiece con /api
if(isset($segments[0]) && $segments[0] === "api"){
    $route = $segments[1] ?? "";
}else{
    $route = "";
}

if ($route === "login" && $method === "GET") {
    http_response_code(405);
    echo json_encode([
        "error" => "Método no permitido",
        "hint" => "Usar POST"
    ]);
    exit;
}



$routes = [

    "POST" => [
        "login" => "routes/login.php",
        "upload-clientes"=> "routes/upload_clientes.php",
        "solicitud_comex" => "routes/solicitud_comex.php",
        "chatbot"=> "routes/post_chatbot.php",
        "enviar_documentacion" => "routes/post_enviar_documentacion.php",
        "register" => "routes/register.php",
        "enviar_documentacion_cliente" => "routes/post_enviar_documentacion_cliente.php",
        "enviar_mensaje"=>"routes/post_enviar_mensajes.php",


    ],
    "GET" => [
        "clientes" => "routes/get_clientes.php",
        "dolar" => "routes/dolar.php",
        "riesgo" => "routes/riesgopais.php",

        "dolar_clp" => "routes/moneda_chilena.php",
       


        "obtener_documentacion" => "routes/get_enviar_documentacion.php",
        "obtener_datos_clientes" => "routes/get_clientes.php",
        "obtener_productos_bancarios"=> "routes/get_productos_bancarios.php",
        "obtener_cuotas_clientes" => "routes/get_cuotas_clientes.php",
        "obtener_deuda_bcra" => "routes/get_central_deudores.php",
        "obtener_mensajes" => "routes/get_obtener_mensajes.php",
        "obtener_conversaciones" => "routes/get_obtener_conversaciones.php",
        "admin_clientes" => "routes/get_admin_clientes.php",
        "obtener_conversacion_cliente"=>"routes/get_obtener_conversacion_cliente.php"

    ],
    "DELETE" => [
        "eliminar_productos" => "routes/delete_productos_comercializacion.php",
        "eliminar_proveedores" => "routes/delete_proveedores_comercializacion.php",
        "eliminar_distribuidores" => "routes/delete_distribuidores_comercializacion.php"
    ],
    "PUT" => [
        "actualizar_productos" => "routes/put_productos_comercializacion.php",
        "actualizar_proveedores" => "routes/put_proveedores_comercializacion.php",
        "actualizar_distribuidores" => "routes/put_distribuidores_comercializacion.php",
    ]
    
];

if (isset($routes[$method][$route])) {
    require_once __DIR__ . "/../" . $routes[$method][$route];
    exit();
}

http_response_code(404);
echo json_encode([
    "error" => "Ruta no encontrada",
    "route" => $route,
    "method"=>$method,
    "uri" => $uri
]);

?>