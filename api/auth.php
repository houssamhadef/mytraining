<?php
require_once __DIR__ . '/../controllers/auth.php';
require_once __DIR__ . '/../lib/utils.php';

setApiCorsHeaders(['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
$authController = new AuthController($db);

$action = $_GET['action'] ?? '';

switch ($action){
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        list($statusCode, $response) = $authController->login($payload);
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
        break;
        }

    case 'check':
        if ($_SERVER['REQUEST_METHOD'] === 'GET'){
        list($statusCode, $response) = $authController->check();
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
        break;
        }
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        list($statusCode, $response) = $authController->register($payload);
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
        break;
        }
    case 'logout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            list($statusCode, $response) = $authController->logout();
            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode($response);
        }

        break;
    case 'profile':
        if ($_SERVER['REQUEST_METHOD'] === 'GET'){
            list($statusCode, $response) = $authController->me();
            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode($response);
        }

        break;
    case 'update':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        list($statusCode, $response) = $authController->update($payload);
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    break;

    case 'stats':
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        list($statusCode, $response) = $adminController->stats();
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    break;
case 'users':
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        list($statusCode, $response) = $adminController->getUsers();
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    break;
case 'inscriptions':
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        list($statusCode, $response) = $adminController->getInscriptions();
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    break;
case 'deleteUser':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        list($statusCode, $response) = $adminController->deleteUser($payload);
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    break;
case 'deleteInscription':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $payload = json_decode(file_get_contents('php://input'), true) ?? [];
        list($statusCode, $response) = $adminController->deleteInscription($payload);
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    break;
    
    default:
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found.',
        ]);
        break;
}

?>
