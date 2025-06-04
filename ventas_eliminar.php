<?php
include_once 'config/database.php';
include_once 'config/session.php';
include_once 'models/Venta.php';

// Require admin
requireAdmin();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create venta object
$venta = new Venta($db);

// Get ID of venta to be deleted
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID no encontrado.');

// Set venta id to be deleted
$venta->id = $id;

// Delete the venta
if ($venta->delete()) {
    // Redirect to ventas page
    header("Location: ventas.php");
} else {
    echo "No se pudo eliminar la venta.";
}
?>