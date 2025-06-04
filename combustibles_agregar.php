<?php
include_once 'config/database.php';
include_once 'config/session.php';
include_once 'models/Combustible.php';

// Require admin
requireAdmin();


// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create combustible object
$combustible = new Combustible($db);

// Process form submission
$message = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Solo guardar tipo y precio_galon
    $combustible->tipo = $_POST['tipo'] ?? '';
    $combustible->precio_galon = $_POST['precio_galon'] ?? 0;

    if ($combustible->create()) {
        $message = "Combustible registrado exitosamente.";
        $success = true;
    } else {
        $message = "No se pudo registrar el combustible.";
    }
}

// Include header
include_once 'includes/header.php';
?>

<!-- Agregar Combustible Content -->
<div class="fade-in">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Registrar Nuevo Combustible</h1>
        <a href="combustibles.php" class="text-blue-600 hover:text-blue-800 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Volver a Combustibles
        </a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $success ? 'bg-green-100 text-green-700 border-green-400' : 'bg-red-100 text-red-700 border-red-400'; ?>">
            <?php echo $message; ?>
        </div>
        <?php if ($success): ?>
            <script>
                setTimeout(function() {
                    window.location.href = 'combustibles.php';
                }, 2000);
            </script>
        <?php endif; ?>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-lg p-8">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Combustible</label>
                    <input type="text" name="tipo" id="tipo" required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="Ej: Corriente, Extra, Diesel, etc.">
                </div>
                <div>
                    <label for="precio_galon" class="block text-sm font-medium text-gray-700 mb-1">Precio por Galón</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="precio_galon" id="precio_galon" step="0.01" min="0.01" required
                               class="block w-full pl-7 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                </div>
            </div>
            <div class="flex justify-end">
                <a href="combustibles.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg mr-4 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i> Guardar Combustible
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>