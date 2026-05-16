<?php
class Product {
    private $conn;
    private $table_name = "products";

    public $id_product;
    public $nombre;
    public $descripcion;
    public $unidad_medida;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all products
    public function read() {
        $query = "SELECT p.*, MIN(sp.precio) as precio_minimo, COUNT(sp.id_supplier) as cantidad_proveedores 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN supplier_prices sp ON p.id_product = sp.id_product 
                  GROUP BY p.id_product";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Search products (AJAX Search logic)
    public function search($keywords) {
        $query = "SELECT p.*, MIN(sp.precio) as precio_minimo 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN supplier_prices sp ON p.id_product = sp.id_product 
                  WHERE LOWER(p.nombre) LIKE LOWER(?) OR LOWER(p.descripcion) LIKE LOWER(?) 
                  GROUP BY p.id_product";
        $stmt = $this->conn->prepare($query);
        $keywords = "%{$keywords}%";
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->execute();
        return $stmt;
    }

    // Search products by distance and price (Haversine formula)
    public function searchWithDistance($keywords, $lat, $lng, $radius) {
        $words = explode(' ', trim($keywords));
        $conditions = array();
        $validWords = array();
        foreach($words as $index => $word) {
            $w = trim($word);
            if(!empty($w)) {
                $conditions[] = "(LOWER(p.nombre) LIKE :kw$index OR LOWER(p.descripcion) LIKE :kw$index OR LOWER(s.nombre_tienda) LIKE :kw$index)";
                $validWords[$index] = $w;
            }
        }
        $whereClause = count($conditions) > 0 ? "(" . implode(' OR ', $conditions) . ")" : "1=1";

        // Use CASE to avoid acos() domain error when lat/lng is NULL
        // If supplier has no coordinates, distancia = 9999 (shown but ranked last)
        $query = "SELECT sp.id_price, sp.precio, sp.cantidad_minima, sp.disponible, 
                         p.id_product, p.nombre as producto_nombre, p.unidad_medida, p.descripcion,
                         s.id_supplier, s.nombre_tienda, s.direccion, s.foto_perfil, s.tiene_envio, s.rating_promedio,
                         CASE 
                           WHEN s.latitud IS NOT NULL AND s.longitud IS NOT NULL
                           THEN ROUND( 6371 * acos( 
                               GREATEST(-1, LEAST(1,
                                 cos(radians(:lat1)) * cos(radians(s.latitud)) 
                                 * cos(radians(s.longitud) - radians(:lng)) 
                                 + sin(radians(:lat2)) * sin(radians(s.latitud))
                               ))
                           ), 1)
                           ELSE 9999
                         END AS distancia
                  FROM supplier_prices sp
                  JOIN products p ON sp.id_product = p.id_product
                  JOIN suppliers s ON sp.id_supplier = s.id_supplier
                  WHERE $whereClause AND sp.disponible = 1
                  HAVING distancia <= :radius OR distancia = 9999
                  ORDER BY sp.precio ASC, distancia ASC
                  LIMIT 50";

        $stmt = $this->conn->prepare($query);
        
        foreach($validWords as $index => $word) {
            $stmt->bindValue(":kw$index", "%" . strtolower($word) . "%");
        }
        
        $stmt->bindValue(':lat1', (float)$lat);
        $stmt->bindValue(':lat2', (float)$lat);
        $stmt->bindValue(':lng',  (float)$lng);
        $stmt->bindValue(':radius', (float)$radius);
        
        $stmt->execute();
        return $stmt;
    }
}
?>
