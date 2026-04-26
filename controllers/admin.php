<?php
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/utils.php';
require_once __DIR__ . '/../middleware/session.php';

class AdminController {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function register(array $payload): array {
        $nom = trim((string) ($payload['nom'] ?? ''));
        $prenom = trim((string) ($payload['prenom'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($nom === '' || $prenom === '' || $email === '' || $password === '') {
            return [400, ['success' => false, 'message' => 'Missing required fields.']];
        }

        if (!isValidEmail($email)) {
            return [422, ['success' => false, 'message' => 'Invalid email format.']];
        }

        $stmt = $this->db->prepare('SELECT * FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            return [409, ['success' => false, 'message' => 'Email already in use.']];
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $id = bin2hex(random_bytes(16));

        $stmt = $this->db->prepare('INSERT INTO admins (id, nom, prenom, email, passwordHash) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$id, $nom, $prenom, $email, $passwordHash]);

        return [201, ['success' => true, 'message' => 'Admin registered successfully.']];
    }

    public function login(array $payload): array {
        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($email === '' || $password === '') {
            return [400, ['success' => false, 'message' => 'Missing email or password.']];
        }

        if (!isValidEmail($email)) {
            return [422, ['success' => false, 'message' => 'Invalid email format.']];
        }

        $stmt = $this->db->prepare('SELECT * FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin || !password_verify($password, $admin['passwordHash'])) {
            return [401, ['success' => false, 'message' => 'Invalid email or password.']];
        }

        $_SESSION['adminId'] = $admin['id'];
        return [200, ['success' => true, 'message' => 'Login successful.']];
    }

   public function check(): array {
    $auth = $this->ensureAdmin();
    if ($auth !== null) return $auth;
    return [200, ['success' => true, 'message' => 'Authenticated.']];
}

public function logout(): array {
    unset($_SESSION['adminId']);

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }

    session_destroy();
    return [200, ['success' => true, 'message' => 'Logged out successfully.']];
}

    public function me(): array {
        if (!isset($_SESSION['adminId'])) {
            return [401, ['success' => false, 'message' => 'Not authenticated.']];
        }

        $stmt = $this->db->prepare('SELECT id, nom, prenom, email, createdAt FROM admins WHERE id = ?');
        $stmt->execute([$_SESSION['adminId']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            return [404, ['success' => false, 'message' => 'Admin not found.']];
        }

        return [200, ['success' => true, 'data' => ['admin' => $admin]]];
    }

    public function ensureAuthenticated(): ?array {
        if (!isset($_SESSION['adminId'])) {
            return [401, ['success' => false, 'message' => 'Not authenticated.']];
        }
        return null;
    }

    public function ensureAdmin(): ?array {
        $auth = $this->ensureAuthenticated();
        if ($auth !== null) {
            return $auth;
        }

        $stmt = $this->db->prepare('SELECT * FROM admins WHERE id = ?');
        $stmt->execute([$_SESSION['adminId']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            return [403, ['success' => false, 'message' => 'Access denied.']];
        }

        return null;
    }

    public function stats(): array {
    $auth = $this->ensureAdmin();
    if ($auth !== null) return $auth;

    $users        = $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $inscriptions = $this->db->query('SELECT COUNT(*) FROM inscriptions')->fetchColumn();
    $modules      = $this->db->query('SELECT COUNT(*) FROM modules')->fetchColumn();

    return [200, ['success' => true, 'data' => compact('users', 'inscriptions', 'modules')]];
}

public function getUsers(): array {
    $auth = $this->ensureAdmin();
    if ($auth !== null) return $auth;

    $stmt = $this->db->query('SELECT id, nom, prenom, cin, email, niveu FROM users');
    return [200, ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]];
}

public function getInscriptions(): array {
    $auth = $this->ensureAdmin();
    if ($auth !== null) return $auth;

    $stmt = $this->db->query('
        SELECT inscriptions.id, users.nom, users.prenom, modules.nom_module
        FROM inscriptions
        JOIN users ON inscriptions.userId = users.id
        JOIN modules ON inscriptions.moduleId = modules.id
    ');
    return [200, ['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]];
}

public function deleteUser(array $payload): array {
    $auth = $this->ensureAdmin();
    if ($auth !== null) return $auth;

    $id = trim((string) ($payload['id'] ?? ''));
    if ($id === '') return [400, ['success' => false, 'message' => 'Missing user id.']];

    $this->db->prepare('DELETE FROM inscriptions WHERE userId = ?')->execute([$id]);
    $this->db->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);

    return [200, ['success' => true, 'message' => 'User deleted.']];
}

public function deleteInscription(array $payload): array {
    $auth = $this->ensureAdmin();
    if ($auth !== null) return $auth;

    $id = trim((string) ($payload['id'] ?? ''));
    if ($id === '') return [400, ['success' => false, 'message' => 'Missing inscription id.']];

    $this->db->prepare('DELETE FROM inscriptions WHERE id = ?')->execute([$id]);

    return [200, ['success' => true, 'message' => 'Inscription deleted.']];
}
    
}