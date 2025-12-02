<?php
/** @var PDO $pdo */
include __DIR__ . "/db.php";

switch ($_POST['action']) {
    case 'veri':
        echo rand(1000, 9999);
        break;
    case "route":
        $routes = $pdo->query("SELECT * FROM `route` ORDER BY `id`")->fetchAll(2);
        $results = [];
        for ($i = 0; $i < count($routes); $i++) {
            $results[] = [
                "id" => $routes[$i]['id'],
                "stations" => count($pdo->query("SELECT * FROM `route-station` where `route` = " . $routes[$i]['id'])->fetchAll(2)),
                "name" => $routes[$i]['name'],
                "row" => $routes[$i]['row']
            ];
        }
        echo json_encode($results);
        break;
    case 'route-station':
        $data = $pdo->query("
        SELECT * FROM `route-station`
        JOIN `route` ON `route-station`.`route` = `route`.`id`
        JOIN `station` ON `route-station`.`station` = `station`.`id`
        WHERE `route-station`.`route` = '{$_POST['id']}'")->fetchAll();
        echo json_encode($data);
        break;
    case 'bus':
        $data = $pdo->query("
        SELECT * FROM `bus`
        JOIN `route` ON `bus`.`route` = `route`.`id`")->fetchAll();
        echo json_encode($data);
        break;
    case 'station':
        $data = $pdo->query("SELECT * FROM `{$_POST['action']}`")->fetchAll();
        echo json_encode($data);
        break;
    case 'map':
        $stations = $pdo->query("SELECT * FROM `route-station`
        JOIN `route` ON `route-station`.`route` = `route`.`id`
        JOIN `station` ON `route-station`.`station` = `station`.`id`
        WHERE `route-station`.`route` = '{$_POST['route']}'")->fetchAll();
        foreach ($stations as $key => $station) {
            $prev = $pdo->query("SELECT SUM(`need`+`stop`) FROM `route-station` WHERE `route` = '{$_POST['route']}' AND `station` < '{$station['station']}'")->fetchColumn();
            $arrive = $prev + $station['need'];
            $laeve = $arrive + $station['stop'];
            $bus = $pdo->query("SELECT * FROM `bus` WHERE `route` = '{$_POST['route']}' AND `time` <= '$laeve' ORDER BY `time` DESC")->fetch();
            if (!empty($bus)) {
                if ($bus['time'] < $arrive) {
                    $station['class'] = '';
                    $station['bus'] = $bus['plate'] . '<br>將在' . ($arrive - $bus['time']) . '分鐘到站';
                } else {
                    $station['class'] = 'text-danger';
                    $station['bus'] = '<br>已到站';
                }
            } else {
                $station['class'] = 'text-secondary';
                $station['bus'] = '<br>未發車';
            }
            $stations[$key] = $station;
        }
        echo json_encode($stations);
        break;
    case "download":
        if ($_POST['type'] == 'json') {
            $results = $pdo->query("SELECT `name`,`mail` FROM `response`")->fetchAll(2);
            echo json_encode($results);
        } else {
            $data = $pdo->query("SELECT `name`,`mail`,`route`,`rate`,`mail` FROM `response`")->fetchAll(2);
            $f = [];
            $f[] = 'name,email,route,evaluate,note';
            foreach ($data as $d) {
                $f[] = implode(",", $d);
            }
            echo implode("\n", $f);
        }
        break;
    default:
        $results = $pdo->query("SELECT * FROM `{$_POST['action']}`")->fetchAll(2);
        echo json_encode($results);
        break;
}
?>