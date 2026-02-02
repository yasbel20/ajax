<?php
// products.php - Obtener productos desde la base de datos
require_once 'config.php';

// Get category from query parameters
$category = isset($_GET['category']) ? sanitize($_GET['category']) : 'todos';

// Conectar a la base de datos
$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos',
        'data' => []
    ]);
    exit;
}

try {
    // Preparar query base
    $query = "SELECT id, name, price, category, occasion, image, description FROM products WHERE is_active = 1";
    $params = [];
    
    // Filtrar por categoría si se especifica
    if ($category !== 'todos') {
        $query .= " AND category = ?";
        $params[] = $category;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    // Ejecutar query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Formatear precios
    foreach ($products as &$product) {
        $product['price'] = floatval($product['price']);
    }
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'data' => $products,
        'count' => count($products),
        'category' => $category
    ]);
    
} catch (PDOException $e) {
    error_log("Products error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener productos',
        'data' => []
    ]);
}
?>
