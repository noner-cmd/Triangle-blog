<?php
// 配置文件路径
$jsonFile = __DIR__ . '/config/page/scj.json';
// 确保目录存在
if (!is_dir(dirname($jsonFile))) {
    mkdir(dirname($jsonFile), 0755, true);
}
// 读取现有数据
$scj = json_decode(file_get_contents($jsonFile), true) ?: [
    'pageTitle' => '收藏夹',
    'navH1' => 'WORLD',
    'leftFirstContainer' => [
        'title' => '🧭收藏夹',
        'image' => '/res/pic/index.webp',
        'description' => '这里收集了许多常用的网页...'
    ],
    'rightContainers' => []
];

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 简易CSRF防护（生产环境需加强）
    if ($_POST['csrf_token'] !== md5('scj_manage_token')) {
        die("非法请求");
    }
    
    if ($_POST['action'] === 'add') {
        // 验证必填字段
        if (!isset($_POST['title'], $_POST['content'], $_POST['buttonLink'], $_POST['buttonText'])) {
            $error = "请填写所有字段";
        } else {
            $newItem = [
                "title" => htmlspecialchars($_POST['title']),
                "content" => htmlspecialchars($_POST['content']),
                "buttonLink" => htmlspecialchars($_POST['buttonLink']),
                "buttonText" => htmlspecialchars($_POST['buttonText'])
            ];
            $scj['rightContainers'][] = $newItem;
            $success = "收藏项添加成功";
        }
    } elseif ($_POST['action'] === 'delete') {
        if (isset($_POST['index']) && is_numeric($_POST['index'])) {
            unset($scj['rightContainers'][$_POST['index']]);
            $scj['rightContainers'] = array_values($scj['rightContainers']); // 重建索引
            $success = "收藏项删除成功";
        } else {
            $error = "无效的删除请求";
        }
    }
    
    // 保存到文件
    if (isset($success) || isset($error)) {
        file_put_contents($jsonFile, json_encode($scj, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>收藏夹管理工具</title>
    <style>
        /* 复用之前的CSS样式，仅修改标题图标 */
        body { font-family: 'Segoe UI', sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .form-section, .list-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { 
            background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;
            margin-right: 5px;
        }
        button.delete { background-color: #dc3545; }
        .success { color: green; margin-top: 10px; }
        .error { color: red; margin-top: 10px; }
        .project-item { 
            padding: 15px; margin-bottom: 10px; border: 1px solid #e9ecef; border-radius: 4px; 
            display: flex; justify-content: space-between; align-items: center;
        }
        .project-actions { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="header">
        <h1>收藏夹管理工具</h1>
        <p>直接操作 <code>scj.json</code> 中的右侧收藏项列表</p>
    </div>

    <!-- 操作反馈 -->
    <?php if (isset($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <!-- 添加收藏项表单 -->
    <div class="form-section">
        <h2>添加新收藏项</h2>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="csrf_token" value="<?= md5('scj_manage_token') ?>"> <!-- 防护令牌 -->

            <div class="form-group">
                <label>收藏标题：</label>
                <input type="text" name="title" required placeholder="如：emoji大全">
            </div>

            <div class="form-group">
                <label>收藏描述：</label>
                <textarea name="content" required rows="3" placeholder="简要说明用途"></textarea>
            </div>

            <div class="form-group">
                <label>网站链接：</label>
                <input type="url" name="buttonLink" required placeholder="https://example.com">
            </div>

            <div class="form-group">
                <label>按钮文字：</label>
                <input type="text" name="buttonText" required placeholder="如：查看网站">
            </div>

            <button type="submit">保存收藏</button>
        </form>
    </div>

    <!-- 收藏项列表 -->
    <div class="list-section">
        <h2>现有收藏项（<?= count($scj['rightContainers']) ?>个）</h2>
        <?php if (empty($scj['rightContainers'])): ?>
            <p>暂无收藏项，点击上方添加</p>
        <?php else: ?>
            <?php foreach ($scj['rightContainers'] as $index => $item): ?>
                <div class="project-item">
                    <div>
                        <strong><?= $item['title'] ?></strong><br>
                        <small><?= $item['content'] ?></small>
                    </div>
                    <div class="project-actions">
                        <a href="<?= $item['buttonLink'] ?>" target="_blank" class="btn btn-sm btn-info">访问</a>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="index" value="<?= $index ?>">
                            <input type="hidden" name="csrf_token" value="<?= md5('scj_manage_token') ?>">
                            <button type="submit" class="delete btn btn-sm btn-danger">删除</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
