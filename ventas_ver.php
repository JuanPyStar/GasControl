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

// Get ID of venta to be viewed
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID no encontrado.');

// Set ID property of venta to be viewed
$venta->id = $id;

// Read the details of venta
$venta->readOne();

// Include header
include_once 'includes/header.php';

// Initialize empleado and cliente names
$empleado_nombre = '';
$cliente_nombre = '';

// Fetch empleado name
if (!empty($venta->id_empleado)) {
    $stmt = $db->prepare("SELECT nombre FROM empleados WHERE id_empleado = ?");
    $stmt->execute([$venta->id_empleado]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $empleado_nombre = $row['nombre'];
    }
}

// Fetch cliente name
if (!empty($venta->id_cliente)) {
    $stmt = $db->prepare("SELECT nombre FROM clientes WHERE id_cliente = ?");
    $stmt->execute([$venta->id_cliente]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $cliente_nombre = $row['nombre'];
    }
}
?>

<!-- Ver Venta Content -->
<div class="fade-in">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Detalles de Venta #<?php echo $venta->id; ?></h1>
        <a href="ventas.php" class="text-blue-600 hover:text-blue-800 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Volver a Ventas
        </a>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="mb-8 pb-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Información de Venta</h2>
                    <p class="text-gray-600">Detalles completos de la transacción</p>
                </div>
                <div class="bg-blue-100 text-blue-800 py-1 px-3 rounded-full text-sm font-medium">
                    <?php 
                        $date = new DateTime($venta->fecha_hora);
                        echo $date->format('d/m/Y H:i'); 
                    ?>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-md font-semibold text-gray-700 mb-4">Detalles de la Transacción</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">ID Venta:</span>
                        <span class="font-medium"><?php echo $venta->id; ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Fecha y Hora:</span>
                        <span class="font-medium">
                            <?php 
                                $date = new DateTime($venta->fecha_hora);
                                echo $date->format('d/m/Y H:i'); 
                            ?>
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Empleado:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($empleado_nombre); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Método de Pago:</span>
                        <span class="font-medium"><?php echo $venta->metodo_pago; ?></span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-md font-semibold text-gray-700 mb-4">Detalles del Producto</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Combustible:</span>
                        <span class="font-medium"><?php echo $venta->id_combustible; ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Galones Vendidos:</span>
                        <span class="font-medium"><?php echo number_format($venta->galones_vendidos, 2); ?> gal</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Precio por Galón:</span>
                        <span class="font-medium">
                            $<?php 
                                echo number_format(
                                    $venta->total_pagado / $venta->galones_vendidos, 
                                    2
                                ); 
                            ?>
                        </span>
                    </div>
                    
                    <div class="flex justify-between text-lg font-bold">
                        <span class="text-gray-800">Total Pagado:</span>
                        <span class="text-blue-600">$<?php echo number_format($venta->total_pagado, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-md font-semibold text-gray-700 mb-4">Información del Cliente</h3>
            
            <?php if (!empty($venta->id_cliente)): ?>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium">Cliente: <?php echo htmlspecialchars($cliente_nombre); ?></p>
                            <p class="text-sm text-gray-600">
                                Este cliente recibió <?php echo number_format($venta->galones_vendidos, 0); ?> puntos de millas por esta compra.
                            </p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-gray-600 italic">No se registró cliente para esta venta.</p>
            <?php endif; ?>
        </div>
        
        <div class="mt-8 flex gap-2">
            <a href="ventas.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors">
                Volver a la Lista
            </a>
            <button type="button"
                onclick="window.print()"
                class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                <i class="fas fa-print mr-2"></i> Imprimir Recibo
            </button>
            <?php if (isAdmin()): ?>
            <a href="ventas_eliminar.php?id=<?php echo $venta->id; ?>" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors" onclick="return confirm('¿Está seguro de eliminar esta venta?')">
                <i class="fas fa-trash-alt mr-2"></i> Eliminar Venta
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    .bg-white.rounded-xl.shadow-lg.p-8, 
    .bg-white.rounded-xl.shadow-lg.p-8 * {
        visibility: visible !important;
    }
    .bg-white.rounded-xl.shadow-lg.p-8 {
        position: absolute !important;
        left: 0; top: 0; width: 100% !important; margin: 0 !important; box-shadow: none !important; border-radius: 0 !important;
    }
    /* Oculta botones y acciones */
    .mt-8.flex, .mt-8.flex * {
        display: none !important;
    }
    /* Si tienes encabezados fuera del recuadro, ocúltalos también */
    .fade-in > .flex.justify-between { display: none !important; }
}
</style>

<?php include_once 'includes/footer.php'; ?>