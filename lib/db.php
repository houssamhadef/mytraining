<?php 
$host = "127.0.0.1";
$username = "root";
$password = "root";

try {
    $db = new PDO("mysql:host=$host", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("CREATE DATABASE IF NOT EXISTS mytraining");

    $db = new PDO("mysql:host=$host;dbname=mytraining", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (PDOException $error){
    die("Database connection failed: " . $error->getMessage());
}

?>