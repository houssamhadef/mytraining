<?php
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/utils.php';
require_once __DIR__ . '/../middleware/session.php';
class ModulesController {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function list(): array {
        $stmt = $this->db->query('SELECT id, nom_module, description, createdAt, updateAt FROM modules ORDER BY id DESC');
        $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            200,
            [
                'success' => true,
                'data' => [
                    'modules' => $modules,
                ],
            ],
        ];
    }

    public function getById(int $id): array {
        $module = $this->findById($id);

        if (!$module) {
            return [404, ['success' => false, 'message' => 'Module not found.']];
        }

        return [200, ['success' => true, 'data' => ['module' => $module]]];
    }

    public function create(array $payload): array {
        $auth = $this->ensureAuthenticated();
        if ($auth !== null) {
            return $auth;
        }

        $nomModule = trim((string) ($payload['nom_module'] ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));

        if ($nomModule === '') {
            return [422, ['success' => false, 'message' => 'nom_module is required.']];
        }

        $stmt = $this->db->prepare('INSERT INTO modules (nom_module, description) VALUES (?, ?)');
        $stmt->execute([$nomModule, $description]);

        $moduleId = (int) $this->db->lastInsertId();
        $module = $this->findById($moduleId);

        return [201, ['success' => true, 'message' => 'Module created.', 'data' => ['module' => $module]]];
    }

    public function update(int $id, array $payload): array {
        $auth = $this->ensureAuthenticated();
        if ($auth !== null) {
            return $auth;
        }

        $module = $this->findById($id);
        if (!$module) {
            return [404, ['success' => false, 'message' => 'Module not found.']];
        }

        $nomModule = trim((string) ($payload['nom_module'] ?? $module['nom_module']));
        $description = trim((string) ($payload['description'] ?? $module['description'] ?? ''));

        if ($nomModule === '') {
            return [422, ['success' => false, 'message' => 'nom_module is required.']];
        }

        $stmt = $this->db->prepare('UPDATE modules SET nom_module = ?, description = ? WHERE id = ?');
        $stmt->execute([$nomModule, $description, $id]);

        $updatedModule = $this->findById($id);

        return [200, ['success' => true, 'message' => 'Module updated.', 'data' => ['module' => $updatedModule]]];
    }

    public function delete(int $id): array {
        $auth = $this->ensureAuthenticated();
        if ($auth !== null) {
            return $auth;
        }

        $module = $this->findById($id);
        if (!$module) {
            return [404, ['success' => false, 'message' => 'Module not found.']];
        }

        $stmt = $this->db->prepare('DELETE FROM modules WHERE id = ?');
        $stmt->execute([$id]);

        return [200, ['success' => true, 'message' => 'Module deleted.']];
    }

    private function findById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT id, nom_module, description, createdAt, updateAt FROM modules WHERE id = ?');
        $stmt->execute([$id]);
        $module = $stmt->fetch(PDO::FETCH_ASSOC);

        return $module ?: null;
    }

    private function ensureAuthenticated(): ?array {
        if (!isset($_SESSION['userId'])) {
            return [401, ['success' => false, 'message' => 'Unauthorized.']];
        }

        return null;
    }
}
