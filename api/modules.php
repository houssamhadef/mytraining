<?php
require_once __DIR__ . '/../controllers/modules.php';
require_once __DIR__ . '/../lib/utils.php';

setApiCorsHeaders(['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
$modulesController = new ModulesController($db);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            list($statusCode, $response) = $modulesController->list();
            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode($response);
        }
        break;
    case 'get':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = (int) ($_GET['id'] ?? 0);
            list($statusCode, $response) = $modulesController->getById($id);
            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode($response);
        }
        break;
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = json_decode(file_get_contents('php://input'), true) ?? [];
            list($statusCode, $response) = $modulesController->create($payload);
            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode($response);
        }
        break;
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $id = (int) ($_GET['id'] ?? 0);
            $payload = json_decode(file_get_contents('php://input'), true) ?? [];
            list($statusCode, $response) = $modulesController->update($id, $payload);
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