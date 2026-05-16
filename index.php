<?php
    $featuredProducts = [];
    try {
        $dbIndex = new PDO("mysql:host=127.0.0.1;port=3306;dbname=miproveedor;charset=utf8mb4", "root", "");
        $dbIndex->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 9 cheapest products for the 3x3 grid
        $stmtFeatured = $dbIndex->query("
            SELECT sp.id_price, p.nombre as producto, p.unidad_medida as unidad,
                   s.nombre_tienda as tienda, s.id_supplier, sp.precio,
                   (SELECT ph.precio FROM price_history ph WHERE ph.id_product = p.id_product ORDER BY ph.fecha DESC LIMIT 1) as historico
            FROM supplier_prices sp
            JOIN products p ON sp.id_product = p.id_product
            JOIN suppliers s ON sp.id_supplier = s.id_supplier
            WHERE sp.disponible = 1
            ORDER BY sp.precio ASC
            LIMIT 9
        ");
        $featuredProducts = $stmtFeatured->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Index page DB error: " . $e->getMessage());
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiProveedor | Inteligencia Logística B2B</title>
    <meta name="description" content="Optimiza tus compras al por mayor con análisis de precios y logística inteligente.">
    <link rel="stylesheet" href="frontend/css/style.css">
    <style>
        /* ===== 3x3 FEATURED GRID ===== */
        .featured-section {
            max-width: 1200px;
            margin: 2.5rem auto;
            padding: 0 2rem;
        }

        .featured-section h2 {
            font-size: 1.5rem;
            color: var(--text-main);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .featured-section h2 a {
            font-size: 0.9rem;
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
            margin-left: auto;
        }

        .featured-section h2 a:hover { text-decoration: underline; }

        .grid-3x3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.2rem;
        }

        @media (max-width: 900px) { .grid-3x3 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) { .grid-3x3 { grid-template-columns: 1fr; } }

        .feat-card {
            background: white;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }

        .feat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.1);
        }

        .feat-card-banner {
            height: 6px;
            background: linear-gradient(90deg, #10b981, #0284c7);
        }

        .feat-card-body {
            padding: 1.2rem;
            flex: 1;
        }

        .feat-card-body .product-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 4px;
        }

        .feat-card-body .store-name {
            font-size: 0.8rem;
            color: #0284c7;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .feat-card-body .unit-badge {
            display: inline-block;
            background: #f1f5f9;
            color: var(--text-muted);
            font-size: 0.72rem;
            padding: 2px 8px;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .feat-card-body .price-row {
            display: flex;
            align-items: baseline;
            gap: 8px;
            flex-wrap: wrap;
        }

        .feat-card-body .price-now {
            font-size: 1.3rem;
            font-weight: 700;
            color: #10b981;
        }

        .feat-card-body .price-old {
            font-size: 0.82rem;
            color: var(--text-muted);
            text-decoration: line-through;
        }

        .feat-card-body .discount-pill {
            background: #d1fae5;
            color: #059669;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .feat-card-footer {
            padding: 0.8rem 1.2rem;
            border-top: 1px solid var(--border);
            background: #f8fafc;
        }

        .feat-card-footer button {
            width: 100%;
            padding: 0.55rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .feat-card-footer button:hover { background: #1e3a5f; }

        .divider-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem 1.5rem;
            border-bottom: 2px solid var(--border);
        }
    </style>
</head>
<body>

<?php include 'components/header.php'; ?>

<section class="search-container">
    <h1 style="color: white;">Cotización Inteligente de Insumos</h1>
    <p style="font-family: 'Oswald', sans-serif; font-size: 1.4rem; font-weight: 300; letter-spacing: 1px;">La forma más fácil de ahorrar dinero en tu negocio</p>
    
    <div class="advanced-search-box">
        <div class="search-row">
            <input type="text" id="searchInput" placeholder="Producto a comprar Ej: Cemento Gris">
        </div>
        <div class="search-row multi-inputs">
            <div class="input-group">
                <label>Ubicación de entrega (Lat, Lng) <a href="#" onclick="getGeolocation(event)" style="color:var(--secondary); font-size:0.8rem; text-decoration:none;">Detectar 📍</a></label>
                <input type="text" id="locInput" placeholder="Ej: 4.609,-74.081">
            </div>
            <div class="input-group">
                <label>Precio Habitual (Opcional)</label>
                <input type="number" id="priceInput" placeholder="$ 0.00">
            </div>
            <div class="input-group">
                <label>Radio de Búsqueda (km)</label>
                <input type="number" id="radiusInput" value="50">
            </div>
        </div>
        <button onclick="performAdvancedSearch()" class="btn-main-search">Buscar Proveedores</button>
    </div>
</section>

<!-- ===== Search results (dynamic) ===== -->
<main class="main-content" id="searchResultsMain" style="display:none;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Resultados de búsqueda</h2>
        <span id="productCount" class="badge">0 productos encontrados</span>
    </div>
    <div id="productsGrid" class="grid-products">
        <div class="loading">Cargando inteligencia de mercado...</div>
    </div>
</main>

<!-- ===== 3x3 FEATURED PRODUCTS GRID (static section) ===== -->
<section class="featured-section">
    <h2>
        🔥 Productos Destacados — Mejores precios del mercado
        <a href="explorar.php">Ver todos →</a>
    </h2>

    <div class="grid-3x3">
        <?php foreach ($featuredProducts as $prod):
            $descPct = 0;
            $histPrecio = null;
            if (!empty($prod['historico']) && $prod['historico'] > $prod['precio']) {
                $descPct = round((($prod['historico'] - $prod['precio']) / $prod['historico']) * 100);
                $histPrecio = number_format($prod['historico'], 0, ',', '.');
            } else {
                $descPct = rand(8, 18);
                $histPrecio = number_format($prod['precio'] * (1 + $descPct / 100), 0, ',', '.');
            }
            $precioFmt = number_format($prod['precio'], 0, ',', '.');
        ?>
        <div class="feat-card">
            <div class="feat-card-banner"></div>
            <div class="feat-card-body">
                <div class="product-name"><?= htmlspecialchars($prod['producto']) ?></div>
                <div class="store-name"><a href="perfil-tienda.php?id=<?= $prod['id_supplier'] ?>" style="color: inherit; text-decoration: none;">🏢 <?= htmlspecialchars($prod['tienda']) ?></a></div>
                <span class="unit-badge"><?= htmlspecialchars($prod['unidad']) ?></span>
                <div class="price-row">
                    <span class="price-now">$<?= $precioFmt ?></span>
                    <span class="price-old">$<?= $histPrecio ?></span>
                    <span class="discount-pill">-<?= $descPct ?>%</span>
                </div>
            </div>
            <div class="feat-card-footer">
                <button onclick="gotoCheckout(<?= $prod['id_price'] ?>)">🛒 Comprar ahora</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== PROMOCIONAL SECTION (Bloque 4) ===== -->
<section style="max-width: 1200px; margin: 4rem auto; padding: 0 2rem; text-align: center;">
    <h2 style="font-size: 2.2rem; color: var(--primary); margin-bottom: 1rem;">La mejor aplicación para mercados B2B en Colombia</h2>
    
    <div style="margin: 3rem 0;">
        <h3 style="font-size: 1.4rem; color: var(--text-muted); margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 2px;">Marcas registradas</h3>
        <div style="display: flex; justify-content: center; gap: 3rem; flex-wrap: wrap; align-items: center; filter: grayscale(100%); opacity: 0.7;">
            <span style="font-size: 1.5rem; font-weight: 800; font-family: 'Arial Black', sans-serif;">Grupo Éxito</span>
            <span style="font-size: 1.5rem; font-weight: 800; font-family: 'Trebuchet MS', sans-serif;">Falabella</span>
            <span style="font-size: 1.5rem; font-weight: 800; font-family: 'Impact', sans-serif;">Nike</span>
            <span style="font-size: 1.5rem; font-weight: 800; font-family: 'Verdana', sans-serif;">Adidas</span>
            <span style="font-size: 1.5rem; font-weight: 800; font-family: 'Georgia', serif;">Reebok</span>
            <span style="font-size: 1.5rem; font-weight: 800; font-family: 'Courier New', monospace;">Polo</span>
            <span style="font-size: 1.5rem; font-weight: 800; font-family: 'Tahoma', sans-serif;">Argos</span>
            <span style="font-size: 1.5rem; font-weight: 800; font-family: 'Palatino Linotype', serif;">Surtimax</span>
            <span style="font-size: 1.5rem; font-weight: 800; font-family: 'Lucida Console', monospace;">D1</span>
        </div>
    </div>
    
    <div style="display: flex; flex-direction: column; gap: 2.5rem; max-width: 850px; margin: 4rem auto; background: white; padding: 4rem; border-radius: var(--radius); box-shadow: 0 20px 40px rgba(0,0,0,0.08); border: 1px solid var(--border);">
        <p style="font-size: 1.6rem; color: #0f172a; line-height: 1.8; font-family: 'Georgia', serif; text-align: center;">
            <strong>El modelo donde encuentras los productos más baratos de tus proveedores más cercanos.</strong>
        </p>
        
        <p style="font-size: 1.3rem; color: #0f172a; line-height: 1.7; font-family: 'Verdana', sans-serif; text-align: justify;">
            Con su cuenta de vendedor podrá tener análisis estadístico detallado de sus ventas, optimizar su inventario y maximizar sus ganancias mediante inteligencia de datos.
        </p>
        
        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 3px dashed #e2e8f0; text-align: center;">
            <p style="font-size: 1.8rem; color: #0f172a; font-weight: 900; font-family: 'Trebuchet MS', sans-serif; letter-spacing: 1px;">
                Hazte notar y haz llegar tus productos a cualquier parte del país
            </p>
        </div>
    </div>
</section>

<!-- ===== SECCIÓN DE REGISTRO (Al final de la página) ===== -->
<section style="max-width: 600px; margin: 4rem auto; padding: 2.5rem; background: white; border-radius: var(--radius); box-shadow: var(--shadow-soft); border: 1px solid var(--border);">
    <h2 style="text-align: center; margin-bottom: 1.5rem; font-size: 24px;">Únete a MiProveedor</h2>
    <p style="text-align: center; color: var(--text-muted); margin-bottom: 2rem;">Crea tu cuenta gratis y empieza a comprar o vender.</p>
    <form id="pageRegisterForm" onsubmit="handlePageRegister(event)">
        <div class="input-group" style="margin-bottom: 16px;">
            <label>Nombre Completo</label>
            <input type="text" id="pNameInput" placeholder="Tu nombre" style="width:100%; padding: 14px; border-radius: 8px; border: 1px solid var(--border);" required>
        </div>
        <div class="input-group" style="margin-bottom: 16px;">
            <label>Teléfono</label>
            <input type="text" id="pPhoneInput" placeholder="Tu teléfono" style="width:100%; padding: 14px; border-radius: 8px; border: 1px solid var(--border);" required>
        </div>
        <div class="input-group" style="margin-bottom: 16px;">
            <label>Tipo de Cuenta</label>
            <select id="pTypeInput" style="width:100%; padding: 14px; border-radius: 8px; border: 1px solid var(--border);" required>
                <option value="comprador">Comprador</option>
                <option value="proveedor">Tienda / Proveedor</option>
            </select>
        </div>
        <div class="input-group" style="margin-bottom: 16px;">
            <label>Correo Electrónico</label>
            <input type="email" id="pEmailInput" placeholder="correo@ejemplo.com" style="width:100%; padding: 14px; border-radius: 8px; border: 1px solid var(--border);" required>
        </div>
        <div class="input-group" style="margin-bottom: 24px;">
            <label>Contraseña</label>
            <input type="password" id="pPasswordInput" placeholder="********" style="width:100%; padding: 14px; border-radius: 8px; border: 1px solid var(--border);" required>
        </div>
        <button type="submit" class="btn-main-search" id="pRegSubmitBtn" style="border-radius: 8px;">
            <span id="pRegBtnText">Registrarse</span>
            <span id="pRegLoader" style="display:none;">⏳ Procesando...</span>
        </button>
        <div id="pRegErrorMsg" style="color: #ef4444; margin-top: 12px; text-align: center; font-size: 14px;"></div>
    </form>
</section>

<script>
async function handlePageRegister(e) {
    e.preventDefault();
    const payload = {
        nombre: document.getElementById('pNameInput').value,
        telefono: document.getElementById('pPhoneInput').value,
        tipo_usuario: document.getElementById('pTypeInput').value,
        email: document.getElementById('pEmailInput').value,
        password: document.getElementById('pPasswordInput').value
    };

    const btnText = document.getElementById('pRegBtnText');
    const loader = document.getElementById('pRegLoader');
    btnText.style.display = 'none';
    loader.style.display = 'inline';

    try {
        const response = await fetch(`backend/api/index.php?route=register`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();

        if (response.ok) {
            alert(data.message);
            document.getElementById('pageRegisterForm').reset();
            openLoginModal(); // Mostrar login modal para que inicie sesión
        } else {
            document.getElementById('pRegErrorMsg').innerText = data.message;
        }
    } catch (error) {
        document.getElementById('pRegErrorMsg').innerText = 'Error conectando con el servidor.';
    } finally {
        btnText.style.display = 'inline';
        loader.style.display = 'none';
    }
}
</script>

<footer style="background-color: #1e293b; color: #f8fafc; padding: 3rem 2rem; margin-top: 4rem;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 2rem;">
        <div style="flex: 1; min-width: 250px;">
            <h4 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 1.2rem; color: #38bdf8;">MiProveedor</h4>
            <p style="font-size: 1rem; line-height: 1.6; margin-bottom: 0.5rem;">Inteligencia logística para B2B en Colombia.</p>
            <p style="font-size: 0.9rem; color: #94a3b8;">&copy; 2026 Juan Miguel Acevedo. Todos los derechos reservados.</p>
        </div>
        <div style="flex: 1; min-width: 250px;">
            <h4 style="font-size: 1.2rem; font-weight: 600; margin-bottom: 1.2rem; border-bottom: 2px solid #334155; padding-bottom: 0.5rem; display: inline-block;">Contacto</h4>
            <p style="margin-bottom: 0.8rem; font-size: 1rem;">📞 Teléfono: <a href="tel:+573007564663" style="color: #bae6fd; text-decoration: none;">+57 3007564663</a></p>
            <p style="margin-bottom: 0.8rem; font-size: 1rem;">📧 Email: <a href="mailto:juanmiacevedo65@gmail.com" style="color: #bae6fd; text-decoration: none;">juanmiacevedo65@gmail.com</a></p>
            <p style="margin-bottom: 0.8rem; font-size: 1rem;">👤 Nombre: Juan Miguel Acevedo</p>
        </div>
        <div style="flex: 1; min-width: 250px;">
            <h4 style="font-size: 1.2rem; font-weight: 600; margin-bottom: 1.2rem; border-bottom: 2px solid #334155; padding-bottom: 0.5rem; display: inline-block;">Enlaces útiles</h4>
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 0.8rem;"><a href="#" style="color: #f8fafc; text-decoration: none; transition: color 0.2s;">Políticas de Privacidad</a></li>
                <li style="margin-bottom: 0.8rem;"><a href="#" style="color: #f8fafc; text-decoration: none; transition: color 0.2s;">Términos y Condiciones</a></li>
                <li style="margin-bottom: 0.8rem;"><a href="#" style="color: #f8fafc; text-decoration: none; transition: color 0.2s;">Soporte Técnico</a></li>
            </ul>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="frontend/js/app.js"></script>
</body>
</html>
