<?php
// 前置歌单ID和token，方便他人修改
$playlistId = 3236900000;
$token = "qjadfh5xdycvmwijcyhurmzcpblrqq";

// 发送请求获取歌单数据
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://v3.alapi.cn/api/music/playlist?token={$token}&id={$playlistId}",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],
]);
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    die("cURL Error #:" . $err);
}

// 解析歌单数据的JSON
$data = json_decode($response, true);
if (json_last_error()!== JSON_ERROR_NONE) {
    die('JSON解析错误: '. json_last_error_msg());
}

// 获取最近收藏的5个歌曲id
$songIds = [];
$playlist = $data['data']['playlist'];
for ($i = 0; $i < 4 && $i < count($playlist); $i++) {
    $songIds[] = $playlist[$i]['id'];
}

$idsStr = implode(',', $songIds);

// 构建获取歌曲详情的URL
$apiUrl = "https://v3.alapi.cn/api/music/detail?token={$token}&id={$idsStr}";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ]
]);
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    die("cURL Error #:" . $err);
}

// 解析歌曲详情数据的JSON
$data = json_decode($response, true);
if (json_last_error()!== JSON_ERROR_NONE) {
    die('JSON解析错误: '. json_last_error_msg());
}

// 提取所需数据
$extractedData = [];
foreach ($data['data']['songs'] as $song) {
    $extractedData[] = [
        'name' => $song['name'],
        'picUrl' => $song['al']['picUrl'],
       'singer' => $song['ar'][0]['name'],
        'id' => $song['id']
    ];
}

// 将数据写入info.json
file_put_contents(__DIR__ . '/config/index/music.json', json_encode($extractedData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// 将数据以JSON格式展示
header('Content-Type: application/json');
echo json_encode($extractedData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);