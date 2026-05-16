// compra.js - Lógica específica para el portal de compras

function getGeolocation(e) {
    e.preventDefault();
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                document.getElementById('locInput').value = `${position.coords.latitude},${position.coords.longitude}`;
            },
            () => alert("No se pudo obtener la ubicación. Verifica los permisos de tu navegador.")
        );
    } else {
        alert("La geolocalización no es soportada por tu navegador.");
    }
}

async function performAdvancedSearch() {
    const query = document.getElementById('searchInput').value;
    if(!query) {
        alert('Por favor selecciona un producto primero desde la pestaña Explorar.');
        return;
    }

    const loc = document.getElementById('locInput').value.split(',');
    const lat = loc[0] ? loc[0].trim() : '4.6097'; // Bogota Default
    const lng = loc[1] ? loc[1].trim() : '-74.0817'; // Bogota Default
    const price = document.getElementById('priceInput').value || 0;
    const radius = document.getElementById('radiusInput').value || 50;

    const grid = document.getElementById('productsGrid');
    const countBadge = document.getElementById('productCount');
    
    grid.innerHTML = '<div class="loading">Escanenado inventarios y rutas logísticas...</div>';

    try {
        const response = await fetch(`backend/api/index.php?route=search&q=${encodeURIComponent(query)}&lat=${lat}&lng=${lng}&radius=${radius}&usual_price=${price}`);
        const data = await response.json();

        if (response.status === 200) {
            renderSearchResults(data);
            countBadge.innerText = `${data.length} opciones encontradas cerca de ti`;
        } else {
            grid.innerHTML = `<p style="grid-column: 1/-1; text-align: center; color: var(--text-muted); font-size: 1.1rem; padding: 2rem;">${data.message}</p>`;
            countBadge.innerText = `0 opciones encontradas`;
        }
    } catch (error) {
        console.error('Search error:', error);
        grid.innerHTML = '<p>Error al realizar la búsqueda avanzada.</p>';
    }
}

function renderSearchResults(products) {
    const grid = document.getElementById('productsGrid');
    grid.innerHTML = '';

    products.forEach((p, index) => {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.style.display = 'flex';
        card.style.flexDirection = 'column';
        
        let ribbon = index === 0 ? '<div style="background:var(--secondary);color:white;text-align:center;font-size:0.85rem;padding:6px;font-weight:bold;">🏆 Mejor Opción (Precio/Distancia)</div>' : '';
        
        let ahorroTag = '';
        if (p.ahorro_calculado > 0) {
            ahorroTag = `<div style="color:var(--secondary); font-weight:bold; font-size:0.95rem; margin-top:5px; padding: 5px; background: #d1fae5; border-radius: 5px; display: inline-block;">💰 Ahorras: $${parseFloat(p.ahorro_calculado).toLocaleString('es-CO')} unitario</div>`;
        }

        card.innerHTML = `
            ${ribbon}
            <div class="product-info" style="flex: 1;">
                <span class="badge" style="background:#e0f2fe; color:#0284c7;">📍 a ${p.distancia_km} km</span>
                <span class="badge">${p.unidad}</span>
                <h3 style="margin-top:10px; margin-bottom:5px;">${p.nombre}</h3>
                
                <div style="display: flex; align-items: center; gap: 10px; margin: 15px 0; padding: 8px; background: var(--bg-dark); border-radius: var(--radius); border: 1px solid var(--border);">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(p.tienda)}&background=0D8ABC&color=fff" alt="${p.tienda}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                    <div>
                        <p style="font-weight: bold; font-size: 0.9rem; margin: 0; color: var(--primary);">${p.tienda}</p>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">${p.direccion_tienda}</p>
                    </div>
                </div>

                <div class="price-tag">$${parseFloat(p.precio).toLocaleString('es-CO')}</div>
                ${ahorroTag}
            </div>
            <div style="display:flex; justify-content:space-between; padding: 0.8rem; gap:10px; background:#f8fafc; border-top: 1px solid var(--border);">
                <button onclick="viewProfile(${p.id_supplier})" style="flex:1; padding:10px; background:white; border:1px solid var(--primary); color:var(--primary); font-weight:bold; border-radius:var(--radius); cursor:pointer; transition: all 0.2s;">🏠 Ver Perfil</button>
                <button onclick="executePurchase(${p.id_price}, '${p.nombre}', ${p.precio})" style="flex:1; padding:10px; background:var(--primary); color:white; border:none; font-weight:bold; border-radius:var(--radius); cursor:pointer; transition: all 0.2s;">🛒 Comprar</button>
            </div>
        `;
        grid.appendChild(card);
    });
}

function viewProfile(id) {
    alert('Redirigiendo al perfil público de la Tienda (ID: ' + id + ').');
}

function executePurchase(id_price, nombre, precio) {
    // Check if user is logged in using the global variable from app.js
    if (!currentUser) {
        alert('Debes iniciar sesión para poder confirmar la compra.');
        openLoginModal();
        return;
    }
    
    const qty = prompt(`¿Cuántas unidades de "${nombre}" deseas comprar?\nPrecio Unitario: $${parseFloat(precio).toLocaleString('es-CO')}`);
    
    if (qty !== null && qty > 0) {
        const total = (qty * precio);
        const confirmMsg = `
RESUMEN DE ORDEN SEGURO
-----------------------
Producto: ${nombre}
Cantidad: ${qty}
Total a Pagar: $${total.toLocaleString('es-CO')}

¿Confirmas el depósito de estos fondos a la cuenta segura de MiProveedor?
El proveedor será notificado para despachar y recibirá el pago ÚNICAMENTE cuando confirmes la entrega.`;
        
        if(confirm(confirmMsg)) {
            alert('¡Compra Procesada con Éxito!\n\nSe ha retenido el dinero en formato Escrow. Ve a "Tus Pedidos" para hacer seguimiento.');
            // Aquí en un futuro se haría un POST request al backend para crear el pedido.
        }
    }
}
