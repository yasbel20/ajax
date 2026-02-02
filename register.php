<?php
// register.php - Registro de nuevos usuarios
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
$name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
$phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';

// Validaciones
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

if (strlen($password) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'La contraseña debe tener al menos 6 caracteres'
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
    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Este email ya está registrado'
        ]);
        exit;
    }
    
    // Hash de la contraseña
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar nuevo usuario
    $stmt = $pdo->prepare("
        INSERT INTO users (email, password, name, phone, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([$email, $hashedPassword, $name, $phone]);
    $userId = $pdo->lastInsertId();
    
    // Crear sesión automáticamente
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name'] = $name;
    
    // Crear carrito para el usuario
    $stmt = $pdo->prepare("INSERT INTO carts (user_id, created_at) VALUES (?, NOW())");
    $stmt->execute([$userId]);
    $_SESSION['cart_id'] = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => '¡Cuenta creada exitosamente!',
        'user' => [
            'id' => $userId,
            'email' => $email,
            'name' => $name
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear la cuenta. Por favor, intenta de nuevo.'
    ]);
}
?>
