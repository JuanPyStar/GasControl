<?php
include_once 'config/database.php';
include_once 'config/session.php';
include_once 'models/Venta.php';
include_once 'models/Cliente.php';
include_once 'models/Combustible.php';

// Require login
requireLogin();

// Get database connection
$database = new Database(); 
$db = $database->getConnection();

// Create objects
$venta = new Venta($db);
$cliente = new Cliente($db);
$combustible = new Combustible($db);

// Obtener empleados solo si es admin
$empleados = [];
if (isAdmin()) {
    $stmt_empleados = $db->prepare("SELECT id_empleado, nombre FROM empleados ORDER BY nombre ASC");
    $stmt_empleados->execute();
    $empleados = $stmt_empleados->fetchAll(PDO::FETCH_ASSOC);
}

$message = '';
$success = false;
$id_cliente = $_POST['id_cliente'] ?? null;

// 1. Buscar cliente por documento_cliente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['buscar_cliente'])) {
    $documento_cliente = trim($_POST['documento_cliente'] ?? '');
    $stmt = $db->prepare("SELECT id_cliente, nombre, documento_cliente, placa_vehiculo FROM clientes WHERE documento_cliente LIKE ?");
    $stmt->execute(["%$documento_cliente%"]);
    $clientes_encontrados = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 2. Registrar venta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_venta'])) {
    $venta->fecha_hora = date('Y-m-d H:i:s');
    if (isAdmin()) {
        $venta->id_empleado = $_POST['id_empleado'] ?? null;
    } else {
        $venta->id_empleado = getUserId();
    }
    $venta->id_combustible = $_POST['id_combustible'] ?? null;
    $venta->id_cliente = $_POST['id_cliente'] ?? null;
    $venta->galones_vendidos = $_POST['galones_vendidos'] ?? 0;
    $venta->total_pagado = $_POST['total_pagado'] ?? 0;
    $venta->metodo_pago = $_POST['metodo_pago'] ?? 'Efectivo';

    if ($venta->create()) {
        // Update fuel stock
        $combustible->id = $venta->id_combustible;
        $combustible->updateStock($venta->galones_vendidos);

        // Add miles to customer (1 mile per gallon)
        if (!empty($venta->id_cliente)) {
            $cliente->id = $venta->id_cliente;
            $cliente->addMiles($venta->galones_vendidos);
        }

        $message = "Venta registrada exitosamente.";
        $success = true;
    } else {
        $message = "No se pudo registrar la venta.";
    }
}

$documento_cliente_precargado = '';
if (isset($_GET['documento_cliente'])) {
    $documento_cliente_precargado = $_GET['documento_cliente'];
} elseif (isset($_POST['documento_cliente'])) {
    $documento_cliente_precargado = $_POST['documento_cliente'];
}

// Include header
include_once 'includes/header.php';
?>

