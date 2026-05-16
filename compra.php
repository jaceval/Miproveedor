<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar | MiProveedor</title>
    <link rel="stylesheet" href="frontend/css/style.css">
</head>
<body>

<header>
    <div class="logo">Mi<span>Proveedor</span></div>
    <nav>
        <li style="list-style: none;">
            <a href="index.php" style="color: white; text-decoration: none; margin-left: 20px;">Explorar</a>
            <a href="#" onclick="handleVender(event)" style="color: white; text-decoration: none; margin-left: 20px;">Vender</a>
            <a href="#" id="authBtn" onclick="openLoginModal(event)" style="color: var(--secondary); text-decoration: none; margin-left: 20px; font-weight: 600;">Iniciar Sesión</a>
        </li>
    </nav>
</header>

<section class="search-container" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
    <h2>Optimiza tu Compra</h2>
    <p>Ingresa tus condiciones para encontrar al proveedor ideal.</p>
    
    <div class="advanced-search-box" style="margin-top: 1rem;">
        <div class="search-row">
            <input type="text" id="searchInput" placeholder="Producto a comprar" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" readonly style="background: #f1f5f9;">
        </div>
        <div class="search-row multi-inputs">
            <div class="input-group">
                <label>Punto de Entrega (Lat, Lng) <a href="#" onclick="getGeolocation(event)" style="color:var(--secondary); font-size:0.8rem; text-decoration:none;">Detectar 📍</a></label>
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
        <button onclick="performAdvancedSearch()" class="btn-main-search">Escanear el Mercado</button>
    </div>
</section>

<!-- Auth Modal (Reutilizado) -->
<div id="authModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAuthModal()">&times;</span>
        <h2 id="modalTitle">Iniciar Sesión</h2>
        <form id="authForm" onsubmit="handleAuth(event)">
            <input type="hidden" id="authMode" value="login">
            <div id="registerFields" style="display:none;">
                <div class="input-group">
                    <label>Nombre Completo</label>
                    <input type="text" id="nameInput" placeholder="Tu nombre">
                </div>
                <div class="input-group">
                    <label>Teléfono</label>
                    <input type="text" id="phoneInput" placeholder="Tu teléfono">
                </div>
                <div class="input-group">
                    <label>Tipo de Usuario</label>
                    <select id="typeInput" style="width:100%; padding: 1rem; border-radius: var(--radius); border: 1px solid var(--border);">
                        <option value="comprador">Comprador</option>
                        <option value="proveedor">Proveedor / Vendedor</option>
                    </select>
                </div>
            </div>
            <div class="input-group" style="margin-top: 10px;">
                <label>Correo Electrónico</label>
                <input type="email" id="emailInput" placeholder="correo@ejemplo.com" required style="width:100%; padding: 1rem; border-radius: var(--radius); border: 1px solid var(--border);">
            </div>
            <div class="input-group" style="margin-top: 10px;">
                <label>Contraseña</label>
                <input type="password" id="passwordInput" placeholder="********" required style="width:100%; padding: 1rem; border-radius: var(--radius); border: 1px solid var(--border);">
            </div>
            <button type="submit" class="btn-main-search" style="margin-top: 20px;">Continuar</button>
        </form>
        <p style="text-align: center; margin-top: 15px; font-size: 0.9rem;">
            <a href="#" id="toggleAuthMode" onclick="toggleAuthMode(event)" style="color: var(--accent); text-decoration: none;">Crear una cuenta nueva</a>
        </p>
    </div>
</div>

<main class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Proveedores Disponibles</h2>
        <span id="productCount" class="badge">Aún no has buscado</span>
    </div>

    <div id="productsGrid" class="grid-products">
        <p style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 2rem;">
            Ingresa tu ubicación y presiona "Escanear el Mercado" para encontrar ofertas.
        </p>
    </div>
</main>

<!-- El archivo base con lógica de login -->
<script src="frontend/js/app.js"></script>
<!-- Lógica específica de la página de compra -->
<script src="frontend/js/compra.js"></script>
</body>
</html>
