-- Add missing products to database
INSERT IGNORE INTO products (nombre, descripcion, unidad_medida) VALUES
('Ladrillo Tolete', 'Ladrillo tolete estándar para construcción', 'Unidad'),
('Aceite Vegetal', 'Aceite vegetal refinado para cocina industrial', 'Litro'),
('Cemento Blanco', 'Saco de 40kg para acabados finos', 'Bulto 40kg'),
('Azúcar Blanca', 'Azúcar refinada para uso industrial', 'Kilogramo'),
('Aceite de Motor 20W50', 'Lubricante para motores de alto rendimiento', 'Litro');

-- Assign prices for the new products (using existing suppliers)
INSERT IGNORE INTO supplier_prices (id_supplier, id_product, precio, cantidad_minima, disponible)
SELECT 1, id_product, 850.00, 1000, 1 FROM products WHERE nombre = 'Ladrillo Tolete';

INSERT IGNORE INTO supplier_prices (id_supplier, id_product, precio, cantidad_minima, disponible)
SELECT 2, id_product, 800.00, 500, 1 FROM products WHERE nombre = 'Ladrillo Tolete';

INSERT IGNORE INTO supplier_prices (id_supplier, id_product, precio, cantidad_minima, disponible)
SELECT 4, id_product, 8500.00, 12, 1 FROM products WHERE nombre = 'Aceite Vegetal';

INSERT IGNORE INTO supplier_prices (id_supplier, id_product, precio, cantidad_minima, disponible)
SELECT 5, id_product, 8200.00, 6, 1 FROM products WHERE nombre = 'Aceite Vegetal';

INSERT IGNORE INTO supplier_prices (id_supplier, id_product, precio, cantidad_minima, disponible)
SELECT 1, id_product, 32000.00, 20, 1 FROM products WHERE nombre = 'Cemento Blanco';

INSERT IGNORE INTO supplier_prices (id_supplier, id_product, precio, cantidad_minima, disponible)
SELECT 4, id_product, 2800.00, 50, 1 FROM products WHERE nombre = 'Azúcar Blanca';

INSERT IGNORE INTO supplier_prices (id_supplier, id_product, precio, cantidad_minima, disponible)
SELECT 6, id_product, 25000.00, 5, 1 FROM products WHERE nombre = 'Aceite de Motor 20W50';