<!-- Agregar Venta Content -->
<div class="fade-in">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Registrar Nueva Venta</h1>
        <a href="ventas.php" class="text-blue-600 hover:text-blue-800 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Volver a Ventas
        </a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $success ? 'bg-green-100 text-green-700 border-green-400' : 'bg-red-100 text-red-700 border-red-400'; ?>">
            <?php echo $message; ?>
        </div>
        <?php if ($success): ?>
            <script>
                setTimeout(function() {
                    window.location.href = 'ventas.php';
                }, 2000);
            </script>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!isAdmin()): ?>
        <div class="bg-white rounded-xl shadow-lg p-8">
            <?php
            // Paso 1: Buscar cliente
            if (!isset($id_cliente) && !isset($_POST['continuar_venta'])):
            ?>
                <form method="post" action="">
                    <label for="documento_cliente" class="block text-sm font-medium text-gray-700 mb-1">Documento del Cliente</label>
                    <div class="flex gap-2 mb-4">
                        <input type="text" name="documento_cliente" id="documento_cliente" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                            placeholder="Ingrese documento del cliente"
                            value="<?php echo htmlspecialchars($documento_cliente_precargado); ?>">
                        <button type="submit" name="buscar_cliente" class="bg-blue-600 text-white px-3 py-2 rounded text-sm">Buscar</button>
                    </div>
                </form>
                <form method="get" action="clientes_agregar.php" class="mb-4">
                    <input type="hidden" name="documento_cliente" value="<?php echo htmlspecialchars($documento_cliente_precargado); ?>">
                    <button type="submit" class="bg-green-600 text-white px-3 py-2 rounded text-sm">
                        Crear nuevo cliente
                    </button>
                </form>
                <?php if (isset($clientes_encontrados)): ?>
                    <?php if ($clientes_encontrados): ?>
                        <form method="post" action="">
                            <table class="min-w-full text-sm mt-4 mb-4 border border-gray-200 rounded-lg overflow-hidden">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2"></th>
                                        <th class="px-4 py-2 text-left">Nombre</th>
                                        <th class="px-4 py-2 text-left">Documento</th>
                                        <th class="px-4 py-2 text-left">Placa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientes_encontrados as $cli): ?>
                                        <tr class="hover:bg-blue-50">
                                            <td class="px-4 py-2 text-center">
                                                <input type="radio" name="id_cliente" value="<?php echo $cli['id_cliente']; ?>" required>
                                            </td>
                                            <td class="px-4 py-2"><?php echo htmlspecialchars($cli['nombre']); ?></td>
                                            <td class="px-4 py-2"><?php echo htmlspecialchars($cli['documento_cliente']); ?></td>
                                            <td class="px-4 py-2"><?php echo htmlspecialchars($cli['placa_vehiculo']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <button type="submit" name="continuar_venta" class="bg-green-600 text-white px-3 py-2 rounded text-sm">Seleccionar cliente y continuar</button>
                        </form>
                    <?php else: ?>
                        <div class="mt-2 text-red-700">Cliente no encontrado.</div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php
            // Paso 2: Formulario de venta con cliente seleccionado
            elseif (isset($_POST['continuar_venta']) && !empty($_POST['id_cliente'])):
                $id_cliente = $_POST['id_cliente'];
                $stmt = $db->prepare("SELECT nombre, documento_cliente, placa_vehiculo FROM clientes WHERE id_cliente = ?");
                $stmt->execute([$id_cliente]);
                $cliente_seleccionado = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
                <div class="mb-4 p-3 bg-gray-100 rounded">
                    <b>Cliente seleccionado:</b><br>
                    <?php echo htmlspecialchars($cliente_seleccionado['nombre']); ?> |
                    Documento: <?php echo htmlspecialchars($cliente_seleccionado['documento_cliente']); ?> |
                    Placa: <?php echo htmlspecialchars($cliente_seleccionado['placa_vehiculo']); ?>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="id_combustible" class="block text-sm font-medium text-gray-700 mb-1">Combustible</label>
                            <select name="id_combustible" id="id_combustible" required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Seleccione un combustible</option>
                                <?php
                                $stmt_combustibles = $combustible->readAll();
                                while ($row_combustible = $stmt_combustibles->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?php echo $row_combustible['id_combustible']; ?>" 
                                            data-precio="<?php echo $row_combustible['precio_galon']; ?>"
                                            data-stock="<?php echo $row_combustible['stock_actual']; ?>">
                                        <?php echo $row_combustible['tipo'] . ' - $' . $row_combustible['precio_galon'] . ' (Stock: ' . $row_combustible['stock_actual'] . ')'; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label for="galones_vendidos" class="block text-sm font-medium text-gray-700 mb-1">Galones Vendidos</label>
                            <input type="number" name="galones_vendidos" id="galones_vendidos" step="0.01" min="0.01" required
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <p class="mt-1 text-sm text-red-600 hidden" id="stock-warning">¡Advertencia! El stock disponible es insuficiente.</p>
                        </div>
                        <div>
                            <label for="total_pagado" class="block text-sm font-medium text-gray-700 mb-1">Total Pagado</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="total_pagado" id="total_pagado" step="0.01" min="0.01" required
                                       class="block w-full pl-7 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>
                        <div>
                            <label for="metodo_pago" class="block text-sm font-medium text-gray-700 mb-1">Método de Pago</label>
                            <select name="metodo_pago" id="metodo_pago" required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                                <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                                <option value="Transferencia">Transferencia</option>
                            </select>
                        </div>
                        <?php if (isAdmin()): ?>
                            <div>
                                <label for="id_empleado" class="block text-sm font-medium text-gray-700 mb-1">Empleado</label>
                                <select name="id_empleado" id="id_empleado" required
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Seleccione un empleado</option>
                                    <?php foreach ($empleados as $empleado): ?>
                                        <option value="<?php echo $empleado['id_empleado']; ?>">
                                            <?php echo htmlspecialchars($empleado['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex justify-end mt-6">
                        <a href="ventas_agregar.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg mr-4 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" name="registrar_venta" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i> Registrar Venta
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="bg-yellow-100 text-yellow-800 p-4 rounded mb-6">
            Los administradores no pueden registrar ventas.
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const combustibleSelect = document.getElementById('id_combustible');
    const galonesInput = document.getElementById('galones_vendidos');
    const totalInput = document.getElementById('total_pagado');
    const stockWarning = document.getElementById('stock-warning');
    if (combustibleSelect && galonesInput && totalInput) {
        function calculateTotal() {
            const selectedOption = combustibleSelect.options[combustibleSelect.selectedIndex];
            const precio = selectedOption ? parseFloat(selectedOption.dataset.precio) : 0;
            const galones = parseFloat(galonesInput.value) || 0;
            totalInput.value = (precio * galones).toFixed(2);
            const stock = selectedOption ? parseFloat(selectedOption.dataset.stock) : 0;
            if (galones > stock) {
                stockWarning.classList.remove('hidden');
            } else {
                stockWarning.classList.add('hidden');
            }
        }
        combustibleSelect.addEventListener('change', calculateTotal);
        galonesInput.addEventListener('input', calculateTotal);
    }
});
</script>

<?php include_once 'includes/footer.php'; ?>