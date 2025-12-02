<?php
include "db.php";

switch ($_POST['action']) {
    case 'station':
        $exist = $pdo->query("SELECT `name` FROM `station` WHERE `name` = '{$_POST['name']}'")->fetch();
        if ($exist['name'] == $_POST['name']) {
            echo "站點已存在";  
        } else {
            echo "編輯成功";
            $pdo->query("UPDATE `station` SET `name`='{$_POST['name']}' WHERE `id` = '{$_POST['id']}'");
        }
        
        break;
    case 'route':
        $exist = $pdo->query("SELECT `name` FROM `route` WHERE `name` = '{$_POST['name']}' AND NOT `id` = '{$_POST['id']}'")->fetch();
        if ($exist) {
            echo "路線已存在";  
        } else {
            echo "編輯成功";
            $pdo->query("DELETE FROM `route-station` WHERE `route` = '{$_POST['id']}'");
            $stations = $_POST['stations'];
            $pdo->query("UPDATE `route` SET `name`='{$_POST['name']}',`row`='{$_POST['row']}' WHERE `id` = '{$_POST['id']}'");
            $route = $pdo->query("SELECT `id` FROM `route` WHERE `name` = '{$_POST['name']}'")->fetchColumn();
            foreach ($stations as $station) {
                $pdo->query("INSERT INTO `route-station`(`station`, `need`, `stop`, `route`,`rank`) VALUES ('{$station['name']}','{$station['need']}','{$station['stop']}','$route','{$station['rank']}')");
            }
        }
        break;
    case 'bus':
        $exist = $pdo->query("SELECT COUNT(*) FROM `bus` WHERE `plate` = '{$_POST['plate']}'")->fetchColumn();
        if ($exist > 1) {
            echo "站點已存在";  
        } else {
            echo "編輯成功";
            $pdo->query("UPDATE `bus` SET `time`='{$_POST['time']}' WHERE `id` = '{$_POST['id']}'");
        }
        break;
    case 'form':
        $form = $pdo->query("SELECT `form` FROM `form` WHERE 1")->fetchColumn();
        echo $form;
        if ($form == 1) {
            $pdo->query("UPDATE `form` SET `form`='0' WHERE 1");
        } else {
            $pdo->query("UPDATE `form` SET `form`='1' WHERE 1");
        }
        
        break;
    case 'basic':
        $pdo->query("UPDATE `basic` SET `start`='{$_POST['start']}',`end`='{$_POST['end']}' WHERE 1");
        break;
}
?>