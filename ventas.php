<?php
include_once 'config/database.php';
include_once 'config/session.php';
include_once 'models/Venta.php';

// Require login
requireLogin();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create venta object
$venta = new Venta($db);

// If employee, only show their sales
if (isEmployee()) {
    $venta->id_empleado = getUserId();
    $stmt = $venta->readByEmployee();
} else {
    // If admin, show all sales
    $where = [];
    $params = [];

    if (!empty($_GET['combustible'])) {
        $where[] = "v.id_combustible = ?";
        $params[] = $_GET['combustible'];
    }
    if (!empty($_GET['desde'])) {
        $where[] = "v.fecha_hora >= ?";
        $params[] = $_GET['desde'] . " 00:00:00";
    }
    if (!empty($_GET['hasta'])) {
        $where[] = "v.fecha_hora <= ?";
        $params[] = $_GET['hasta'] . " 23:59:59";
    }

    $where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "SELECT v.*, 
                e.nombre AS empleado, 
                c.nombre AS cliente, 
                co.tipo AS combustible 
            FROM ventas v
            LEFT JOIN empleados e ON v.id_empleado = e.id_empleado
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN combustibles co ON v.id_combustible = co.id_combustible
            $where_sql
            ORDER BY v.fecha_hora DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
}

$num = $stmt->rowCount();

// Include header
include_once 'includes/header.php';
?>

<!-- Ventas Content -->
<div class="fade-in">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Registro de Ventas</h1>
        <?php if (!isAdmin()): ?>
        <a href="ventas_agregar.php" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i> Nueva Venta
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Search Form -->
    <?php if (isAdmin()): ?>
    <form method="get" class="flex flex-col md:flex-row md:items-end gap-4 mb-6 w-full">
        <div class="w-full md:w-auto">
            <label for="combustible" class="block text-xs text-gray-600 mb-1">Tipo de Combustible</label>
            <select name="combustible" id="combustible" class="border border-gray-300 rounded px-6 py-3 text-sm w-full md:w-auto">
                <option value="">Todos</option>
                <?php
                $stmt_comb = $db->query("SELECT id_combustible, tipo FROM combustibles");
                while ($c = $stmt_comb->fetch(PDO::FETCH_ASSOC)):
                ?>
                <option value="<?php echo $c['id_combustible']; ?>" <?php if(isset($_GET['combustible']) && $_GET['combustible'] == $c['id_combustible']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($c['tipo']); ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="w-full md:w-auto">
            <label class="block text-xs text-gray-600 mb-1">Desde</label>
            <input type="date" name="desde" value="<?php echo $_GET['desde'] ?? ''; ?>" class="border border-gray-300 rounded px-6 py-3 text-sm w-full md:w-auto">
        </div>
        <div class="w-full md:w-auto">
            <label class="block text-xs text-gray-600 mb-1">Hasta</label>
            <input type="date" name="hasta" value="<?php echo $_GET['hasta'] ?? ''; ?>" class="border border-gray-300 rounded px-6 py-3 text-sm w-full md:w-auto">
        </div>
        <div class="flex items-end gap-2 w-full md:w-auto">
            <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Buscar
            </button>
            <?php if (!empty($_GET['combustible']) || !empty($_GET['desde']) || !empty($_GET['hasta'])): ?>
            <a href="ventas.php"
               class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Limpiar
            </a>
            <?php endif; ?>
        </div>
    </form>
    <?php endif; ?>

    <!-- Ventas Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <?php if ($num > 0): ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Empleado
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cliente
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Combustible
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Galones
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Método
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
                                    <?php echo $row['id_venta']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php 
                                            $date = new DateTime($row['fecha_hora']);
                                            echo $date->format('d/m/Y H:i'); 
                                        ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $row['empleado'] ?? 'N/A'; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $row['cliente'] ?? 'N/A'; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $row['combustible'] ?? 'N/A'; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $row['galones_vendidos'] ?? 'N/A'; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium">$<?php echo number_format($row['total_pagado'] ?? 0, 2); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $row['metodo_pago'] ?? 'N/A'; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="ventas_ver.php?id=<?php echo $row['id_venta']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <?php if (isAdmin()): ?>
                                    <a href="ventas_eliminar.php?id=<?php echo $row['id_venta']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Está seguro de eliminar esta venta?')">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="p-8 text-center">
                    <div class="inline-flex rounded-full bg-yellow-100 p-4 mb-4">
                        <div class="rounded-full bg-yellow-200 p-4">
                            <i class="fas fa-shopping-cart text-yellow-500 text-2xl"></i>
                        </div>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron ventas</h3>
                    <p class="text-gray-500 mb-6">
                        Aún no se han registrado ventas en el sistema.
                    </p>
                    <a href="ventas_agregar.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i> Registrar Venta
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>