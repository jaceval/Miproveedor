<?php
// components/header.php
?>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&display=swap" rel="stylesheet">
<header>
    <!-- Navegación Izquierda -->
    <nav style="flex: 1; display: flex; justify-content: flex-start;">
        <ul style="list-style: none; display: flex; align-items: center; gap: 20px; padding: 0; margin: 0;">
            <li><a href="index.php">Inicio</a></li>
            <li><a href="explorar.php">Explorar</a></li>
            <li><a href="#" onclick="handleVender(event)">Vender</a></li>
        </ul>
    </nav>

    <!-- Centro: Logo y Nombre -->
    <div class="logo-container" style="flex: 1; display: flex; justify-content: center;">
        <a href="index.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
            <img src="fotos/logopng.png" alt="MiProveedor Logo" style="height: 40px; vertical-align: middle; object-fit: contain;">
            <span class="logo-text" style="color: white; font-family: 'Oswald', sans-serif; font-size: 24px; font-weight: 700;">MiProveedor</span>
        </a>
    </div>

    <!-- Navegación Derecha (Perfil/Ingresar) -->
    <nav style="flex: 1; display: flex; justify-content: flex-end;">
        <ul style="list-style: none; display: flex; align-items: center; gap: 20px; padding: 0; margin: 0;">
            <li id="navGuest">
                <a href="#" onclick="openLoginModal(event)" style="color: #10b981; font-weight: 600;">Ingresar</a>
            </li>
            
            <li id="navAuth" style="display: none; align-items: center; gap: 20px;">
                <div style="position: relative;">
                    <button onclick="document.getElementById('notifDropdown').style.display = document.getElementById('notifDropdown').style.display === 'none' ? 'block' : 'none';" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer;">
                        🔔
                    </button>
                    <div id="notifDropdown" class="dropdown-content" style="display: none; position: absolute; right: 0; background-color: white; min-width: 200px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); z-index: 1000; border-radius: 8px; overflow: hidden; margin-top: 12px; border: 1px solid var(--border);">
                        <div style="padding: 12px 16px; border-bottom: 1px solid var(--border); font-size: 14px; color: black;">Producto comprado</div>
                        <div style="padding: 12px 16px; border-bottom: 1px solid var(--border); font-size: 14px; color: black;">Pedido en camino</div>
                        <div style="padding: 12px 16px; font-size: 14px; color: black;">Pedido 4829 entregado</div>
                    </div>
                </div>
                <div class="dropdown" style="position: relative;">
                    <button onclick="toggleDropdown()" style="background: none; border: none; color: white; font-weight: 600; font-size: 15px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        👤 <span id="profileName">Usuario</span> <span style="font-size: 10px;">▼</span>
                    </button>
                    <div id="profileDropdown" class="dropdown-content" style="display: none; position: absolute; right: 0; background-color: white; min-width: 180px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); z-index: 1000; border-radius: 8px; overflow: hidden; margin-top: 12px; border: 1px solid var(--border);">
                        <a href="mi-perfil.php" style="color: black; padding: 12px 16px; text-decoration: none; display: block; border-bottom: 1px solid var(--border); font-size: 14px;">Mi perfil</a>
                        <a href="mi-perfil.php?tab=historial" style="color: black; padding: 12px 16px; text-decoration: none; display: block; border-bottom: 1px solid var(--border); font-size: 14px;">Historial de compras</a>
                        <a href="mi-perfil.php?tab=tienda" id="dropStats" style="color: black; padding: 12px 16px; text-decoration: none; display: none; border-bottom: 1px solid var(--border); font-size: 14px;">Mi Tienda</a>
                        <a href="mi-perfil.php?tab=estadisticas" id="dropStats2" style="color: black; padding: 12px 16px; text-decoration: none; display: none; border-bottom: 1px solid var(--border); font-size: 14px;">Estadísticas</a>
                        <a href="#" style="color: black; padding: 12px 16px; text-decoration: none; display: block; border-bottom: 1px solid var(--border); font-size: 14px;">Soporte</a>
                        <a href="#" onclick="logout(event)" style="color: #ef4444; padding: 12px 16px; text-decoration: none; display: block; font-weight: 600; font-size: 14px;">Cerrar sesión</a>
                    </div>
                </div>
            </li>
        </ul>
    </nav>
</header>

<!-- Auth Modal Integrado -->
<div id="authModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeAuthModal()">&times;</span>
        <h2 id="modalTitle">Acceso</h2>
        <form id="authForm" onsubmit="handleAuth(event)">
            <input type="hidden" id="authMode" value="login">
            <div id="registerFields" style="display:none;">
                <div class="input-group">
                    <label>Nombre Completo</label>
                    <input type="text" id="nameInput" placeholder="Tu nombre" style="width:100%; padding: 14px 16px; border-radius: 8px; border: 1px solid var(--border); font-family: 'Inter', sans-serif;">
                </div>
                <div class="input-group" style="margin-top: 16px;">
                    <label>Teléfono</label>
                    <input type="text" id="phoneInput" placeholder="Tu teléfono" style="width:100%; padding: 14px 16px; border-radius: 8px; border: 1px solid var(--border); font-family: 'Inter', sans-serif;">
                </div>
                <div class="input-group" style="margin-top: 16px;">
                    <label>Tipo de Cuenta</label>
                    <select id="typeInput" style="width:100%; padding: 14px 16px; border-radius: 8px; border: 1px solid var(--border); font-family: 'Inter', sans-serif; background: #f8fafc;">
                        <option value="comprador">Comprador</option>
                        <option value="proveedor">Tienda / Proveedor</option>
                    </select>
                </div>
            </div>
            <div class="input-group" style="margin-top: 16px;">
                <label>Correo Electrónico</label>
                <input type="email" id="emailInput" placeholder="correo@ejemplo.com" required style="width:100%; padding: 14px 16px; border-radius: 8px; border: 1px solid var(--border); font-family: 'Inter', sans-serif;">
            </div>
            <div class="input-group" style="margin-top: 16px;">
                <label>Contraseña</label>
                <input type="password" id="passwordInput" placeholder="********" required style="width:100%; padding: 14px 16px; border-radius: 8px; border: 1px solid var(--border); font-family: 'Inter', sans-serif;">
            </div>
            <button type="submit" class="btn-main-search" id="authSubmitBtn" style="margin-top: 24px; border-radius: 8px;">
                <span id="authBtnTextNormal">Continuar</span>
                <span id="authLoader" style="display:none;">⏳ Procesando...</span>
            </button>
            <div id="authErrorMsg" style="color: #ef4444; margin-top: 12px; text-align: center; font-size: 14px;"></div>
        </form>
        <p style="text-align: center; margin-top: 20px; font-size: 14px;">
            <a href="#" id="toggleAuthMode" onclick="toggleAuthMode(event)" style="color: var(--primary); text-decoration: none; font-weight: 500;">Crear una cuenta nueva</a>
        </p>
    </div>
</div>
