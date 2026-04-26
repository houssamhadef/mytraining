<?php 
require_once "lib/db.php";
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] == 'GET'){
    global $db;
    try {
$db->exec("USE mytraining");

$users = "CREATE TABLE IF NOT EXISTS users (
id VARCHAR(36) PRIMARY KEY,
nom VARCHAR(50) NOT NULL,
prenom VARCHAR(50) NOT NULL,
cin VARCHAR(50) NOT NULL,
email VARCHAR(100) NOT NULL UNIQUE,
niveu  VARCHAR(50) NOT NULL,
passwordHash VARCHAR(255) NOT NULL,
createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updateAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$modules = "CREATE TABLE IF NOT EXISTS modules (
id INT AUTO_INCREMENT PRIMARY KEY,
nom_module VARCHAR(100) NOT NULL,
description TEXT,
createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updateAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$inscriptions = "CREATE TABLE IF NOT EXISTS inscriptions (
id VARCHAR(36) PRIMARY KEY,
userId VARCHAR(36) NOT NULL,
moduleId INT NOT NULL,
createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updateAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (userId) REFERENCES users(id),
FOREIGN KEY (moduleId) REFERENCES modules(id)
)";

$admins = "CREATE TABLE IF NOT EXISTS admins (
id VARCHAR(36) PRIMARY KEY,
nom VARCHAR(50) NOT NULL,
prenom VARCHAR(50) NOT NULL,
email VARCHAR(100) NOT NULL UNIQUE,
passwordHash VARCHAR(255) NOT NULL,
createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updateAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
    
    $db->exec($users);
    echo "[DB] Users table exist";
    $db->exec($modules);
    echo "[DB] Modules table exist";
    $db->exec($inscriptions);
    echo "[DB] Inscriptions table exist";
    $db->exec($admins);
    echo "[DB] Admins table exist";
    http_response_code(200);
    echo json_encode(["success" => true]);
}catch (Exception $error){
    http_response_code(500);
    echo json_encode(["Server Error" => $error->getMessage(), "success" => false]);
}
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
}
?>