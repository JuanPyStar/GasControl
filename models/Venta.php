<?php
class Venta {
    private $conn;
    private $table_name = "ventas";

    public $id;
    public $fecha_hora;
    public $id_empleado;
    public $id_combustible;
    public $id_cliente;
    public $galones_vendidos;
    public $total_pagado;
    public $metodo_pago;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new sale
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET fecha_hora = :fecha_hora, 
                    id_empleado = :id_empleado, 
                    id_combustible = :id_combustible,
                    id_cliente = :id_cliente,
                    galones_vendidos = :galones_vendidos,
                    total_pagado = :total_pagado,
                    metodo_pago = :metodo_pago";
        
        $stmt = $this->conn->prepare($query);
        
        // Set current date and time if not provided
        if (empty($this->fecha_hora)) {
            $this->fecha_hora = date('Y-m-d H:i:s');
        }
        
        // Sanitize input
        $this->fecha_hora = htmlspecialchars(strip_tags($this->fecha_hora));
        $this->id_empleado = htmlspecialchars(strip_tags($this->id_empleado));
        $this->id_combustible = htmlspecialchars(strip_tags($this->id_combustible));
        $this->id_cliente = htmlspecialchars(strip_tags($this->id_cliente));
        $this->galones_vendidos = htmlspecialchars(strip_tags($this->galones_vendidos));
        $this->total_pagado = htmlspecialchars(strip_tags($this->total_pagado));
        $this->metodo_pago = htmlspecialchars(strip_tags($this->metodo_pago));
        
        // Bind values
        $stmt->bindParam(":fecha_hora", $this->fecha_hora);
        $stmt->bindParam(":id_empleado", $this->id_empleado);
        $stmt->bindParam(":id_combustible", $this->id_combustible);
        $stmt->bindParam(":id_cliente", $this->id_cliente);
        $stmt->bindParam(":galones_vendidos", $this->galones_vendidos);
        $stmt->bindParam(":total_pagado", $this->total_pagado);
        $stmt->bindParam(":metodo_pago", $this->metodo_pago);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Read all sales
    public function readAll() {
        $query = "SELECT v.id_venta, v.fecha_hora, e.documento as empleado, c.tipo as combustible, 
                    cl.documento_cliente as cliente, v.galones_vendidos, v.total_pagado, v.metodo_pago
                FROM " . $this->table_name . " v
                LEFT JOIN empleados e ON v.id_empleado = e.id_empleado
                LEFT JOIN combustibles c ON v.id_combustible = c.id_combustible
                LEFT JOIN clientes cl ON v.id_cliente = cl.id_cliente
                ORDER BY v.fecha_hora DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Read sales by employee
    public function readByEmployee() {
        $query = "SELECT v.id_venta, v.fecha_hora, e.documento as empleado, c.tipo as combustible, 
                    cl.documento_cliente as cliente, v.galones_vendidos, v.total_pagado, v.metodo_pago
                FROM " . $this->table_name . " v
                LEFT JOIN empleados e ON v.id_empleado = e.id_empleado
                LEFT JOIN combustibles c ON v.id_combustible = c.id_combustible
                LEFT JOIN clientes cl ON v.id_cliente = cl.id_cliente
                WHERE v.id_empleado = ?
                ORDER BY v.fecha_hora DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_empleado);
        $stmt->execute();
        
        return $stmt;
    }

    // Read one sale
    public function readOne() {
        $query = "SELECT v.id_venta, v.fecha_hora, e.documento as empleado, c.tipo as combustible, 
                    cl.documento_cliente as cliente, v.galones_vendidos, v.total_pagado, v.metodo_pago,
                    v.id_empleado, v.id_combustible, v.id_cliente
                FROM " . $this->table_name . " v
                LEFT JOIN empleados e ON v.id_empleado = e.id_empleado
                LEFT JOIN combustibles c ON v.id_combustible = c.id_combustible
                LEFT JOIN clientes cl ON v.id_cliente = cl.id_cliente
                WHERE v.id_venta = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id = $row['id_venta'];
            $this->fecha_hora = $row['fecha_hora'];
            $this->id_empleado = $row['id_empleado'];
            $this->id_combustible = $row['id_combustible'];
            $this->id_cliente = $row['id_cliente'];
            $this->galones_vendidos = $row['galones_vendidos'];
            $this->total_pagado = $row['total_pagado'];
            $this->metodo_pago = $row['metodo_pago'];
            return true;
        }
        
