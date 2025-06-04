<?php
include_once 'config/database.php';
include_once 'config/session.php';
include_once 'models/Venta.php';
include_once 'models/Combustible.php';
include_once 'models/Cliente.php';
include_once 'models/User.php';

// Require admin
requireAdmin();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Create objects
$venta = new Venta($db);
$combustible = new Combustible($db);
$cliente = new Cliente($db);
$user = new User($db);

// Determinar rango de fechas
$range = $_GET['range'] ?? '7';
$from = null;
$to = null;

if ($range === 'personalizado' && !empty($_GET['from']) && !empty($_GET['to'])) {
    $from = $_GET['from'];
    $to = $_GET['to'];
} elseif ($range === '30') {
    $from = date('Y-m-d', strtotime('-29 days'));
    $to = date('Y-m-d');
} elseif ($range === 'mes_actual') {
    $from = date('Y-m-01');
    $to = date('Y-m-d');
} elseif ($range === 'mes_anterior') {
    $from = date('Y-m-01', strtotime('first day of last month'));
    $to = date('Y-m-t', strtotime('last day of last month'));
} else { // 7 días por defecto
    $from = date('Y-m-d', strtotime('-6 days'));
    $to = date('Y-m-d');
}

// Ahora pasa $from y $to a tu función de estadísticas
$stats = $venta->getStats($from, $to);

// Generar todas las fechas del rango
$all_dates = [];
$period = new DatePeriod(
    new DateTime($from),
    new DateInterval('P1D'),
    (new DateTime($to))->modify('+1 day')
);
foreach ($period as $date) {
    $all_dates[$date->format('Y-m-d')] = [
        'sale_date' => $date->format('Y-m-d'),
        'sales_count' => 0,
        'total_income' => 0
    ];
}

// Reemplazar los datos reales en las fechas correspondientes
if (isset($stats['by_day'])) {
    foreach ($stats['by_day'] as $day) {
        $all_dates[$day['sale_date']] = $day;
    }
}

// Ahora $all_dates tiene todas las fechas del rango, con ceros donde no hay ventas
$stats['by_day'] = array_values($all_dates);

// Get counts
$stmt_combustibles = $combustible->readAll();
$num_combustibles = $stmt_combustibles->rowCount();

$stmt_clientes = $cliente->readAll();
$num_clientes = $stmt_clientes->rowCount();

$stmt_users = $user->readAll();
$num_users = $stmt_users->rowCount();

// Include header
include_once 'includes/header.php';
?>

