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

// Search parameters
$fecha = $_GET['fecha'] ?? '';
$proveedor = $_GET['proveedor'] ?? '';

// Where clause and parameters for the query
$where = [];
$params = [];

// Build the query based on search parameters
if ($fecha) {
    $where[] = "DATE(fecha_recepcion) = :fecha";
    $params[':fecha'] = $fecha;
}
if ($proveedor) {
    $where[] = "proveedor LIKE :proveedor";
    $params[':proveedor'] = "%$proveedor%";
}

$sql = "SELECT s.*, c.tipo AS combustible
        FROM suministros s
        LEFT JOIN combustibles c ON s.id_combustible = c.id_combustible";
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY s.fecha_recepcion DESC";

// Prepare and execute the query
$stmt = $db->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->execute();
$num = $stmt->rowCount();

// Include header
include_once 'includes/header.php';
?>

<!-- Suministros Content -->
<div class="fade-in">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Suministros</h1>
        <a href="suministros_agregar.php" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i> Nuevo Suministro
        </a>
    </div>
    
    <!-- Search Form -->
    <form method="get" class="mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="proveedor" value="<?php echo htmlspecialchars($_GET['proveedor'] ?? ''); ?>" 
                    placeholder="Buscar por proveedor"
                    class="block w-64 pl-10 pr-3 h-11 border border-gray-300 rounded-md shadow-sm placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700">Fecha de Recepción</label>
            <input type="date" name="fecha" value="<?php echo htmlspecialchars($_GET['fecha'] ?? ''); ?>"
                class="px-3 h-11 border border-gray-300 rounded-md shadow-sm" />
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 h-11 rounded-md">Buscar</button>
        <?php if (!empty($_GET['fecha']) || !empty($_GET['proveedor'])): ?>
            <a href="suministros.php"
               class="inline-flex items-center px-4 h-11 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 ml-2"
               style="display:inline-flex;align-items:center;">
                Limpiar
            </a>
        <?php endif; ?>
    </form>

    <!-- Suministros Table -->
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
                                Combustible
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cantidad Recibida
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha de Recepción
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Proveedor
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
                                    <?php echo $row['id_suministro']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $row['combustible']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo number_format($row['cantidad_recibida'], 2); ?> gal</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php 
                                            $date = new DateTime($row['fecha_recepcion']);
                                            echo $date->format('d/m/Y H:i'); 
                                        ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $row['proveedor']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="suministros_editar.php?id=<?php echo $row['id_suministro']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="suministros_eliminar.php?id=<?php echo $row['id_suministro']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Está seguro de eliminar este suministro? Esto ajustará el inventario de combustible.')">
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
                        <i class="fas fa-truck-loading text-yellow-500 text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron suministros</h3>
                <p class="text-gray-500 mb-6">
                    Aún no se han registrado suministros de combustible en el sistema.
                </p>
                <a href="suministros_agregar.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> Registrar Suministro
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>