<?php
$id_supplier = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_supplier == 0) {
    header("Location: explorar.php");
    exit;
}

$store = null;
$products = [];

try {
    $db = new PDO("mysql:host=127.0.0.1;port=3306;dbname=miproveedor;charset=utf8mb4", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get store info
    $stmtStore = $db->prepare("SELECT * FROM suppliers WHERE id_supplier = ?");
    $stmtStore->execute([$id_supplier]);
    $store = $stmtStore->fetch(PDO::FETCH_ASSOC);

    if ($store) {
        // Get store products
        $stmtProducts = $db->prepare("
            SELECT sp.id_price, sp.precio, p.nombre as producto, p.unidad_medida as unidad
            FROM supplier_prices sp
            JOIN products p ON sp.id_product = p.id_product
            WHERE sp.id_supplier = ? AND sp.disponible = 1
        ");
        $stmtProducts->execute([$id_supplier]);
        $products = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Store profile DB error: " . $e->getMessage());
}

if (!$store) {
    echo "Tienda no encontrada o error de conexión.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($store['nombre_tienda']) ?> | MiProveedor</title>
    <link rel="stylesheet" href="frontend/css/style.css">
    <style>
        .store-header {
            background: white;
            padding: 3rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-top: 2rem;
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        .store-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--border);
        }
        .store-details h1 {
            margin-bottom: 0.5rem;
            color: var(--primary);
        }
        .store-ranking {
            font-size: 1.2rem;
            color: #fbbf24;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

<?php include 'components/header.php'; ?>

<main class="main-content">
    <div class="store-header">
        <img src="fotos/<?= !empty($store['foto_perfil']) ? htmlspecialchars($store['foto_perfil']) : 'distribuidora.png' ?>" alt="Foto tienda" class="store-photo">
        <div class="store-details">
            <h1><?= htmlspecialchars($store['nombre_tienda']) ?></h1>
            <div class="store-ranking">⭐ <?= $store['rating_promedio'] > 0 ? $store['rating_promedio'] : 'Sin calificar' ?></div>
            <p style="color: var(--text-muted); font-size: 1.1rem;"><?= htmlspecialchars($store['descripcion']) ?></p>
            <p style="margin-top: 10px; font-weight: bold;">📍 <?= htmlspecialchars($store['direccion']) ?></p>
        </div>
    </div>

    <h2 style="margin-top: 3rem; border-bottom: 2px solid var(--border); padding-bottom: 10px;">Catálogo de Productos</h2>
    
    <div class="grid-products" style="margin-top: 2rem;">
        <?php foreach ($products as $p): ?>
        <div class="product-card">
            <div class="product-info">
                <h3><?= htmlspecialchars($p['producto']) ?></h3>
                <p style="color: var(--text-muted);">📦 <?= htmlspecialchars($p['unidad']) ?></p>
                <div class="price-tag">$<?= number_format($p['precio'], 0, ',', '.') ?></div>
                <button class="btn-main-search" onclick="gotoCheckout(<?= $p['id_price'] ?>)">Comprar</button>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if(empty($products)): ?>
            <p style="color: var(--text-muted);">Esta tienda no tiene productos disponibles actualmente.</p>
        <?php endif; ?>
    </div>
</main>

<script src="frontend/js/app.js"></script>
</body>
</html>