<!-- Reportes Content -->
<div class="fade-in">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Reportes y Estadísticas</h1>
        <p class="text-sm text-gray-600"><?php echo date('d M, Y'); ?></p>
    </div>
    
    <!-- Overview Stats -->
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
                    <h3 class="text-gray-500 text-sm">Combustibles</h3>
                    <p class="text-2xl font-bold"><?php echo $num_combustibles; ?></p>
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
    
    <!-- Sales Over Time Chart -->
    <div class="bg-white rounded-xl shadow p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
            <h3 class="text-lg font-bold text-gray-800 mb-2 md:mb-0">
                Ventas e Ingresos
                <?php
                    // Mostrar rango seleccionado
                    if (isset($_GET['range'])) {
                        switch ($_GET['range']) {
                            case '7': echo '(Últimos 7 días)'; break;
                            case '30': echo '(Últimos 30 días)'; break;
                            case 'mes_actual': echo '(Mes actual)'; break;
                            case 'mes_anterior': echo '(Mes anterior)'; break;
                            case 'personalizado':
                                if (!empty($_GET['from']) && !empty($_GET['to'])) {
                                    echo '(Del ' . date('d M Y', strtotime($_GET['from'])) . ' al ' . date('d M Y', strtotime($_GET['to'])) . ')';
                                } else {
                                    echo '(Personalizado)';
                                }
                                break;
                        }
                    } else {
                        echo '(Últimos 7 días)';
                    }
                ?>
            </h3>
            <form method="get" class="flex items-center gap-2" id="range-form" autocomplete="off">
                <label for="range" class="text-sm text-gray-700">Rango:</label>
                <select name="range" id="range" class="border rounded px-2 py-1 text-sm">
                    <option value="7" <?php if(($_GET['range'] ?? '') == '7') echo 'selected'; ?>>Últimos 7 días</option>
                    <option value="30" <?php if(($_GET['range'] ?? '') == '30') echo 'selected'; ?>>Últimos 30 días</option>
                    <option value="mes_actual" <?php if(($_GET['range'] ?? '') == 'mes_actual') echo 'selected'; ?>>Mes actual</option>
                    <option value="mes_anterior" <?php if(($_GET['range'] ?? '') == 'mes_anterior') echo 'selected'; ?>>Mes anterior</option>
                    <option value="personalizado" <?php if(($_GET['range'] ?? '') == 'personalizado') echo 'selected'; ?>>Personalizado</option>
                </select>
                <input type="date" name="from" id="from" class="border rounded px-2 py-1 text-sm <?php echo (($_GET['range'] ?? '') == 'personalizado') ? '' : 'hidden'; ?>" value="<?php echo $_GET['from'] ?? ''; ?>">
                <span id="a-label" class="text-sm text-gray-700 <?php echo (($_GET['range'] ?? '') == 'personalizado') ? '' : 'hidden'; ?>">a</span>
                <input type="date" name="to" id="to" class="border rounded px-2 py-1 text-sm <?php echo (($_GET['range'] ?? '') == 'personalizado') ? '' : 'hidden'; ?>" value="<?php echo $_GET['to'] ?? ''; ?>">
                <?php if (!empty($_GET['range']) || !empty($_GET['from']) || !empty($_GET['to'])): ?>
                    <a href="reportes.php"
                       class="ml-2 text-xs bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 px-3 py-[0.375rem] rounded transition-colors flex items-center h-[32px]"
                       style="height:32px; line-height:1.5;">
                        Limpiar
                    </a>
                <?php endif; ?>
            </form>
        </div>
        <div class="h-80 flex flex-col">
            <div class="flex-1">
                <canvas id="salesChart"></canvas>
            </div>
            <div class="flex justify-end mt-4">
                <button id="export-sales-btn" class="bg-red-500 hover:bg-red-600 text-white text-xs px-4 py-2 rounded flex items-center gap-2 shadow">
                    <i class="fa-solid fa-file-pdf"></i> Exportar
                </button>
            </div>
        </div>
    </div>
    
    <!-- Sales by Fuel Type -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Ventas por Tipo de Combustible</h3>
            <div class="h-64">
                <canvas id="fuelSalesChart"></canvas>
                <div class="flex justify-end mt-2">
                    <button id="export-fuel-sales-btn" class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded flex items-center gap-1">
                        <i class="fa-solid fa-file-pdf"></i> Exportar
                    </button>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Ingresos por Tipo de Combustible</h3>
            <div class="h-64">
                <canvas id="fuelIncomeChart"></canvas>
                <div class="flex justify-end mt-2">
                    <button id="export-fuel-income-btn" class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded flex items-center gap-1">
                        <i class="fa-solid fa-file-pdf"></i> Exportar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Customers -->
    <div class="bg-white rounded-xl shadow p-6 mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Clientes con Más Millas</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Placa</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Millas</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    // Query to get top 5 customers by miles
                    $query = "SELECT id_cliente, nombre, placa_vehiculo, telefono, millas 
                              FROM clientes 
                              ORDER BY millas DESC 
                              LIMIT 5";
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['id_cliente']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $row['nombre']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['placa_vehiculo']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['telefono']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm bg-blue-100 text-blue-800 py-1 px-2 rounded-full inline-block">
                                <i class="fas fa-award mr-1"></i> <?php echo $row['millas'] ?? 0; ?> pts
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Export Reports -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Exportar Reportes</h3>
        <p class="text-gray-600 mb-4">Exporte los datos para análisis detallado en Excel.</p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center mb-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                        <i class="fas fa-shopping-cart text-blue-600"></i>
                    </div>
                    <h4 class="font-medium">Reporte de Ventas</h4>
                </div>
                <p class="text-sm text-gray-600 mb-3">Descargar todas las ventas con detalles completos.</p>
                <!-- Ventas -->
                <a href="export_ventas.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                    <i class="fas fa-download mr-1"></i> Exportar datos
                </a>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center mb-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                        <i class="fas fa-users text-green-600"></i>
                    </div>
                    <h4 class="font-medium">Reporte de Clientes</h4>
                </div>
                <p class="text-sm text-gray-600 mb-3">Descargar listado de clientes con millas acumuladas.</p>
                <!-- Clientes -->
                <a href="export_clientes.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                    <i class="fas fa-download mr-1"></i> Exportar datos
                </a>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center mb-3">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                        <i class="fas fa-gas-pump text-purple-600"></i>
                    </div>
                    <h4 class="font-medium">Reporte de Inventario</h4>
                </div>
                <p class="text-sm text-gray-600 mb-3">Descargar estado actual del inventario de combustibles.</p>
                <!-- Inventario -->
                <a href="export_inventario.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                    <i class="fas fa-download mr-1"></i> Exportar datos
                </a>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const rangeSelect = document.getElementById('range');
    const fromInput = document.getElementById('from');
    const toInput = document.getElementById('to');
    const aLabel = document.getElementById('a-label');
    const form = document.getElementById('range-form');

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
        if (rangeSelect.value !== 'personalizado') {
            form.submit();
        }
    });
    toggleCustomDates();

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
});
</script>

