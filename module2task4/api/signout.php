<?php
include "db.php";

$token = $_GET['token'] ?? null;
$data = $pdo->query("SELECT * FROM `admin` WHERE `token` = '$token'")->fetch();
if ($data) {
    echo json_encode([
        "success" => true,
        "username" => $data['username'],
    ]);
    $pdo->query("UPDATE `admin` SET `token`='' WHERE `token` = '$token'");
} else {
    echo json_encode([
        "success" => false,
        "data" => "MSG_INAVLID_ACCESS_TOKEN",
    ]);
}

?>