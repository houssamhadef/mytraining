<?php 

function isValidEmail(string $email): bool {
    return preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email) === 1;
}

function isValidCin(string $cin): bool {
    return preg_match('/^\d{8}$/', $cin) === 1;
}

function readJsonBody(): array {
    $raw = file_get_contents('php://input');

    if ($raw === false || $raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function sendJson(int $statusCode, array $payload): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
}

function setApiCorsHeaders(array $allowedMethods): void {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowedOrigins = [
        'http://127.0.0.1:8000',
        'http://localhost:8000',
        'http://127.0.0.1:5500',
        'http://localhost:5500',
    ];

    if (in_array($origin, $allowedOrigins, true)) {
        header("Access-Control-Allow-Origin: {$origin}");
        header('Vary: Origin');
    }

    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: ' . implode(', ', $allowedMethods));
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}





?>