<script>
// Sales Over Time Chart
const salesData = <?php 
    $labels = [];
    $values_sales = [];
    $values_income = [];
    
    if (isset($stats['by_day'])) {
        foreach ($stats['by_day'] as $day) {
            $date = new DateTime($day['sale_date']);
            $labels[] = $date->format('d M');
            $values_sales[] = $day['sales_count'];
            $values_income[] = $day['total_income'];
        }
    }
    
    echo json_encode([
        'labels' => $labels,
        'sales' => $values_sales,
        'income' => $values_income
    ]);
?>;

const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: salesData.labels,
        datasets: [
            {
                label: 'Ventas',
                data: salesData.sales,
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 2,
                tension: 0.3,
                yAxisID: 'y'
            },
            {
                label: 'Ingresos ($)',
                data: salesData.income,
                backgroundColor: 'rgba(16, 185, 129, 0.2)',
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 2,
                tension: 0.3,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Número de Ventas'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Ingresos ($)'
                },
                grid: {
                    drawOnChartArea: false,
                },
                ticks: {
                    callback: function(value) {
                        return '$' + value;
                    }
                }
            }
        }
    }
});

// Fuel Type Charts
const fuelData = <?php 
    $labels = [];
    $values_sales = [];
    $values_income = [];
    $values_gallons = [];
    $colors = ['rgba(59, 130, 246, 0.8)', 'rgba(16, 185, 129, 0.8)', 'rgba(139, 92, 246, 0.8)', 'rgba(245, 158, 11, 0.8)'];
    
    if (isset($stats['by_fuel_type'])) {
        foreach ($stats['by_fuel_type'] as $fuel) {
            $labels[] = $fuel['tipo'];
            $values_sales[] = $fuel['sales_count'];
            $values_income[] = $fuel['total_income'];
            $values_gallons[] = $fuel['total_gallons'];
        }
    }
    
    echo json_encode([
        'labels' => $labels,
        'sales' => $values_sales,
        'income' => $values_income,
        'gallons' => $values_gallons,
        'colors' => array_slice($colors, 0, count($labels))
    ]);
?>;

