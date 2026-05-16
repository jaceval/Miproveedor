<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | MiProveedor</title>
    <link rel="stylesheet" href="frontend/css/style.css">
    <style>
        .checkout-container {
            max-width: 800px;
            margin: 3rem auto;
            background: white;
            padding: 2.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }
        .form-section { margin-bottom: 2rem; }
        .form-section h3 { margin-bottom: 1rem; color: var(--primary); border-bottom: 2px solid var(--bg-dark); padding-bottom: 0.5rem; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .input-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem;}
        .input-group input, .input-group select { width: 100%; padding: 0.8rem; border: 1px solid var(--border); border-radius: var(--radius); font-family: inherit;}
        .summary-card { background: var(--bg-dark); padding: 1.5rem; border-radius: var(--radius); text-align: center; margin-bottom: 2rem; border: 1px dashed var(--accent);}
    </style>
</head>
<body>
<header>
    <div class="logo">Mi<span>Proveedor</span></div>
    <nav>
        <li style="list-style: none;">
            <a href="index.php" style="color: white; text-decoration: none; margin-left: 20px;">Volver al Buscador</a>
        </li>
    </nav>
</header>

<main class="main-content">
    <div class="checkout-container">
        <h2 style="text-align: center; margin-bottom: 1rem;">Finalizar Compra Logística (Escrow)</h2>
        <div class="summary-card">
            <h3>💳 Producto Seleccionado</h3>
            <p id="productName" style="color: var(--text-muted); font-size: 1.1rem;">Cargando...</p>
            <p id="productPrice" style="font-size: 1.4rem; font-weight: bold; color: var(--secondary); margin-top: 10px;">$ 0.00 COP / unidad</p>
        </div>

        <form id="checkoutForm" onsubmit="processCheckout(event)">
            <div class="form-section">
                <h3>Información de Entrega</h3>
                <div class="grid-2">
                    <div class="input-group">
                        <label>Nombre de quien recibe</label>
                        <input type="text" id="recieverName" required placeholder="Ej: Juan Pérez - Almacén">
                    </div>
                    <div class="input-group">
                        <label>Lugar exacto de entrega</label>
                        <input type="text" id="deliveryAddress" required placeholder="Dirección de la obra o bodega">
                    </div>
                    <div class="input-group">
                        <label>Cantidad (Unidades/Bultos)</label>
                        <input type="number" id="purchaseQty" required min="1" value="1" onchange="updateTotal()">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Facturación y Pagos</h3>
                <div class="grid-2">
                    <div class="input-group">
                        <label>Método de Pago</label>
                        <select id="paymentMethod" required>
                            <option value="transferencia">Transferencia Bancaria (PSE)</option>
                            <option value="tarjeta">Tarjeta de Crédito Empresarial</option>
                            <option value="credito">Crédito a 30 días (Sujeto a estudio)</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Número de NIT para Factura</label>
                        <input type="text" id="invoiceNim" required placeholder="Ej: 900.xxx.xxx-x">
                    </div>
                </div>
            </div>

            <div style="background: #f8fafc; padding: 1.5rem; border-radius: var(--radius); text-align: right; border-top: 2px solid var(--border);">
                <p style="font-size: 1rem; color: var(--text-muted);">Total a Pagar Seguramente:</p>
                <h2 id="totalAmount" style="color: var(--primary); font-size: 2rem;">$ 0.00</h2>
                <button type="submit" class="btn-main-search" style="margin-top: 1rem;">Confirmar y Procesar Pago</button>
            </div>
        </form>
    </div>
</main>

<script>
    let unitPrice = 0;
    
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const name = urlParams.get('nombre');
        const price = urlParams.get('precio');
        
        if(!name || !price) {
            alert('Datos de compra inválidos.');
            window.location.href = 'index.php';
            return;
        }

        document.getElementById('productName').innerText = name;
        unitPrice = parseFloat(price);
        document.getElementById('productPrice').innerText = '$' + unitPrice.toLocaleString('es-CO') + ' / unidad';
        updateTotal();
    });

    function updateTotal() {
        const qty = document.getElementById('purchaseQty').value || 1;
        const total = qty * unitPrice;
        document.getElementById('totalAmount').innerText = '$' + total.toLocaleString('es-CO');
    }

    function processCheckout(e) {
        e.preventDefault();
        alert('Pago procesado correctamente. \n\nEl dinero ha sido retenido por MiProveedor. El proveedor ha sido notificado para despachar la mercancía. \n\nRecibirás tu factura por correo.');
        window.location.href = 'index.php';
    }
</script>
</body>
</html>
