<?php
include "db.php";

switch ($_POST['action']) {
    case 'veri':
        echo rand(1000,9999);
        break;
    case "station":
        $pdo->query("DELETE FROM `route-station` WHERE `station` = `{$_POST['id']}`");
        $pdo->query("DELETE FROM `{$_POST['action']}` WHERE `id` = '{$_POST['id']}'");
        break;
    case 'route':
        $pdo->query("DELETE FROM `route-station` WHERE `route` = '{$_POST['id']}'");
        $pdo->query("DELETE FROM `route` WHERE `id` = '{$_POST['id']}'");
        break;
    default:
        $pdo->query("DELETE FROM `{$_POST['action']}` WHERE `id` = '{$_POST['id']}'");
        break;
}
?>