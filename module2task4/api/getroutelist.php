<?php
include "db.php";

$type = $_GET['type'] ?? null;
$keywword = $_GET['keyword'] ?? null;
$min = $_GET['min'] ?? null;
$max = $_GET['max'] ?? null;

if (!isset($type) || $type == 'all') {
    $datas = $pdo->query("SELECT * FROM `route`")->fetchAll();
    $result = [];
    foreach ($datas as $data) {
        $result[] = [
            "routename" => $data['name'],
            "stopcount" => $data['stations'],
        ];
    }
    echo json_encode([
        "success"=> true,
        "data" => $result,
    ]);
} else if ($type == 'count' && $max > $min) {
    $datas = $pdo->query("SELECT * FROM `route` WHERE `stations` BETWEEN '$min' AND '$max'")->fetchAll();
    $result = [];
    foreach ($datas as $data) {
        $result[] = [
            "routename" => $data['name'],
            "stopcount" => $data['stations'],
        ];
    }
    echo json_encode([
        "success" => true,
        "data" => $result,
    ]);
} else if ($type == 'name' && isset($keywword)) {
    $datas = $pdo->query("SELECT * FROM `route` WHERE `name` = '$keywword'")->fetchAll();
    $result = [];
    foreach ($datas as $data) {
        $result[] = [
            "routename" => $data['name'],
            "stopcount" => $data['stations'],
        ];
    }
    echo json_encode([
        "success" => true,
        "data" => $result,
    ]);
} else {
    echo json_encode([
        "success" => false,
        "data" => "MSG_WRONG_DATA_TYPE",
    ]);
    
}
