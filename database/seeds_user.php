<?php
// c:\xampp\htdocs\miproveedor\database\seeds_user.php
include_once 'c:\xampp\htdocs\miproveedor\backend\config\db.php';
$database = new Database();
$db = $database->getConnection();

// Add foto column if not exists
try {
    $db->exec("ALTER TABLE products ADD COLUMN foto VARCHAR(255) DEFAULT 'default_product.png'");
} catch(Exception $e) {}

$suppliers = [
    ['nombre' => 'Ferretería Central', 'direccion' => 'Calle 45 #12-34, Medellín', 'descripcion' => 'Proveedor de materiales de construcción y herramientas.', 'foto' => 'ferreteria.png'],
    ['nombre' => 'Químicos Industriales SAS', 'direccion' => 'Carrera 30 #22-10, Medellín', 'descripcion' => 'Venta de productos químicos industriales.', 'foto' => 'quimicos.png'],
    ['nombre' => 'Distribuidora Norte', 'direccion' => 'Av. 80 #50-20, Bello', 'descripcion' => 'Distribución de alimentos al por mayor.', 'foto' => 'distribuidora.png'],
    ['nombre' => 'AgroCampo Colombia', 'direccion' => 'Km 5 vía Rionegro', 'descripcion' => 'Insumos agrícolas y fertilizantes.', 'foto' => 'agrocampo.png'],
    ['nombre' => 'Suministros Urbanos', 'direccion' => 'Calle 10 #8-15, Envigado', 'descripcion' => 'Productos generales para negocios urbanos.', 'foto' => 'suministros.png']
];

$products_by_store = [
    0 => [ // Ferretería Central
        ['nombre' => 'Cemento Gris', 'precio' => 28000, 'unidad_medida' => 'bolsa', 'descripcion' => 'Cemento de alta resistencia para construcción.', 'foto' => 'cemento.jpg'],
        ['nombre' => 'Arena Fina', 'precio' => 50000, 'unidad_medida' => 'm3', 'descripcion' => 'Arena para mezcla de concreto.', 'foto' => 'arena.jpg'],
        ['nombre' => 'Grava', 'precio' => 60000, 'unidad_medida' => 'm3', 'descripcion' => 'Material para construcción.', 'foto' => 'grava.jpg'],
        ['nombre' => 'Varilla Acero', 'precio' => 35000, 'unidad_medida' => 'unidad', 'descripcion' => 'Varilla de acero reforzado.', 'foto' => 'varilla.jpg'],
        ['nombre' => 'Ladrillo', 'precio' => 800, 'unidad_medida' => 'unidad', 'descripcion' => 'Ladrillo rojo estándar.', 'foto' => 'ladrillo.jpg']
    ],
    1 => [ // Químicos
        ['nombre' => 'Ácido Clorhídrico', 'precio' => 45000, 'unidad_medida' => 'litro', 'descripcion' => 'Uso industrial y limpieza pesada.', 'foto' => 'acido.jpg'],
        ['nombre' => 'Soda Cáustica', 'precio' => 30000, 'unidad_medida' => 'kg', 'descripcion' => 'Producto químico para limpieza.', 'foto' => 'soda.jpg'],
        ['nombre' => 'Alcohol Industrial', 'precio' => 15000, 'unidad_medida' => 'litro', 'descripcion' => 'Desinfección y uso industrial.', 'foto' => 'alcohol.jpg'],
        ['nombre' => 'Peróxido de Hidrógeno', 'precio' => 20000, 'unidad_medida' => 'litro', 'descripcion' => 'Uso químico especializado.', 'foto' => 'peroxido.jpg'],
        ['nombre' => 'Cloro Líquido', 'precio' => 12000, 'unidad_medida' => 'litro', 'descripcion' => 'Desinfectante.', 'foto' => 'cloro.jpg']
    ],
    2 => [ // Distribuidora Norte
        ['nombre' => 'Harina de Trigo', 'precio' => 120000, 'unidad_medida' => 'bulto', 'descripcion' => 'Harina para panadería.', 'foto' => 'harina.jpg'],
        ['nombre' => 'Arroz', 'precio' => 140000, 'unidad_medida' => 'bulto', 'descripcion' => 'Arroz blanco premium.', 'foto' => 'arroz.jpg'],
        ['nombre' => 'Azúcar', 'precio' => 130000, 'unidad_medida' => 'bulto', 'descripcion' => 'Azúcar refinada.', 'foto' => 'azucar.jpg'],
        ['nombre' => 'Aceite Vegetal', 'precio' => 9000, 'unidad_medida' => 'litro', 'descripcion' => 'Aceite para cocina.', 'foto' => 'aceite.jpg'],
        ['nombre' => 'Sal', 'precio' => 50000, 'unidad_medida' => 'bulto', 'descripcion' => 'Sal industrial.', 'foto' => 'sal.jpg']
    ],
    3 => [ // AgroCampo
        ['nombre' => 'Fertilizante NPK', 'precio' => 80000, 'unidad_medida' => 'saco', 'descripcion' => 'Fertilizante completo.', 'foto' => 'fertilizante.jpg'],
        ['nombre' => 'Semillas Maíz', 'precio' => 40000, 'unidad_medida' => 'kg', 'descripcion' => 'Semillas certificadas.', 'foto' => 'maiz.jpg'],
        ['nombre' => 'Abono Orgánico', 'precio' => 30000, 'unidad_medida' => 'saco', 'descripcion' => 'Abono natural.', 'foto' => 'abono.jpg'],
        ['nombre' => 'Herbicida', 'precio' => 60000, 'unidad_medida' => 'litro', 'descripcion' => 'Control de maleza.', 'foto' => 'herbicida.jpg'],
        ['nombre' => 'Insecticida', 'precio' => 70000, 'unidad_medida' => 'litro', 'descripcion' => 'Control de plagas.', 'foto' => 'insecticida.jpg']
    ],
    4 => [ // Suministros Urbanos
        ['nombre' => 'Papel Higiénico', 'precio' => 25000, 'unidad_medida' => 'paquete', 'descripcion' => 'Paquete por 12 unidades.', 'foto' => 'papel.jpg'],
        ['nombre' => 'Detergente', 'precio' => 18000, 'unidad_medida' => 'kg', 'descripcion' => 'Detergente en polvo.', 'foto' => 'detergente.jpg'],
        ['nombre' => 'Jabón Líquido', 'precio' => 15000, 'unidad_medida' => 'litro', 'descripcion' => 'Limpieza general.', 'foto' => 'jabon.jpg'],
        ['nombre' => 'Desinfectante', 'precio' => 12000, 'unidad_medida' => 'litro', 'descripcion' => 'Elimina bacterias.', 'foto' => 'desinfectante.jpg'],
        ['nombre' => 'Bolsas Plásticas', 'precio' => 10000, 'unidad_medida' => 'paquete', 'descripcion' => 'Paquete por 100 unidades.', 'foto' => 'bolsas.jpg']
    ]
];

