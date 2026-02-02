<?php
// data_products.php - Devuelve datos de productos en formato JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Array de productos
$products = [
    [
        'id' => 1,
        'name' => 'Bolsi Neverfull MM LV x TM',
        'price' => 2600.00,
        'category' => 'tote',
        'occasion' => 'casual',
        'image' => 'img/bolso10.jpg',
        'description' => 'Icónico bolso tote en lona monograma multicolor',
        'color' => 'Multicolor',
        'stock' => 15
    ],
    [
        'id' => 2,
        'name' => 'Bolsi Neverfull MM',
        'price' => 1550.00,
        'category' => 'tote',
        'occasion' => 'casual',
        'image' => 'img/bolso11.jpg',
        'description' => 'El bolso perfecto para el día a día',
        'color' => 'Monogram',
        'stock' => 8
    ],
    [
        'id' => 3,
        'name' => 'Bolsi Speedy Soft 30 LV x TM',
        'price' => 3500.00,
        'category' => 'bandolera',
        'occasion' => 'formal',
        'image' => 'img/bolso12.jpg',
        'description' => 'Edición especial en piel premium multicolor',
        'color' => 'Rainbow',
        'stock' => 3
    ],
    [
        'id' => 4,
        'name' => 'Bolsi Alma BB',
        'price' => 1650.00,
        'category' => 'bandolera',
        'occasion' => 'formal',
        'image' => 'img/bolso2.jpg',
        'description' => 'Elegancia estructurada en formato compacto',
        'color' => 'Ébano',
        'stock' => 12
    ],
    [
        'id' => 5,
        'name' => 'Bolsi Capucines MM',
        'price' => 5200.00,
        'category' => 'bandolera',
        'occasion' => 'formal',
        'image' => 'img/bolso1.jpg',
        'description' => 'Artesanía excepcional en piel Taurillon',
        'color' => 'Negro',
        'stock' => 5
    ],
    [
        'id' => 6,
        'name' => 'Bolsi Twist MM',
        'price' => 3400.00,
        'category' => 'bandolera',
        'occasion' => 'noche',
        'image' => 'img/bolso8.jpg',
        'description' => 'Diseño moderno con cierre icónico',
        'color' => 'Oro Rosa',
        'stock' => 7
    ],
    [
        'id' => 7,
        'name' => 'Bolsi Coussin PM',
        'price' => 2900.00,
        'category' => 'bandolera',
        'occasion' => 'casual',
        'image' => 'img/bolso7.jpg',
        'description' => 'Piel acolchada con cadena dorada',
        'color' => 'Crema',
        'stock' => 9
    ],
    [
        'id' => 8,
        'name' => 'Bolsi Pochette Métis',
        'price' => 1980.00,
        'category' => 'bandolera',
        'occasion' => 'casual',
        'image' => 'img/bolso6.jpg',
        'description' => 'Versátil y sofisticado, perfecto para cualquier ocasión',
        'color' => 'Monogram',
        'stock' => 11
    ],
    [
        'id' => 9,
        'name' => 'Bolsi Mini Clutch Evening',
        'price' => 1250.00,
        'category' => 'clutch',
        'occasion' => 'noche',
        'image' => 'img/bolso5.jpg',
        'description' => 'Elegancia minimalista para eventos especiales',
        'color' => 'Lentejuelas',
        'stock' => 4
    ],
    [
        'id' => 10,
        'name' => 'Bolsi Backpack Explorer',
        'price' => 2100.00,
        'category' => 'mochila',
        'occasion' => 'viaje',
        'image' => 'img/bolso4.jpg',
        'description' => 'Funcionalidad y estilo para tus aventuras',
        'color' => 'Caqui',
        'stock' => 6
    ],
    [
        'id' => 11,
        'name' => 'Bolsi Zippy Wallet',
        'price' => 720.00,
        'category' => 'cartera',
        'occasion' => 'casual',
        'image' => 'img/bolso3.jpg',
        'description' => 'Cartera clásica con múltiples compartimentos',
        'color' => 'Granate',
        'stock' => 20
    ],
    [
        'id' => 12,
        'name' => 'Bolsi Card Holder Elite',
        'price' => 380.00,
        'category' => 'cartera',
        'occasion' => 'casual',
        'image' => 'img/bolso2.jpg',
        'description' => 'Minimalismo de lujo en piel premium',
        'color' => 'Negro',
        'stock' => 25
    ]
];

// Filtrar por categoría si se especifica
$category = isset($_GET['category']) ? $_GET['category'] : 'todos';
$filteredProducts = $products;

if ($category !== 'todos') {
    $filteredProducts = array_filter($products, function($product) use ($category) {
        return $product['category'] === $category;
    });
    $filteredProducts = array_values($filteredProducts);
}

// Filtrar por búsqueda si se especifica
$search = isset($_GET['search']) ? strtolower($_GET['search']) : '';
if (!empty($search)) {
    $filteredProducts = array_filter($filteredProducts, function($product) use ($search) {
        return strpos(strtolower($product['name']), $search) !== false ||
               strpos(strtolower($product['description']), $search) !== false ||
               strpos(strtolower($product['category']), $search) !== false ||
               strpos(strtolower($product['occasion']), $search) !== false;
    });
    $filteredProducts = array_values($filteredProducts);
}

// Respuesta JSON
$response = [
    'success' => true,
    'data' => $filteredProducts,
    'count' => count($filteredProducts),
    'category' => $category,
    'search' => $search,
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>