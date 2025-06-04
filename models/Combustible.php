<?php
class Combustible {
    private $conn;
    private $table_name = "combustibles";

    public $id;
    public $tipo;
    public $precio_galon;
    public $stock_actual;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new fuel
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET tipo = :tipo, 
                    precio_galon = :precio_galon, 
                    stock_actual = :stock_actual";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->precio_galon = htmlspecialchars(strip_tags($this->precio_galon));
        $this->stock_actual = htmlspecialchars(strip_tags($this->stock_actual));
        
        // Bind values
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":precio_galon", $this->precio_galon);
        $stmt->bindParam(":stock_actual", $this->stock_actual);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Read all fuels
    public function readAll() {
        $query = "SELECT id_combustible, tipo, precio_galon, stock_actual 
                FROM " . $this->table_name . " 
                ORDER BY tipo ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Read one fuel
    public function readOne() {
        $query = "SELECT id_combustible, tipo, precio_galon, stock_actual 
                FROM " . $this->table_name . " 
                WHERE id_combustible = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id = $row['id_combustible'];
            $this->tipo = $row['tipo'];
            $this->precio_galon = $row['precio_galon'];
            $this->stock_actual = $row['stock_actual'];
            return true;
        }
        
        return false;
    }

    // Update fuel
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET tipo = :tipo, 
                    precio_galon = :precio_galon, 
                    stock_actual = :stock_actual
                WHERE id_combustible = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->precio_galon = htmlspecialchars(strip_tags($this->precio_galon));
        $this->stock_actual = htmlspecialchars(strip_tags($this->stock_actual));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind values
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":precio_galon", $this->precio_galon);
        $stmt->bindParam(":stock_actual", $this->stock_actual);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Update fuel stock (decrease after sale)
    public function updateStock($gallons_sold) {
        $query = "UPDATE " . $this->table_name . " 
                SET stock_actual = stock_actual - :gallons_sold
                WHERE id_combustible = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":gallons_sold", $gallons_sold);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Delete fuel
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_combustible = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>