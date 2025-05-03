<?php
// 配置文件路径
$jsonFile = __DIR__ . '/config/index/todo.json';
// 读取待办事项
$todos = json_decode(file_get_contents($jsonFile), true) ?: [];

// 定义固定标签选项（可根据需求扩展）
$urgencyTags = [
    "tag-high" => "高紧急",
    "tag-medium" => "中紧急",
    "tag-low" => "低紧急"
];
$typeTags = [
    "tag-daily" => "日常",
    "tag-work" => "工作",

];

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'add') {
        // 验证必填字段
        if (!isset($_POST['text'], $_POST['urgency'], $_POST['type'])) {
            $error = "请填写所有字段";
        } else {
            $newTodo = [
                "text" => htmlspecialchars($_POST['text']),
                "tags" => [$_POST['urgency'], $_POST['type']],
                "progress" => isset($_POST['progress']) ? intval($_POST['progress']) : 0
            ];
            $todos[] = $newTodo;
            $success = "待办事项添加成功";
        }
    } elseif ($_POST['action'] === 'delete') {
        if (isset($_POST['index']) && is_numeric($_POST['index'])) {
            unset($todos[$_POST['index']]);
            $todos = array_values($todos); // 重建索引
            $success = "待办事项删除成功";
        }
    } elseif ($_POST['action'] === 'update') {
        if (isset($_POST['index'], $_POST['progress']) && is_numeric($_POST['index'])) {
            $todos[$_POST['index']]['progress'] = intval($_POST['progress']);
            $success = "进度更新成功";
        }
    }
    
    // 保存到文件
    file_put_contents($jsonFile, json_encode($todos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>待办事项管理</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .form-section, .list-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="number"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .tag-group { display: flex; gap: 15px; margin-bottom: 15px; }
        .tag-radio { display: flex; align-items: center; gap: 5px; }
        button { 
            background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;
            margin-right: 5px;
        }
        button.delete { background-color: #dc3545; }
        .success { color: green; margin-top: 10px; }
        .error { color: red; margin-top: 10px; }
        .todo-item { 
            padding: 15px; margin-bottom: 10px; border: 1px solid #e9ecef; border-radius: 4px; 
            display: flex; justify-content: space-between; align-items: center;
        }
        .progress-bar {
            height: 12px;
            background-color: #f0f0f0;
            border-radius: 6px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background-color: #007bff;
            width: <?= isset($progress) ? $progress : 0 ?>%;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>待办事项管理</h1>
        <p>标签分为「紧急程度」和「类型」两个维度</p>
    </div>

    <!-- 操作反馈 -->
    <?php if (isset($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <!-- 添加待办事项表单 -->
    <div class="form-section">
        <h2>添加新待办</h2>
        <form method="post">
            <input type="hidden" name="action" value="add">

            <div class="form-group">
                <label>待办内容：</label>
                <input type="text" name="text" required placeholder="如：学习PHP">
            </div>

            <div class="tag-group">
                <div>
                    <label>紧急程度：</label>
                    <?php foreach ($urgencyTags as $value => $text): ?>
                        <div class="tag-radio">
                            <input type="radio" id="urgency-<?= $value ?>" name="urgency" value="<?= $value ?>" required>
                            <label for="urgency-<?= $value ?>"><?= $text ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div>
                    <label>类型：</label>
                    <?php foreach ($typeTags as $value => $text): ?>
                        <div class="tag-radio">
                            <input type="radio" id="type-<?= $value ?>" name="type" value="<?= $value ?>" required>
                            <label for="type-<?= $value ?>"><?= $text ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label>初始进度：</label>
                <input type="number" name="progress" min="0" max="100" value="0">
            </div>

            <button type="submit">添加待办</button>
        </form>
    </div>

    <!-- 待办事项列表 -->
    <div class="list-section">
        <h2>现有待办（<?= count($todos) ?>项）</h2>
        <?php if (empty($todos)): ?>
            <p>暂无待办事项，点击上方添加</p>
        <?php else: ?>
            <?php foreach ($todos as $index => $todo): ?>
                <div class="todo-item">
                    <div>
                        <strong><?= $todo['text'] ?></strong><br>
                        <small>标签：<?= $urgencyTags[$todo['tags'][0]] ?> / <?= $typeTags[$todo['tags'][1]] ?></small>
                    </div>
                    
                    <div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $todo['progress'] ?>%"></div>
                        </div>
                        <span><?= $todo['progress'] ?>%</span>
                        
                        <form method="post" style="display: inline; margin-left: 10px;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="index" value="<?= $index ?>">
                            <input type="number" name="progress" min="0" max="100" value="<?= $todo['progress'] ?>" style="width: 60px; padding: 4px;">
                            <button type="submit">更新</button>
                        </form>

                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="index" value="<?= $index ?>">
                            <button type="submit" class="delete">删除</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
