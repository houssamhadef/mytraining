<?php
require_once __DIR__ . '/../controllers/admin.php';
require_once __DIR__ . '/../lib/utils.php';
require_once __DIR__ . '/../middleware/session.php';

setApiCorsHeaders(['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$adminController = new AdminController($db);
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = json_decode(file_get_contents('php://input'), true) ?? [];
            list($statusCode, $response) = $adminController->register($payload);
            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode($response);
        }
        break;
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = json_decode(file_get_contents('php://input'), true) ?? [];
            list($statusCode, $response) = $adminController->login($payload);
            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode($response);
        }
        break;
    case 'logout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            list($statusCode, $response) = $adminController->logout();
            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode($response);
        }
        break;
    case 'profile':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            list($statusCode, $response) = $adminController->me();
            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode($response);
        }
        break;
    case 'check':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            list($statusCode, $response) = $adminController->check(); // ← was ensureAdmin()
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
        echo json_encode(['success' => false, 'message' => 'Endpoint not found.']);
        break;
}