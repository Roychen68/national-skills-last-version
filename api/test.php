<?php
header("Content-type:application/json");
include "db.php";
session_destroy();

function bfs_init($startId, $endId)
{
    global $pdo;
    $results = $pdo->query("SELECT * FROM `station`")->fetchAll(2);
    foreach ($results as &$result) {
        $temp = $pdo->query("SELECT * FROM `route-station` where `station` = " . $result['id'])->fetchAll(2);
        $result['temp'] = [];
        foreach ($temp as $item) {
            $tmp = $pdo->query("SELECT * FROM `route-station` where `route` = " . $item['route'])->fetchAll(2);
            foreach ($tmp as $value) {
                if ($value['station'] != $result['id']) {
                    // 轉成字串，避免之後比較時型別不一致
                    $result['temp'][] = $value['station'];
                }
            }
        }
        // 去重，避免重複鄰居造成繞路
        $result['temp'] = array_values(array_unique($result['temp']));
    }
    // ★ 關鍵：解除 foreach by-reference 的殘留參照！
    unset($result);

//    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

    function bfs($arr = [], $stack = [], $now = [], $endId, $results, &$collector = [])
    {
        // 抵達終點：把當前路徑收進收集器，不要 return 全局結果，讓遞迴繼續找其它路徑
        if ($now['id'] == $endId) {
            $collector[] = $stack; // 例如 ["2","4","8"]
            return;
        }

        foreach ($now['temp'] as $itemId) {
            // 避免循環（不走回已在路徑中的節點）
            if (in_array($itemId, array_map('strval', $stack), true)) continue;

            // 找到鄰居的完整節點資料
            $match = array_values(array_filter($results, function ($f) use ($itemId) {
                return $f['id'] == $itemId;
            }));
            $nextNow = $match[0];
            // 繼續往下遞迴，不提前 return，讓其它分支也能被探索
            bfs($arr, [...$stack, $itemId], $nextNow, $endId, $results, $collector);
        }
    }


    $checked = $results;
    $ans = [];
    foreach ($results as $result) {
        if ($result['id'] == $startId) {
            bfs($checked, [$startId], $result, $endId, $results, $ans);
        }
    }
    return $ans;

}

$projects = bfs_init(2, 8);
//echo json_encode($projects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

// == MVP：ORDER BY + 無 JOIN 的最簡版（只改這一段） ==
$ans = [];
$planIdx = 1;

foreach ($projects as $project) {
    $temp = [
        'planId' => $planIdx++,   // 4.1 方案編號
        'totalTime' => 0,            // 4.2 行駛時間
        'transferCount' => 0,            // 4.3 轉車次數（段數-1）
        'routeStack' => [],           // 合併後的同一路段
        'detailRoute' => [],           // 4.4 詳細路線段
        'res' => $project      // 站點序列
    ];

    $currentRoute = ''; // 目前續乘的路線（字串；空字串代表無）

    for ($i = 0; $i < count($project) - 1; $i++) {
        $u = (int)$project[$i];
        $v = (int)$project[$i + 1];

        // v 端所有路線（做存在性/rank 對照用）
        $rowsV = $pdo->query("SELECT `route`,`rank` FROM `route-station` WHERE `station` = {$v}")->fetchAll(2);
        if (!$rowsV) continue;

        $vRouteRank = [];
        foreach ($rowsV as $rv) {
            $vRouteRank[$rv['route']] = (int)$rv['rank'];
        }

        $rowsU = $pdo->query("
            SELECT * FROM `route-station`
            WHERE `station` = {$u}
            ORDER BY (`need` + `stop`) ASC
        ")->fetchAll(2);
        if (!$rowsU) continue;

        // 先找「同一路線 & rank 相鄰」的第一個
        $choice = null;
        foreach ($rowsU as $ru) {
            $r = $ru['route'];
            if (isset($vRouteRank[$r]) && abs((int)$ru['rank'] - $vRouteRank[$r]) == 1) {
                $choice = $ru;
                break;
            }
        }
        // 找不到就退而求其次：同一路線（不看 rank）
        if ($choice == null) {
            foreach ($rowsU as $ru) {
                $r = $ru['route'];
                if (isset($vRouteRank[$r])) {
                    $choice = $ru;
                    break;
                }
            }
        }


        $routeId = $choice['route'];
        $segTime = (int)$choice['need'] + (int)$choice['stop'];

        $detail = $pdo->query("SELECT * FROM `route` WHERE `id` = {$routeId}")->fetch(2) ? : ['id' => $routeId];

        // 合併同一路線的連續段
        $last = count($temp['routeStack']) - 1;
        if ($last >= 0 && $temp['routeStack'][$last]['route'] == $routeId) {
            $temp['routeStack'][$last]['time'] += $segTime;
            $temp['routeStack'][$last]['endStation'] = $v;
        } else {
            $temp['routeStack'][] = [
                // 依你原本欄位風格：以 u 端的那列為基準
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
        $currentRoute = $routeId; // 優先續乘
    }

    // 4.3 轉車次數 = 段數 - 1
    $temp['transferCount'] = max(0, count($temp['routeStack']) - 1);

    // 4.4 詳細路線（4.4.1~4.4.5）
    foreach ($temp['routeStack'] as $seg) {
        $temp['detailRoute'][] = [
            'startRoute' => $seg['route'],        // 4.4.1 起點路線
            'startStation' => $seg['startStation'], // 4.4.2 起點站點
            'endRoute' => $seg['route'],        // 4.4.3 終點路線（同一路段）
            'endStation' => $seg['endStation'],   // 4.4.4 終點站點
            'segmentTime' => (int)$seg['time']             // 4.4.5 路線行駛時間
        ];
    }

    $ans[] = $temp;
}
echo json_encode($ans, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;