// Fuel Sales Chart
const fuelSalesCtx = document.getElementById('fuelSalesChart').getContext('2d');
new Chart(fuelSalesCtx, {
    type: 'doughnut',
    data: {
        labels: fuelData.labels,
        datasets: [{
            data: fuelData.sales,
            backgroundColor: fuelData.colors,
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            },
            title: {
                display: true,
                text: 'Cantidad de Ventas por Tipo de Combustible'
            }
        }
    }
});

// Fuel Income Chart
const fuelIncomeCtx = document.getElementById('fuelIncomeChart').getContext('2d');
new Chart(fuelIncomeCtx, {
    type: 'bar',
    data: {
        labels: fuelData.labels,
        datasets: [{
            label: 'Ingresos ($)',
            data: fuelData.income,
            backgroundColor: fuelData.colors,
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            title: {
                display: true,
                text: 'Ingresos por Tipo de Combustible'
            }
        },
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
</script>

<!-- Font Awesome (si no está ya) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<!-- jsPDF y html2canvas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
function getSelectedRangeText() {
    // Busca el texto del rango mostrado en el h3 (el que ya muestras arriba del gráfico)
    const h3 = document.querySelector('#salesChart').closest('.bg-white').querySelector('h3');
    if (h3) {
        // Quita el título principal y deja solo el rango
        let txt = h3.textContent.replace('Ventas e Ingresos', '').trim();
        return txt ? txt : '';
    }
    return '';
}

document.getElementById('export-sales-btn').addEventListener('click', function() {
    const chartDiv = document.getElementById('salesChart').parentElement;
    const rango = getSelectedRangeText();
    html2canvas(chartDiv).then(function(canvas) {
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jspdf.jsPDF({ orientation: 'landscape', unit: 'pt', format: [canvas.width, canvas.height + 100] });
        pdf.setFontSize(18);
        pdf.text("Ventas e Ingresos", 40, 40);
        pdf.setFontSize(12);
        if (rango) pdf.text(rango, 40, 60);
        pdf.addImage(imgData, 'PNG', 0, 90, canvas.width, canvas.height);
        pdf.save('ventas_ingresos.pdf');
    });
});

document.getElementById('export-fuel-sales-btn').addEventListener('click', function() {
    const chartDiv = document.getElementById('fuelSalesChart').parentElement;
    const h3 = chartDiv.closest('.bg-white').querySelector('h3');
    let rango = '';
    if (h3) {
        rango = h3.textContent.replace('Ventas por Tipo de Combustible', '').trim();
    }
    html2canvas(chartDiv).then(function(canvas) {
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jspdf.jsPDF({ orientation: 'landscape', unit: 'pt', format: [canvas.width, canvas.height + 100] });
        pdf.setFontSize(18);
        pdf.text("Ventas por Tipo de Combustible", 40, 40);
        pdf.setFontSize(12);
        if (rango) pdf.text(rango, 40, 60);
        pdf.addImage(imgData, 'PNG', 0, 90, canvas.width, canvas.height);
        pdf.save('ventas_por_tipo_combustible.pdf');
    });
});

document.getElementById('export-fuel-income-btn').addEventListener('click', function() {
    const chartDiv = document.getElementById('fuelIncomeChart').parentElement;
    const h3 = chartDiv.closest('.bg-white').querySelector('h3');
    let rango = '';
    if (h3) {
        rango = h3.textContent.replace('Ingresos por Tipo de Combustible', '').trim();
    }
    html2canvas(chartDiv).then(function(canvas) {
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jspdf.jsPDF({ orientation: 'landscape', unit: 'pt', format: [canvas.width, canvas.height + 100] });
        pdf.setFontSize(18);
        pdf.text("Ingresos por Tipo de Combustible", 40, 40);
        pdf.setFontSize(12);
        if (rango) pdf.text(rango, 40, 60);
        pdf.addImage(imgData, 'PNG', 0, 90, canvas.width, canvas.height);
        pdf.save('ingresos_por_tipo_combustible.pdf');
    });
});
</script>

<?php include_once 'includes/footer.php'; ?>