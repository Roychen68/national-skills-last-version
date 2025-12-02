<?php
/* @var PDO $pdo */
include "db.php";

$buses = [];

if (!isset($_GET['plate'])) {
    $results = $pdo->query("SELECT * FROM `bus`")->fetchAll();
    foreach ($results as $item) {
        $buses['buses'][] = [
            "plate" => $item['plate'],
            "runtime" => $item['time']
        ];
    }
} else {
    $results = $pdo->query("
    SELECT * FROM `bus`
    JOIN `route` ON `bus`.`route` = `route`.`id`
    WHERE `plate` = '{$_GET['plate']}'
    ")->fetch();
    if (!$results) {
        echo json_encode([
            "error" => "找不到車輛:" . $_GET['plate']
        ]);
        return;
    } else {
        $buses['buses'][] = [
            "plate" => $results['plate'],
            "runtime" => $results['time'],
            "currentRoute" => $results['name']
        ];
    }
}
echo json_encode($buses);