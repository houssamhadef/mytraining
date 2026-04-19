<?php 
require_once "lib/db.php";
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");
$body = json_decode(file_get_contents("php://input"), true) ?? [];
$action = $_GET["action"] ?? null;
$params = explode("/", $action);
$action = $params[0] ?? null;

$cin = $body["cin"] ?? null;
$email = $body["email"] ?? null;
$password = $body["password"] ?? null;


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
try {
    if ($action == "login"){
    if ((!$cin && !$email) || !$password){
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields", "success" => false]);
    exit;
}
    if ($email){
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }else if ($cin){
    $stmt = $db->prepare("SELECT * FROM users WHERE cin = ?");
    $stmt->execute([$cin]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$user){
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "User with this email or cin not found"]);
        exit;
    }

    $stored_hash = $user['passwordHash'];
    if (!password_verify($password, $stored_hash)){
        http_response_code(401);
        echo json_encode(["error" => "Invalid email or password", "success" => false]);
        exit;
    }
    session_start([
    'cookie_lifetime' => 86400,
    'cookie_path' => '/',
    'cookie_samesite' => 'None',
    'cookie_secure' => false,
    ]);
    $_SESSION['userId'] = $user['id'];
    $_SESSION['CIN'] = $user['cin'];

    http_response_code(200);
    echo json_encode([
        "success" => true,
    ]);
    
    }else if ($action == "register"){
        $nom = $body["nom"] ?? null;
        $prenom = $body["prenom"] ?? null;
        $cin = $body["cin"] ?? null;
        $niveu = $body["niveu"] ?? null;

        if (!$nom || !$prenom || !$cin || !$email || !$niveu || !$password){
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields", "success" => false]);
            exit;
        }
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR cin = ?");
        $stmt->execute([$email, $cin]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = bin2hex(random_bytes(16));

        if ($user){
            http_response_code(409);
            echo json_encode(["success" => false, "message" => "User with this email or cin already exist"]);
            exit;
        }
        $stmt = $db->prepare("INSERT INTO users (id, nom, prenom, cin, email, niveu, passwordHash) VALUES(?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $nom, $prenom, $cin, $email, $niveu, $hashed_password]);
        

        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "User created successfuly"
        ]);

    }else if ($action == "modules"){

        $moduleId = $params[1] ?? null;
        $nom_module = $body['nom_module'] ?? null;
        $description = $body['description'] ?? null;

        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            if (!$nom_module){
                echo json_encode(["success" => false, "message" => "Nom Module is required"]);
                exit;
            }

            $stmt = $db->prepare("INSERT INTO modules(nom_module, description) VALUES(?, ?)");
            $stmt->execute([$nom_module, $description]);
            $id = $db->lastInsertId();
            $stmt = $db->prepare("SELECT * FROM modules WHERE id = ?");
            $stmt->execute([$id]);
            $module = $stmt->fetch(PDO::FETCH_ASSOC);
            http_response_code(201);
            echo json_encode(["success" => true, "message" => "Module created", "data" => $module]);

        }else if ($_SERVER['REQUEST_METHOD']  == 'GET'){
            if (!$moduleId){
                echo json_encode(["success" => false, "message" => "ModuleId is required "]);
                exit;
            }
            $stmt = $db->prepare("SELECT * FROM modules WHERE id = ?");
            $stmt->execute([$moduleId]);
            $module = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(["success" => true, "data" => $module]);

        }else if ($_SERVER['REQUEST_METHOD'] == 'DELETE'){

                if (!$moduleId){
                echo json_encode(["success" => false, "message" => "ModuleId is required "]);
                exit;
            }
            $stmt = $db->prepare("DELETE FROM modules WHERE id = ?");
            $stmt->execute([$moduleId]);
            http_response_code(200);
            echo json_encode(["success" => true, "message" => "Module deleted successfuly"]);

        }else if ($_SERVER['REQUEST_METHOD'] == 'PUT'){
            if (!$moduleId){
                echo json_encode(["success" => false, "message" => "ModuleId is required "]);
                exit;
            }
            
            $stmt = $db->prepare("UPDATE modules SET nom_module = ?, description = ? WHERE id = ?");
            $stmt->execute([$nom_module, $description, $moduleId]);
            echo json_encode(["success" => true, "message" => "Module updated"]);
        }
    }
}catch (Exception $error){
    http_response_code(500);
    echo json_encode(["Server Error" => $error->getMessage(),
    "success" => false]);
    exit;
}
?>