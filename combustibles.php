<?php
include_once 'config/database.php';
include_once 'config/session.php';
include_once 'models/Combustible.php';

// Require Admin         
requireAdmin();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create combustible object
$combustible = new Combustible($db);

// Search functionality
$buscar = $_GET['buscar'] ?? '';
$estado = $_GET['estado'] ?? '';
$query = "SELECT c.* FROM combustibles c WHERE 1=1";

$params = [];
if ($buscar !== '') {
    $query .= " AND c.tipo LIKE ?";
    $like = "%$buscar%";
    $params[] = $like;
}

if ($estado !== '') {
    $query .= " AND c.stock_actual ";
    if ($estado == 'Suficiente') {
        $query .= "> 20";
    } else if ($estado == 'Bajo') {
        $query .= " BETWEEN 11 AND 20";
    } else if ($estado == 'Crítico') {
        $query .= "<= 10";
    }
}

$query .= " ORDER BY c.id_combustible DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$num = $stmt->rowCount();

// Include header
include_once 'includes/header.php';
?>

<!-- Combustibles Content -->
<div class="fade-in">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Combustibles</h1>
        <a href="combustibles_agregar.php" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i> Nuevo Combustible
        </a>
    </div>
    
    <!-- Barra de búsqueda -->
    <form method="get" class="flex flex-col md:flex-row gap-4 mb-6 w-full">
        <div class="relative w-full md:w-1/3">
            <input type="text" name="buscar" value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>"
                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-200"
                placeholder="Buscar por tipo">
            <span class="absolute left-3 top-2.5 text-gray-400">
                <i class="fas fa-search"></i>
            </span>
        </div>
        <div>
            <select name="estado" class="border border-gray-300 rounded px-4 py-2 text-sm">
                <option value="">Todos los estados</option>
                <option value="Suficiente" <?php if(($_GET['estado'] ?? '') == 'Suficiente') echo 'selected'; ?>>Suficiente</option>
                <option value="Bajo" <?php if(($_GET['estado'] ?? '') == 'Bajo') echo 'selected'; ?>>Bajo</option>
                <option value="Crítico" <?php if(($_GET['estado'] ?? '') == 'Crítico') echo 'selected'; ?>>Crítico</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">Buscar</button>
            <?php if (!empty($_GET['buscar']) || !empty($_GET['estado'])): ?>
            <a href="combustibles.php"
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Limpiar
            </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Mensaje de éxito o error -->
    <?php
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

    <!-- Combustibles Table -->
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
                                Tipo
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Precio por Galón
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Stock Actual
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <?php 
                                // Determine stock status
                                $stock_status = 'bg-green-100 text-green-800';
                                $stock_label = 'Suficiente';
                                
                                if ($row['stock_actual'] <= 10) {
                                    $stock_status = 'bg-red-100 text-red-800';
                                    $stock_label = 'Crítico';
                                } else if ($row['stock_actual'] <= 20) {
                                    $stock_status = 'bg-yellow-100 text-yellow-800';
                                    $stock_label = 'Bajo';
                                }

                                $buscar = strtolower($_GET['buscar'] ?? '');
                                $tipo = strtolower($row['tipo']);
                                $estadoSel = $_GET['estado'] ?? '';

                                // Si hay búsqueda por tipo y no coincide, saltar
                                if ($buscar && strpos($tipo, $buscar) === false) {
                                    continue;
                                }
                                // Si hay filtro de estado y no coincide, saltar
                                if ($estadoSel && $stock_label !== $estadoSel) {
                                    continue;
                                }
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $row['id_combustible']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $row['tipo']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">$<?php echo number_format($row['precio_galon'], 2); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo number_format($row['stock_actual'], 2); ?> gal</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $stock_status; ?>">
                                        <?php echo $stock_label; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="combustibles_editar.php?id=<?php echo $row['id_combustible']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <?php if (isAdmin()): ?>
                                    <a href="combustibles_eliminar.php?id=<?php echo $row['id_combustible']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Está seguro de eliminar este combustible?')">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                    <?php endif; ?>
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
                        <i class="fas fa-oil-can text-yellow-500 text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron combustibles</h3>
                <p class="text-gray-500 mb-6">
                    Aún no se han registrado combustibles en el sistema.
                </p>
                <a href="combustibles_agregar.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> Registrar Combustible
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>