<?php
// cart.php - Gestión del carrito de compras
require_once 'config.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para usar el carrito',
        'require_login' => true
    ]);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos'
    ]);
    exit;
}

try {
    switch ($action) {
        case 'add':
            addToCart($pdo);
            break;
        
        case 'remove':
            removeFromCart($pdo);
            break;
        
        case 'update':
            updateCartQuantity($pdo);
            break;
        
        case 'get':
            getCart($pdo);
            break;
        
        case 'clear':
            clearCart($pdo);
            break;
        
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
            break;
    }
} catch (PDOException $e) {
    error_log("Cart error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la solicitud'
    ]);
}

// Función para agregar producto al carrito
function addToCart($pdo) {
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if ($productId <= 0 || $quantity <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos inválidos'
        ]);
        return;
    }
    
    // Obtener información del producto
    $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'Producto no encontrado'
        ]);
        return;
    }
    
    if ($product['stock'] < $quantity) {
        echo json_encode([
            'success' => false,
            'message' => 'Stock insuficiente'
        ]);
        return;
    }
    
    // Obtener o crear carrito
    $cartId = $_SESSION['cart_id'];
    
    // Verificar si el producto ya está en el carrito
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cartId, $productId]);
    $existingItem = $stmt->fetch();
    
    if ($existingItem) {
        // Actualizar cantidad
        $newQuantity = $existingItem['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newQuantity, $existingItem['id']]);
    } else {
        // Agregar nuevo item
        $stmt = $pdo->prepare("
            INSERT INTO cart_items (cart_id, product_id, quantity, price, added_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$cartId, $productId, $quantity, $product['price']]);
    }
    
    // Actualizar carrito
    $stmt = $pdo->prepare("UPDATE carts SET updated_at = NOW() WHERE id = ?");
    $stmt->execute([$cartId]);
    
    // Obtener carrito actualizado
    getCart($pdo);
}

// Función para eliminar producto del carrito
function removeFromCart($pdo) {
    $itemId = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    
    if ($itemId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos inválidos'
        ]);
        return;
    }
    
    $cartId = $_SESSION['cart_id'];
    
    // Eliminar item
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND cart_id = ?");
    $stmt->execute([$itemId, $cartId]);
    
    // Actualizar carrito
    $stmt = $pdo->prepare("UPDATE carts SET updated_at = NOW() WHERE id = ?");
    $stmt->execute([$cartId]);
    
    getCart($pdo);
}

// Función para actualizar cantidad
function updateCartQuantity($pdo) {
    $itemId = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if ($itemId <= 0 || $quantity < 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos inválidos'
        ]);
        return;
    }
    
    $cartId = $_SESSION['cart_id'];
    
    if ($quantity == 0) {
        // Eliminar item si la cantidad es 0
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND cart_id = ?");
        $stmt->execute([$itemId, $cartId]);
    } else {
        // Actualizar cantidad
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND cart_id = ?");
        $stmt->execute([$quantity, $itemId, $cartId]);
    }
    
    // Actualizar carrito
    $stmt = $pdo->prepare("UPDATE carts SET updated_at = NOW() WHERE id = ?");
    $stmt->execute([$cartId]);
    
    getCart($pdo);
}

// Función para obtener el carrito
function getCart($pdo) {
    $cartId = $_SESSION['cart_id'];
    
    // Obtener items del carrito con información del producto
    $stmt = $pdo->prepare("
        SELECT 
            ci.id,
            ci.product_id,
            ci.quantity,
            ci.price,
            p.name,
            p.image,
            p.stock,
            (ci.quantity * ci.price) as subtotal
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.cart_id = ?
        ORDER BY ci.added_at DESC
    ");
    $stmt->execute([$cartId]);
    $items = $stmt->fetchAll();
    
    // Calcular total
    $total = 0;
    $itemCount = 0;
    foreach ($items as $item) {
        $total += $item['subtotal'];
        $itemCount += $item['quantity'];
    }
    
    echo json_encode([
        'success' => true,
        'items' => $items,
        'total' => $total,
        'item_count' => $itemCount,
        'message' => count($items) > 0 ? 'Carrito cargado' : 'Carrito vacío'
    ]);
}

// Función para vaciar el carrito
function clearCart($pdo) {
    $cartId = $_SESSION['cart_id'];
    
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $stmt->execute([$cartId]);
    
    $stmt = $pdo->prepare("UPDATE carts SET updated_at = NOW() WHERE id = ?");
    $stmt->execute([$cartId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Carrito vaciado',
        'items' => [],
        'total' => 0,
        'item_count' => 0
    ]);
}
?>
