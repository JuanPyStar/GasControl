<?php
include_once 'config/database.php';
include_once 'models/Combustible.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create combustible object
$combustible = new Combustible($db);

// Get ID of combustible to be deleted
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID no encontrado.');

// Set combustible id to be deleted
$combustible->id = $id;

// Verificar si hay ventas asociadas
$stmt = $db->prepare("SELECT COUNT(*) FROM ventas WHERE id_combustible = ?");
$stmt->execute([$id]);
$ventas_count = $stmt->fetchColumn();

// Verificar si hay suministros asociados
$stmt2 = $db->prepare("SELECT COUNT(*) FROM suministros WHERE id_combustible = ?");
$stmt2->execute([$id]);
$suministros_count = $stmt2->fetchColumn();

if ($ventas_count > 0) {
    $message = "No se puede eliminar el combustible porque tiene ventas registradas.";
    $success = false;
} elseif ($suministros_count > 0) {
    $message = "No se puede eliminar el combustible porque tiene suministros registrados.";
    $success = false;
} else {
    // Delete the combustible
    if ($combustible->delete()) {
        $message = "Combustible eliminado correctamente.";
        $success = true;
    } else {
        $message = "No se pudo eliminar el combustible.";
        $success = false;
    }
}

// Redirigir con mensaje
header("Location: combustibles.php?message=" . urlencode($message) . "&success=" . $success);
exit;
?>