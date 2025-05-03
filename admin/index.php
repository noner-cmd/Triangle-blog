<?php
session_start();
$showContent = false;
$error = false;
$correctPassword = 'admin'; // 修改此处密码

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputPassword = $_POST['password'] ?? '';
    if ($inputPassword === $correctPassword) {
        $_SESSION['authenticated'] = true;
        $showContent = true;
    } else {
        $error = true;
    }
} elseif (isset($_SESSION['authenticated'])) {
    $showContent = true;
}

// 退出逻辑
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>博客管理后台</title>
    <style>
        .popup, .content { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        .error { color: red; text-align: center; margin: 10px 0; }
        .button-group { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin: 20px 0; }
        .btn { display: block; padding: 12px; background: #4CAF50; color: white; text-align: center; text-decoration: none; border-radius: 4px; transition: background 0.3s; }
        .btn:hover { background: #45a049; }
    </style>
</head>
<body>
    <?php if ($showContent): ?>
        <!-- 成功内容区域（6个链接按钮） -->
        <div class="content">
            <h1>博客管理后台</h1>
            <div class="button-group">
                <a href="/a.php" class="btn">添加友链</a>
                <a href="/e.php" class="btn">撰写即刻</a>
                <a href="/t.php" class="btn">编辑Todo</a>
                <a href="/p.php" class="btn">编辑“项目”</a>
                <a href="/s.php" class="btn">编辑“收藏”</a>
                <a href="/m.php" class="btn">刷新音乐</a>
            </div>
            <p><a href="?logout=1" style="color: #ff4444;">退出登录</a></p>
        </div>
        
    <?php else: ?>
        <!-- 登录弹窗区域 -->
        <div class="popup">
            <?php if ($error): ?>
                <div class="error">密码错误，请重新输入！</div>
            <?php endif; ?>
            
            <h2>请输入访问密码</h2>
            <form method="post">
                <input type="password" name="password" placeholder="输入密码" required style="width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ddd;">
                <button type="submit" style="width: 100%; padding: 12px; background: #337ab7; color: white; border: none; cursor: pointer;">确认</button>
            </form>
        </div>
    <?php endif; ?>
</body>
</html>
