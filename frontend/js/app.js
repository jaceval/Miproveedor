// app.js - Client side logic for MiProveedor
let currentUser = null;

document.addEventListener('DOMContentLoaded', () => {
    checkLoginStatus();
});

function checkLoginStatus() {
    const userStr = localStorage.getItem('miproveedor_user');
    const navGuest = document.getElementById('navGuest');
    const navAuth = document.getElementById('navAuth');
    
    if (userStr) {
        currentUser = JSON.parse(userStr);
        
        if(navGuest) navGuest.style.display = 'none';
        if(navAuth) navAuth.style.display = 'flex';
        
        const profileName = document.getElementById('profileName');
        if(profileName) profileName.innerText = currentUser.nombre;
        
        const dropStats = document.getElementById('dropStats');
        const dropStats2 = document.getElementById('dropStats2');
        if(dropStats) {
            dropStats.style.display = (currentUser.has_store) ? 'block' : 'none';
        }
        if(dropStats2) {
            dropStats2.style.display = (currentUser.has_store) ? 'block' : 'none';
        }
    } else {
        currentUser = null;
        if(navGuest) navGuest.style.display = 'block';
        if(navAuth) navAuth.style.display = 'none';
    }
}

function toggleDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    if (dropdown) {
        dropdown.style.display = (dropdown.style.display === 'none') ? 'block' : 'none';
    }
}

// Cerrar dropdown al hacer click fuera
window.onclick = function(event) {
    if (!event.target.closest('.dropdown')) {
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown && dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
        }
    }
}

function logout(e) {
    if(e) e.preventDefault();
    localStorage.removeItem('miproveedor_user');
    checkLoginStatus();
    window.location.href = 'http://localhost/miproveedor/index.php';
}

// ======================== MODAL LOGIC ========================
function openLoginModal(e) {
    if(e) e.preventDefault();
    document.getElementById('authModal').style.display = 'block';
    
    // Reset to login mode
    document.getElementById('authMode').value = 'login';
    document.getElementById('modalTitle').innerText = 'Acceso';
    document.getElementById('registerFields').style.display = 'none';
    document.getElementById('toggleAuthMode').innerText = 'Crear una cuenta nueva';
}

function closeAuthModal() {
    document.getElementById('authModal').style.display = 'none';
}

function toggleAuthMode(e) {
    if(e) e.preventDefault();
    const modeInput = document.getElementById('authMode');
    const title = document.getElementById('modalTitle');
    const fields = document.getElementById('registerFields');
    const toggleBtn = document.getElementById('toggleAuthMode');

    if (modeInput.value === 'login') {
        modeInput.value = 'register';
        title.innerText = 'Crear Cuenta';
        fields.style.display = 'block';
        toggleBtn.innerText = 'Ya tengo una cuenta';
        
        // Make register fields required
        document.getElementById('nameInput').required = true;
    } else {
        modeInput.value = 'login';
        title.innerText = 'Iniciar Sesión';
        fields.style.display = 'none';
        toggleBtn.innerText = 'Crear una cuenta nueva';
        
        // Remove requirements
        document.getElementById('nameInput').required = false;
    }
}

