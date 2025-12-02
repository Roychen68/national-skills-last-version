<?php
include "db.php";

$datas = $pdo->query("SELECT * FROM `response`
JOIN `route` ON `response`.`route` = `route`.`id`")->fetchAll();
foreach ($datas as $data) {
    $name = $data[1];
    $route = $data['name'];
    $result[$route][$name][] = [
        "mail" => $data['mail'],
        "note" => $data['note'],
    ];
}
echo json_encode($result);
?>