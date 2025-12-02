<?php
header("Content-type:application/json");
include "db.php";
session_destroy();

/**
 * 輸入：
 *   $_POST['form']['start'] 起點站ID
 *   $_POST['form']['end']   終點站ID
 *
 * 規則：
 *   1) 路線只允許「順向」：同一路線 rank r -> r+1（不允許 r+1 -> r）
 *   2) 每段 (u->v) 的時間 = stop(u) + need(v)
 *      => 第一站不計 need，終點不計 stop
 */

//$start = isset($_POST['form']['start']) ? $_POST['form']['start'] : '';
//$end = isset($_POST['form']['end']) ? $_POST['form']['end'] : '';

$start = "3";
$end = "4";
if ($start == '' || $end == '') {
    echo json_encode(['error' => 'start / end 缺少參數'], JSON_UNESCAPED_UNICODE);
    exit;
}

function bfs_init()
{
    global $start;
    global $end;
    global $pdo;

    $startId = $start;
    $endId = $end;

    // 站點
    $rows = $pdo->query("SELECT * FROM `station`")->fetchAll(2);
    $stations = [];
    foreach ($rows as $r) {
        $stations[$r['id']] = $r + ['temp' => []]; // temp = 單向鄰居（順向）
    }

    // 建立 route -> rank->station 對照
    $rsAll = $pdo->query("SELECT `route`,`station`,`rank` FROM `route-station` ORDER BY `route`,`rank`")->fetchAll(2);
    $routeRankToStation = []; // $routeRankToStation[route][rank] = stationId
    foreach ($rsAll as $row) {
        $route = $row['route'];
        $rank = $row['rank'];
        $st = $row['station'];
        if (!isset($routeRankToStation[$route])) $routeRankToStation[$route] = [];
        $routeRankToStation[$route][$rank] = $st;
    }
    /*
    $routeRankToStation={
        $routeid: {
            $rank: $st
        }
    }
    */

    // 單向鄰接：只連 r -> r+1
    foreach ($routeRankToStation as $route => $rankMap) {
        if (!$rankMap) continue;
        ksort($rankMap, SORT_NUMERIC);
        $ranks = array_keys($rankMap);
        for ($i = 0; $i < count($ranks) - 1; $i++) {
            $r = $ranks[$i];
            $rp = $ranks[$i + 1];
            if ($rp == $r + 1) {
                $u = $rankMap[$r];
                $v = $rankMap[$rp];
                if (isset($stations[$u])) $stations[$u]['temp'][] = $v; // 只加「往後」的邊
            }
        }
    }

    // 去重
    foreach ($stations as &$st) {
        $st['temp'] = array_values(array_unique(array_map('strval', $st['temp'])));
    }
    unset($st);

    // BFS：因為是單向邊，天然避免倒走
    $results = array_values($stations);
    function bfs($stack, $now, $endId, $results, &$collector)
    {
        if ($now['id'] == $endId) {
            $collector[] = $stack;
            return;
        }
        foreach ($now['temp'] as $itemId) {
            if (in_array($itemId, $stack, true)) continue; // 保險
            $match = array_values(array_filter($results, fn($f) => $f['id'] == $itemId));
            if (!$match) continue;
            bfs([...$stack, $itemId], $match[0], $endId, $results, $collector);
        }
    }

    $ans = [];
    if (!isset($stations[$startId])) return [];
    bfs([$startId], $stations[$startId], $endId, $results, $ans);
    return $ans;
}

$projects = bfs_init();

