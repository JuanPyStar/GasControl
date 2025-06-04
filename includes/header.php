<?php
include_once 'config/session.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GasControl - Sistema de Gestión para Gasolineras</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
        }
        .sidebar {
            background: linear-gradient(180deg, #003366 0%, #002244 100%);
            transition: all 0.3s;
        }
        .sidebar-link {
            transition: all 0.2s;
        }
        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        .sidebar-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #fff;
        }
        .main-content {
            transition: margin-left 0.3s;
        }
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        .toggle-btn {
            transition: transform 0.3s;
        }
        .toggle-btn.active {
            transform: rotate(180deg);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar w-64 fixed inset-y-0 left-0 z-30 text-white overflow-y-auto">
            <div class="px-6 pt-8 pb-6 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="bg-white p-2 rounded-full mr-3">
                        <i class="fas fa-gas-pump text-blue-800 text-xl"></i>
                    </div>
                    <h1 class="text-xl font-bold">GasControl</h1>
                </div>
                <button id="toggle-sidebar" class="toggle-btn lg:hidden text-white focus:outline-none">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>
            
            <nav class="mt-2 px-4">
                <p class="text-xs text-gray-400 px-2 mb-2">MENÚ PRINCIPAL</p>
                
                <a href="ventas.php" class="sidebar-link flex items-center p-3 mb-2 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'ventas.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span class="ml-3">Ventas</span>
                </a>
                
                
                <?php if (isAdmin()): ?>
                
                <a href="dashboard.php" class="sidebar-link flex items-center p-3 mb-2 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span class="ml-3">Dashboard</span>
                </a>

                 <a href="clientes.php" class="sidebar-link flex items-center p-3 mb-2 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'clientes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users w-5"></i>
                    <span class="ml-3">Clientes</span>
                </a>
                
                <a href="combustibles.php" class="sidebar-link flex items-center p-3 mb-2 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'combustibles.php' ? 'active' : ''; ?>">
                    <i class="fas fa-oil-can w-5"></i>
                    <span class="ml-3">Combustibles</span>
                </a>
                <p class="text-xs text-gray-400 px-2 mb-2 mt-6">ADMINISTRACIÓN</p>
                <a href="empleados.php" class="sidebar-link flex items-center p-3 mb-2 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'empleados.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-tie w-5"></i>
                    <span class="ml-3">Empleados</span>
                </a>
                
                <a href="suministros.php" class="sidebar-link flex items-center p-3 mb-2 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'suministros.php' ? 'active' : ''; ?>">
                    <i class="fas fa-truck-loading w-5"></i>
                    <span class="ml-3">Suministros</span>
                </a>
                
                <a href="reportes.php" class="sidebar-link flex items-center p-3 mb-2 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'reportes.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span class="ml-3">Reportes</span>
                </a>
                <?php endif; ?>
            </nav>
            
            <div class="mt-auto px-4 py-6 border-t border-blue-800">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-blue-400 flex items-center justify-center">
                        <span class="text-white font-medium"><?php echo substr(getUserName(), 0, 1); ?></span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium"><?php echo getUserName(); ?></p>
                        <p class="text-xs text-gray-400"><?php echo isAdmin() ? 'Administrador' : 'Empleado'; ?></p>
                    </div>
                </div>
                <a href="logout.php" class="mt-4 block text-sm text-center p-2 rounded bg-red-600 hover:bg-red-700 transition-colors">
                    <i class="fas fa-sign-out-alt mr-1"></i> Cerrar Sesión
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div id="main-content" class="main-content flex-1 ml-64 p-6">
            <!-- Mobile Header -->
            <div class="lg:hidden flex items-center justify-between mb-6 p-4 bg-white rounded-lg shadow">
                <button id="show-sidebar" class="text-blue-800 focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="flex items-center">
                    <div class="bg-blue-800 p-2 rounded-full mr-2">
                        <i class="fas fa-gas-pump text-white text-sm"></i>
                    </div>
                    <h1 class="text-lg font-bold">GasControl</h1>
                </div>
                <div></div>
            </div>