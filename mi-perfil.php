<?php
    $allProducts = [];
    $adminStores = [];
    $adminStats = [
        'establecimientos' => 0,
        'ofertas' => 0,
        'volumen_bruto' => 0,
        'comision_estimada' => 0,
        'ticket_promedio' => 0
    ];
    $topAdminStores = [];
    try {
        $dbProducts = new PDO("mysql:host=127.0.0.1;port=3306;dbname=miproveedor;charset=utf8mb4", "root", "");
        $dbProducts->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmtP = $dbProducts->query("
            SELECT s.id_user, p.nombre, p.unidad_medida, sp.precio 
            FROM products p 
            JOIN supplier_prices sp ON p.id_product = sp.id_product
            JOIN suppliers s ON sp.id_supplier = s.id_supplier
        ");
        $allProducts = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        $stmtStores = $dbProducts->query("
            SELECT
                s.id_supplier,
                s.nombre_tienda,
                s.rating_promedio,
                s.direccion,
                u.fecha_registro,
                COALESCE(SUM(sp.precio), 0) AS volumen_bruto,
                COUNT(sp.id_price) AS ofertas
            FROM suppliers s
            LEFT JOIN users u ON s.id_user = u.id_user
            LEFT JOIN supplier_prices sp ON s.id_supplier = sp.id_supplier
            GROUP BY s.id_supplier, s.nombre_tienda, s.rating_promedio, s.direccion, u.fecha_registro
            ORDER BY s.id_supplier DESC
        ");
        $adminStores = $stmtStores->fetchAll(PDO::FETCH_ASSOC);

        $stmtStats = $dbProducts->query("
            SELECT
                COUNT(DISTINCT s.id_supplier) AS establecimientos,
                COUNT(sp.id_price) AS ofertas,
                COALESCE(SUM(sp.precio), 0) AS volumen_bruto,
                COALESCE(SUM(sp.precio * 0.05), 0) AS comision_estimada,
                COALESCE(AVG(sp.precio), 0) AS ticket_promedio
            FROM suppliers s
            LEFT JOIN supplier_prices sp ON s.id_supplier = sp.id_supplier
        ");
        $adminStats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: $adminStats;

        $stmtTopStores = $dbProducts->query("
            SELECT
                s.nombre_tienda,
                COUNT(sp.id_price) AS ofertas,
                COALESCE(SUM(sp.precio), 0) AS volumen_bruto,
                COALESCE(SUM(sp.precio * 0.05), 0) AS comision_estimada
            FROM suppliers s
            LEFT JOIN supplier_prices sp ON s.id_supplier = sp.id_supplier
            GROUP BY s.id_supplier, s.nombre_tienda
            ORDER BY comision_estimada DESC, volumen_bruto DESC
            LIMIT 5
        ");
        $topAdminStores = $stmtTopStores->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Profile page DB error: " . $e->getMessage());
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | MiProveedor</title>
    <link rel="stylesheet" href="frontend/css/style.css">
    <style>
        .profile-layout { display: flex; max-width: 1100px; margin: 3rem auto; gap: 2rem; padding: 0 1rem; }
        .sidebar { width: 250px; flex-shrink: 0; background: #1a202c; border-radius: var(--radius); box-shadow: var(--shadow); border: 1px solid #475569; overflow: hidden; align-self: start; }
        .sidebar-item { display: block; padding: 15px 20px; border-bottom: 1px solid #475569; cursor: pointer; color: #f8fafc; font-weight: 600; text-decoration: none; transition: background 0.2s; }
        .sidebar-item:hover { background: #2d3748; }
        .sidebar-item.active { background: #0c4a6e; color: #38bdf8; border-left: 4px solid #0284c7; }
        .content-area { flex: 1; background: #1a202c; border-radius: var(--radius); box-shadow: var(--shadow); border: 1px solid #475569; padding: 2rem; color: #f8fafc; }
        .seller-only, .admin-only { display: none; }
        
        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .modal-content { background-color: #1a202c; color: #f8fafc; margin: 5% auto; padding: 20px; border: 1px solid #475569; width: 90%; max-width: 500px; border-radius: var(--radius); }
        .close { color: #94a3b8; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #f8fafc; }
        .input-group input, .input-group select, .input-group textarea { width: 100%; padding: 10px; border: 1px solid #475569; border-radius: var(--radius); background: #2d3748; color: #f8fafc; }
        
        .product-list-item { display: flex; align-items: center; justify-content: space-between; padding: 15px; border: 1px solid #475569; border-radius: var(--radius); margin-bottom: 10px; background: #2d3748; }
        .product-list-item img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; background: #475569; }

        .header-action { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #475569; padding-bottom: 15px; }
        
        /* Admin specific styles */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #475569; color: #f8fafc; }
        th { background: #0f172a; color: #94a3b8; }
        td { background: #1e293b; }
        .chart-mock { height: 300px; background: #1a202c; border-radius: var(--radius); border: 1px solid #475569; padding: 20px; margin-top: 20px;}
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include 'components/header.php'; ?>

<main class="main-content">
    <div class="profile-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div style="padding: 20px; background: var(--primary); color: white; text-align: center;">
                <div id="profilePicContainer" style="width: 80px; height: 80px; background: white; color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; margin: 0 auto 10px auto; position: relative; overflow: hidden; cursor: pointer; border: 2px solid white;" onclick="document.getElementById('profilePhotoInput').click()">
                    <span id="userInitial">U</span>
                    <img id="profilePreview" src="" alt="Profile" style="display: none; width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;">
                    <div class="profile-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: normal; opacity: 0; transition: opacity 0.2s;">
                        Cambiar<br>foto
                    </div>
                </div>
                <input type="file" id="profilePhotoInput" style="display: none;" accept="image/*" onchange="previewProfilePhoto(event)">
                <style>
                    #profilePicContainer:hover .profile-overlay { opacity: 1 !important; }
                </style>
                <script>
                    function previewProfilePhoto(event) {
                        const file = event.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                document.getElementById('profilePreview').src = e.target.result;
                                document.getElementById('profilePreview').style.display = 'block';
                                document.getElementById('userInitial').style.display = 'none';
                            }
                            reader.readAsDataURL(file);
                        }
                    }
                </script>
                <h3 id="userName" style="margin: 0; font-size: 1.1rem;">Usuario</h3>
                <p id="userRoleBadge" style="margin: 5px 0 0 0; font-size: 0.8rem; opacity: 0.8; text-transform: uppercase;">Comprador</p>
            </div>
            
            <a class="sidebar-item active" onclick="showTab('info')">Mi perfil</a>
            <a class="sidebar-item" onclick="showTab('estadisticas')">Estadísticas</a>
            <a class="sidebar-item" onclick="showTab('establecimientos')">Establecimientos</a>
        </aside>

        <!-- Content Area -->
        <section class="content-area">
            
            <!-- TAB: Mi Información -->
            <div id="tab-info" class="tab-content">
                <div style="text-align: right; margin-bottom: 15px;">
                    <button class="btn-main-search" id="btnRegisterStoreHeader" style="padding: 10px 20px; display: none;" onclick="openStoreModal()">Registrar tienda</button>
                </div>
                <div class="header-action" style="border-bottom: 1px solid var(--border); padding-bottom: 15px;">
                    <h2 style="margin: 0;">Información de la Cuenta</h2>
                </div>
                <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0 0 5px 0;">Nombre Completo</p>
                        <p id="infoName" style="font-weight: bold; margin: 0; font-size: 1.1rem;">-</p>
                    </div>
                    <div>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0 0 5px 0;">Correo Electrónico</p>
                        <p id="infoEmail" style="font-weight: bold; margin: 0; font-size: 1.1rem;">-</p>
                    </div>
                    <div>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0 0 5px 0;">Rol Actual</p>
                        <p id="infoRole" style="font-weight: bold; margin: 0; font-size: 1.1rem; text-transform: capitalize;">-</p>
                    </div>
                </div>

                <div id="storeInfoContainer" class="seller-only" style="margin-top: 30px; padding: 20px; background: #f8fafc; border-radius: var(--radius); border: 1px solid var(--border); display: none;">
                    <h3 style="margin-top: 0; margin-bottom: 15px; color: var(--primary);">Información de la Empresa</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <p style="margin:0;"><strong>Empresa:</strong> <span id="storeInfoName">-</span></p>
                        <p style="margin:0;"><strong>NIT:</strong> <span id="storeInfoNit">-</span></p>
                        <p style="margin:0;"><strong>Contacto:</strong> <span id="storeInfoContact">-</span></p>
                        <p style="margin:0;"><strong>Dirección:</strong> <span id="storeInfoAddress">-</span></p>
                    </div>
                    <div style="margin-top: 15px; padding: 10px; background: #e0f2fe; border-radius: 8px; display: inline-block;">
                        <h4 style="margin: 0; color: #0284c7;" id="totalInventarioText">Patrimonio: $0</h4>
                    </div>
                </div>
            </div>

            </div>

            <!-- TAB: Tiendas (Admin) -->
            <div id="tab-establecimientos" class="tab-content" style="display: none;">
                <div class="header-action" style="margin-bottom: 20px;">
                    <h2 style="margin: 0;">Establecimientos registrados</h2>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <input type="text" id="searchAdminTiendas" placeholder="Filtrar por nombre..." style="padding: 8px 15px; border-radius: var(--radius); border: 1px solid var(--border); background: white; color: #0f172a;" onkeyup="filterAdminTiendas()">
                        <input type="date" id="searchAdminDate" style="padding: 8px 15px; border-radius: var(--radius); border: 1px solid var(--border); background: white; color: #0f172a;" onchange="filterAdminTiendas()">
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table id="adminTiendasTable" style="min-width: 1100px; background: white;">
                        <thead>
                            <tr>
                                <th>Nombre del establecimiento</th>
                                <th>Dirección</th>
                                <th>Fecha de registro</th>
                                <th>Tipo de servicio</th>
                                <th>Ingresos estimados</th>
                                <th>Ranking</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $count = 0;
                            foreach ($adminStores as $s):
                                $count++;
                                if ($count > 15) continue;
                                $fechaRegistro = !empty($s['fecha_registro']) ? date('Y-m-d', strtotime($s['fecha_registro'])) : '';
                            ?>
                            <tr data-name="<?= htmlspecialchars(strtolower($s['nombre_tienda'])) ?>" data-date="<?= htmlspecialchars($fechaRegistro) ?>">
                                <td class="td-nombre"><?= htmlspecialchars($s['nombre_tienda']) ?></td>
                                <td><?= htmlspecialchars($s['direccion'] ?? 'Sin dirección') ?></td>
                                <td><?= $fechaRegistro ?: 'Sin fecha' ?></td>
                                <td><?= ((int)$s['id_supplier'] % 2 === 0) ? 'Mayorista' : 'Minorista' ?></td>
                                <td>$<?= number_format((float)$s['volumen_bruto'], 0, ',', '.') ?></td>
                                <td>⭐ <?= $s['rating_promedio'] > 0 ? $s['rating_promedio'] : 'N/A' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB: Estadísticas (Admin) -->
            <div id="tab-estadisticas" class="tab-content" style="display: none;">
                <h2 style="margin-top: 0; border-bottom: 1px solid var(--border); padding-bottom: 15px;">Informe de ingresos por comisión del 5%</h2>

                <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px;">
                    <div style="background: #f8fafc; padding: 18px; border-radius: var(--radius); border: 1px solid var(--border);">
                        <p style="margin: 0 0 6px 0; color: var(--text-muted); font-size: 0.85rem;">Establecimientos</p>
                        <h3 style="margin: 0; font-size: 1.8rem; color: var(--secondary);"><?= number_format((int)$adminStats['establecimientos'], 0, ',', '.') ?></h3>
                    </div>
                    <div style="background: #f8fafc; padding: 18px; border-radius: var(--radius); border: 1px solid var(--border);">
                        <p style="margin: 0 0 6px 0; color: var(--text-muted); font-size: 0.85rem;">Ofertas activas</p>
                        <h3 style="margin: 0; font-size: 1.8rem; color: var(--secondary);"><?= number_format((int)$adminStats['ofertas'], 0, ',', '.') ?></h3>
                    </div>
                    <div style="background: #f8fafc; padding: 18px; border-radius: var(--radius); border: 1px solid var(--border);">
                        <p style="margin: 0 0 6px 0; color: var(--text-muted); font-size: 0.85rem;">Volumen bruto estimado</p>
                        <h3 style="margin: 0; font-size: 1.8rem; color: var(--secondary);">$<?= number_format((float)$adminStats['volumen_bruto'], 0, ',', '.') ?> COP</h3>
                    </div>
                    <div style="background: #f8fafc; padding: 18px; border-radius: var(--radius); border: 1px solid var(--border);">
                        <p style="margin: 0 0 6px 0; color: var(--text-muted); font-size: 0.85rem;">Comisión estimada 5%</p>
                        <h3 style="margin: 0; font-size: 1.8rem; color: var(--secondary);">$<?= number_format((float)$adminStats['comision_estimada'], 0, ',', '.') ?> COP</h3>
                    </div>
                </div>

                <div style="background: white; padding: 2rem; border-radius: var(--radius); border: 1px solid var(--border); color: #0f172a; margin-bottom: 24px;">
                    <h3 style="color: #0f172a; margin-top: 0;">Resumen ejecutivo</h3>
                    <p style="margin-bottom: 0.9rem; line-height: 1.7;">La plataforma opera con una comisión fija del <strong>5%</strong> sobre cada venta. Con base en las ofertas registradas, el volumen bruto estimado asciende a <strong>$<?= number_format((float)$adminStats['volumen_bruto'], 0, ',', '.') ?> COP</strong> y la comisión potencial generada es de <strong>$<?= number_format((float)$adminStats['comision_estimada'], 0, ',', '.') ?> COP</strong>.</p>
                    <p style="margin-bottom: 0; line-height: 1.7;">El rendimiento se concentra principalmente en categorías de construcción y suministros de alta rotación. Mantener el 5% ayuda a conservar competitividad, mientras que aumentar visibilidad y conversión eleva el ingreso total sin presionar al proveedor.</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                    <div class="chart-mock" style="background: white; height: auto; min-height: 220px;">
                        <h4 style="text-align: center; color: #0f172a; margin-top: 0;">Top establecimientos por comisión</h4>
                        <table style="width:100%; background:white; color:#0f172a; margin-top:12px;">
                            <thead>
                                <tr>
                                    <th style="color:#475569; background:#f8fafc;">Establecimiento</th>
                                    <th style="color:#475569; background:#f8fafc;">Ofertas</th>
                                    <th style="color:#475569; background:#f8fafc;">Comisión</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topAdminStores as $row): ?>
                                <tr>
                                    <td style="background:#fff; color:#0f172a;"><?= htmlspecialchars($row['nombre_tienda']) ?></td>
                                    <td style="background:#fff; color:#0f172a;"><?= number_format((int)$row['ofertas'], 0, ',', '.') ?></td>
                                    <td style="background:#fff; color:#0f172a;">$<?= number_format((float)$row['comision_estimada'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="chart-mock" style="background: white; height: auto; min-height: 220px;">
                        <h4 style="text-align: center; color: #0f172a; margin-top: 0;">Recomendaciones estratégicas</h4>
                        <ul style="color:#0f172a; line-height:1.7; padding-left:1.2rem; margin-top:12px;">
                            <li>Impulsar categorías con mayor ticket promedio.</li>
                            <li>Destacar los proveedores mejor posicionados por comisión.</li>
                            <li>Optimizar promociones cruzadas para elevar el volumen total.</li>
                            <li>Monitorear el impacto del 5% sobre el ingreso bruto mensual.</li>
                        </ul>
                    </div>
                </div>
            </div>

<!-- Modal Registrar Tienda -->
<div id="storeModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeStoreModal()">&times;</span>
        <h2>Registrar Tienda</h2>
        <form id="addStoreForm" onsubmit="saveStore(event)">
            <div class="input-group">
                <label>Foto de la tienda (Opcional)</label>
                <input type="file" id="storePhoto" accept="image/*">
            </div>
            <div class="input-group">
                <label>Nombre de la empresa (Opcional)</label>
                <input type="text" id="storeName" placeholder="Ej: Ferretería ABC SAS">
            </div>
            <div class="input-group">
                <label>NIT (Opcional)</label>
                <input type="text" id="storeNit" placeholder="Ej: 900.123.456-7">
            </div>
            <div class="input-group">
                <label>Persona de primer contacto o gerente (Opcional)</label>
                <input type="text" id="storeContact" placeholder="Ej: Juan Pérez">
            </div>
            <div class="input-group">
                <label>Dirección (Opcional)</label>
                <input type="text" id="storeAddress" placeholder="Ej: Calle Falsa 123">
            </div>
            <div class="input-group">
                <label>Descripción (Opcional)</label>
                <textarea id="storeDesc" rows="3" placeholder="Descripción de tu negocio"></textarea>
            </div>
            <button type="submit" class="btn-main-search" style="width: 100%;">Crear Tienda</button>
        </form>
    </div>
</div>

<!-- Modal Añadir Producto -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeProductModal()">&times;</span>
        <h2>Añadir Producto</h2>
        <form id="addProductForm" onsubmit="saveProduct(event)">
            <div class="input-group">
                <label>Foto del producto</label>
                <input type="file" id="prodPhoto" accept="image/*">
            </div>
            <div class="input-group">
                <label>Nombre del producto</label>
                <input type="text" id="prodName" required>
            </div>
            <div class="input-group">
                <label>Descripción</label>
                <textarea id="prodDesc" rows="3" required></textarea>
            </div>
            <div class="input-group">
                <label>Precio por unidad o metro</label>
                <input type="number" id="prodPrice" required placeholder="Ej: 5000">
            </div>
            <div class="input-group">
                <label>Unidad de medida</label>
                <input type="text" id="prodUnit" required placeholder="Ej: Unidad, Metro, Kg">
            </div>
            <button type="submit" class="btn-main-search" style="width: 100%;">Guardar producto</button>
        </form>
    </div>
</div>

<script src="frontend/js/app.js"></script>
<script>
    const dbProducts = <?= json_encode($allProducts) ?>;

    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(initProfile, 100);
    });

    function initProfile() {
        if (!currentUser) {
            alert('Debes iniciar sesión para ver tu perfil.');
            window.location.href = 'index.php';
            return;
        }

        document.getElementById('userName').innerText = currentUser.nombre;
        document.getElementById('userInitial').innerText = currentUser.nombre.charAt(0).toUpperCase();
        document.getElementById('userRoleBadge').innerText = currentUser.tipo;
        
        document.getElementById('infoName').innerText = currentUser.nombre;
        document.getElementById('infoEmail').innerText = currentUser.email || 'correo@registrado.com';
        document.getElementById('infoRole').innerText = currentUser.tipo;

        // Ensure we only show allowed tabs regardless of role, as this is now the admin/unified dashboard.
        // We removed buyer/seller specific tabs from HTML.

        // Handle URL parameters for automatic actions
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');

        if (tab === 'estadisticas') {
            showTab('estadisticas');
        } else if (tab === 'establecimientos') {
            showTab('establecimientos');
        } else {
            showTab('info');
        }

        // Initialize charts if they exist
        setTimeout(() => {
            if(typeof initSellerChart === 'function') initSellerChart();
            if(typeof initAdminChart === 'function') initAdminChart();
        }, 500);
    }

    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.sidebar-item').forEach(el => el.classList.remove('active'));
        
        document.getElementById('tab-' + tabId).style.display = 'block';
        event.currentTarget.classList.add('active');
    }

    function initSellerChart() {
        // Chart 1: Bar
        const ctx1 = document.getElementById('chart1');
        if(ctx1) {
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: ['Cemento', 'Varilla', 'Harina', 'Arroz', 'Acido'],
                    datasets: [{
                        label: 'Ingresos por comision ($)',
                        data: [4500000, 3200000, 6100000, 2100000, 1500000],
                        backgroundColor: '#3b82f6'
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
        
        // Chart 2: Pie
        const ctx2 = document.getElementById('chart2');
        if(ctx2) {
            new Chart(ctx2, {
                type: 'pie',
                data: {
                    labels: ['Construccion', 'Alimentos', 'Quimicos', 'Resto'],
                    datasets: [{
                        data: [35, 25, 30, 10],
                        backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // Chart 3: Line
        const ctx3 = document.getElementById('chart3');
        if(ctx3) {
            new Chart(ctx3, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Comisiones generadas al 5% ($)',
                        data: [150000, 210000, 180000, 300000, 250000, 400000],
                        borderColor: '#8b5cf6',
                        tension: 0.3,
                        fill: true,
                        backgroundColor: 'rgba(139, 92, 246, 0.1)'
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // Chart 4: Scatter
        const ctx4 = document.getElementById('chart4');
        if(ctx4) {
            new Chart(ctx4, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Volumen vs Comisión Generada',
                        data: [
                            {x: 1000, y: 50}, {x: 2000, y: 100}, {x: 5000, y: 250}, 
                            {x: 8000, y: 400}, {x: 12000, y: 600}, {x: 15000, y: 750}
                        ],
                        backgroundColor: '#ec4899'
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: {
                        x: { title: { display: true, text: 'Volumen de ventas (miles)' } },
                        y: { title: { display: true, text: 'Comisión al 5% (Miles)' } }
                    }
                }
            });
        }
    }

    function updateSellerChart() {
        // No longer applicable as filters were removed from HTML per instructions
    }

    function initAdminChart() {
        const ctx = document.getElementById('adminIncomeChart');
        if(!ctx) return;
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Ingresos App ($)',
                    data: [15000000, 22000000, 18000000, 35000000, 31000000, 45230000],
                    borderColor: '#3b82f6',
                    tension: 0.3,
                    fill: true,
                    backgroundColor: 'rgba(59, 130, 246, 0.1)'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    function filterAdminTiendas() {
        const nameInput = document.getElementById("searchAdminTiendas").value.toLowerCase().trim();
        const dateInput = document.getElementById("searchAdminDate").value.trim();
        const rows = document.querySelectorAll("#adminTiendasTable tbody tr");

        rows.forEach(row => {
            const name = (row.getAttribute("data-name") || "").toLowerCase();
            const rowDate = row.getAttribute("data-date") || "";
            const matchesName = !nameInput || name.includes(nameInput);
            const matchesDate = !dateInput || rowDate === dateInput;
            row.style.display = (matchesName && matchesDate) ? "" : "none";
        });
    }
</script>
</body>
</html>
