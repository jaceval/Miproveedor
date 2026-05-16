-- Database: miproveedor
CREATE DATABASE IF NOT EXISTS miproveedor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE miproveedor;

-- 1. Users table
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    tipo_usuario ENUM('comprador', 'proveedor') NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado BOOLEAN DEFAULT TRUE
);

-- 2. Suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
    id_supplier INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    nombre_tienda VARCHAR(150) NOT NULL,
    descripcion TEXT,
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8),
    direccion VARCHAR(255),
    foto_perfil VARCHAR(255) DEFAULT 'default_supplier.png',
    tiene_envio BOOLEAN DEFAULT FALSE,
    rating_promedio DECIMAL(2, 1) DEFAULT 0,
    FOREIGN KEY (id_user) REFERENCES users (id_user) ON DELETE CASCADE
);

-- 3. Products table (Master table)
CREATE TABLE IF NOT EXISTS products (
    id_product INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    unidad_medida VARCHAR(50) NOT NULL
);

-- 4. Supplier Prices (Current prices)
CREATE TABLE IF NOT EXISTS supplier_prices (
    id_price INT AUTO_INCREMENT PRIMARY KEY,
    id_supplier INT NOT NULL,
    id_product INT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    cantidad_minima INT DEFAULT 1,
    disponible BOOLEAN DEFAULT TRUE,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (id_supplier, id_product),
    FOREIGN KEY (id_supplier) REFERENCES suppliers (id_supplier) ON DELETE CASCADE,
    FOREIGN KEY (id_product) REFERENCES products (id_product) ON DELETE CASCADE
);

-- 5. Price History (Analytics data)
CREATE TABLE IF NOT EXISTS price_history (
    id_history INT AUTO_INCREMENT PRIMARY KEY,
    id_supplier INT NOT NULL,
    id_product INT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_supplier) REFERENCES suppliers (id_supplier) ON DELETE CASCADE,
    FOREIGN KEY (id_product) REFERENCES products (id_product) ON DELETE CASCADE
);

-- 6. Reviews
CREATE TABLE IF NOT EXISTS reviews (
    id_review INT AUTO_INCREMENT PRIMARY KEY,
    id_supplier INT NOT NULL,
    id_user INT NOT NULL,
    calificacion INT CHECK (calificacion BETWEEN 1 AND 5),
    comentario TEXT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_supplier) REFERENCES suppliers (id_supplier) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users (id_user) ON DELETE CASCADE
);

-- 7. Orders
CREATE TABLE IF NOT EXISTS orders (
    id_order INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_supplier INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM(
        'pendiente',
        'confirmado',
        'cancelado'
    ) DEFAULT 'pendiente',
    total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users (id_user),
    FOREIGN KEY (id_supplier) REFERENCES suppliers (id_supplier)
);

-- 8. Order Details
CREATE TABLE IF NOT EXISTS order_details (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_order INT NOT NULL,
    id_product INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_order) REFERENCES orders (id_order) ON DELETE CASCADE,
    FOREIGN KEY (id_product) REFERENCES products (id_product)
);

-- Mandatory Indices
CREATE INDEX idx_product ON supplier_prices (id_product);

CREATE INDEX idx_supplier ON supplier_prices (id_supplier);

CREATE INDEX idx_price ON supplier_prices (precio);

CREATE INDEX idx_fecha ON supplier_prices (fecha_actualizacion);

-- Extra analysis table: Price Alerts
CREATE TABLE IF NOT EXISTS price_alerts (
    id_alert INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_product INT NOT NULL,
    precio_objetivo DECIMAL(10, 2) NOT NULL,
    estado BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_user) REFERENCES users (id_user),
    FOREIGN KEY (id_product) REFERENCES products (id_product)
);

-- SEED DATA
-- 1. Users
INSERT INTO
    users (
        nombre,
        email,
        password,
        tipo_usuario
    )
VALUES (
        'Admin MiProveedor',
        'admin@miproveedor.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'proveedor'
    ),
    (
        'Constructor Juan',
        'juan@obra.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'comprador'
    );

-- 2. Suppliers
INSERT INTO
    suppliers (
        id_user,
        nombre_tienda,
        descripcion,
        latitud,
        longitud,
        direccion,
        tiene_envio
    )
VALUES (
        1,
        'Mega Ferretería Central',
        'Tu aliado en construcción masiva',
        4.6097,
        -74.0817,
        'Av El Dorado # 45-20',
        TRUE
    ),
    (
        1,
        'Ferretería El Maestro',
        'Los mejores precios del barrio para constructores',
        4.6200,
        -74.0700,
        'Calle 50 # 10-10',
        TRUE
    ),
    (
        1,
        'Materiales Occidente',
        'Distribuidor mayorista de occidente',
        4.6500,
        -74.1200,
        'Av Boyacá # 80-20',
        FALSE
    ),
    (
        1,
        'AgroAlimentos del Sur',
        'Suministros al por mayor para restaurantes',
        4.5981,
        -74.0758,
        'Corabastos Sur',
        TRUE
    ),
    (
        1,
        'Víveres La Economía',
        'Todo para tu tienda o restaurante',
        4.6300,
        -74.0900,
        'Plaza Paloquemao',
        TRUE
    ),
    (
        1,
        'Químicos Globales SAS',
        'Especialistas en reactivos industriales',
        4.7110,
        -74.0721,
        'Zona Industrial',
        FALSE
    ),
    (
        1,
        'Suministros Químicos Bogotá',
        'Reactivos y solventes de alta pureza',
        4.6800,
        -74.1000,
        'Calle 80 # 70',
        TRUE
    );

-- 3. Products
INSERT INTO
    products (
        nombre,
        descripcion,
        unidad_medida
    )
VALUES (
        'Cemento Gris Portland',
        'Saco de 50kg de alta resistencia',
        'Bulto 50kg'
    ),
    (
        'Cemento Blanco',
        'Saco de 40kg para acabados',
        'Bulto 40kg'
    ),
    (
        'Harina de Trigo Industrial',
        'Harina refinada para panadería mayorista',
        'Saco 25kg'
    ),
    (
        'Arroz Blanco',
        'Arroz empacado por arroba',
        'Arroba 12.5kg'
    ),
    (
        'Ácido Sulfúrico 98%',
        'Insumo industrial para procesos químicos',
        'Galón'
    ),
    (
        'Varilla Corrugada 1/2',
        'Acero de refuerzo para construcción',
        'Unidad 6m'
    );

-- 4. Initial Prices
INSERT INTO
    supplier_prices (
        id_supplier,
        id_product,
        precio,
        cantidad_minima
    )
VALUES (1, 1, 28500.00, 50), -- Cemento en ferreteria central
    (2, 1, 27000.00, 20), -- Cemento en maestro (más barato, más cerca dependiendo)
    (3, 1, 29500.00, 100), -- Cemento en occidente
    (1, 6, 32000.00, 20), -- Varilla en ferreteria central
    (3, 6, 31000.00, 50), -- Varilla en occidente
    (4, 3, 85000.00, 10), -- Harina en AgroAlimentos
    (5, 3, 87000.00, 5), -- Harina en La Economía
    (4, 4, 35000.00, 20), -- Arroz en AgroAlimentos
    (6, 5, 120000.00, 5), -- Ácido en Globales
    (7, 5, 115000.00, 10);
-- Ácido en Suministros