<?php
include_once 'config/database.php';
include_once 'config/session.php';
include_once 'models/Venta.php';
include_once 'models/Combustible.php';
include_once 'models/Cliente.php';

requireAdmin();
// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create objects
$venta = new Venta($db);
$combustible = new Combustible($db);
$cliente = new Cliente($db);

// Determinar rango de fechas
$sales_range = $_GET['sales_range'] ?? '7';
$from = null;
$to = null;
$today = new DateTime();

switch ($sales_range) {
    case '30':
        $from = (clone $today)->modify('-29 days')->format('Y-m-d');
        $to = $today->format('Y-m-d');
        break;
    case 'mes_actual':
        $from = $today->format('Y-m-01');
        $to = $today->format('Y-m-d');
        break;
    case 'mes_anterior':
        $from = (clone $today)->modify('first day of last month')->format('Y-m-d');
        $to = (clone $today)->modify('last day of last month')->format('Y-m-d');
        break;
    case 'personalizado':
        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        // Si falta alguna fecha, usa el rango de los últimos 7 días como fallback
        if (empty($from) || empty($to)) {
            $from = (clone $today)->modify('-6 days')->format('Y-m-d');
            $to = $today->format('Y-m-d');
        }
        break;
    case '7':
    default:
        $from = (clone $today)->modify('-6 days')->format('Y-m-d');
        $to = $today->format('Y-m-d');
        break;
}

// Pasa $from y $to a tu método getStats o crea un método getStatsByRange($from, $to)
$stats = $venta->getStatsByRange($from, $to);

// Get all fuels
$stmt_combustibles = $combustible->readAll();
$num_combustibles = $stmt_combustibles->rowCount();

// Get all customers
$stmt_clientes = $cliente->readAll();
$num_clientes = $stmt_clientes->rowCount();

// Include header
include_once 'includes/header.php';
?>