        return false;
    }

    // Update sale
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET fecha_hora = :fecha_hora, 
                    id_empleado = :id_empleado, 
                    id_combustible = :id_combustible,
                    id_cliente = :id_cliente,
                    galones_vendidos = :galones_vendidos,
                    total_pagado = :total_pagado,
                    metodo_pago = :metodo_pago
                WHERE id_venta = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->fecha_hora = htmlspecialchars(strip_tags($this->fecha_hora));
        $this->id_empleado = htmlspecialchars(strip_tags($this->id_empleado));
        $this->id_combustible = htmlspecialchars(strip_tags($this->id_combustible));
        $this->id_cliente = htmlspecialchars(strip_tags($this->id_cliente));
        $this->galones_vendidos = htmlspecialchars(strip_tags($this->galones_vendidos));
        $this->total_pagado = htmlspecialchars(strip_tags($this->total_pagado));
        $this->metodo_pago = htmlspecialchars(strip_tags($this->metodo_pago));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind values
        $stmt->bindParam(":fecha_hora", $this->fecha_hora);
        $stmt->bindParam(":id_empleado", $this->id_empleado);
        $stmt->bindParam(":id_combustible", $this->id_combustible);
        $stmt->bindParam(":id_cliente", $this->id_cliente);
        $stmt->bindParam(":galones_vendidos", $this->galones_vendidos);
        $stmt->bindParam(":total_pagado", $this->total_pagado);
        $stmt->bindParam(":metodo_pago", $this->metodo_pago);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Delete sale
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_venta = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Get sales statistics
    public function getStats($from = null, $to = null) {
        $stats = [];
        $where = '';
        $params = [];
        if ($from && $to) {
            $where = "WHERE DATE(fecha_hora) BETWEEN :from AND :to";
            $params[':from'] = $from;
            $params[':to'] = $to;
        }

        // Total sales
        $query = "SELECT COUNT(*) as total_sales, 
                    SUM(total_pagado) as total_income,
                    SUM(galones_vendidos) as total_gallons
                FROM " . $this->table_name . " $where";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['total_sales'] = $row['total_sales'];
        $stats['total_income'] = $row['total_income'];
        $stats['total_gallons'] = $row['total_gallons'];
        
        // Sales by fuel type
        $query = "SELECT c.tipo, COUNT(*) as sales_count, 
                    SUM(v.total_pagado) as total_income,
                    SUM(v.galones_vendidos) as total_gallons
                FROM " . $this->table_name . " v
                JOIN combustibles c ON v.id_combustible = c.id_combustible
                $where
                GROUP BY c.tipo";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        $stats['by_fuel_type'] = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats['by_fuel_type'][] = $row;
        }
        
        // Sales by day (last 7 days)
        $query = "SELECT DATE(fecha_hora) as sale_date, 
                    COUNT(*) as sales_count,
                    SUM(total_pagado) as total_income
                FROM " . $this->table_name . "
                WHERE fecha_hora >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(fecha_hora)
                ORDER BY sale_date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $stats['by_day'] = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats['by_day'][] = $row;
        }
        
        return $stats;
    }

    // Get sales statistics by date range
    public function getStatsByRange($from, $to) {
        $stats = [
            'total_sales' => 0,
            'total_income' => 0,
            'total_gallons' => 0,
            'by_day' => [],
            'by_fuel_type' => []
        ];

        // Totales generales
        $query = "SELECT COUNT(*) as total_sales, 
                     SUM(total_pagado) as total_income, 
                     SUM(galones_vendidos) as total_gallons
              FROM ventas
              WHERE DATE(fecha_hora) BETWEEN :from AND :to";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':from', $from);
        $stmt->bindParam(':to', $to);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $stats['total_sales'] = (int)($row['total_sales'] ?? 0);
            $stats['total_income'] = (float)($row['total_income'] ?? 0);
            $stats['total_gallons'] = (float)($row['total_gallons'] ?? 0);
        }

        // Ventas por día
        $query = "SELECT DATE(fecha_hora) as sale_date, 
                     SUM(total_pagado) as total_income,
                     COUNT(*) as sales_count
              FROM ventas
              WHERE DATE(fecha_hora) BETWEEN :from AND :to
              GROUP BY sale_date
              ORDER BY sale_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':from', $from);
        $stmt->bindParam(':to', $to);
        $stmt->execute();
        $stats['by_day'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ventas por tipo de combustible
        $query = "SELECT c.tipo, 
                     SUM(v.total_pagado) as total_income,
                     SUM(v.galones_vendidos) as total_gallons,
                     COUNT(*) as sales_count
              FROM ventas v
              JOIN combustibles c ON v.id_combustible = c.id_combustible
              WHERE DATE(v.fecha_hora) BETWEEN :from AND :to
              GROUP BY c.tipo";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':from', $from);
        $stmt->bindParam(':to', $to);
        $stmt->execute();
        $stats['by_fuel_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }
}

?>