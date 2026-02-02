<?php
header("Content-type:application/json");
include "db.php";
session_destroy();

$start = isset($_POST['form']['start']) ? $_POST['form']['start'] : '';
$end   = isset($_POST['form']['end'])   ? $_POST['form']['end']   : '';

// $start = "3";
// $end   = "4";

if ($start === '' || $end === '') {
    echo json_encode(['error' => 'start / end 缺少參數'], JSON_UNESCAPED_UNICODE);
    exit;
}

function bfs_init()
{
    global $start;
    global $end;
    global $pdo;

    $startId = (string)$start;
    $endId   = (string)$end;

    // 站點
    $rows = $pdo->query("SELECT * FROM `station`")->fetchAll(PDO::FETCH_ASSOC);
    $stations = [];
    foreach ($rows as $r) {
        $id = (string)$r['id'];
        // temp = 單向鄰居（順向）
        $stations[$id] = $r + ['temp' => []];
        $stations[$id]['id'] = $id;
    }

    // 建立 route -> station list，依 station 排序（因為 rank 都是 1，不用 rank）
    $rsAll = $pdo->query("
        SELECT `route`, `station`, `rank`, `need`, `stop`
        FROM `route-station`
        ORDER BY `route`, `station`
    ")->fetchAll(PDO::FETCH_ASSOC);

    $routeStations = []; // $routeStations[route] = [stationId1, stationId2, ...]
    foreach ($rsAll as $row) {
        $route = $row['route'];
        $st    = (string)$row['station'];
        if (!isset($routeStations[$route])) {
            $routeStations[$route] = [];
        }
        $routeStations[$route][] = $st;
    }

    /*
    $routeStations = {
        $routeId: [
            $stationId1,
            $stationId2,
            ...
        ]
    }
    */

    // 單向鄰接：只連「同一路線中相鄰的站」 station[i] -> station[i+1]
    foreach ($routeStations as $route => $stationList) {
        if (!$stationList) continue;

        // 去重 & reindex
        $stationList = array_values(array_unique(array_map('strval', $stationList)));
        $count = count($stationList);

        for ($i = 0; $i < $count - 1; $i++) {
            $u = $stationList[$i];
            $v = $stationList[$i + 1];
            if (isset($stations[$u])) {
                $stations[$u]['temp'][] = $v; // 只加「往後」的邊
            }
        }
    }

    // 去重 neighbors
    foreach ($stations as &$st) {
        $st['temp'] = array_values(array_unique(array_map('strval', $st['temp'])));
    }
    unset($st);

    // BFS：因為是單向邊，天然避免倒走
    $results = array_values($stations);

    // DFS-style BFS 搜尋所有路徑（沿著 temp）
    function bfs($stack, $now, $endId, $results, &$collector)
    {
        if ((string)$now['id'] === (string)$endId) {
            $collector[] = $stack;
            return;
        }

        foreach ($now['temp'] as $itemId) {
            $itemId = (string)$itemId;
            if (in_array($itemId, $stack, true)) continue; // 保險避免 loop

            $match = array_values(array_filter(
                $results,
                function ($f) use ($itemId) {
                    return (string)$f['id'] === $itemId;
                }
            ));

            if (!$match) continue;
            bfs([...$stack, $itemId], $match[0], $endId, $results, $collector);
        }
    }

    $ans = [];
    if (!isset($stations[$startId])) {
        // 起點不存在
        return [];
    }

    bfs([$startId], $stations[$startId], $endId, $results, $ans);
    return $ans;
}

$projects = bfs_init();

// 計算（每段 = stop(u) + need(v)；僅順向）
$ans = [];
foreach ($projects as $project) {
    $temp = [
        'totalTime'     => 0,
        'transferCount' => 0,
        'routeStack'    => [],
        'detailRoute'   => [],
        'res'           => array_map('strval', $project),
    ];

    $currentRoute = '';

    for ($i = 0; $i < count($project) - 1; $i++) {
        $u = $project[$i];
        $v = $project[$i + 1];

        // v 端：依 route 拿 need（不再使用 rank）
        $rowsV = $pdo->query("
            SELECT `route`, `need`
            FROM `route-station`
            WHERE `station` = " . (int)$v . "
        ")->fetchAll(PDO::FETCH_ASSOC);

        if (!$rowsV) continue;

        $vNeedByRoute = []; // $vNeedByRoute[route] = need(v)
        foreach ($rowsV as $rv) {
            $r = $rv['route'];
            $vNeedByRoute[$r] = $rv['need'];
        }

        // u 端：續乘優先，其次 stop 較小
        $rowsU = $pdo->query("
            SELECT *
            FROM `route-station`
            WHERE `station` = " . (int)$u . "
            ORDER BY (`route` = " . $pdo->quote($currentRoute) . ") DESC, `stop` ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        if (!$rowsU) continue;

        // 只需要「同一路線」，因為 BFS 已保證 u->v 是相鄰的站
        $choice = null;
        foreach ($rowsU as $ru) {
            $r = $ru['route'];
            if (isset($vNeedByRoute[$r])) {
                $choice = $ru;
                break;
            }
        }
        if ($choice === null) continue;

        $routeId = $choice['route'];
        $needV   = isset($vNeedByRoute[$routeId]) ? $vNeedByRoute[$routeId] : 0;

        // 段時間 = stop(u) + need(v)
        if ($i == 0) {
            // 第一段：如果整個路徑只有 2 個站，直接用 need(v)
            if (count($project) == 2) {
                $segTime = $needV;
            } else {
                // 第一段中間站可以視需求決定，這裡先當作 0 或 stop+need
                $segTime = $choice['stop'] + $needV;
            }
        } elseif ($i + 1 == count($project) - 1) {
            // 最後一段
            $segTime = $needV;
        } else {
            $segTime = $choice['stop'] + $needV;
        }

        // route 詳細
        $detail = $pdo->query("
            SELECT *
            FROM `route`
            WHERE `id` = " . (int)$routeId . "
            LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC);
        if (!$detail) $detail = ['id' => $routeId];

        // 合併同一路線連續段
        $last = count($temp['routeStack']) - 1;
        if ($last >= 0 && $temp['routeStack'][$last]['route'] == $routeId) {
            $temp['routeStack'][$last]['time']       += $segTime;
            $temp['routeStack'][$last]['endStation']  = $v;
        } else {
            $temp['routeStack'][] = [
                'id'           => $choice['id'],
                'station'      => $u,
                'startStation' => $u,
                'endStation'   => $v,
                'rank'         => $choice['rank'],
                'need'         => $choice['need'],
                'stop'         => $choice['stop'],
                'route'        => $routeId,
                'detail'       => $detail,
                'time'         => $segTime,
            ];
        }

        $temp['totalTime'] += $segTime;
        $currentRoute       = $routeId;
    }

    // 轉車次數 = 合併段數 - 1
    $temp['transferCount'] = max(0, count($temp['routeStack']) - 1);

    // 詳細路線
    foreach ($temp['routeStack'] as $seg) {
        $temp['detailRoute'][] = [
            'startRoute'   => $seg['route'],
            'startStation' => $seg['startStation'],
            'endRoute'     => $seg['route'],
            'endStation'   => $seg['endStation'],
            'segmentTime'  => $seg['time'],
        ];
    }

    $ans[] = $temp;
}

// 名稱替換（前端顯示）
$stationNameById = $pdo->query("SELECT id, name FROM `station`")
    ->fetchAll(PDO::FETCH_KEY_PAIR);
$routeNameById   = $pdo->query("SELECT id, name FROM `route`")
    ->fetchAll(PDO::FETCH_KEY_PAIR);

foreach ($ans as &$plan) {
    foreach ($plan['routeStack'] as &$seg) {
        $rid      = $seg['route'];
        $sidStart = $seg['startStation'];
        $sidEnd   = $seg['endStation'];

        if (isset($routeNameById[$rid])) {
            $seg['route'] = $routeNameById[$rid];
        }
        if (isset($stationNameById[$sidStart])) {
            $seg['startStation'] = $stationNameById[$sidStart];
        }
        if (isset($stationNameById[$sidEnd])) {
            $seg['endStation'] = $stationNameById[$sidEnd];
        }
    }
    unset($seg);
}
unset($plan);

// 如需依總時間排序：
// usort($ans, fn($a, $b) => $a['totalTime'] - $b['totalTime']);

echo json_encode($ans, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