<!-- Dashboard Content -->
<div class="fade-in">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-sm text-gray-600"><?php echo date('d M, Y'); ?></p>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card bg-white rounded-xl shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="rounded-full bg-blue-100 p-3">
                    <i class="fas fa-shopping-cart text-blue-500"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Total Ventas</h3>
                    <p class="text-2xl font-bold"><?php echo $stats['total_sales'] ?? 0; ?></p>
                </div>
            </div>
        </div>
        
        <div class="card bg-white rounded-xl shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="rounded-full bg-green-100 p-3">
                    <i class="fas fa-dollar-sign text-green-500"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Ingresos Totales</h3>
                    <p class="text-2xl font-bold">$<?php echo number_format($stats['total_income'] ?? 0, 2); ?></p>
                </div>
            </div>
        </div>
        
        <div class="card bg-white rounded-xl shadow p-6 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="rounded-full bg-purple-100 p-3">
                    <i class="fas fa-gas-pump text-purple-500"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Galones Vendidos</h3>
                    <p class="text-2xl font-bold"><?php echo number_format($stats['total_gallons'] ?? 0, 2); ?></p>
                </div>
            </div>
        </div>
        
        <div class="card bg-white rounded-xl shadow p-6 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="rounded-full bg-yellow-100 p-3">
                    <i class="fas fa-users text-yellow-500"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Clientes</h3>
                    <p class="text-2xl font-bold"><?php echo $num_clientes; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Date Range Filter -->
    <form id="sales-range-form" class="flex flex-wrap gap-2 mb-4 items-end">
        <label for="range" class="text-sm text-gray-700">Rango:</label>
        <select id="sales-range" name="sales_range" class="border border-gray-300 rounded px-4 py-2 text-sm h-[40px]">
            <option value="7" <?php if(($sales_range ?? '7') == '7') echo 'selected'; ?>>Últimos 7 días</option>
            <option value="30" <?php if(($sales_range ?? '') == '30') echo 'selected'; ?>>Últimos 30 días</option>
            <option value="mes_actual" <?php if(($sales_range ?? '') == 'mes_actual') echo 'selected'; ?>>Este mes</option>
            <option value="mes_anterior" <?php if(($sales_range ?? '') == 'mes_anterior') echo 'selected'; ?>>Mes anterior</option>
            <option value="personalizado" <?php if(($sales_range ?? '') == 'personalizado') echo 'selected'; ?>>Personalizado</option>
        </select>
        <input type="date" id="sales-from" name="from" value="<?php echo htmlspecialchars($_GET['from'] ?? ''); ?>" class="border border-gray-300 rounded px-4 py-2 text-sm h-[40px] hidden">
        <span id="a-label" class="text-gray-600 text-sm hidden">a</span>
        <input type="date" id="sales-to" name="to" value="<?php echo htmlspecialchars($_GET['to'] ?? ''); ?>" class="border border-gray-300 rounded px-4 py-2 text-sm h-[40px] hidden">
        <?php
        // Mostrar Limpiar solo si el filtro no es el default
        $showClear = (
            ($sales_range ?? '7') !== '7' ||
            !empty($_GET['from']) ||
            !empty($_GET['to'])
        );
        if ($showClear): ?>
        <a href="dashboard.php"
           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 h-[40px]">
            Limpiar
        </a>
        <?php endif; ?>
    </form>
    
    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Sales by Day Chart -->
        <div class="card bg-white rounded-xl shadow p-6">
            <div class="flex justify-between items-center mb-2">
                <h3 id="sales-chart-title" class="text-lg font-bold text-gray-800">Ventas por Día (Últimos 7 días)</h3>
                <button id="export-sales-btn" class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded flex items-center gap-1">
                    <i class="fa-solid fa-file-pdf"></i> Exportar
                </button>
            </div>
            <div>
                <canvas id="salesChart" height="300"></canvas>
            </div>
        </div>
        
        <!-- Sales by Fuel Type Chart -->
        <div class="card bg-white rounded-xl shadow p-6">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-lg font-bold text-gray-800">Ventas por Tipo de Combustible</h3>
                <button id="export-fuel-btn" class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded flex items-center gap-1">
                    <i class="fa-solid fa-file-pdf"></i> Exportar
                </button>
            </div>
            <div>
                <canvas id="fuelChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Fuel Stock -->
    <div class="card bg-white rounded-xl shadow p-6 mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Stock de Combustible</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left">Tipo</th>
                        <th class="py-3 px-4 text-left">Precio por Galón</th>
                        <th class="py-3 px-4 text-left">Stock Actual</th>
                        <th class="py-3 px-4 text-left">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($row = $stmt_combustibles->fetch(PDO::FETCH_ASSOC)): ?>
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
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4"><?php echo $row['tipo']; ?></td>
                            <td class="py-3 px-4">$<?php echo number_format($row['precio_galon'], 2); ?></td>
                            <td class="py-3 px-4"><?php echo number_format($row['stock_actual'], 2); ?> gal</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $stock_status; ?>">
                                    <?php echo $stock_label; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (!isAdmin()): ?>
        <!-- Solo empleados: Registrar Venta -->
        <div class="card bg-gradient-to-r from-blue-500 to-blue-700 rounded-xl shadow p-6 text-white">
            <h3 class="text-lg font-bold mb-4">Registrar Venta</h3>
            <p class="mb-4">Registre una nueva venta de combustible y actualice el inventario.</p>
            <a href="ventas_agregar.php" class="inline-block bg-white text-blue-700 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition-colors">
                Registrar ahora
            </a>
        </div>
        <?php else: ?>
        <!-- Solo admin: Reportes de Ventas -->
        <div class="card bg-gradient-to-r from-blue-500 to-blue-700 rounded-xl shadow p-6 text-white">
            <h3 class="text-lg font-bold mb-4">Reportes de Ventas</h3>
            <p class="mb-4">Genere y descargue reportes detallados de ventas por fecha, combustible y más.</p>
            <a href="reportes.php" class="inline-block bg-white text-blue-700 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition-colors">
                Ir a reportes
            </a>
        </div>
        <?php endif; ?>

        <div class="card bg-gradient-to-r from-purple-500 to-purple-700 rounded-xl shadow p-6 text-white">
            <h3 class="text-lg font-bold mb-4">Añadir Cliente</h3>
            <p class="mb-4">Registre un nuevo cliente en el sistema para acumular millas.</p>
            <a href="clientes_agregar.php" class="inline-block bg-white text-purple-700 px-4 py-2 rounded-lg font-medium hover:bg-purple-50 transition-colors">
                Añadir cliente
            </a>
        </div>

        <?php if (isAdmin()): ?>
        <div class="card bg-gradient-to-r from-green-500 to-green-700 rounded-xl shadow p-6 text-white">
            <h3 class="text-lg font-bold mb-4">Gestionar Suministros</h3>
            <p class="mb-4">Registre nuevos suministros de combustible y actualice el inventario.</p>
            <a href="suministros.php" class="inline-block bg-white text-green-700 px-4 py-2 rounded-lg font-medium hover:bg-green-50 transition-colors">
                Ir a suministros
            </a>
        </div>
        <?php else: ?>
        <div class="card bg-gradient-to-r from-yellow-500 to-yellow-700 rounded-xl shadow p-6 text-white">
            <h3 class="text-lg font-bold mb-4">Consultar Clientes</h3>
            <p class="mb-4">Busque clientes y verifique sus millas acumuladas.</p>
            <a href="clientes.php" class="inline-block bg-white text-yellow-700 px-4 py-2 rounded-lg font-medium hover:bg-yellow-50 transition-colors">
                Ver clientes
            </a>
        </div>
        <?php endif; ?>
    </div>



<script>
// Sales Chart
const salesData = <?php 
    $labels = [];
    $values = [];
    
    if (isset($stats['by_day'])) {
        foreach ($stats['by_day'] as $day) {
            $date = new DateTime($day['sale_date']);
            $labels[] = $date->format('d M');
            $values[] = $day['total_income'];
        }
    }
    
    echo json_encode([
        'labels' => $labels,
        'values' => $values
    ]);
?>;

const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: salesData.labels,
        datasets: [{
            label: 'Ventas ($)',
            data: salesData.values,
            backgroundColor: 'rgba(59, 130, 246, 0.2)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value;
                    }
                }
            }
        }
    }
});

