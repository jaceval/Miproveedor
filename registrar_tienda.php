<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Tienda | MiProveedor</title>
    <link rel="stylesheet" href="frontend/css/style.css">
    <style>
        .checkout-container {
            max-width: 600px;
            margin: 3rem auto;
            background: white;
            padding: 2.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }
        .input-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem;}
        .input-group input, .input-group textarea, .input-group select { width: 100%; padding: 0.8rem; border: 1px solid var(--border); border-radius: var(--radius); font-family: inherit;}
    </style>
</head>
<body>
<?php include 'components/header.php'; ?>

<main class="main-content">
    <div class="checkout-container">
        <h2>Abre tu Tienda en MiProveedor</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Digitaliza tu inventario y alcanza a miles de compradores.</p>
        
        <form id="storeForm" onsubmit="createStore(event)">
            <div class="input-group" style="margin-bottom: 1.5rem;">
                <label>Nombre de la Empresa o Tienda</label>
                <input type="text" required placeholder="Ej: Ferretería ABC SAS">
            </div>
            
            <div class="input-group" style="margin-bottom: 1.5rem;">
                <label>Breve Descripción</label>
                <textarea required placeholder="¿Qué vendes y por qué eres el mejor?" rows="3"></textarea>
            </div>

            <div class="input-group" style="margin-bottom: 1.5rem;">
                <label>Dirección Principal de Despacho</label>
                <input type="text" required placeholder="Ej: Av Siempre Viva 123">
            </div>

            <div class="input-group" style="margin-bottom: 1.5rem;">
                <label>¿Cuentas con logística/transporte propio?</label>
                <select required>
                    <option value="1">Sí, tengo camiones/vehículos propios</option>
                    <option value="0">No, necesito que MiProveedor asigne un transportista</option>
                </select>
            </div>

            <button type="submit" class="btn-main-search">Registrar mi Negocio</button>
        </form>
    </div>
</main>

<script>
    function createStore(e) {
        e.preventDefault();
        alert('¡Tienda registrada con éxito!\n\nPronto implementaremos el Dashboard de vendedor donde podrás subir de forma masiva (Excel) todos tus precios.');
        window.location.href = 'index.php';
    }
</script>
</body>
</html>
