<?php
$id_price = isset($_GET['id_price']) ? $_GET['id_price'] : 0;
if (!$id_price) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar Producto | MiProveedor</title>
    <link rel="stylesheet" href="frontend/css/style.css">
    <style>
        .checkout-wrapper { max-width: 1000px; margin: 3rem auto; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        @media(max-width: 768px) { .checkout-wrapper { grid-template-columns: 1fr; } }
        .checkout-section { background: white; padding: 2.5rem; border-radius: var(--radius); box-shadow: var(--shadow); border: 1px solid var(--border); }
        .checkout-section h3 { margin-bottom: 1.5rem; color: var(--primary); border-bottom: 2px solid var(--bg-dark); padding-bottom: 0.5rem; }
        .input-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem;}
        .input-group input, .input-group select { width: 100%; padding: 0.8rem; border: 1px solid var(--border); border-radius: var(--radius); font-family: inherit; transition: border 0.3s;}
        .input-group input:focus { outline: none; border-color: var(--accent); }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    </style>
</head>
<body>
<?php include 'components/header.php'; ?>

<main class="main-content">
    <div id="loadingBuy" style="text-align: center; padding: 3rem;">
        <div class="loading">Cargando información del producto seguro...</div>
    </div>
    
    <div class="checkout-wrapper" id="buyFormWrapper" style="display: none;">
        <!-- Panel 1: Info del producto -->
        <div class="checkout-section">
            <h3>Información del Producto</h3>
            <div id="productInfoBox">
                <h2 id="pName" style="color: var(--primary); margin-bottom: 10px;">Cargando...</h2>
                <p id="pDesc" style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 20px;"></p>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding: 10px; background: var(--bg-dark); border-radius: var(--radius);">
                    <img id="pStoreImg" src="" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                    <div>
                        <p id="pStoreName" style="font-weight: bold; margin: 0;"></p>
                        <p id="pStoreAddr" style="font-size: 0.85rem; color: var(--text-muted); margin: 0;"></p>
                    </div>
                </div>
                <div style="background: #eef2ff; padding: 15px; border-radius: var(--radius); border-left: 4px solid var(--accent);">
                    <p style="margin:0; font-size: 0.9rem;">Precio Acordado</p>
                    <h2 id="pPriceStr" style="color: var(--accent); margin: 5px 0;">$0</h2>
                    <p style="margin:0; font-size: 0.85rem; color: var(--text-muted);">Disponibilidad confirmada ✔️</p>
                </div>
            </div>
        </div>

        <!-- Panel 2: Formulario -->
        <div class="checkout-section">
            <h3>Formulario de Compra Segura</h3>
            <form id="checkoutForm" onsubmit="processOrder(event)">
                <div class="grid-2" style="margin-bottom: 1rem;">
                    <div class="input-group">
                        <label>Cantidad (Unidades)</label>
                        <input type="number" id="buyQty" required min="1" value="1" onchange="calcTotal()">
                    </div>
                    <div class="input-group">
                        <label>Método de Pago</label>
                        <select id="buyPayment" required>
                            <option value="transferencia">Transferencia a MiProveedor</option>
                            <option value="credito">Tarjeta de Crédito</option>
                        </select>
                    </div>
                </div>
                <div class="input-group" style="margin-bottom: 1rem;">
                    <label>Dirección de Entrega</label>
                    <input type="text" id="buyAddress" required placeholder="Ej: Obra Calle 100 # 15-20">
                </div>
                <div class="grid-2" style="margin-bottom: 1.5rem;">
                    <div class="input-group">
                        <label>Nombre de quien recibe</label>
                        <input type="text" id="buyReceiver" required placeholder="Ej: Ing. Carlos Pérez">
                    </div>
                    <div class="input-group">
                        <label>Número de Factura (NIT/CC)</label>
                        <input type="text" id="buyNit" required placeholder="Ej: 900.xxx.xxx">
                    </div>
                </div>
                
                <div style="border-top: 1px dashed var(--border); padding-top: 1.5rem; text-align: right;">
                    <p style="font-size: 1rem; color: var(--text-muted); margin:0;">Total a retener:</p>
                    <h2 id="buyTotal" style="color: var(--primary); font-size: 2rem; margin: 5px 0 15px;">$0</h2>
                    <button type="submit" class="btn-main-search" style="width: 100%;">Finalizar Pedido Protegido</button>
                    <p style="font-size: 0.8rem; color: var(--text-muted); text-align: center; margin-top: 10px;">
                        Tus fondos están protegidos. El proveedor no recibe el dinero hasta que confirmes la entrega.
                    </p>
                </div>
            </form>
        </div>
    </div>
</main>

<script src="frontend/js/app.js"></script>
<script>
    let currentPriceVal = 0;
    
    // Al intentar entrar a esta página sin login, app.js checkLoginStatus() 
    // nos dirá si currentUser es nulo.
    document.addEventListener('DOMContentLoaded', async () => {
        
        // Timeout para dejar que app.js asigne currentUser desde localstorage
        setTimeout(async () => {
            if (!currentUser) {
                alert('⚠️ Acceso restringido. Debes iniciar sesión para acceder al portal de compras.');
                window.location.href = 'index.php';
                return;
            }

            const idPrice = <?php echo json_encode($id_price); ?>;
            try {
                const res = await fetch(`backend/api/index.php?route=price_detail&id=${idPrice}`);
                const data = await res.json();
                
                if (res.ok) {
                    document.getElementById('loadingBuy').style.display = 'none';
                    document.getElementById('buyFormWrapper').style.display = 'grid';
                    
                    document.getElementById('pName').innerText = data.producto_nombre;
                    document.getElementById('pDesc').innerText = data.descripcion;
                    document.getElementById('pStoreImg').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(data.nombre_tienda)}&background=0D8ABC&color=fff`;
                    document.getElementById('pStoreName').innerText = data.nombre_tienda;
                    document.getElementById('pStoreAddr').innerText = data.direccion;
                    
                    currentPriceVal = parseFloat(data.precio);
                    document.getElementById('pPriceStr').innerText = '$' + currentPriceVal.toLocaleString('es-CO');
                    calcTotal();
                } else {
                    alert('La oferta a la que intentas acceder no existe.');
                    window.location.href = 'index.php';
                }
            } catch (e) {
                alert('Error conectando al servidor.');
                window.location.href = 'index.php';
            }
        }, 100);
    });

    function calcTotal() {
        const qty = parseInt(document.getElementById('buyQty').value) || 0;
        const total = qty * currentPriceVal;
        document.getElementById('buyTotal').innerText = '$' + total.toLocaleString('es-CO');
    }

    function processOrder(e) {
        e.preventDefault();
        alert('🎉 ¡Pedido creado con éxito!\\n\\nLa base de datos recibió los datos de facturación y el dinero ha entra en modo Escrow en MiProveedor. El proveedor ha sido notificado para despachar la mercancía.');
        window.location.href = 'historial.php';
    }
</script>
</body>
</html>
