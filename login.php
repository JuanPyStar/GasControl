<?php
include_once 'config/database.php';
include_once 'config/session.php';
include_once 'models/User.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: dashboard.php");
    } else {
        header("Location: ventas.php");
    }
    exit();
}



$error_message = "";

// Process login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();
    
    $documento = $_POST['documento'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Primero verificar si es admin
    $query = "SELECT * FROM admin WHERE documento = :documento";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':documento', $documento);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        // Verificar contraseña (asumiendo que no está hasheada en la base de datos)
        if ($password === $admin['password']) {
            // Iniciar sesión como admin
            $_SESSION['user_id'] = $admin['documento'];
            $_SESSION['user_role'] = 'admin';
            $_SESSION['user_name'] = 'Administrador';
            header("Location: dashboard.php");
            exit();
        }
    }
    
    // Si no es admin, verificar si es empleado
    $user = new User($db);
    if ($user->login($documento, $password)) {
        // Redirect to dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        $error_message = "Credenciales inválidas. Intente de nuevo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GasControl - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
        }
        .login-container {
            background: linear-gradient(135deg, rgba(0,32,76,0.95) 0%, rgba(0,42,102,0.95) 100%);
        }
        .logo-pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-white rounded-xl shadow-xl overflow-hidden">
            <div class="login-container p-8 text-white">
                <div class="text-center mb-8">
                    <div class="logo-pulse inline-block bg-white p-4 rounded-full mb-4">
                        <i class="fas fa-gas-pump text-blue-800 text-4xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold">GasControl</h2>
                    <p class="text-blue-200">Sistema de Gestión para Gasolineras</p>
                </div>
                
                <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <p><?php echo $error_message; ?></p>
                </div>
                <?php endif; ?>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-6">
                        <label for="documento" class="block text-sm font-medium text-blue-200 mb-2">Documento</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-id-card text-gray-400"></i>
                            </div>
                            <input type="text" id="documento" name="documento" class="bg-blue-50 text-gray-800 pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" placeholder="Ingrese su documento" required>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-blue-200 mb-2">Contraseña</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" id="password" name="password" class="bg-blue-50 text-gray-800 pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" placeholder="Ingrese su contraseña" required>
                        </div>
                    </div>
                    
                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-300">
                            Iniciar Sesión
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="px-8 py-6 bg-gray-50">
                <div class="text-center text-gray-500 text-sm">
                    <p>© 2025 GasControl - Todos los derechos reservados</p>
                </div>
            </div>
        </div>
    </div>


</body>
</html>