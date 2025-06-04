<?php
class Cliente {
    private $conn;
    private $table_name = "clientes";

    public $id;
    public $nombre;
    public $documento_cliente;
    public $placa_vehiculo;
    public $telefono;
    public $puntos_millas = 0;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new customer
    public function create() {
        // First check if table has millas column, if not add it
        $this->addMillasColumnIfNotExists();
        
        $query = "INSERT INTO " . $this->table_name . " 
                SET nombre = :nombre,
                    documento_cliente = :documento_cliente,
                    placa_vehiculo = :placa_vehiculo, 
                    telefono = :telefono,
                    millas = :millas";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->documento_cliente = htmlspecialchars(strip_tags($this->documento_cliente));
        $this->placa_vehiculo = htmlspecialchars(strip_tags($this->placa_vehiculo));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        
        // Bind values
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":documento_cliente", $this->documento_cliente);
        $stmt->bindParam(":placa_vehiculo", $this->placa_vehiculo);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":millas", $this->puntos_millas);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Add millas column if it doesn't exist
    private function addMillasColumnIfNotExists() {
        $query = "SHOW COLUMNS FROM " . $this->table_name . " LIKE 'millas'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            $alter_query = "ALTER TABLE " . $this->table_name . " ADD COLUMN millas INT DEFAULT 0";
            $this->conn->exec($alter_query);
        }
    }

    // Read all customers
    public function readAll() {
        // First check if table has millas column, if not add it
        $this->addMillasColumnIfNotExists();
        
        $query = "SELECT id_cliente, nombre,documento_cliente, placa_vehiculo, telefono, millas 
                FROM " . $this->table_name . " 
                ORDER BY nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Read one customer
    public function readOne() {
        // First check if table has millas column, if not add it
        $this->addMillasColumnIfNotExists();
        
        $query = "SELECT id_cliente, nombre,documento_cliente, placa_vehiculo, telefono, millas 
                FROM " . $this->table_name . " 
                WHERE id_cliente = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id = $row['id_cliente'];
            $this->nombre = $row['nombre'];
            $this->documento_cliente = $row['documento_cliente'];
            $this->placa_vehiculo = $row['placa_vehiculo'];
            $this->telefono = $row['telefono'];
            $this->puntos_millas = $row['millas'] ?? 0;
            return true;
        }
        
        return false;
    }

    // Update customer
    public function update() {
        // First check if table has millas column, if not add it
        $this->addMillasColumnIfNotExists();
        
        $query = "UPDATE " . $this->table_name . " 
                SET nombre = :nombre,
                    documento_cliente = :documento_cliente, 
                    placa_vehiculo = :placa_vehiculo, 
                    telefono = :telefono,
                    millas = :millas
                WHERE id_cliente = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->documento_cliente = htmlspecialchars(strip_tags($this->documento_cliente));
        $this->placa_vehiculo = htmlspecialchars(strip_tags($this->placa_vehiculo));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind values
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":documento_cliente", $this->documento_cliente);
        $stmt->bindParam(":placa_vehiculo", $this->placa_vehiculo);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":millas", $this->puntos_millas);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Add miles to customer
    public function addMiles($miles) {
        // First check if table has millas column, if not add it
        $this->addMillasColumnIfNotExists();
        
        $query = "UPDATE " . $this->table_name . " 
                SET millas = millas + :miles
                WHERE id_cliente = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":miles", $miles);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Delete customer
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_cliente = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Search customers
    public function search($keywords) {
        // First check if table has millas column, if not add it
        $this->addMillasColumnIfNotExists();
        
        $query = "SELECT id_cliente, nombre,documento_cliente, placa_vehiculo, telefono, millas 
                FROM " . $this->table_name . " 
                WHERE nombre LIKE ? OR documento_cliente LIKE ? OR placa_vehiculo LIKE ? OR telefono LIKE ?
                ORDER BY nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";
        
        // Bind values
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);
        $stmt->bindParam(4, $keywords);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
}
?>