// 計算（每段 = stop(u) + need(v)；僅順向）
$ans = [];
foreach ($projects as $project) {
    $temp = [
        'totalTime' => 0,
        'transferCount' => 0,
        'routeStack' => [],
        'detailRoute' => [],
        'res' => array_map('strval', $project),
    ];

    $currentRoute = '';
//    echo count($project)."L".PHP_EOL.PHP_EOL;
    for ($i = 0; $i < count($project) - 1; $i++) {
        $u = $project[$i];
        $v = $project[$i + 1];

        // v 端：拿到它在各 route 的 rank 與 need（要用 need(v)）
        $rowsV = $pdo->query("SELECT `route`,`rank`,`need` FROM `route-station` WHERE `station` = {$v}")->fetchAll(2);
        if (!$rowsV) continue;
        $vRouteRank = [];
        $vNeedByRoute = [];
        foreach ($rowsV as $rv) {
            $r = $rv['route'];
            $vRouteRank[$r] = $rv['rank'];
            $vNeedByRoute[$r] = $rv['need'];
        }

        // u 端：續乘優先，其次 stop 較小
        $rowsU = $pdo->query("
            SELECT * FROM `route-station`
            WHERE `station` = {$u}
            ORDER BY (`route` = '{$currentRoute}') DESC, `stop` ASC
        ")->fetchAll(2);
        if (!$rowsU) continue;

        // 只接受「同一路線且 rank(v) = rank(u) + 1」→ 強制順向
        $choice = null;
        foreach ($rowsU as $ru) {
            $r = $ru['route'];
            if (isset($vRouteRank[$r]) && ($ru['rank'] + 1) == $vRouteRank[$r]) {
                $choice = $ru;
                break;
            }
        }
        if ($choice == null) continue;

        $routeId = $choice['route'];
        $needV = isset($vNeedByRoute[$routeId]) ? $vNeedByRoute[$routeId] : 0;

        // 段時間 = stop(u) + need(v)
//        echo ($i + 1) . PHP_EOL;
        if ($i == 0) {
            $segTime = 0;
            if (count($project) == 2) {
                $segTime = $needV;
            }
        } elseif ($i + 1 == count($project) - 1) {
//            echo 'e' . PHP_EOL;
            $segTime = $needV;
        } else {
            $segTime = $choice['stop'] + $needV;
        }

        // route 詳細
        $detail = $pdo->query("SELECT * FROM `route` WHERE `id` = {$routeId} LIMIT 1")->fetch(2);
        if (!$detail) $detail = ['id' => $routeId];

        // 合併同一路線連續段
        $last = count($temp['routeStack']) - 1;
        if ($last >= 0 && $temp['routeStack'][$last]['route'] == $routeId) {
            $temp['routeStack'][$last]['time'] += $segTime;
            $temp['routeStack'][$last]['endStation'] = $v;
        } else {
            $temp['routeStack'][] = [
                'id' => $choice['id'],
                'station' => $u,
                'startStation' => $u,
                'endStation' => $v,
                'rank' => $choice['rank'],
                'need' => $choice['need'],
                'stop' => $choice['stop'],
                'route' => $routeId,
                'detail' => $detail,
                'time' => $segTime
            ];
        }

        $temp['totalTime'] += $segTime;
        $currentRoute = $routeId;
    }

    // 轉車次數 = 合併段數 - 1
    $temp['transferCount'] = max(0, count($temp['routeStack']) - 1);

    // 詳細路線
    foreach ($temp['routeStack'] as $seg) {
        $temp['detailRoute'][] = [
            'startRoute' => $seg['route'],
            'startStation' => $seg['startStation'],
            'endRoute' => $seg['route'],
            'endStation' => $seg['endStation'],
            'segmentTime' => $seg['time']
        ];
    }

    $ans[] = $temp;
}

// 名稱替換（前端顯示）
$stationNameById = $pdo->query("SELECT id, name FROM `station`")->fetchAll(PDO::FETCH_KEY_PAIR);
$routeNameById = $pdo->query("SELECT id, name FROM `route`")->fetchAll(PDO::FETCH_KEY_PAIR);

foreach ($ans as &$plan) {
    foreach ($plan['routeStack'] as &$seg) {
        $rid = $seg['route'];
        $sidStart = $seg['startStation'];
        $sidEnd = $seg['endStation'];

        $seg['route'] = $routeNameById[$rid] ?? $seg['route'];
        $seg['startStation'] = $stationNameById[$sidStart] ?? $seg['startStation'];
        $seg['endStation'] = $stationNameById[$sidEnd] ?? $seg['endStation'];
    }
    unset($seg);
}
unset($plan);

// 如需依總時間排序：
// usort($ans, fn($a,$b) => $a['totalTime'] - $b['totalTime']);

echo json_encode($ans, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
