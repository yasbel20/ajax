<?php
// search.php - Búsqueda de productos desde la base de datos
require_once 'config.php';

// Get search query
$query = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';

if (empty($query)) {
    echo json_encode([
        'success' => false,
        'message' => 'Por favor, introduce un término de búsqueda',
        'data' => []
    ]);
    exit;
}

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
    // Buscar en nombre, categoría y descripción
    $searchTerm = '%' . $query . '%';
    
    $stmt = $pdo->prepare("
        SELECT id, name, price, category, description, image, occasion
        FROM products 
        WHERE is_active = 1 
        AND (
            LOWER(name) LIKE ? 
            OR LOWER(category) LIKE ? 
            OR LOWER(description) LIKE ?
            OR LOWER(occasion) LIKE ?
        )
        ORDER BY 
            CASE 
                WHEN LOWER(name) LIKE ? THEN 1
                WHEN LOWER(category) LIKE ? THEN 2
                ELSE 3
            END,
            name ASC
    ");
    
    $stmt->execute([
        $searchTerm, 
        $searchTerm, 
        $searchTerm, 
        $searchTerm,
        $searchTerm,
        $searchTerm
    ]);
    
    $results = $stmt->fetchAll();
    
    // Formatear precios
    foreach ($results as &$product) {
        $product['price'] = floatval($product['price']);
    }
    
    // Return results
    echo json_encode([
        'success' => true,
        'message' => count($results) > 0 
            ? 'Se encontraron ' . count($results) . ' producto(s)'
            : 'No se encontraron resultados',
        'data' => $results,
        'query' => $query,
        'count' => count($results)
    ]);
    
} catch (PDOException $e) {
    error_log("Search error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al realizar la búsqueda',
        'data' => []
    ]);
}
?>
