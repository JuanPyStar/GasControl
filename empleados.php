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

// Search parameters
$q = $_GET['q'] ?? '';
$turno = $_GET['turno'] ?? '';
$fecha_ingreso = $_GET['fecha_ingreso'] ?? '';

$where = [];
$params = [];

if ($q) {
    $where[] = "(nombre LIKE :q OR documento LIKE :q)";
    $params[':q'] = "%$q%";
}
if ($turno) {
    $where[] = "t.descripcion = :turno";
    $params[':turno'] = $turno;
}
if ($fecha_ingreso) {
    $where[] = "fecha_ingreso = :fecha_ingreso";
    $params[':fecha_ingreso'] = $fecha_ingreso;
}

$sql = "SELECT e.*, t.descripcion AS turno_descripcion 
        FROM empleados e 
        LEFT JOIN turnos t ON e.turno_id = t.id_turno";
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY e.nombre ASC";
$stmt = $db->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->execute();
$num = $stmt->rowCount();

// Include header
include_once 'includes/header.php';
?>

<!-- Empleados Content -->
<div class="fade-in">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Empleados</h1>
        <a href="empleados_agregar.php" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i> Nuevo Empleado
        </a>
    </div>
    
    <!-- Search Form -->
    <form method="get" class="mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>"
                    placeholder="Buscar por nombre o documento"
                    class="block w-80 pl-10 pr-3 h-11 border border-gray-300 rounded-md shadow-sm placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                />
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700">Turno</label>
            <select name="turno" class="px-3 h-11 border border-gray-300 rounded-md shadow-sm">
                <option value="">Todos</option>
                <option value="Mañana" <?php if(($_GET['turno'] ?? '')=='Mañana') echo 'selected'; ?>>Mañana</option>
                <option value="Tarde" <?php if(($_GET['turno'] ?? '')=='Tarde') echo 'selected'; ?>>Tarde</option>
                <option value="Noche" <?php if(($_GET['turno'] ?? '')=='Noche') echo 'selected'; ?>>Noche</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700">Fecha de Ingreso</label>
            <input type="date" name="fecha_ingreso" value="<?php echo htmlspecialchars($_GET['fecha_ingreso'] ?? ''); ?>"
                class="px-3 h-11 border border-gray-300 rounded-md shadow-sm" />
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 h-11 rounded-md">Buscar</button>
        <?php if (!empty($_GET['q']) || !empty($_GET['turno']) || !empty($_GET['fecha_ingreso'])): ?>
            <a href="empleados.php"
               class="inline-flex items-center px-4 h-11 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 ml-2"
               style="display:inline-flex;align-items:center;">
                Limpiar
            </a>
        <?php endif; ?>
    </form>

    <!-- Mensaje de éxito o error -->
    <?php if (isset($_GET['message'])): ?>
        <div id="alerta-msg" class="mb-4 p-4 rounded-lg <?php echo (isset($_GET['success']) && $_GET['success'] == '1') ? 'bg-green-100 text-green-700 border-green-400' : 'bg-red-100 text-red-700 border-red-400'; ?>">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
        <script>
            setTimeout(function() {
                var alerta = document.getElementById('alerta-msg');
                if (alerta) alerta.style.display = 'none';
            }, 3500); // 3.5 segundos
        </script>
    <?php endif; ?>

    <!-- Empleados Table -->
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
                                Cargo
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Turno
                            </th>    
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Teléfono
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha Ingreso
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
                                    <?php echo $row['id_empleado']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $row['nombre']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $row['documento']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm px-2 py-1 rounded-full inline-block 
                                        <?php echo $row['cargo'] === 'Administrador' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'; ?>">
                                        <?php echo $row['cargo']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $row['turno_descripcion']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $row['telefono']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php 
                                            $date = new DateTime($row['fecha_ingreso']);
                                            echo $date->format('d/m/Y'); 
                                        ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="empleados_editar.php?id=<?php echo $row['id_empleado']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="empleados_eliminar.php?id=<?php echo $row['id_empleado']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Está seguro de eliminar este empleado?')">
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
                        <i class="fas fa-user-tie text-yellow-500 text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron empleados</h3>
                <p class="text-gray-500 mb-6">
                    Aún no se han registrado empleados en el sistema.
                </p>
                <a href="empleados_agregar.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> Registrar Empleado
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>