<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Vendedor | MiProveedor</title>
    <link rel="stylesheet" href="frontend/css/style.css">
    <style>
        .panel-container { max-width: 800px; margin: 3rem auto; padding: 2rem; background: white; border-radius: var(--radius); box-shadow: var(--shadow); }
        .add-product-form { display: none; margin-top: 2rem; background: #f8fafc; padding: 2rem; border-radius: var(--radius); border: 1px solid var(--border); }
        .input-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem;}
        .input-group input, .input-group textarea { width: 100%; padding: 0.8rem; border: 1px solid var(--border); border-radius: var(--radius); font-family: inherit; margin-bottom: 1rem;}
        .photo-preview { width: 100px; height: 100px; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; background: #fff; margin-bottom: 1rem; overflow: hidden; color: #94a3b8; }
        .photo-preview img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>
<?php include 'components/header.php'; ?>

<main class="main-content">
    <div class="panel-container">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 2rem;">
            <h2>Panel de Vendedor</h2>
            <button class="btn-main-search" style="padding: 10px 20px;" onclick="toggleAddProduct()">+ Añadir producto</button>
        </div>

        <div id="addProductSection" class="add-product-form">
            <h3 style="margin-top: 0;">Nuevo Producto</h3>
            <form onsubmit="saveProduct(event)">
                <div class="input-group">
                    <label>Foto del producto</label>
                    <div class="photo-preview" id="productPhotoPreview">📷</div>
                    <input type="file" accept="image/*" onchange="previewProductPhoto(event)" style="border: none; padding: 0;">
                </div>
                <div class="input-group">
                    <label>Nombre del producto</label>
                    <input type="text" id="prodName" required placeholder="Ej: Cemento Gris">
                </div>
                <div class="input-group">
                    <label>Precio ($)</label>
                    <input type="number" id="prodPrice" required placeholder="0.00">
                </div>
                <div class="input-group">
                    <label>Descripción</label>
                    <textarea id="prodDesc" required placeholder="Detalles del producto..."></textarea>
                </div>
                <div class="input-group">
                    <label>Stock (Opcional)</label>
                    <input type="number" id="prodStock" placeholder="Cantidad disponible">
                </div>
                <button type="submit" class="btn-main-search">Guardar producto</button>
            </form>
        </div>

        <div id="productsList" style="margin-top: 2rem;">
            <h3>Tus Productos Activos</h3>
            <p style="color: var(--text-muted);">Aún no tienes productos registrados en esta sesión.</p>
        </div>
    </div>
</main>

<script src="frontend/js/app.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            if (!currentUser || !currentUser.has_store) {
                alert('No tienes acceso a este panel. Registra tu tienda primero.');
                window.location.href = 'crear-tienda.php';
            }
        }, 100);
    });

    function toggleAddProduct() {
        const form = document.getElementById('addProductSection');
        form.style.display = form.style.display === 'block' ? 'none' : 'block';
    }

    function previewProductPhoto(e) {
        const file = e.target.files[0];
        if(file) {
            const reader = new FileReader();
            reader.onload = function(evt) {
                document.getElementById('productPhotoPreview').innerHTML = `<img src="${evt.target.result}">`;
            }
            reader.readAsDataURL(file);
        }
    }

    function saveProduct(e) {
        e.preventDefault();
        alert('Producto guardado exitosamente.');
        toggleAddProduct();
        e.target.reset();
        document.getElementById('productPhotoPreview').innerHTML = '📷';
        
        document.getElementById('productsList').innerHTML = `
            <h3>Tus Productos Activos</h3>
            <div style="background: white; border: 1px solid var(--border); padding: 15px; border-radius: var(--radius); margin-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h4 style="margin: 0;">Producto Registrado</h4>
                    <p style="margin: 5px 0 0 0; color: var(--text-muted); font-size: 0.9rem;">Stock: Disponible</p>
                </div>
                <div style="font-weight: bold; color: var(--primary);">Activo</div>
            </div>
        `;
    }
</script>
</body>
</html>
