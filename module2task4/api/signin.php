<?php
include "db.php";

$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;
if (!isset($username) || !isset($password)) {
    echo json_encode([
        "success" => false,
        "data" => "MSG_MISSING_FIELD",
    ]);
    return;
}

$exist = $pdo->query("SELECT * FROM `admin` WHERE `username` = '$username' AND `password` = '$password'")->fetch();
if ($exist < 1) {
    echo json_encode([
        "success" => false,
        "data" => "MSG_INVALID_LOGIN"
    ]);
}

$datas = $pdo->query("SELECT * FROM `admin` WHERE `username` = '$username' AND `password` = '$password'")->fetchAll();
foreach ($datas as $data) {
    echo json_encode([
        "success" => true,
        "id" => $data['id'],
        "username" => $data['username'],
    ]);
    $token = hash("sha256",$username);
    $pdo->query("UPDATE `admin` SET `token`='$token' WHERE `username` = '$username' AND `password` = '$password'");
}
?>