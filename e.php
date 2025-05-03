<?php
// 图片压缩处理函数
function compressImage($sourcePath, $destPath, $maxSize = 800 * 1024) {
    // 验证图片有效性
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) return false;
    list($width, $height, $type) = $imageInfo;
    $mime = image_type_to_mime_type($type);

    // 创建源图像资源
    $createFuncs = [
        IMAGETYPE_JPEG => 'imagecreatefromjpeg',
        IMAGETYPE_PNG => 'imagecreatefrompng',
        IMAGETYPE_GIF => 'imagecreatefromgif'
    ];
    if (!isset($createFuncs[$type]) || !function_exists($createFuncs[$type])) {
        return false;
    }
    $source = $createFuncs[$type]($sourcePath);
    if (!$source) return false;

    // 处理小文件：直接复制避免原图留存
    if (filesize($sourcePath) <= $maxSize) {
        return copy($sourcePath, $destPath);
    }

    // 计算压缩比例（保持宽高比）
    $ratio = sqrt($maxSize / filesize($sourcePath));
    $newWidth = max(1, round($width * $ratio));
    $newHeight = max(1, round($height * $ratio));

    // 创建目标图像资源
    $target = imagecreatetruecolor($newWidth, $newHeight);
    
    // 处理PNG透明通道
    if ($type === IMAGETYPE_PNG) {
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transColor = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefill($target, 0, 0, $transColor);
    }

    // 高质量图像缩放
    imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // 保存图像
    $saveFuncs = [
        IMAGETYPE_JPEG => ['imagejpeg', 85], // JPG压缩质量85%
        IMAGETYPE_PNG => ['imagepng', null],
        IMAGETYPE_GIF => ['imagegif', null]
    ];
    $saveFunc = $saveFuncs[$type][0];
    $quality = $saveFuncs[$type][1];
    $result = $quality === null ? $saveFunc($target, $destPath) : $saveFunc($target, $destPath, $quality);

    // 释放资源
    imagedestroy($source);
    imagedestroy($target);
    return $result;
}

// 安全限制：仅允许GET/POST请求
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'GET'])) {
    http_response_code(405);
    exit('Method Not Allowed');
}

// 删除动态处理
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $jsonFile = __DIR__ . '/config/essay/essay.json';
    $data = file_exists($jsonFile) 
        ? json_decode(file_get_contents($jsonFile), true) 
        : [];
    $deleteIndex = (int)$_GET['delete'];

    if (isset($data[$deleteIndex])) {
        // 删除关联图片
        foreach ($data[$deleteIndex]['images'] as $imagePath) {
            $realPath = __DIR__ . '/DCIM/' . basename($imagePath);
            if (file_exists($realPath) && is_file($realPath)) {
                unlink($realPath);
            }
        }

        // 删除JSON数据并重置索引
        unset($data[$deleteIndex]);
        $data = array_values($data);
        file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// 表单提交处理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonFile = __DIR__ . '/config/essay/essay.json';
    $data = file_exists($jsonFile) 
        ? json_decode(file_get_contents($jsonFile), true) 
        : [];
    $uploadDir = __DIR__ . '/DCIM/';
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
    $images = [];

    // 处理多图片上传
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['error'] as $key => $error) {
            if ($error === UPLOAD_ERR_OK) {
                $file = $_FILES['images'];
                $fileName = $file['name'][$key];
                $fileTmp = $file['tmp_name'][$key];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $fileSize = $file['size'][$key];

                // 验证文件类型和大小限制（服务器端防护）
                if (in_array($fileExt, $allowedExts) && $fileSize < 100 * 1024 * 1024) {
                    $newFileName = uniqid() . '.' . $fileExt;
                    $destPath = $uploadDir . $newFileName;
                    $tempPath = $uploadDir . 'tmp_' . $newFileName; // 临时文件路径

                    // 移动上传文件到临时目录
                    if (!move_uploaded_file($fileTmp, $tempPath)) {
                        continue; // 移动失败跳过
                    }

                    // 处理图片（压缩或复制）
                    if (compressImage($tempPath, $destPath)) {
                        unlink($tempPath); // 删除临时文件
                        $images[] = '/DCIM/' . $newFileName; // 存储相对路径
                    } else {
                        unlink($tempPath); // 处理失败删除临时文件
                        continue;
                    }
                }
            }
        }
    }

    // 构建新动态条目
    $newEntry = [
        'content' => trim($_POST['content']),
        'images' => $images,
        'time' => date('Y-m-d H:i:s')
    ];

    // 验证内容非空并保存
    if (!empty($newEntry['content'])) {
        $data[] = $newEntry;
        file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // 重定向回当前页面
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>动态管理系统</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { max-width: 800px; margin: 20px auto; padding: 20px; }
        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .dynamic-list { margin-top: 30px; }
        .dynamic-item {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .dynamic-meta {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-top: 10px;
        }
        .action-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
            transition: background 0.3s;
        }
        .action-btn:hover { background: #c0392b; }
        .form-group {
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            height: 120px;
            font-size: 1em;
        }
        .file-input {
            display: block;
            margin-top: 10px;
            color: #7f8c8d;
        }
        .image-preview {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
        .image-preview img {
            max-width: 100px;
            height: auto;
            border-radius: 4px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <h1>动态管理系统</h1>

    <!-- 动态列表 -->
    <div class="dynamic-list">
        <?php
        $jsonFile = __DIR__ . '/config/essay/essay.json';
        $data = file_exists($jsonFile) 
            ? json_decode(file_get_contents($jsonFile), true) 
            : [];

        if (empty($data)): ?>
            <p>暂无动态，请添加新内容</p>
        <?php else: ?>
            <?php foreach ($data as $index => $item): ?>
                <div class="dynamic-item">
                    <p><?= htmlspecialchars($item['content']) ?></p>
                    <div class="dynamic-meta">
                        发布时间：<?= $item['time'] ?>
                        <a href="?delete=<?= $index ?>" class="action-btn" 
                           onclick="return confirm('确认删除此动态？\n将同时删除所有关联图片！')">
                            删除动态
                        </a>
                    </div>
                    <?php if (!empty($item['images'])): ?>
                        <div class="image-preview">
                            <?php foreach ($item['images'] as $imagePath): ?>
                                <img src="<?= $imagePath ?>" alt="动态图片">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- 新增表单 -->
    <h2>发布新动态</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="content">动态内容（必填）：</label>
            <textarea name="content" id="content" required></textarea>
        </div>
        <div class="form-group">
            <label for="images">上传图片（可选，支持多张）：</label>
            <input type="file" name="images[]" id="images" multiple accept="image/*">
            <p class="file-input">支持格式：JPG/JPEG/PNG/GIF，单文件最大5MB</p>
        </div>
        <button type="submit">发布动态</button>
    </form>
</body>
</html>