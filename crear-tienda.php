<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Tienda | MiProveedor</title>
    <link rel="stylesheet" href="frontend/css/style.css">
    <style>
        .checkout-container { max-width: 600px; margin: 3rem auto; background: white; padding: 2.5rem; border-radius: var(--radius); box-shadow: var(--shadow); border: 1px solid var(--border); }
        .input-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem;}
        .input-group input, .input-group textarea, .input-group select { width: 100%; padding: 0.8rem; border: 1px solid var(--border); border-radius: var(--radius); font-family: inherit;}
        .photo-upload { display: flex; align-items: center; gap: 15px; margin-bottom: 1.5rem; }
        .photo-preview { width: 80px; height: 80px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px dashed #cbd5e1; font-size: 2rem; color: #94a3b8; }
        .photo-preview img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>
<?php include 'components/header.php'; ?>

<main class="main-content">
    <div class="checkout-container">
        <h2>Registro de Proveedor</h2>
        <form id="storeForm" onsubmit="createStore(event)">
            <div class="photo-upload">
                <div class="photo-preview" id="photoPreview">📷</div>
                <div>
                    <label style="font-weight: 600; font-size: 0.9rem; display: block; margin-bottom: 5px;">Foto de la Tienda (Opcional)</label>
                    <input type="file" id="storePhoto" accept="image/*" onchange="previewPhoto(event)">
                </div>
            </div>
            <div class="input-group" style="margin-bottom: 1.5rem;">
                <label>Nombre de la Empresa (Opcional)</label>
                <input type="text" id="storeName" placeholder="Ej: Ferretería ABC SAS">
            </div>
            <div class="input-group" style="margin-bottom: 1.5rem;">
                <label>NIT (Opcional)</label>
                <input type="text" id="storeNit" placeholder="Ej: 900.123.456-7">
            </div>
            <div class="input-group" style="margin-bottom: 1.5rem;">
                <label>Dirección (Opcional)</label>
                <input type="text" id="storeLoc" placeholder="Ej: Av Siempre Viva 123">
            </div>
            <div class="input-group" style="margin-bottom: 1.5rem;">
                <label>Descripción (Opcional)</label>
                <textarea id="storeDesc" placeholder="Describe los productos o servicios que ofreces" rows="3"></textarea>
            </div>
            <button type="submit" class="btn-main-search">Registrar tienda</button>
        </form>
    </div>
</main>

<script src="frontend/js/app.js"></script>
<script>
    function previewPhoto(e) {
        const file = e.target.files[0];
        if(file) {
            const reader = new FileReader();
            reader.onload = function(evt) {
                document.getElementById('photoPreview').innerHTML = `<img src="${evt.target.result}">`;
            }
            reader.readAsDataURL(file);
        }
    }
    
    async function createStore(e) {
        e.preventDefault();
        if (!currentUser) {
            alert('Debes iniciar sesión para crear una tienda.');
            return;
        }
        
        // Save store data logically (simulated since we're using frontend state for session)
        let storeData = {
            nombre: document.getElementById('storeName').value,
            nit: document.getElementById('storeNit').value,
            direccion: document.getElementById('storeLoc').value,
            descripcion: document.getElementById('storeDesc').value,
        };

        currentUser.has_store = true;
        currentUser.storeData = storeData;
        localStorage.setItem('miproveedor_user', JSON.stringify(currentUser));
        
        alert('Tienda registrada exitosamente.');
        window.location.href = 'mi-perfil.php';
    }
</script>
</body>
</html>
