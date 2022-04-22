<?php
require_once "pdo.php";
header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT name from institution where name LIKE :prefix";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(':prefix' => $_GET['term'] . '%'));
$data = array();
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $data [] = $row['name'];
}

echo json_encode($data,JSON_PRETTY_PRINT);
