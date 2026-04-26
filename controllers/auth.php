<?php
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/utils.php';
require_once __DIR__ . '/../middleware/session.php';
class AuthController {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function login(array $payload): array {
        $identifier = trim((string) ($payload['identifier'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $cin = trim((string) ($payload['cin'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($identifier === '' && $email !== '') {
            $identifier = $email;
        }

        if ($identifier === '' && $cin !== '') {
            $identifier = $cin;
        }

        if ($identifier === '' || $password === '') {
            return [400, ['success' => false, 'message' => 'Missing required fields.']];
        }

        if (isValidEmail($identifier)) {
            $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        } elseif (isValidCin($identifier)) {
            $stmt = $this->db->prepare('SELECT * FROM users WHERE cin = ?');
        } else {
            return [422, ['success' => false, 'message' => 'Identifier must be a valid email or an 8-digit CIN.']];
        }

        $stmt->execute([$identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return [404, ['success' => false, 'message' => 'User not found.']];
        }

        if (!password_verify($password, (string) $user['passwordHash'])) {
            return [401, ['success' => false, 'message' => 'Invalid credentials.']];
        }

        session_regenerate_id(true);
        $_SESSION['userId'] = $user['id'];
        $_SESSION['cin'] = $user['cin'];

        return [
            200,
            [
                'success' => true,
                'message' => 'Login successful.',
                'data' => [
                    'user' => $this->sanitizeUser($user),
                ],
            ],
        ];
    }

    public function register(array $payload): array {
    $nom = trim((string) ($payload['nom'] ?? ''));
    $prenom = trim((string) ($payload['prenom'] ?? ''));
    $cin = trim((string) ($payload['cin'] ?? ''));
    $email = trim((string) ($payload['email'] ?? ''));
    $password = (string) ($payload['password'] ?? '');
    $niveau = trim((string) ($payload['niveu'] ?? $payload['niveau'] ?? ''));
    $modules = $payload['modules'] ?? [];

    if ($nom === '' || $prenom === '' || $cin === '' || $email === '' || $password === '' || $niveau === '') {
        return [400, ['success' => false, 'message' => 'Missing required fields.']];
    }

    if (!isValidEmail($email)) {
        return [422, ['success' => false, 'message' => 'Invalid email format.']];
    }

    if (!isValidCin($cin)) {
        return [422, ['success' => false, 'message' => 'CIN must contain exactly 8 digits.']];
    }

    if (strlen($password) < 8) {
        return [422, ['success' => false, 'message' => 'Password must be at least 8 characters.']];
    }

    if (empty($modules) || !is_array($modules)) {
        return [400, ['success' => false, 'message' => 'Please select at least one module.']];
    }

    if (count($modules) > 2) {
        return [422, ['success' => false, 'message' => 'Maximum 2 modules allowed.']];
    }

    $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? OR cin = ?');
    $stmt->execute([$email, $cin]);

    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        return [409, ['success' => false, 'message' => 'User with this email or CIN already exists.']];
    }

    $placeholders = implode(',', array_fill(0, count($modules), '?'));
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM modules WHERE id IN ($placeholders)");
    $stmt->execute($modules);
    $validCount = (int) $stmt->fetchColumn();

    if ($validCount !== count($modules)) {
        return [422, ['success' => false, 'message' => 'One or more selected modules are invalid.']];
    }

    try {
        $this->db->beginTransaction();

        $userId = bin2hex(random_bytes(16));
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare('INSERT INTO users (id, nom, prenom, cin, email, niveu, passwordHash) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $nom, $prenom, $cin, $email, $niveau, $passwordHash]);

        $stmt = $this->db->prepare('INSERT INTO inscriptions (id, userId, moduleId) VALUES (?, ?, ?)');
foreach ($modules as $moduleId) {
    $inscriptionId = bin2hex(random_bytes(16));
    $stmt->execute([$inscriptionId, $userId, $moduleId]);
}

        $this->db->commit();

    } catch (\Exception $e) {
        return [500, ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]];
    }

    return [
        201,
        [
            'success' => true,
            'message' => 'User created successfully.',
            'data' => [
                'userId' => $userId,
            ],
        ],
    ];
}

    public function me(): array {
        $userId = $_SESSION['userId'] ?? null;

        if (!$userId) {
            return [401, ['success' => false, 'message' => 'Unauthorized.']];
        }

        $stmt = $this->db->prepare('SELECT 
    users.id,
    users.nom,
    users.prenom,
    users.cin,
    users.email,
    users.niveu,
    GROUP_CONCAT(modules.id) as module_ids,
    GROUP_CONCAT(modules.nom_module) as module_names
FROM users
LEFT JOIN inscriptions ON users.id = inscriptions.userId
LEFT JOIN modules ON inscriptions.moduleId = modules.id
WHERE users.id = ?
GROUP BY 
    users.id,
    users.nom,
    users.prenom,
    users.cin,
    users.email,
    users.niveu;');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            unset($_SESSION['userId'], $_SESSION['cin']);
            return [401, ['success' => false, 'message' => 'Invalid session.']];
        }

        return [
            200,
            [
                'success' => true,
                'data' => [ 
                    'user' => $user,
                ],
            ],
        ];
    }

    public function logout(): array {
        unset($_SESSION['userId'], $_SESSION['cin']);

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }

        session_destroy();

        return [200, ['success' => true, 'message' => 'Logged out successfully.']];
    }
    public function check(): array {
        $isAuthenticated = isset($_SESSION['userId']);
        return [200, ['success' => true, 'authenticated' => $isAuthenticated]];
    }
    private function sanitizeUser(array $user): array {
        return [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'cin' => $user['cin'],
            'email' => $user['email'],
            'niveau' => $user['niveu'] ?? ($user['niveau'] ?? ''),

        ];
    }

    public function update(array $payload): array {
    $userId = $_SESSION['userId'] ?? null;
    if (!$userId) {
        return [401, ['success' => false, 'message' => 'Unauthorized.']];
    }

    $nom     = trim((string) ($payload['nom']    ?? ''));
    $prenom  = trim((string) ($payload['prenom'] ?? ''));
    $niveau  = trim((string) ($payload['niveau'] ?? ''));
    $modules = $payload['modules'] ?? [];

    if ($nom === '' || $prenom === '' || $niveau === '') {
        return [400, ['success' => false, 'message' => 'Missing required fields.']];
    }

    if (!is_array($modules) || count($modules) === 0 || count($modules) > 2) {
        return [422, ['success' => false, 'message' => 'Select between 1 and 2 modules.']];
    }

    $placeholders = implode(',', array_fill(0, count($modules), '?'));
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM modules WHERE id IN ($placeholders)");
    $stmt->execute($modules);
    if ((int) $stmt->fetchColumn() !== count($modules)) {
        return [422, ['success' => false, 'message' => 'Invalid module selection.']];
    }

    try {
        $this->db->beginTransaction();

        $stmt = $this->db->prepare('UPDATE users SET nom = ?, prenom = ?, niveu = ? WHERE id = ?');
        $stmt->execute([$nom, $prenom, $niveau, $userId]);

        $stmt = $this->db->prepare('DELETE FROM inscriptions WHERE userId = ?');
        $stmt->execute([$userId]);

        $stmt = $this->db->prepare('INSERT INTO inscriptions (id, userId, moduleId) VALUES (?, ?, ?)');
        foreach ($modules as $moduleId) {
            $stmt->execute([bin2hex(random_bytes(16)), $userId, $moduleId]);
        }

        $this->db->commit();
        return [200, ['success' => true, 'message' => 'Profile updated successfully.']];

    } catch (\Exception $e) {
        $this->db->rollBack();
        return [500, ['success' => false, 'message' => 'Update failed: ' . $e->getMessage()]];
    }
}
}


