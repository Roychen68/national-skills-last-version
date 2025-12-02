<?php
/* @var PDO $pdo */
include "db.php";

switch ($_POST['action']) {
    case 'station':
        $exist = $pdo->query("SELECT COUNT(*) FROM `station` WHERE `name` = '{$_POST['name']}'")->fetchColumn();
        if ($exist > 0) {
            echo "站點已存在";  
        } else {
            echo "新增成功";
            $pdo->query("INSERT INTO `station`(`name`) VALUES ('{$_POST['name']}')");
        }
        
        break;
    case 'route':
        $exist = $pdo->query("SELECT COUNT(*) FROM `route` WHERE `name` = '{$_POST['name']}'")->fetchColumn();
        if ($exist > 0) {
            echo "路線已存在";  
        } else {
            echo "新增成功";
            $pdo->query("SELECT * FROM `route-station` WHERE 1");
            $stations = $_POST['stations'];
            $pdo->query("INSERT INTO `route`(`name`,`row`) VALUES ('{$_POST['name']}','{$_POST['row']}')");
            $route = $pdo->query("SELECT `id` FROM `route` WHERE `name` = '{$_POST['name']}'")->fetchColumn();
            foreach ($stations as $station) {
                $pdo->query("INSERT INTO `route-station`(`station`, `need`, `stop`, `route`,`rank`) VALUES ('{$station['name']}','{$station['need']}','{$station['stop']}','$route','{$station['rank']}')");
            }
        }
        break;
    case 'bus':
        $exist = $pdo->query("SELECT COUNT(*) FROM `bus` WHERE `plate` = '{$_POST['plate']}'")->fetchColumn();
        if ($exist > 0) {
            echo "車輛已存在";
        } else {
            echo "新增成功";
            $pdo->query("INSERT INTO `bus`( `plate`, `time`, `route`) VALUES ('{$_POST['plate']}','{$_POST['time']}','{$_POST['route']}')");
        }
        break;
    case 'response':
        $form = $_POST['form'];
        date_default_timezone_set("Asia/Taipei");
        $now = date("Y-m-d H:i:s");
        $basic = $pdo->query("SELECT * FROM `basic` WHERE 1")->fetch();
        $enable = $pdo->query("SELECT `form` FROM `form` WHERE 1")->fetchColumn();
        if ($enable != 1) {
            echo "該表單目前不接受回應";
            return;
        }
        if ($basic['start'] > $now || $now > $basic['end']) {
            echo "該表單目前不在回應時間內";
            return;
        }
        $pdo->query("INSERT INTO `response`(`mail`, `name`, `route`, `note`, `rate`) VALUES ('{$form['mail']}','{$form['name']}','{$form['route']}','{$form['note']}','{$form['rate']}')");
        echo "已送出回應";
        break;
}
?>