async function handleAuth(e) {
    e.preventDefault();
    const mode = document.getElementById('authMode').value;
    const email = document.getElementById('emailInput').value;
    const password = document.getElementById('passwordInput').value;
    
    let payload = { email, password };
    
    if (mode === 'register') {
        payload.nombre = document.getElementById('nameInput').value;
        payload.telefono = document.getElementById('phoneInput').value;
        payload.tipo_usuario = document.getElementById('typeInput').value;
    }

    const btnText = document.getElementById('authBtnTextNormal');
    const loader = document.getElementById('authLoader');
    if(btnText && loader) {
        btnText.style.display = 'none';
        loader.style.display = 'inline';
    }

    try {
        const response = await fetch(`backend/api/index.php?route=${mode}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();

        if (response.ok) {
            if (mode === 'register') {
                alert(data.message);
                toggleAuthMode();
            } else {
                localStorage.setItem('miproveedor_user', JSON.stringify(data.user));
                window.location.reload();
            }
        } else {
            document.getElementById('authErrorMsg').innerText = data.message;
        }
    } catch (error) {
        console.error('Auth Error:', error);
        document.getElementById('authErrorMsg').innerText = 'Error conectando con el servidor.';
    } finally {
        if(btnText && loader) {
            btnText.style.display = 'inline';
            loader.style.display = 'none';
        }
    }
}

// ======================== ACTIONS LOGIC ========================

function handleVender(e) {
    e.preventDefault();
    if (!currentUser) {
        alert('Para vender necesitas iniciar sesión primero.');
        openLoginModal();
    } else {
        // Redirigir siempre al perfil, si no tiene tienda se le abrirá el formulario automáticamente
        if (!currentUser.has_store) {
            window.location.href = 'mi-perfil.php?action=create_store';
        } else {
            window.location.href = 'mi-perfil.php?tab=tienda';
        }
    }
}

// ======================== SEARCH LOGIC ========================

function getGeolocation(e) {
    e.preventDefault();
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                document.getElementById('locInput').value = `${position.coords.latitude},${position.coords.longitude}`;
            },
            () => alert("No se pudo obtener la ubicación.")
        );
    } else {
        alert("La geolocalización no es soportada por tu navegador.");
    }
}

function performAdvancedSearch() {
    const query = document.getElementById('searchInput') ? document.getElementById('searchInput').value.trim() : '';
    if(!query) {
        alert("Por favor ingresa un producto a buscar.");
        return;
    }

    const locVal = document.getElementById('locInput') ? document.getElementById('locInput').value : '';
    const loc = locVal.split(',');
    const lat = loc[0] ? loc[0].trim() : '4.6097';
    const lng = loc[1] ? loc[1].trim() : '-74.0817';
    
    const priceEl = document.getElementById('priceInput');
    const price = priceEl ? priceEl.value : 0;
    
    const radiusEl = document.getElementById('radiusInput');
    const radius = radiusEl ? radiusEl.value : 50;

    const grid = document.getElementById('productsGrid');
    const countBadge = document.getElementById('productCount');
    
    if(!grid) return;
    
    const resultsMain = document.getElementById('searchResultsMain');
    if(resultsMain) resultsMain.style.display = 'block';
    
    grid.innerHTML = '<div style="text-align:center; padding:2rem; color:var(--text-muted);">🔄 Escaneando el mercado y calculando rutas logísticas...</div>';

    $.ajax({
        url: 'backend/api/index.php',
        type: 'GET',
        dataType: 'json',
        data: { route: 'search', q: query, lat: lat, lng: lng, radius: radius, usual_price: price },
        success: function(data) {
            renderAdvancedProducts(data);
            if(countBadge) countBadge.innerText = `${data.length} opciones cerca de ti`;
        },
        error: function(xhr) {
            let msg = 'No se encontraron resultados.';
            try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
            grid.innerHTML = `<p style="grid-column: 1/-1; text-align: center; padding: 2rem;">${msg}</p>`;
            if(countBadge) countBadge.innerText = `0 opciones`;
        }
    });
}

function renderAdvancedProducts(products) {
    const grid = document.getElementById('productsGrid');
    grid.innerHTML = '';

    products.forEach((p, index) => {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.style.display = 'flex';
        card.style.flexDirection = 'column';
        
        let ribbon = index === 0 ? '<div style="background:var(--secondary);color:white;text-align:center;font-size:0.85rem;padding:6px;font-weight:bold;">🏆 Mejor Opción Sugerida</div>' : '';
        
        let ahorroTag = '';
        if (p.ahorro_calculado > 0) {
            ahorroTag = `<div style="color:var(--secondary); font-weight:bold; font-size:0.95rem; margin-top:5px; padding: 5px; background: #d1fae5; border-radius: 5px; display: inline-block;">💰 Ahorras: $${parseFloat(p.ahorro_calculado).toLocaleString('es-CO')} c/u</div>`;
        }

        card.innerHTML = `
            ${ribbon}
            <div class="product-info" style="flex: 1;">
                <span class="badge" style="background:#e0f2fe; color:#0284c7;">📍 a ${p.distancia_km} km</span>
                <span class="badge">${p.unidad}</span>
                <h3 style="margin-top:10px; margin-bottom:5px;">${p.nombre}</h3>
                <div style="display: flex; align-items: center; gap: 10px; margin: 15px 0; padding: 8px; background: var(--bg-dark); border-radius: var(--radius); border: 1px solid var(--border);">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(p.tienda)}&background=0D8ABC&color=fff" alt="${p.tienda}" style="width: 40px; height: 40px; border-radius: 50%;">
                    <div>
                        <p style="font-weight: bold; font-size: 0.9rem; margin: 0; color: var(--primary);">${p.tienda}</p>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">${p.direccion_tienda}</p>
                    </div>
                </div>
                <div class="price-tag">$${parseFloat(p.precio).toLocaleString('es-CO')}</div>
                ${ahorroTag}
            </div>
            <div style="display:flex; justify-content:space-between; padding: 0.8rem; gap:10px; background:#f8fafc; border-top: 1px solid var(--border);">
                <button onclick="gotoCheckout(${p.id_price})" style="flex:1; padding:12px; background:var(--primary); color:white; border:none; font-weight:bold; border-radius:var(--radius); cursor:pointer;">🛒 Comprar Ahora</button>
            </div>
        `;
        grid.appendChild(card);
    });
}

function gotoCheckout(id_price) {
    window.location.href = `pedidos.php?id_price=${id_price}`;
}
