<?php
require_once 'backend/config/db.php';
$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    die("Error conectando a BD.\n");
}

// Ensure database and schema exists
$sql = file_get_contents('database/schema.sql');
try {
    $conn->exec($sql);
    echo "Schema executed.\n";
} catch (Exception $e) {
    echo "Schema error (might already exist): " . $e->getMessage() . "\n";
}

// 1. Modificar tabla users para aceptar 'admin'
try {
    $conn->exec("ALTER TABLE users MODIFY tipo_usuario ENUM('comprador', 'proveedor', 'admin') NOT NULL");
    echo "Enum modificado.\n";
} catch (Exception $e) {
    echo "Error alterando enum: " . $e->getMessage() . "\n";
}

// 2. Insertar admin
$email = "admin_" . rand(1000, 9999) . "@miproveedor.com";
$password_plain = "admin123";
$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare("INSERT INTO users (nombre, email, password, tipo_usuario) VALUES (?, ?, ?, 'admin')");
    $stmt->execute(['Super Admin', $email, $password_hash]);
    echo "=== ADMIN CREADO ===\n";
    echo "Email: $email\n";
    echo "Password: $password_plain\n";
    echo "====================\n";
} catch (Exception $e) {
    echo "Error insertando admin: " . $e->getMessage() . "\n";
}

// 3. Importar CSV
$file = fopen('database/productos.csv', 'r');
if ($file !== false) {
    fgetcsv($file); // skip header
    $stmt = $conn->prepare("INSERT INTO products (nombre, descripcion, unidad_medida) VALUES (?, ?, ?)");
    $count = 0;
    while (($data = fgetcsv($file)) !== false) {
        if (count($data) >= 4) {
            $nombre = $data[1];
            $descripcion = $data[2];
            $unidad = $data[3];
            try {
                $stmt->execute([$nombre, $descripcion, $unidad]);
                $count++;
            } catch (Exception $e) {}
        }
    }
    fclose($file);
    echo "Se importaron $count productos del CSV.\n";
} else {
    echo "No se pudo abrir el CSV.\n";
}

?>
