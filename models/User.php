<?php
class User {
    private $conn;
    private $table_name = "empleados";

    public $id_empleado;
    public $nombre;
    public $documento;
    public $password;
    public $cargo;
    public $turno_id;
    public $telefono;
    public $fecha_ingreso;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($documento, $password) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE documento = :documento";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':documento', $documento);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar contraseña (asumiendo que no está hasheada en la base de datos)
            if ($password === $empleado['password']) {
                // Iniciar sesión
                $_SESSION['user_id'] = $empleado['id_empleado'];
                $_SESSION['user_role'] = 'empleado';
                $_SESSION['user_name'] = $empleado['nombre'];
                $_SESSION['user_cargo'] = $empleado['cargo'];
                return true;
            }
        }
        return false;
    }


    // Create new user (employee)
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET nombre = :nombre, 
                    documento = :documento, 
                    cargo = :cargo,
                    turno_id = :turno_id,
                    password = :password,
                    telefono = :telefono,
                    fecha_ingreso = :fecha_ingreso";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->documento = htmlspecialchars(strip_tags($this->documento));
        $this->cargo = htmlspecialchars(strip_tags($this->cargo));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        
        // Bind values
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":documento", $this->documento);
        $stmt->bindParam(":cargo", $this->cargo);
        $stmt->bindParam(":turno_id", $this->turno_id);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":telefono", $this->telefono);
        $fecha_ingreso = date('Y-m-d');
        $stmt->bindParam(":fecha_ingreso", $fecha_ingreso);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Read all users
    public function readAll() {
        $query = "SELECT e.id_empleado, e.nombre, e.documento, e.cargo, e.turno_id, t.descripcion AS turno_descripcion, e.telefono, e.fecha_ingreso 
              FROM " . $this->table_name . " e
              LEFT JOIN turnos t ON e.turno_id = t.id_turno
              ORDER BY e.nombre ASC";
    
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
    
        return $stmt;
    }

    // Read one user
    public function readOne() {
        $query = "SELECT * FROM empleados WHERE id_empleado = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id_empleado);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->nombre = $row['nombre'];
            $this->documento = $row['documento'];
            $this->cargo = $row['cargo'];
            $this->turno_id = $row['turno_id'];
            $this->telefono = $row['telefono'];
            // Si tienes más campos, asígnalos aquí
        }
    }

    // Update user
    public function update() {
        $query = "UPDATE empleados SET nombre=:nombre, documento=:documento, cargo=:cargo, turno_id=:turno_id, telefono=:telefono WHERE id_empleado=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':documento', $this->documento);
        $stmt->bindParam(':cargo', $this->cargo);
        $stmt->bindParam(':turno_id', $this->turno_id);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':id', $this->id_empleado);
        return $stmt->execute();
    }

    // Update password
    public function updatePassword() {
        $query = "UPDATE " . $this->table_name . " 
                SET password = :password 
                WHERE id_empleado = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->id_empleado = htmlspecialchars(strip_tags($this->id_empleado));
        
        // Bind values
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":id", $this->id_empleado);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Delete user
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_empleado = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_empleado);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>