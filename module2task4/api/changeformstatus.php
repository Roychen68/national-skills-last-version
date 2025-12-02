<?php
include "db.php";

$form = $pdo->query("SELECT `form` FROM `form` WHERE 1")->fetchColumn();

if ($form == 1) {
    $pdo->query("UPDATE `form` SET `form`='0' WHERE 1");
} else {
    $pdo->query("UPDATE `form` SET `form`='1' WHERE 1");
}

echo json_encode([
    "success" => true,
    "data" => "",
])
?>