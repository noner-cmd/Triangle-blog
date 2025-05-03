<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>添加博主信息</title>
</head>
<body>
    <h1>添加新博主信息</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <label for="name">博主名称:</label><br>
        <input type="text" id="name" name="name" required><br><br>

        <label for="url">博客网址:</label><br>
        <input type="text" id="url" name="url" required><br><br>

        <label for="avatar">头像链接:</label><br>
        <input type="text" id="avatar" name="avatar" required><br><br>

        <label for="desc">博主描述:</label><br>
        <textarea id="desc" name="desc" required></textarea><br><br>

        <input type="submit" value="添加">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // 接收表单数据
        $newBlogger = [
            "name" => $_POST["name"],
            "url" => $_POST["url"],
            "avatar" => $_POST["avatar"],
            "desc" => $_POST["desc"]
        ];

        // 定义 JSON 文件路径
        $jsonPath = __DIR__ . '/config/other/friends.json';
        
        // 读取原有数据（处理文件不存在或为空的情况）
        $jsonContent = file_get_contents($jsonPath);
        $bloggers = json_decode($jsonContent, true) ?: []; // 解码失败时默认空数组
        
        // 添加新数据
        $bloggers[] = $newBlogger;
        
        // 重新编码并写入文件（保留中文且格式化）
        $newJsonContent = json_encode($bloggers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($jsonPath, $newJsonContent);
        
        // 提示成功信息
        echo "<p>新博主信息已成功添加，当前共有 " . count($bloggers) . " 条记录。</p>";
    }
    ?>
</body>
</html>
