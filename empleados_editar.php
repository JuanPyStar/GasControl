<?php
include_once 'config/database.php';
include_once 'config/session.php';
include_once 'models/User.php';

// Require admin
requireAdmin();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create user object
$user = new User($db);

// Get ID of user to be edited
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID no encontrado.');

// Set ID property of user to be edited
$user->id_empleado = $id; // <-- Usa id_empleado, no id

// Read the details of user
$user->readOne();

// Process form submission
$message = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Set user property values
    $user->id_empleado = $id; // <-- Asegúrate de esto
    $user->nombre = $_POST['nombre'] ?? '';
    $user->documento = $_POST['documento'] ?? '';
    $user->cargo = $_POST['cargo'] ?? '';
    $user->turno_id = $_POST['turno_id'] ?? '';
    $user->telefono = $_POST['telefono'] ?? '';
    
    // Check if password needs to be updated
    if (!empty($_POST['password'])) {
        $user->password = $_POST['password'];
        $user->updatePassword();
    }
    
    // Update the user
    if ($user->update()) {
        $message = "Empleado actualizado exitosamente.";
        $success = true;
    } else {
        $message = "No se pudo actualizar el empleado.";
    }
}

// Include header
include_once 'includes/header.php';
?>

<!-- Editar Empleado Content -->
<div class="fade-in">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Editar Empleado</h1>
        <a href="empleados.php" class="text-blue-600 hover:text-blue-800 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Volver a Empleados
        </a>
    </div>
    
    <?php if (!empty($message)): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $success ? 'bg-green-100 text-green-700 border-green-400' : 'bg-red-100 text-red-700 border-red-400'; ?>">
            <?php echo $message; ?>
        </div>
        <?php if ($success): ?>
            <script>
                // Redirect to empleados.php after 2 seconds
                setTimeout(function() {
                    window.location.href = 'empleados.php';
                }, 2000);
            </script>
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="bg-white rounded-xl shadow-lg p-8">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $id); ?>" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo</label>
                    <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($user->nombre); ?>" required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="documento" class="block text-sm font-medium text-gray-700 mb-1">Documento de Identidad</label>
                    <input type="text" name="documento" id="documento" value="<?php echo htmlspecialchars($user->documento); ?>" required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="cargo" class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
                    <select name="cargo" id="cargo" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Seleccione un cargo</option>
                        <option value="Empleado" <?php echo $user->cargo == 'Empleado' ? 'selected' : ''; ?>>Empleado</option>
                    </select>
                </div>
                <div>
                    <label for="turno_id" class="block text-sm font-medium text-gray-700 mb-1">Turno</label>
                    <select name="turno_id" id="turno_id" required class="form-control">
                        <option value="">Seleccione un turno</option>
                        <?php
                        $query = "SELECT id_turno, descripcion FROM turnos";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                        ?>
                        <option value="<?= $row['id_turno']; ?>" <?= $user->turno_id == $row['id_turno'] ? 'selected' : ''; ?>>
                            <?= $row['descripcion']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>

                </div>
                
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" value="<?php echo htmlspecialchars($user->telefono); ?>" required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                
                <div class="md:col-span-2">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña (dejar en blanco para mantener la actual)</label>
                    <input type="password" name="password" id="password"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <p class="mt-1 text-sm text-gray-500">La contraseña debe tener al menos 6 caracteres.</p>
                </div>
            </div>
            
            <div class="flex justify-end">
                <a href="empleados.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg mr-4 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i> Actualizar Empleado
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>