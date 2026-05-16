<?php
$productosDestacados = [];
try {
    $dbExplorar = new PDO("mysql:host=127.0.0.1;port=3306;dbname=miproveedor;charset=utf8mb4", "root", "");
    $dbExplorar->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Load all products ordered by price ASC (cheapest first)
    $stmtAll = $dbExplorar->query("
            SELECT sp.id_price, p.nombre as producto, p.unidad_medida as unidad, p.descripcion,
                   s.nombre_tienda as tienda, s.id_supplier, s.direccion, s.tiene_envio,
                   sp.precio, sp.cantidad_minima,
                   (SELECT ph.precio FROM price_history ph WHERE ph.id_product = p.id_product ORDER BY ph.fecha DESC LIMIT 1) as historico
            FROM supplier_prices sp
            JOIN products p ON sp.id_product = p.id_product
            JOIN suppliers s ON sp.id_supplier = s.id_supplier
            WHERE sp.disponible = 1
            ORDER BY sp.precio ASC
            LIMIT 16
        ");
    $productosDestacados = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Explorar page DB error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorar Productos | MiProveedor</title>
    <link rel="stylesheet" href="frontend/css/style.css">
    <style>
        /* ===== EXPLORAR LAYOUT ===== */
        .explorar-wrapper {
            display: flex;
            min-height: calc(100vh - 70px); /* Ajuste por el header */
            position: relative;
            background: url('fotos/cosecha_explorar.png') no-repeat center center/cover;
            background-attachment: fixed;
        }

        .explorar-wrapper::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            backdrop-filter: blur(2px);
            background: rgba(45, 55, 72, 0.92); /* Dark grey background */
            z-index: 0;
        }

        .explorar-wrapper > * {
            position: relative;
            z-index: 1;
        }

        /* Left sidebar: 1/4 width, blue tone */
        .explorar-sidebar {
            width: 25%;
            min-width: 250px;
            background: linear-gradient(160deg, #0f172a 0%, #1e3a5f 100%);
            padding: 2rem 1.5rem;
            position: sticky;
            top: 80px;
            height: calc(100vh - 80px);
            overflow-y: auto;
            flex-shrink: 0;
        }

        .explorar-sidebar h2 {
            color: #fff;
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .explorar-sidebar .advanced-search-box {
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin: 0;
            max-width: 100%;
            box-shadow: none;
            color: #fff;
        }

        .explorar-sidebar .search-row input[type="text"],
        .explorar-sidebar .search-row input[type="number"] {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .explorar-sidebar .search-row input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .explorar-sidebar .search-row input:focus {
            border-color: #10b981;
            outline: none;
        }

        .explorar-sidebar .input-group label {
            color: rgba(255, 255, 255, 0.8);
        }

        .explorar-sidebar .input-group input {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            width: 100%;
            padding: 0.7rem;
            border-radius: var(--radius);
            font-family: inherit;
        }

        .explorar-sidebar .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .sidebar-filter-section {
            margin-top: 1.5rem;
        }

        .sidebar-filter-section label.filter-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.82rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: block;
            margin-bottom: 6px;
        }

        .sidebar-filter-section select,
        .sidebar-filter-section input[type="number"] {
            width: 100%;
            padding: 0.6rem 0.8rem;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: #fff;
            font-family: inherit;
            margin-bottom: 1rem;
        }

        .sidebar-filter-section select option {
            background: #1e293b;
            color: #fff;
        }

        .sidebar-filter-section .checkbox-row {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            cursor: pointer;
            margin-bottom: 1rem;
        }

        .sidebar-filter-section .checkbox-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #10b981;
        }

        /* Right results: 2/3 */
        .explorar-results {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .explorar-results h2 {
            font-size: 1.4rem;
            color: var(--text-main);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .explorar-results h2 .results-badge {
            background: #10b981;
            color: white;
            font-size: 0.8rem;
            padding: 3px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        /* 4-column grid */
        .products-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        @media (max-width: 1200px) {
            .products-grid-4 {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 900px) {
            .products-grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }

            .explorar-sidebar {
                width: 40%;
            }
        }

        @media (max-width: 600px) {
            .explorar-wrapper {
                flex-direction: column;
            }

            .explorar-sidebar {
                width: 100%;
                height: auto;
                position: relative;
                top: 0;
            }

            .products-grid-4 {
                grid-template-columns: 1fr;
            }
        }

        /* Product card small */
        .prod-card-sm {
            background: #1a202c;
            border-radius: var(--radius);
            border: 1px solid #475569;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .prod-card-sm:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .prod-card-sm .card-header {
            background: linear-gradient(135deg, #0f172a, #1e3a5f);
            color: white;
            padding: 1rem;
        }

        .prod-card-sm .card-header h4 {
            font-size: 0.92rem;
            margin: 0;
            line-height: 1.3;
            color: white;
        }

        .prod-card-sm .card-body {
            padding: 0.8rem 1rem;
            flex: 1;
        }

        .prod-card-sm .store-name {
            font-size: 0.78rem;
            color: #0284c7;
            font-weight: 600;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .prod-card-sm .prod-unit {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .prod-card-sm .prod-prices {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .prod-card-sm .price-historico {
            font-size: 0.78rem;
            color: var(--text-muted);
            text-decoration: line-through;
        }

        .prod-card-sm .price-actual {
            font-size: 1.1rem;
            font-weight: 700;
            color: #10b981;
        }

        .prod-card-sm .discount-badge {
            display: inline-block;
            background: #d1fae5;
            color: #059669;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 10px;
            margin-top: 4px;
        }

        .prod-card-sm .card-footer {
            padding: 0.6rem 1rem;
            border-top: 1px solid #475569;
            background: #1e293b;
        }

        .prod-card-sm .btn-buy-sm {
            display: block;
            width: 100%;
            padding: 0.5rem;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: background 0.2s;
        }

        .prod-card-sm .btn-buy-sm:hover {
            background: #059669;
        }

        .btn-search-explorar {
            width: 100%;
            padding: 0.9rem;
            background: #10b981;
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 0.5rem;
        }

        .btn-search-explorar:hover {
            background: #059669;
        }

        /* Search result cards */
        .search-result-card {
            background: #1e293b;
            border: 1px solid #475569;
            border-radius: var(--radius);
            padding: 1rem 1.1rem;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s, box-shadow 0.2s;
            color: #f8fafc;
        }

        .search-result-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.28);
        }

        .search-result-card .search-card-title {
            color: #f8fafc;
            font-size: 1.03rem;
            font-weight: 700;
            margin: 0;
        }

        .search-result-card .search-card-meta {
            color: #cbd5e1;
            font-size: 0.84rem;
            line-height: 1.45;
        }

        .search-result-card .search-card-price {
            font-size: 1.3rem;
            font-weight: 800;
            color: #10b981;
            white-space: nowrap;
            text-align: right;
        }

        .search-result-card .search-card-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            align-self: flex-start;
            background: rgba(16, 185, 129, 0.14);
            color: #86efac;
            border: 1px solid rgba(16, 185, 129, 0.35);
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .search-result-card .btn-buy-sm {
            width: auto;
            min-width: 120px;
            padding: 0.7rem 1rem;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.86rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .search-result-card .btn-buy-sm:hover {
            background: #059669;
        }
    </style>
</head>

<body>
    <?php include 'components/header.php'; ?>

    <div class="explorar-wrapper">

        <!-- ===== LEFT SIDEBAR: Buscador + Filtros (1/3) ===== -->
        <aside class="explorar-sidebar">
            <h2>🔍 Buscar Productos</h2>

            <div style="margin-bottom: 1.2rem;">
                <div class="input-group" style="margin-bottom: 1rem;">
                    <label
                        style="color:rgba(255,255,255,0.8); font-size:0.82rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px; display:block;">Nombre
                        del producto</label>
                    <input type="text" id="searchInput" placeholder="Ej: Cemento, Harina...">
                </div>

                <div class="input-group" style="margin-bottom: 1rem;">
                    <label
                        style="color:rgba(255,255,255,0.8); font-size:0.82rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px; display:block;">
                        Ubicación (Lat, Lng)
                        <a href="#" onclick="getGeolocation(event)"
                            style="color:#10b981; font-size:0.75rem; text-decoration:none; margin-left:6px;">📍
                            Detectar</a>
                    </label>
                    <input type="text" id="locInput" placeholder="Ej: 4.609,-74.081">
                </div>

                <div class="input-group" style="margin-bottom: 1rem;">
                    <label
                        style="color:rgba(255,255,255,0.8); font-size:0.82rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px; display:block;">Precio
                        Habitual (Opcional)</label>
                    <input type="number" id="priceInput" placeholder="$ 0.00">
                </div>

                <div class="input-group" style="margin-bottom: 1rem;">
                    <label
                        style="color:rgba(255,255,255,0.8); font-size:0.82rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px; display:block;">Perímetro
                        (km)</label>
                    <input type="number" id="radiusInput" value="50">
                </div>
            </div>

            <div class="sidebar-filter-section">
                <label class="filter-label">Distancia</label>
                <select id="filterDistance" onchange="applyFiltersAndRender()">
                    <option value="">Cualquiera</option>
                    <option value="asc">Más cercanos primero</option>
                </select>

                <label class="filter-label">Cantidad</label>
                <input type="number" id="filterMinQty" placeholder="Ej: 10" oninput="applyFiltersAndRender()">

                <div class="checkbox-row">
                    <input type="checkbox" id="filterTransport" onchange="applyFiltersAndRender()">
                    <label for="filterTransport" style="color:rgba(255,255,255,0.8); cursor:pointer;">🚚 Con
                        transporte</label>
                </div>
            </div>

            <button class="btn-search-explorar" onclick="performSearchExplorar()">🔎 Buscar Proveedores</button>
        </aside>

        <!-- ===== RIGHT: Results (2/3) ===== -->
        <main class="explorar-results">

            <!-- Search results (dynamic via AJAX) -->
            <div id="searchResultsSection" style="display:none; margin-bottom: 3rem;">
                <h2>
                    📋 Resultados de Búsqueda
                    <span class="results-badge" id="searchCount">0</span>
                </h2>
                <div class="products-grid-4" id="productsGrid"></div>
            </div>

            <!-- Static discounted products (from DB) -->
            <h2>
                🔥 Productos en Promoción
                <span class="results-badge"><?= count($productosDestacados) ?> productos</span>
            </h2>

            <div class="products-grid-4" id="staticGrid">
                <?php foreach ($productosDestacados as $p):
                    $descPct = 0;
                    if (!empty($p['historico']) && $p['historico'] > $p['precio']) {
                        $descPct = round((($p['historico'] - $p['precio']) / $p['historico']) * 100);
                    } else {
                        $descPct = rand(8, 20); // fallback visual
                        $p['historico'] = $p['precio'] * (1 + $descPct / 100);
                    }
                    $historicoFmt = number_format($p['historico'], 0, ',', '.');
                    $precioFmt = number_format($p['precio'], 0, ',', '.');
                    ?>
                    <div class="prod-card-sm">
                        <div class="card-header">
                            <h4><?= htmlspecialchars($p['producto']) ?></h4>
                        </div>
                        <div class="card-body">
                            <div class="store-name"><a href="perfil-tienda.php?id=<?= $p['id_supplier'] ?>" style="color: inherit; text-decoration: none;">🏢 <?= htmlspecialchars($p['tienda']) ?></a></div>
                            <div class="prod-unit">📦 <?= htmlspecialchars($p['unidad']) ?></div>
                            <div class="prod-prices">
                                <span class="price-historico">Antes: $<?= $historicoFmt ?></span>
                                <span class="price-actual">$<?= $precioFmt ?></span>
                                <span class="discount-badge">-<?= $descPct ?>% HOY</span>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn-buy-sm" onclick="gotoCheckout(<?= $p['id_price'] ?>)">🛒 Comprar</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </main>
    </div>

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
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="frontend/js/app.js"></script>
<script>
    var currentResults = [];
    var searchTimer = null;

    $(document).ready(function() {
        var urlParams = new URLSearchParams(window.location.search);
        var q = urlParams.get('q');
        if (q) {
            $('#searchInput').val(q);
            if (urlParams.get('loc')) $('#locInput').val(urlParams.get('loc'));
            if (urlParams.get('price')) $('#priceInput').val(urlParams.get('price'));
            if (urlParams.get('radius')) $('#radiusInput').val(urlParams.get('radius'));
            doSearch();
        }

        $('#searchInput').on('keyup', function() {
            clearTimeout(searchTimer);
            var val = $(this).val().trim();
            if (val.length >= 2) {
                searchTimer = setTimeout(function() { doSearch(); }, 300);
            }
        });
    });

    function performSearchExplorar() { doSearch(); }

    function doSearch() {
        var query = $('#searchInput').val().trim();
        if (!query) {
            alert('Por favor ingresa un producto a buscar.');
            return;
        }

        var locVal = $('#locInput').val();
        var loc = locVal.split(',');
        var lat = (loc[0] && !isNaN(loc[0].trim())) ? loc[0].trim() : '4.6097';
        var lng = (loc[1] && !isNaN(loc[1].trim())) ? loc[1].trim() : '-74.0817';
        var usual_price = parseFloat($('#priceInput').val()) || 0;
        var radius = parseInt($('#radiusInput').val()) || 50;

        $('#searchResultsSection').show();
        $('#searchCount').text('...');
        $('#productsGrid').html('<div style="grid-column:1/-1;text-align:center;padding:2rem;color:#cbd5e1;font-size:1.1rem;background:#0f172a;border:1px solid #334155;border-radius:12px;">Buscando productos...</div>');

        $.ajax({
            url: 'backend/api/index.php',
            type: 'GET',
            dataType: 'json',
            data: { route: 'search', q: query, lat: lat, lng: lng, radius: radius, usual_price: usual_price },
            success: function(data) {
                currentResults = Array.isArray(data) ? data : [];
                renderResults(currentResults);
            },
            error: function(xhr) {
                currentResults = [];
                var msg = 'No se encontraron resultados para "' + query + '".';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
                $('#productsGrid').html(
                    '<div style="grid-column:1/-1;text-align:center;padding:2rem;color:#cbd5e1;background:#0f172a;border:1px solid #334155;border-radius:12px;">' +
                    '<strong style="display:block;color:#f8fafc;margin-bottom:6px;">' + msg + '</strong>' +
                    '<span style="color:#94a3b8;">Prueba con otro t?rmino o ajusta el radio de b?squeda.</span>' +
                    '</div>'
                );
                $('#searchCount').text('0');
            }
        });
    }

    function renderResults(results) {
        var grid = document.getElementById('productsGrid');
        var filterMinQty = parseInt($('#filterMinQty').val()) || 1;
        var filterTransport = $('#filterTransport').is(':checked');

        var filtered = results.slice();
        if (filterTransport) filtered = filtered.filter(function(p){ return p.tiene_envio == 1; });

        filtered.forEach(function(p) {
            p.total_price = parseFloat(p.precio || 0) * filterMinQty;
        });

        filtered.sort(function(a, b) {
            return (a.total_price || 0) - (b.total_price || 0);
        });

        $('#searchCount').text(filtered.length);
        grid.style.display = 'flex';
        grid.style.flexDirection = 'column';
        grid.style.gap = '0.9rem';
        grid.style.border = 'none';
        grid.style.borderRadius = '0';
        grid.style.overflow = 'visible';

        if (filtered.length === 0) {
            grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:2rem;color:#cbd5e1;background:#0f172a;border:1px solid #334155;border-radius:12px;">No se encontraron resultados.</div>';
            return;
        }

        var html = '';
        filtered.forEach(function(p) {
            var distStr = (p.distancia_km !== undefined && p.distancia_km !== null && p.distancia_km !== '' && p.distancia_km !== '?') ? p.distancia_km + ' km' : 'Sin distancia';
            var envioStr = p.tiene_envio == 1 ? 'Con env?o' : 'Sin env?o';
            html += '<div class="search-result-card">'
                + '<div style="display:flex; flex-direction:column; gap:0.4rem; min-width:0; flex:1;">'
                + '<span class="search-card-badge">' + envioStr + '</span>'
                + '<h4 class="search-card-title">' + (p.nombre || 'Producto') + '</h4>'
                + '<div class="search-card-meta">'
                + '<div><strong style="color:#38bdf8;">' + (p.tienda || 'Sin proveedor') + '</strong></div>'
                + '<div>' + (p.direccion_tienda || 'Sin direcci?n') + '</div>'
                + '<div>Distancia: ' + distStr + '</div>'
                + '<div>Unidad: ' + (p.unidad || 'N/D') + '</div>'
                + '</div>'
                + '</div>'
                + '<div style="display:flex; align-items:center; gap:0.9rem; margin-left:auto; flex-shrink:0;">'
                + '<div class="search-card-price">$' + parseFloat(p.precio || 0).toLocaleString('es-CO') + '</div>'
                + '<button class="btn-buy-sm" onclick="gotoCheckout(' + (p.id_price || 0) + ')">Comprar</button>'
                + '</div>'
                + '</div>';
        });

        grid.innerHTML = html;
    }

    function applyFiltersAndRender() {
        if (currentResults.length > 0) renderResults(currentResults);
    }
</script>
</body>
</html>
