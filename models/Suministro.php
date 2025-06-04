<?php
class Suministro {
    private $conn;
    private $table_name = "suministros";

    public $id;
    public $id_combustible;
    public $cantidad_recibida;
    public $fecha_recepcion;
    public $proveedor;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new supply
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET id_combustible = :id_combustible, 
                    cantidad_recibida = :cantidad_recibida, 
                    fecha_recepcion = :fecha_recepcion,
                    proveedor = :proveedor";
        
        $stmt = $this->conn->prepare($query);
        
        // Set current date and time if not provided
        if (empty($this->fecha_recepcion)) {
            $this->fecha_recepcion = date('Y-m-d H:i:s');
        }
        
        // Sanitize input
        $this->id_combustible = htmlspecialchars(strip_tags($this->id_combustible));
        $this->cantidad_recibida = htmlspecialchars(strip_tags($this->cantidad_recibida));
        $this->fecha_recepcion = htmlspecialchars(strip_tags($this->fecha_recepcion));
        $this->proveedor = htmlspecialchars(strip_tags($this->proveedor));
        
        // Bind values
        $stmt->bindParam(":id_combustible", $this->id_combustible);
        $stmt->bindParam(":cantidad_recibida", $this->cantidad_recibida);
        $stmt->bindParam(":fecha_recepcion", $this->fecha_recepcion);
        $stmt->bindParam(":proveedor", $this->proveedor);
        
        // Execute query
        if ($stmt->execute()) {
            // Update the combustible stock
            $query = "UPDATE combustibles 
                    SET stock_actual = stock_actual + :cantidad
                    WHERE id_combustible = :id_combustible";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cantidad", $this->cantidad_recibida);
            $stmt->bindParam(":id_combustible", $this->id_combustible);
            $stmt->execute();
            
            return true;
        }
        
        return false;
    }

    // Read all supplies
    public function readAll() {
        $query = "SELECT s.id_suministro, c.tipo as combustible, s.cantidad_recibida, 
                    s.fecha_recepcion, s.proveedor, s.id_combustible
                FROM " . $this->table_name . " s
                JOIN combustibles c ON s.id_combustible = c.id_combustible
                ORDER BY s.fecha_recepcion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Read one supply
    public function readOne() {
        $query = "SELECT s.id_suministro, c.tipo as combustible, s.cantidad_recibida, 
                    s.fecha_recepcion, s.proveedor, s.id_combustible
                FROM " . $this->table_name . " s
                JOIN combustibles c ON s.id_combustible = c.id_combustible
                WHERE s.id_suministro = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id = $row['id_suministro'];
            $this->id_combustible = $row['id_combustible'];
            $this->cantidad_recibida = $row['cantidad_recibida'];
            $this->fecha_recepcion = $row['fecha_recepcion'];
            $this->proveedor = $row['proveedor'];
            return true;
        }
        
        return false;
    }

    // Update supply
    public function update() {
        // First get the original quantity
        $query = "SELECT cantidad_recibida FROM " . $this->table_name . " WHERE id_suministro = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $original_quantity = $row['cantidad_recibida'];
        
        // Update the supply record
        $query = "UPDATE " . $this->table_name . " 
                SET id_combustible = :id_combustible, 
                    cantidad_recibida = :cantidad_recibida, 
                    fecha_recepcion = :fecha_recepcion,
                    proveedor = :proveedor
                WHERE id_suministro = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->id_combustible = htmlspecialchars(strip_tags($this->id_combustible));
        $this->cantidad_recibida = htmlspecialchars(strip_tags($this->cantidad_recibida));
        $this->fecha_recepcion = htmlspecialchars(strip_tags($this->fecha_recepcion));
        $this->proveedor = htmlspecialchars(strip_tags($this->proveedor));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind values
        $stmt->bindParam(":id_combustible", $this->id_combustible);
        $stmt->bindParam(":cantidad_recibida", $this->cantidad_recibida);
        $stmt->bindParam(":fecha_recepcion", $this->fecha_recepcion);
        $stmt->bindParam(":proveedor", $this->proveedor);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            // Adjust the fuel stock
            $quantity_difference = $this->cantidad_recibida - $original_quantity;
            
            $query = "UPDATE combustibles 
                    SET stock_actual = stock_actual + :difference
                    WHERE id_combustible = :id_combustible";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":difference", $quantity_difference);
            $stmt->bindParam(":id_combustible", $this->id_combustible);
            $stmt->execute();
            
            return true;
        }
        
        return false;
    }

    // Delete supply
    public function delete() {
        // First get the supply details to adjust stock
        $query = "SELECT id_combustible, cantidad_recibida FROM " . $this->table_name . " WHERE id_suministro = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $combustible_id = $row['id_combustible'];
        $cantidad = $row['cantidad_recibida'];
        
        // Delete the supply record
        $query = "DELETE FROM " . $this->table_name . " WHERE id_suministro = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
            // Adjust the stock of combustible
            $query = "UPDATE combustibles 
                    SET stock_actual = stock_actual - :cantidad
                    WHERE id_combustible = :id_combustible";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":cantidad", $cantidad);
            $stmt->bindParam(":id_combustible", $combustible_id);
            $stmt->execute();
            
            return true;
        }
        
        return false;
    }
}
?>