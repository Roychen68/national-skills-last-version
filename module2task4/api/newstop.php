<?php
include "db.php";

$name = $_POST['name'] ?? null;

if (!isset($name)) {
    echo json_encode([
        "success"=> false,
        "data"=> "MSG_MISSING_FIELD",
    ]);
} else {
    $exist = $pdo->query("SELECT COUNT(*) FROM `station` WHERE `name` = '{$_POST['name']}'")->fetchColumn();
    if ($exist > 0) {
        echo json_encode([
            "success"=> false,
            "data"=> "MSG_STOP_EXIST",
        ]);
    } else {
        echo json_encode([
            "success"=>true,
            "data"=>[
                "name"=>$name,
            ],
        ]);
        $pdo->query("INSERT INTO `station`(`name`) VALUES ('{$_POST['name']}')");
    }
    
}
