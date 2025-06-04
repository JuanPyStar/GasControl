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

// Get ID of cliente to be edited
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID no encontrado.');

// Set ID property of cliente to be edited
$cliente->id = $id;

// Read the details of cliente
$cliente->readOne();

// Process form submission
$message = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Set cliente property values
    $cliente->nombre = $_POST['nombre'] ?? '';
    $cliente->documento_cliente = $_POST['documento_cliente'] ?? '';
    $cliente->placa_vehiculo = $_POST['placa_vehiculo'] ?? '';
    $cliente->telefono = $_POST['telefono'] ?? '';
    $cliente->puntos_millas = $_POST['millas'] ?? 0;
    
    // Update the cliente
    if ($cliente->update()) {
        $message = "Cliente actualizado exitosamente.";
        $success = true;
    } else {
        $message = "No se pudo actualizar el cliente.";
    }
}

// Include header
include_once 'includes/header.php';
?>

<!-- Editar Cliente Content -->
<div class="fade-in">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Editar Cliente</h1>
        <a href="clientes.php" class="text-blue-600 hover:text-blue-800 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Volver a Clientes
        </a>
    </div>
    
    <?php if (!empty($message)): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $success ? 'bg-green-100 text-green-700 border-green-400' : 'bg-red-100 text-red-700 border-red-400'; ?>">
            <?php echo $message; ?>
        </div>
        <?php if ($success): ?>
            <script>
                // Redirect to clientes.php after 2 seconds
                setTimeout(function() {
                    window.location.href = 'clientes.php';
                }, 2000);
            </script>
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="bg-white rounded-xl shadow-lg p-8">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $id); ?>" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($cliente->nombre); ?>" required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="documento_cliente" class="block text-sm font-medium text-gray-700 mb-1">Documento</label>
                    <input type="text" name="documento_cliente" id="documento_cliente" value="<?php echo htmlspecialchars($cliente->documento_cliente); ?>" required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="placa_vehiculo" class="block text-sm font-medium text-gray-700 mb-1">Placa del Vehículo</label>
                    <input type="text" name="placa_vehiculo" id="placa_vehiculo" value="<?php echo htmlspecialchars($cliente->placa_vehiculo); ?>" required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" value="<?php echo htmlspecialchars($cliente->telefono); ?>" required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="millas" class="block text-sm font-medium text-gray-700 mb-1">Puntos de Millas</label>
                    <input type="number" name="millas" id="millas" value="<?php echo htmlspecialchars($cliente->puntos_millas); ?>"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
            </div>
            
            <div class="flex justify-end">
                <a href="clientes.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg mr-4 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i> Actualizar Cliente
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>