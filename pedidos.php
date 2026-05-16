<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar Pedido | MiProveedor</title>
    <link rel="stylesheet" href="frontend/css/style.css">
    <style>
        .checkout-container { max-width: 700px; margin: 3rem auto; background: white; padding: 2.5rem; border-radius: var(--radius); box-shadow: var(--shadow); border: 1px solid var(--border); }
        .input-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.95rem; color: var(--text-main); }
        .input-group input, .input-group textarea, .input-group select { width: 100%; padding: 0.8rem; border: 1px solid var(--border); border-radius: var(--radius); font-family: inherit; margin-bottom: 1.5rem; }
        .input-group input:focus, .input-group textarea:focus { border-color: var(--primary); outline: none; }
        .order-summary { background: #f8fafc; padding: 15px; border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 2rem; }
    </style>
</head>
<body>
<?php include 'components/header.php'; ?>

<main class="main-content">
    <div class="checkout-container">
        <h2 style="margin-top: 0; color: var(--primary); border-bottom: 2px solid var(--border); padding-bottom: 10px;">Finalizar tu Pedido</h2>
        
        <div class="order-summary">
            <h3 style="margin-top: 0; font-size: 1.1rem;">Resumen de la compra</h3>
            <p style="margin: 5px 0;">Estás a punto de confirmar un pedido importante. Completa los datos logísticos y financieros a continuación.</p>
        </div>

        <form id="orderForm" onsubmit="submitOrder(event)">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="input-group">
                    <label>Cantidad solicitada</label>
                    <input type="number" id="orderQty" required placeholder="Ej: 50" min="1">
                </div>
                <div class="input-group">
                    <label>NIT de tu empresa</label>
                    <input type="text" id="orderNit" required placeholder="Ej: 900.123.456-7">
                </div>
            </div>

            <div class="input-group">
                <label>Nombre de contacto</label>
                <input type="text" id="orderContact" required placeholder="Persona que recibe o autoriza">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="input-group">
                    <label>Teléfono</label>
                    <input type="tel" id="orderPhone" required placeholder="Ej: 300 123 4567">
                </div>
                <div class="input-group">
                    <label>Correo Electrónico</label>
                    <input type="email" id="orderEmail" required placeholder="Para notificaciones">
                </div>
            </div>

            <div class="input-group">
                <label>Dirección de entrega</label>
                <input type="text" id="orderAddress" required placeholder="Dirección completa y ciudad">
            </div>

            <div class="input-group">
                <label>Información Bancaria (Para pago o garantía)</label>
                <input type="text" id="orderBank" required placeholder="Banco, Tipo de cuenta, Número">
            </div>

            <button type="submit" class="btn-main-search" style="width: 100%; font-size: 1.1rem; padding: 15px;">Confirmar Pedido</button>
        </form>
    </div>
</main>

<script src="frontend/js/app.js"></script>
<script>
    function submitOrder(e) {
        e.preventDefault();
        
        if (!currentUser) {
            alert('Por favor, inicia sesión para poder registrar tu pedido.');
            openLoginModal();
            return;
        }

        // Mock saving the order
        alert('¡Pedido procesado exitosamente! Nos pondremos en contacto contigo pronto.');
        window.location.href = 'explorar.php';
    }
</script>
</body>
</html>
