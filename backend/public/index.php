<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Habilitar informes de errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar dependencias y configuración
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../middlewares/AuthMiddleware.php';
require __DIR__ . '/../middlewares/RoleMiddleware.php';
require __DIR__ . '/../controllers/ProductController.php';
require __DIR__ . '/../controllers/AuthController.php';
require __DIR__ . '/../controllers/VentaController.php';
require __DIR__ . '/../controllers/ClientController.php';
require __DIR__ . '/../controllers/UserController.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Conectar a la base de datos
$pdo = require __DIR__ . '/../config/database.php';

// Instanciar controlador de autenticación
$auth = new AuthController($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Ruta de login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/login') {
    $auth->login();
}

// Ruta protegida de ejemplo para administradores
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/admin-only') {
    $user = RoleMiddleware::allow(['Administrador']);
    echo json_encode(["message" => "Solo admin", "user" => $user]);
}

// Ruta protegida de ejemplo
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/test-protected') {
    $user = AuthMiddleware::verify();
    echo json_encode([
        "message" => "Acceso permitido",
        "user" => $user
    ]);
}

// Instanciar controlador de productos
$product = new ProductController($pdo);

// GET productos
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/products') {
    RoleMiddleware::allow(['Administrador']);
    $product->index();
}

// POST producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/products') {
    RoleMiddleware::allow(['Administrador']);
    $product->store();
}

// PUT producto
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && preg_match('/\/products\/(\d+)/', $_SERVER['REQUEST_URI'], $m)) {
    RoleMiddleware::allow(['Administrador']);
    $product->update($m[1]);
}

// DELETE producto
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && preg_match('/\/products\/(\d+)/', $_SERVER['REQUEST_URI'], $m)) {
    RoleMiddleware::allow(['Administrador']);
    $product->destroy($m[1]);
}

// Instanciar controlador de ventas
$venta = new VentaController($pdo);

// POST venta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/venta') {
    RoleMiddleware::allow(['Administrador', 'Vendedor']);
    $venta->store();
    exit;
}

// GET ventas
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/venta') {
    RoleMiddleware::allow(['Administrador', 'Vendedor']);
    $venta->index();
    exit;
}

// GET venta por ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('#^/venta/(\d+)$#', $_SERVER['REQUEST_URI'], $m)) {
    RoleMiddleware::allow(['Administrador', 'Vendedor']);
    $venta->show($m[1]);
    exit;
}

// Instanciar controlador de clientes
$client = new ClientController($pdo);

// Rutas para clientes
// LISTAR TODOS LOS CLIENTES
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/clients') {
    RoleMiddleware::allow(['Administrador', 'Vendedor']);
    $client->index();
    exit;
}

// CREAR CLIENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/clients') {
    RoleMiddleware::allow(['Administrador', 'Vendedor']);
    $client->store();
    exit;
}

// VER CLIENTE POR ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('#^/clients/(\d+)$#', $_SERVER['REQUEST_URI'], $m)) {
    RoleMiddleware::allow(['Administrador', 'Vendedor']);
    $client->show($m[1]);
    exit;
}

// ACTUALIZAR CLIENTE
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && preg_match('#^/clients/(\d+)$#', $_SERVER['REQUEST_URI'], $m)) {
    RoleMiddleware::allow(['Administrador', 'Vendedor']);
    $client->update($m[1]);
    exit;
}

// ELIMINAR CLIENTE
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && preg_match('#^/clients/(\d+)$#', $_SERVER['REQUEST_URI'], $m)) {
    RoleMiddleware::allow(['Administrador', 'Vendedor']);
    $client->destroy($m[1]);
    exit;
}

// Instanciar controlador de usuarios
$users = new UserController($pdo);

// LISTAR
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/usuarios') {
    RoleMiddleware::allow(['Administrador']);
    $users->index();
}

// CREAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/usuarios') {
    RoleMiddleware::allow(['Administrador']);
    $users->store();
}

// ACTUALIZAR
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && preg_match('/\/usuarios\/(\d+)/', $_SERVER['REQUEST_URI'], $m)) {
    RoleMiddleware::allow(['Administrador']);
    $users->update($m[1]);
}

// ELIMINAR
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && preg_match('/\/usuarios\/(\d+)/', $_SERVER['REQUEST_URI'], $m)) {
    RoleMiddleware::allow(['Administrador']);
    $users->destroy($m[1]);
} 
 
// Fin del archivo index.php
?>
