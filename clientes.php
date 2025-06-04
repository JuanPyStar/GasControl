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

// Query clientes
$search = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($search)) {
    $stmt = $cliente->search($search);
} else {
    $stmt = $cliente->readAll();
}

$num = $stmt->rowCount();

// Include header
include_once 'includes/header.php';
?>

<!-- Clientes Content -->
<div class="fade-in">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Clientes</h1>
        <a href="clientes_agregar.php" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i> Nuevo Cliente
        </a>
    </div>

    <!-- Search & Filter SIN fondo blanco -->
    <form action="clientes.php" method="GET" class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <label for="search" class="sr-only">Buscar</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                       placeholder="Buscar por nombre,documento, placa o teléfono">
            </div>
        </div>
        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Buscar
        </button>
        <?php if (!empty($search)): ?>
        <a href="clientes.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Limpiar
        </a>
        <?php endif; ?>
    </form>

    <?php
    // Mostrar mensaje si viene por GET
    if (isset($_GET['message'])):
        $success = isset($_GET['success']) && $_GET['success'] == '1';
    ?>
        <div id="alerta-msg" class="mb-4 p-4 rounded-lg <?php echo $success ? 'bg-green-100 text-green-700 border-green-400' : 'bg-red-100 text-red-700 border-red-400'; ?>">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
        <script>
        setTimeout(function() {
            var alerta = document.getElementById('alerta-msg');
            if (alerta) alerta.style.display = 'none';
        }, 3500);
        </script>
    <?php endif; ?>

    <!-- Clientes Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <?php if ($num > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nombre
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Documento
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Placa
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Teléfono
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Millas
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $row['id_cliente']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $row['nombre']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $row['documento_cliente']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $row['placa_vehiculo']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $row['telefono']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm bg-blue-100 text-blue-800 py-1 px-2 rounded-full inline-block">
                                        <i class="fas fa-award mr-1"></i> <?php echo $row['millas'] ?? 0; ?> pts
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="clientes_editar.php?id=<?php echo $row['id_cliente']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="clientes_eliminar.php?id=<?php echo $row['id_cliente']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Está seguro de eliminar este cliente?')">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-8 text-center">
                <div class="inline-flex rounded-full bg-yellow-100 p-4 mb-4">
                    <div class="rounded-full bg-yellow-200 p-4">
                        <i class="fas fa-users text-yellow-500 text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron clientes</h3>
                <p class="text-gray-500 mb-6">
                    <?php echo !empty($search) ? 'No hay clientes que coincidan con su búsqueda.' : 'Aún no se han registrado clientes en el sistema.'; ?>
                </p>
                <a href="clientes_agregar.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> Registrar Cliente
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>