// Fuel Chart
const fuelData = <?php 
    $labels = [];
    $values = [];
    $colors = ['rgba(59, 130, 246, 0.8)', 'rgba(16, 185, 129, 0.8)', 'rgba(139, 92, 246, 0.8)', 'rgba(245, 158, 11, 0.8)'];
    
    if (isset($stats['by_fuel_type'])) {
        foreach ($stats['by_fuel_type'] as $index => $fuel) {
            $labels[] = $fuel['tipo'];
            $values[] = $fuel['total_income'];
        }
    }
    
    echo json_encode([
        'labels' => $labels,
        'values' => $values,
        'colors' => array_slice($colors, 0, count($labels))
    ]);
?>;

const fuelCtx = document.getElementById('fuelChart').getContext('2d');
new Chart(fuelCtx, {
    type: 'doughnut',
    data: {
        labels: fuelData.labels,
        datasets: [{
            data: fuelData.values,
            backgroundColor: fuelData.colors,
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        return `${label}: $${value}`;
                    }
                }
            }
        }
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rangeSelect = document.getElementById('sales-range');
    const fromInput = document.getElementById('sales-from');
    const toInput = document.getElementById('sales-to');
    const aLabel = document.getElementById('a-label');
    const form = document.getElementById('sales-range-form');

    function toggleCustomDates() {
        if (rangeSelect.value === 'personalizado') {
            fromInput.classList.remove('hidden');
            toInput.classList.remove('hidden');
            aLabel.classList.remove('hidden');
        } else {
            fromInput.classList.add('hidden');
            toInput.classList.add('hidden');
            aLabel.classList.add('hidden');
        }
    }
    rangeSelect.addEventListener('change', function() {
        toggleCustomDates();
        // Si no es personalizado, envía el formulario automáticamente
        if (rangeSelect.value !== 'personalizado') {
            form.submit();
        }
    });
    toggleCustomDates();

    // Si es personalizado, envía el formulario al cambiar las fechas
    fromInput.addEventListener('change', function() {
        if (rangeSelect.value === 'personalizado' && fromInput.value && toInput.value) {
            form.submit();
        }
    });
    toInput.addEventListener('change', function() {
        if (rangeSelect.value === 'personalizado' && fromInput.value && toInput.value) {
            form.submit();
        }
    });

    // Cambiar el título del gráfico según el rango seleccionado
    const salesChartTitle = document.getElementById('sales-chart-title');
    const rangeTitles = {
        '7': 'Ventas por Día (Últimos 7 días)',
        '30': 'Ventas por Día (Últimos 30 días)',
        'mes_actual': 'Ventas por Día (Mes Actual)',
        'mes_anterior': 'Ventas por Día (Mes Anterior)',
        'personalizado': 'Ventas por Día (Personalizado)'
    };

    function updateChartTitle() {
        const value = rangeSelect.value;
        salesChartTitle.textContent = rangeTitles[value] || 'Ventas por Día';
    }

    rangeSelect.addEventListener('change', updateChartTitle);
    updateChartTitle();
});
</script>

<!-- Agrega esto antes de cerrar </body> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
document.getElementById('export-pdf-btn').addEventListener('click', function() {
    // Selecciona solo la sección de los gráficos
    const chartsSection = document.querySelector('.grid.grid-cols-1.lg\\:grid-cols-2.gap-6.mb-8');
    html2canvas(chartsSection).then(function(canvas) {
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jspdf.jsPDF({
            orientation: 'landscape',
            unit: 'pt',
            format: [canvas.width, canvas.height]
        });
        pdf.addImage(imgData, 'PNG', 0, 0, canvas.width, canvas.height);
        pdf.save('graficos_dashboard.pdf');
    });
});
</script>

<script>
document.getElementById('export-sales-btn').addEventListener('click', function() {
    const chartCard = document.getElementById('salesChart').parentElement;
    const chartTitle = document.getElementById('sales-chart-title').textContent;
    html2canvas(chartCard).then(function(canvas) {
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jspdf.jsPDF({
            orientation: 'landscape',
            unit: 'pt',
            format: [canvas.width, canvas.height + 140]
        });
        pdf.setFontSize(18);
        pdf.text(chartTitle, 40, 40);
        pdf.addImage(imgData, 'PNG', 0, 100, canvas.width, canvas.height);
        pdf.save('ventas_por_dia.pdf');
    });
});

document.getElementById('export-fuel-btn').addEventListener('click', function() {
    const chartCard = document.getElementById('fuelChart').parentElement;
    const chartTitle = "Ventas por Tipo de Combustible";
    html2canvas(chartCard).then(function(canvas) {
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jspdf.jsPDF({
            orientation: 'landscape',
            unit: 'pt',
            format: [canvas.width, canvas.height + 140]
        });
        pdf.setFontSize(18);
        pdf.text(chartTitle, 40, 40);
        pdf.addImage(imgData, 'PNG', 0, 100, canvas.width, canvas.height);
        pdf.save('ventas_por_combustible.pdf');
    });
});
</script>

<?php include_once 'includes/footer.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">