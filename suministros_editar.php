<?php
include_once 'config/database.php';
include_once 'config/session.php';
include_once 'models/Suministro.php';
include_once 'models/Combustible.php';

// Require admin
requireAdmin();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create objects
$suministro = new Suministro($db);
$combustible = new Combustible($db);

// Get ID of suministro to be edited
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID no encontrado.');

// Set ID property of suministro to be edited
$suministro->id = $id;

// Read the details of suministro
$suministro->readOne();

// Get all combustibles
$stmt_combustibles = $combustible->readAll();

// Process form submission
$message = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Set suministro property values
    $suministro->id_combustible = $_POST['id_combustible'] ?? null;
    $suministro->cantidad_recibida = $_POST['cantidad_recibida'] ?? 0;
    $suministro->fecha_recepcion = $_POST['fecha_recepcion'] ?? date('Y-m-d H:i:s');
    $suministro->proveedor = $_POST['proveedor'] ?? '';
    
    // Update the suministro
    if ($suministro->update()) {
        $message = "Suministro actualizado exitosamente y stock ajustado.";
        $success = true;
    } else {
        $message = "No se pudo actualizar el suministro.";
    }
}

// Include header
include_once 'includes/header.php';
?>

<!-- Editar Suministro Content -->
<div class="fade-in">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Editar Suministro</h1>
        <a href="suministros.php" class="text-blue-600 hover:text-blue-800 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Volver a Suministros
        </a>
    </div>
    
    <?php if (!empty($message)): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $success ? 'bg-green-100 text-green-700 border-green-400' : 'bg-red-100 text-red-700 border-red-400'; ?>">
            <?php echo $message; ?>
        </div>
        <?php if ($success): ?>
            <script>
                // Redirect to suministros.php after 2 seconds
                setTimeout(function() {
                    window.location.href = 'suministros.php';
                }, 2000);
            </script>
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="bg-white rounded-xl shadow-lg p-8">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $id); ?>" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="id_combustible" class="block text-sm font-medium text-gray-700 mb-1">Combustible</label>
                    <select name="id_combustible" id="id_combustible" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Seleccione un combustible</option>
                        <?php while ($row_combustible = $stmt_combustibles->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?php echo $row_combustible['id_combustible']; ?>" <?php echo $suministro->id_combustible == $row_combustible['id_combustible'] ? 'selected' : ''; ?>>
                                <?php echo $row_combustible['tipo'] . ' (Stock actual: ' . $row_combustible['stock_actual'] . ' gal)'; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div>
                    <label for="cantidad_recibida" class="block text-sm font-medium text-gray-700 mb-1">Cantidad Recibida (Galones)</label>
                    <input type="number" name="cantidad_recibida" id="cantidad_recibida" step="0.01" min="0.01" value="<?php echo htmlspecialchars($suministro->cantidad_recibida); ?>" required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="fecha_recepcion" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Recepción</label>
                    <input type="datetime-local" name="fecha_recepcion" id="fecha_recepcion" value="<?php echo date('Y-m-d\TH:i', strtotime($suministro->fecha_recepcion)); ?>" required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="proveedor" class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                    <input type="text" name="proveedor" id="proveedor" value="<?php echo htmlspecialchars($suministro->proveedor); ?>" required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
            </div>
            
            <div class="flex justify-end">
                <a href="suministros.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg mr-4 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i> Actualizar Suministro
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>