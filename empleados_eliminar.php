<?php
include_once 'config/database.php';
include_once 'models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID no encontrado.');

// Verificar si el empleado tiene ventas asociadas
$stmt = $db->prepare("SELECT COUNT(*) FROM ventas WHERE id_empleado = ?");
$stmt->execute([$id]);
$ventas_count = $stmt->fetchColumn();

if ($ventas_count > 0) {
    // Redirigir con mensaje de error
    header("Location: empleados.php?message=" . urlencode("No se puede eliminar el empleado porque tiene ventas registradas.") . "&success=0");
    exit;
} else {
    // Eliminar empleado
    $user->id = $id;
    if ($user->delete()) {
        header("Location: empleados.php?message=" . urlencode("Empleado eliminado correctamente.") . "&success=1");
        exit;
    } else {
        header("Location: empleados.php?message=" . urlencode("No se pudo eliminar el empleado.") . "&success=0");
        exit;
    }
}
?>