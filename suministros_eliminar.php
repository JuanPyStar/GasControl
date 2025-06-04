<?php
include_once 'config/database.php';
include_once 'config/session.php';
include_once 'models/Suministro.php';

// Require admin
requireAdmin();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create suministro object
$suministro = new Suministro($db);

// Get ID of suministro to be deleted
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID no encontrado.');

// Set suministro id to be deleted
$suministro->id = $id;

// Delete the suministro
if ($suministro->delete()) {
    // Redirect to suministros page
    header("Location: suministros.php");
} else {
    echo "No se pudo eliminar el suministro.";
}
?>