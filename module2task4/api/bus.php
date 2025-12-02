<?php
include "db.php";

$datas = $pdo->query("SELECT * FROM `bus`
JOIN `route` ON `bus`.`route` = `route`.`id`
")->fetchAll();

foreach ($datas as $data) {
    $route = $data['name'];
    $routes[$route][] = [
        "plate" => $data['plate'],
        "time" => $data['time'],
    ];
}
echo json_encode($routes);
?>