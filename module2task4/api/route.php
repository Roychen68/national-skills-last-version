<?php
include "db.php";

$datas = $pdo->query("SELECT * FROM `route-station`
JOIN `route` ON `route-station`.`route` = `route`.`id`
JOIN `station` ON `route-station`.`station` = `station`.`id`
")->fetchAll();
foreach ($datas as $data) {
    $route = $data[6];
    $routes[] = [
        "station" => $data['name'],
        "need" => $data['need'],
        "stop" => $data['stop'],
    ];
    $result = [
        $route => [
            "count" => $data['stations'],
            "stations" => $routes,
        ],
    ];
}
echo json_encode($result);
?>