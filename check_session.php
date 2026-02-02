<?php
// check_session.php - Verificar estado de sesión
require_once 'config.php';

if (isLoggedIn()) {
    $user = getCurrentUser();
    
    if ($user) {
        echo json_encode([
            'logged_in' => true,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'phone' => $user['phone']
            ]
        ]);
    } else {
        echo json_encode([
            'logged_in' => false
        ]);
    }
} else {
    echo json_encode([
        'logged_in' => false
    ]);
}
?>
