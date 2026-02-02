<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get category from query parameters
$category = isset($_GET['category']) ? $_GET['category'] : 'todos';

// Sample luxury handbag products
$allProducts = [
    [
        'id' => 1,
        'name' => 'Bolsi Neverfull MM LV x TM',
        'price' => 2600.00,
        'category' => 'tote',
        'occasion' => 'casual',
        'image' => 'img/bolso10.jpg',
        'description' => 'Icónico bolso tote en lona monograma multicolor'
    ],
    [
        'id' => 2,
        'name' => 'Bolsi Neverfull MM',
        'price' => 1550.00,
        'category' => 'tote',
        'occasion' => 'casual',
        'image' => 'img/bolso11.jpg',
        'description' => 'El bolso perfecto para el día a día'
    ],
    [
        'id' => 3,
        'name' => 'Bolsi Speedy Soft 30 LV x TM',
        'price' => 3500.00,
        'category' => 'bandolera',
        'occasion' => 'formal',
        'image' => 'img/bolso12.jpg',
        'description' => 'Edición especial en piel premium multicolor'
    ],
    [
        'id' => 4,
        'name' => 'Bolsi Alma BB',
        'price' => 1650.00,
        'category' => 'bandolera',
        'occasion' => 'formal',
        'image' => 'img/bolso2.jpg',
        'description' => 'Elegancia estructurada en formato compacto'
    ],
    [
        'id' => 5,
        'name' => 'Bolsi Capucines MM',
        'price' => 5200.00,
        'category' => 'bandolera',
        'occasion' => 'formal',
        'image' => 'img/bolso1.jpg',
        'description' => 'Artesanía excepcional en piel Taurillon'
    ],
    [
        'id' => 6,
        'name' => 'Bolsi Twist MM',
        'price' => 3400.00,
        'category' => 'bandolera',
        'occasion' => 'noche',
        'image' => 'img/bolso8.jpg',
        'description' => 'Diseño moderno con cierre icónico'
    ],
    [
        'id' => 7,
        'name' => 'Bolsi Coussin PM',
        'price' => 2900.00,
        'category' => 'bandolera',
        'occasion' => 'casual',
        'image' => 'img/bolso7.jpg',
        'description' => 'Piel acolchada con cadena dorada'
    ],
    [
        'id' => 8,
        'name' => 'Bolsi Pochette Métis',
        'price' => 1980.00,
        'category' => 'bandolera',
        'occasion' => 'casual',
        'image' => 'img/bolso6.jpg',
        'description' => 'Versátil y sofisticado, perfecto para cualquier ocasión'
    ],
    [
        'id' => 9,
        'name' => 'Bolsi Mini Clutch Evening',
        'price' => 1250.00,
        'category' => 'clutch',
        'occasion' => 'noche',
        'image' => 'img/bolso5.jpg',
        'description' => 'Elegancia minimalista para eventos especiales'
    ],
    [
        'id' => 10,
        'name' => 'Bolsi Backpack Explorer',
        'price' => 2100.00,
        'category' => 'mochila',
        'occasion' => 'viaje',
        'image' => 'img/bolso4.jpg',
        'description' => 'Funcionalidad y estilo para tus aventuras'
    ],
    [
        'id' => 11,
        'name' => 'Bolsi Zippy Wallet',
        'price' => 720.00,
        'category' => 'cartera',
        'occasion' => 'casual',
        'image' => 'img/bolso3.jpg',
        'description' => 'Cartera clásica con múltiples compartimentos'
    ],
    [
        'id' => 12,
        'name' => 'Bolsi Card Holder Elite',
        'price' => 380.00,
        'category' => 'cartera',
        'occasion' => 'casual',
        'image' => 'img/bolso2.jpg',
        'description' => 'Minimalismo de lujo en piel premium'
    ]
];

// Filter products by category if specified
$products = $allProducts;
if ($category !== 'todos') {
    $products = array_filter($allProducts, function($product) use ($category) {
        return $product['category'] === $category;
    });
    $products = array_values($products); // Re-index array
}

// Return JSON response
$response = [
    'success' => true,
    'data' => $products,
    'count' => count($products),
    'category' => $category
];

echo json_encode($response);
?>