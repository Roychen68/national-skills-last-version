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

$exist = $pdo->query("SELECT * FROM `admin` WHERE `username` = '$username' AND `password` = '$password'")->fetchColumn();
if ($exist > 0) {
    echo json_encode([
        "success" => false,
        "data" => "MSG_USER_EXIST"
    ]);
    return;
}

echo json_encode([
    "success" => true,
    "data" => ""
]);
$pdo->query("INSERT INTO `admin`(`username`, `password`) VALUES ('$username','$password')");
?>