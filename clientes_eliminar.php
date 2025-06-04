<?php
include_once 'config/database.php';
include_once 'config/session.php';
include_once 'models/Cliente.php';

// Require admin
requireAdmin();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create cliente object
$cliente = new Cliente($db);

// Get ID of cliente to be deleted
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID no encontrado.');

// Set cliente id to be deleted
$cliente->id = $id;

$stmt = $db->prepare("SELECT COUNT(*) FROM ventas WHERE id_cliente = ?");
$stmt->execute([$id]);
$ventas_count = $stmt->fetchColumn();

if ($ventas_count > 0) {
    $message = "No se puede eliminar el cliente porque tiene ventas registradas.";
    $success = false;
} else {
    if ($cliente->delete()) {
        $message = "Cliente eliminado correctamente.";
        $success = true;
    } else {
        $message = "No se pudo eliminar el cliente.";
        $success = false;
    }
}

// Redirect to clientes page with message
header("Location: clientes.php?message=" . urlencode($message) . "&success=" . $success);
exit;
?>