$userId = 1; // Admin user as the default user for these suppliers

foreach ($suppliers as $i => $sup) {
    // Check if supplier exists
    $stmt = $db->prepare("SELECT id_supplier FROM suppliers WHERE nombre_tienda = ?");
    $stmt->execute([$sup['nombre']]);
    if ($stmt->rowCount() > 0) {
        $supplierId = $stmt->fetchColumn();
    } else {
        $stmt = $db->prepare("INSERT INTO suppliers (id_user, nombre_tienda, descripcion, direccion, foto_perfil) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $sup['nombre'], $sup['descripcion'], $sup['direccion'], $sup['foto']]);
        $supplierId = $db->lastInsertId();
    }

    foreach ($products_by_store[$i] as $prod) {
        // Insert product
        $stmt = $db->prepare("SELECT id_product FROM products WHERE nombre = ? AND descripcion = ?");
        $stmt->execute([$prod['nombre'], $prod['descripcion']]);
        if ($stmt->rowCount() > 0) {
            $productId = $stmt->fetchColumn();
        } else {
            $stmt = $db->prepare("INSERT INTO products (nombre, descripcion, unidad_medida, foto) VALUES (?, ?, ?, ?)");
            $stmt->execute([$prod['nombre'], $prod['descripcion'], $prod['unidad_medida'], $prod['foto']]);
            $productId = $db->lastInsertId();
        }

        // Insert supplier price
        $stmt = $db->prepare("SELECT id_price FROM supplier_prices WHERE id_supplier = ? AND id_product = ?");
        $stmt->execute([$supplierId, $productId]);
        if ($stmt->rowCount() == 0) {
            $stmt = $db->prepare("INSERT INTO supplier_prices (id_supplier, id_product, precio, cantidad_minima) VALUES (?, ?, ?, ?)");
            $stmt->execute([$supplierId, $productId, $prod['precio'], 1]);
        }

        // Insert historical price (+ 15% to simulate a discount)
        $historico = $prod['precio'] * 1.15;
        $stmt = $db->prepare("INSERT INTO price_history (id_supplier, id_product, precio, fecha) VALUES (?, ?, ?, DATE_SUB(NOW(), INTERVAL 30 DAY))");
        $stmt->execute([$supplierId, $productId, $historico]);
    }
}

echo "Database seeded successfully.\n";
