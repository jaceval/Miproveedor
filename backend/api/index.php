<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../models/Product.php';
include_once '../models/User.php';

try {
    $db = new PDO("mysql:unix_socket=C:/xampp/mysql/mysql.sock;dbname=miproveedor;charset=utf8mb4", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("set names utf8mb4");
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array("message" => "No se pudo conectar a la base de datos."));
    error_log("API DB connection error: " . $e->getMessage());
    exit();
}

$route = isset($_GET['route']) ? $_GET['route'] : '';
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
if ($method == 'GET') {
    if ($route == 'products') {
        $product = new Product($db);
        $stmt = $product->read();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $products_arr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $product_item = array(
                    "id" => $row['id_product'],
                    "nombre" => $row['nombre'],
                    "descripcion" => html_entity_decode($row['descripcion']),
                    "unidad" => $row['unidad_medida'],
                    "precio_desde" => $row['precio_minimo'],
                    "proveedores" => $row['cantidad_proveedores']
                );
                array_push($products_arr, $product_item);
            }
            http_response_code(200);
            echo json_encode($products_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No se encontraron productos."));
        }
    } elseif ($route == 'search') {
        $product = new Product($db);
        $q = isset($_GET['q']) ? $_GET['q'] : '';
        $lat = isset($_GET['lat']) ? $_GET['lat'] : '4.6097';
        $lng = isset($_GET['lng']) ? $_GET['lng'] : '-74.0817';
        $radius = isset($_GET['radius']) ? $_GET['radius'] : '50';
        $usual_price = isset($_GET['usual_price']) ? $_GET['usual_price'] : 0;

        $stmt = $product->searchWithDistance($q, $lat, $lng, $radius);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) === 0 && trim($q) !== '') {
            // Fallback amplio: si el radio no devuelve coincidencias, buscamos sin limitar la distancia.
            $stmt = $product->searchWithDistance($q, $lat, $lng, 999999);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if (count($rows) > 0) {
            $products_arr = array();
            foreach ($rows as $row) {
                $ahorro = 0;
                if ($usual_price > 0 && $usual_price > $row['precio']) {
                    $ahorro = $usual_price - $row['precio'];
                }

                $product_item = array(
                    "id_price" => $row['id_price'],
                    "id_product" => $row['id_product'],
                    "nombre" => $row['producto_nombre'],
                    "descripcion" => html_entity_decode($row['descripcion']),
                    "unidad" => $row['unidad_medida'],
                    "precio" => $row['precio'],
                    "tienda" => $row['nombre_tienda'],
                    "foto_tienda" => $row['foto_perfil'],
                    "direccion_tienda" => $row['direccion'],
                    "distancia_km" => round($row['distancia'], 1),
                    "ahorro_calculado" => $ahorro,
                    "id_supplier" => $row['id_supplier'],
                    "cantidad_minima" => $row['cantidad_minima'],
                    "tiene_envio" => $row['tiene_envio']
                );
                array_push($products_arr, $product_item);
            }
            http_response_code(200);
            echo json_encode($products_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No se encontraron resultados en ese perÃ­metro."));
        }
    } elseif ($route == 'price_detail') {
        $id_price = isset($_GET['id']) ? $_GET['id'] : 0;
        
        $query = "SELECT sp.id_price, sp.precio, sp.cantidad_minima, sp.disponible, 
                         p.id_product, p.nombre as producto_nombre, p.unidad_medida, p.descripcion,
                         s.id_supplier, s.nombre_tienda, s.direccion, s.foto_perfil, s.tiene_envio, s.rating_promedio
                  FROM supplier_prices sp
                  JOIN products p ON sp.id_product = p.id_product
                  JOIN suppliers s ON sp.id_supplier = s.id_supplier
                  WHERE sp.id_price = :id_price LIMIT 0,1";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_price', $id_price);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode($row);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Oferta no encontrada."));
        }
    }
} elseif ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if ($route == 'login') {
        $user = new User($db);
        $user->email = $data->email;
        $stmt = $user->login();
        
        if($stmt->rowCount() > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($data->password, $row['password'])) {
                // Verificar si tiene tienda registrada
                $storeQuery = "SELECT id_supplier FROM suppliers WHERE id_user = :id_user";
                $storeStmt = $db->prepare($storeQuery);
                $storeStmt->bindParam(':id_user', $row['id_user']);
                $storeStmt->execute();
                $has_store = $storeStmt->rowCount() > 0;

                http_response_code(200);
                echo json_encode(array(
                    "message" => "Login exitoso.",
                    "user" => array(
                        "id" => $row['id_user'],
                        "nombre" => $row['nombre'],
                        "tipo" => $row['tipo_usuario'],
                        "has_store" => $has_store
                    )
                ));
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "ContraseÃ±a incorrecta."));
            }
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Usuario no encontrado."));
        }
    } elseif ($route == 'register') {
        $user = new User($db);
        $user->nombre = $data->nombre;
        $user->email = $data->email;
        $user->password = $data->password;
        $user->telefono = $data->telefono;
        $user->tipo_usuario = $data->tipo_usuario;
        
        if($user->register()) {
            http_response_code(201);
            echo json_encode(array("message" => "Usuario registrado exitosamente."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo registrar el usuario."));
        }
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "MÃ©todo no permitido."));
}
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(array(
        "message" => "Error interno en la API.",
        "error" => $e->getMessage()
    ));
}
?>
