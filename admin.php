<!DOCTYPE html>
<?php
    $dbAdmin = new PDO("mysql:host=127.0.0.1;port=3306;dbname=miproveedor;charset=utf8mb4", "root", "");
    $stmtTiendas = $dbAdmin->query("
        SELECT s.nombre_tienda, s.direccion, COUNT(sp.id_product) as total_productos
        FROM suppliers s
        LEFT JOIN supplier_prices sp ON s.id_supplier = sp.id_supplier
        GROUP BY s.id_supplier
        ORDER BY total_productos DESC
    ");
    $tiendasAdmin = $stmtTiendas->fetchAll(PDO::FETCH_ASSOC);
?>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador | MiProveedor</title>
    <link rel="stylesheet" href="frontend/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 80vh; max-width: 1200px; margin: 2rem auto; gap: 2rem; padding: 0 1rem; }
        .sidebar { width: 250px; background: white; border-radius: var(--radius); box-shadow: var(--shadow); border: 1px solid var(--border); overflow: hidden; align-self: start; }
        .sidebar-item { display: block; padding: 15px 20px; border-bottom: 1px solid var(--border); cursor: pointer; color: var(--text-main); font-weight: 600; text-decoration: none; transition: background 0.2s; }
        .sidebar-item:hover { background: #f8fafc; }
        .sidebar-item.active { background: #e0f2fe; color: #0284c7; border-left: 4px solid #0284c7; }
        .content-area { flex: 1; background: white; border-radius: var(--radius); box-shadow: var(--shadow); border: 1px solid var(--border); padding: 2rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border); }
        th { background: #f8fafc; color: var(--text-muted); }
        .chart-mock { height: 300px; background: linear-gradient(180deg, #e0f2fe 0%, #f8fafc 100%); border-radius: var(--radius); border: 1px dashed #cbd5e1; display: flex; align-items: flex-end; justify-content: space-around; padding: 20px; margin-top: 20px;}
        .bar { width: 40px; background: var(--primary); border-radius: 4px 4px 0 0; }
    </style>
</head>
<body>
<?php include 'components/header.php'; ?>

<main class="main-content">
    <div class="admin-layout" id="adminContainer" style="display: none;">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div style="padding: 20px; background: #0f172a; color: white; text-align: center;">
                <h3 style="margin: 0;">Panel Admin</h3>
            </div>
            <a class="sidebar-item active" onclick="showAdminTab('tiendas')">Tiendas registradas</a>
            <a class="sidebar-item" onclick="showAdminTab('recaudos')">Recaudos</a>
        </aside>

        <!-- Content Area -->
        <section class="content-area">
            <div id="tab-tiendas" class="admin-tab">
                <h2 style="margin-top: 0; border-bottom: 1px solid var(--border); padding-bottom: 15px;">Tiendas Registradas</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nombre de Tienda</th>
                            <th>Ubicación</th>
                            <th>Productos Registrados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tiendasAdmin as $tienda): ?>
                        <tr>
                            <td><?= htmlspecialchars($tienda['nombre_tienda']) ?></td>
                            <td><?= htmlspecialchars($tienda['direccion'] ?: 'No especificada') ?></td>
                            <td><?= $tienda['total_productos'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div id="tab-recaudos" class="admin-tab" style="display: none;">
                <h2 style="margin-top: 0; border-bottom: 1px solid var(--border); padding-bottom: 15px;">Reporte de Recaudos</h2>
                <div style="background: #f8fafc; padding: 20px; border-radius: var(--radius); border: 1px solid var(--border); display: inline-block;">
                    <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">Total Recaudado (Comisiones)</p>
                    <h3 style="margin: 5px 0 0 0; font-size: 2rem; color: var(--secondary);">$45,230,000 COP</h3>
                </div>
                
                <h3 style="margin-top: 30px;">Histórico Anual</h3>
                <div class="chart-mock">
                    <div class="bar" style="height: 30%;" title="Ene"></div>
                    <div class="bar" style="height: 45%;" title="Feb"></div>
                    <div class="bar" style="height: 60%;" title="Mar"></div>
                    <div class="bar" style="height: 50%;" title="Abr"></div>
                    <div class="bar" style="height: 80%;" title="May"></div>
                    <div class="bar" style="height: 100%;" title="Jun"></div>
                </div>
                <div style="display: flex; justify-content: space-around; color: var(--text-muted); font-size: 0.8rem; margin-top: 10px;">
                    <span>Ene</span><span>Feb</span><span>Mar</span><span>Abr</span><span>May</span><span>Jun</span>
                </div>
            </div>
        </section>
    </div>
</main>

<script src="frontend/js/app.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(initAdmin, 100);
    });

    function initAdmin() {
        if (!currentUser) {
            alert('Acceso denegado. Debes iniciar sesión.');
            window.location.href = 'index.php';
            return;
        }

        if (currentUser.tipo !== 'admin') {
            alert('Acceso restringido. Solo administradores pueden ver esta página.');
            window.location.href = 'index.php';
            return;
        }

        document.getElementById('adminContainer').style.display = 'flex';
    }

    function showAdminTab(tabId) {
        document.querySelectorAll('.admin-tab').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.sidebar-item').forEach(el => el.classList.remove('active'));
        
        document.getElementById('tab-' + tabId).style.display = 'block';
        event.currentTarget.classList.add('active');
    }
</script>
</body>
</html>
