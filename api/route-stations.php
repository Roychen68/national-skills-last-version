<?php
/* @var PDO $pdo */
include 'db.php';
$route = [];
$results = $pdo->query("
SELECT * FROM `route-station`
JOIN `route` ON `route-station`.`route` = `route`.`id`
JOIN `station` ON `route-station`.`station` = `station`.`id`
WHERE `route`.`name` = '{$_GET['route']}'
")->fetchAll();
if (!$results) {
    echo json_encode([
        "error"=>"找不到路線:".$_GET['route']
    ]);
    return;
} else {
    foreach ($results as $item) {
        $stations[] = $item['name'];
    }
    $route[] = [
        "routeName" => $_GET['route'],
        "stations" => $stations,
    ];
}
echo json_encode($route);