<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id_user;
    public $nombre;
    public $email;
    public $password;
    public $telefono;
    public $tipo_usuario;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nombre=:nombre, email=:email, password=:password, telefono=:telefono, tipo_usuario=:tipo_usuario";

        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->tipo_usuario = htmlspecialchars(strip_tags($this->tipo_usuario));

        // Hash de la contraseña bcrypt
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":tipo_usuario", $this->tipo_usuario);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login() {
        $query = "SELECT id_user, nombre, password, tipo_usuario FROM " . $this->table_name . " WHERE email = :email LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        
        return $stmt;
    }
}
?>
