<?php
// login.php - Autenticación de usuarios
require_once 'config.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

// Obtener datos del POST
$email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validaciones básicas
if (empty($email) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Email y contraseña son obligatorios'
    ]);
    exit;
}

if (!validateEmail($email)) {
    echo json_encode([
        'success' => false,
        'message' => 'Por favor, introduce un email válido'
    ]);
    exit;
}

// Conectar a la base de datos
$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos'
    ]);
    exit;
}

try {
    // Buscar usuario por email
    $stmt = $pdo->prepare("
        SELECT id, email, password, name, phone, is_active 
        FROM users 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Verificar si el usuario existe y la contraseña es correcta
    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Email o contraseña incorrectos'
        ]);
        exit;
    }
    
    // Verificar si la cuenta está activa
    if ($user['is_active'] != 1) {
        echo json_encode([
            'success' => false,
            'message' => 'Tu cuenta ha sido desactivada. Contacta con soporte.'
        ]);
        exit;
    }
    
    // Actualizar último login
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Crear sesión
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['name'];
    
    // Obtener o crear carrito para el usuario
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user['id']]);
    $cart = $stmt->fetch();
    
    if (!$cart) {
        // Crear nuevo carrito si no existe
        $stmt = $pdo->prepare("INSERT INTO carts (user_id, created_at) VALUES (?, NOW())");
        $stmt->execute([$user['id']]);
        $_SESSION['cart_id'] = $pdo->lastInsertId();
    } else {
        $_SESSION['cart_id'] = $cart['id'];
    }
    
    // Obtener items del carrito
    $stmt = $pdo->prepare("
        SELECT ci.*, p.name, p.image 
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.cart_id = ?
    ");
    $stmt->execute([$_SESSION['cart_id']]);
    $cartItems = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'message' => '¡Bienvenido de nuevo!',
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'phone' => $user['phone']
        ],
        'cart_items' => $cartItems
    ]);
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al iniciar sesión. Por favor, intenta de nuevo.'
    ]);
}
?>
