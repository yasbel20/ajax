<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Get POST data
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Validate email
if (empty($email)) {
    echo json_encode([
        'success' => false,
        'message' => 'Por favor, introduce tu correo electrónico'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Por favor, introduce un correo electrónico válido'
    ]);
    exit;
}

// In a real application, you would:
// 1. Connect to database
// 2. Check if email already exists
// 3. Insert email into subscribers table
// 4. Send confirmation email

// Simulate database operations
$existingEmails = ['test@example.com', 'existing@bolsi.com'];

if (in_array($email, $existingEmails)) {
    echo json_encode([
        'success' => false,
        'message' => 'Este correo ya está suscrito a nuestro newsletter'
    ]);
    exit;
}

// Simulate successful subscription
// In production, save to database here
$timestamp = date('Y-m-d H:i:s');

// Log subscription (in real app, this would be database insert)
$logEntry = "$timestamp - New subscription: $email\n";
@file_put_contents('newsletter_subscriptions.log', $logEntry, FILE_APPEND);

// Send success response
echo json_encode([
    'success' => true,
    'message' => '¡Gracias por suscribirte! Recibirás nuestras novedades pronto.',
    'email' => $email,
    'timestamp' => $timestamp
]);
?>