<?php
/* @var PDO $pdo */
include 'db.php';
$routes = [];
$results = $pdo->query("SELECT * FROM `route`");
foreach ($results as $result) {
    $routes['routes'][] = [
        'name' => $result['name'],
        'stationCountCount' => $result['stations']
    ];
}
echo json_encode($routes);