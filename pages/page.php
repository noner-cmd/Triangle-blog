<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- 页面标题动态绑定 -->
    <title id="page-title"></title> 
<link rel="stylesheet" href="/res/css/roll.css" > <script src="/res/js/roll.js"></script>
    <link rel="stylesheet" href="/res/css/styles.css">
    <link rel="stylesheet" href="/res/css/stylespc.css" media="screen and (min-width: 800px)">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="res/pic/favicon.ico" type="image/jpeg">
</head>

<body>
  <!-- 顶部导航栏 -->
  <header>
      <div id="hamburger" onclick="toggleSidebar()">
          <i class="fas fa-bars"></i>
      </div>
      <!-- 导航栏H1动态绑定 -->
      <h1>
          <a href="/index.html" style="color: #394E6A; text-decoration: none;" id="nav-h1"></a>
      </h1>
      <div id="weather">
          <i class="fa-solid fa-earth-americas"></i>
      </div>
  </header>

  <!-- 侧边栏（图标需手动维护，JSON不控制） -->
  <div id="sidebar">
      <a href="/index.html" class="sidebar-link">
          <i class="fa-solid fa-house"></i> 
          <span class="text-30">主页</span> 
      </a>
      <a href="/pages/page.php?s=project" class="sidebar-link">
          <i class="fa-solid fa-tasks"></i>
          <span class="text-30">项目</span>
      </a>
      <a href="/pages/page.php?s=scj" class="sidebar-link">
          <i class="fa-solid fa-star"></i>
          <span class="text-30">收藏</span>
      </a>
      <a href="/pages/essay.html" class="sidebar-link">
          <i class="fa-solid fa-book"></i>
          <span class="text-30">即刻</span>
      </a>
      <a href="/pages/friend.html" class="sidebar-link">
          <i class="fa-solid fa-users"></i>
          <span class="text-30">友链</span>
      </a>
      <a href="/pages/about.html" class="sidebar-link">
          <i class="fa-solid fa-compass"></i>
          <span class="text-30">关于</span>
      </a>
  </div>

    <!-- 主体布局 -->
    <div class="layout">
        <!-- 左侧容器 -->
        <div class="left">
            <div class="container" id="left-first-container">
                <!-- 左侧第一个容器标题动态绑定 -->
                <div class="title" id="left-title"></div> 
            </div>
            <div class="container">
<div style="text-align:center;">
    <img src="/res/pic/avatar.jpg" alt="头像" 
         style="border-radius:50%;margin-bottom:20px;margin-top:40px;width:150px;height:150px;border:3px solid #fff;box-shadow:0 0 10px rgba(0,0,0,0.1);">
    <div style="border-top:2px solid #e5e5e5;margin:20px 0;width:100%;"></div>

<div class="icons" id="social-links"></div></div></div>

            <div class="container">
                

                       <div class="title"><i class="fa-solid fa-compass"></i>介绍</div>
                <!-- 左侧文字描述动态绑定 -->
                <div class="content" id="left-description"></div> 
            </div>
            
            



        </div>

        <!-- 右侧容器动态生成区域 -->
        <div class="right" id="right-containers"></div> 
    </div>

    <!-- 引入JSON解析脚本（PHP动态输出数据） -->
    <script>
        document.addEventListener('DOMContentLoaded', init);
        <?php
        // ----------------------
        // PHP 数据处理部分
        // ----------------------
        // 安全获取 GET 参数（防止 XSS 攻击）
        $s = isset($_GET['s']) ? htmlspecialchars($_GET['s']) : 'index';
        $jsonFileName = $s . '.json'; // 生成文件名
        $jsonFilePath = $_SERVER['DOCUMENT_ROOT'] . '/config/page/' . $jsonFileName; // 完整文件路径

        // 初始化数据对象
        $data = [
            'pageTitle' => '默认页面标题',
            'navH1' => '网站名称',
            'leftFirstContainer' => [
                'title' => '默认标题',
                'image' => '',
                'description' => '默认描述内容'
            ],
            'rightContainers' => []
        ];

        // 检查文件是否存在且可读取
        if (is_file($jsonFilePath) && is_readable($jsonFilePath)) {
            $jsonContent = file_get_contents($jsonFilePath);
            $loadedData = json_decode($jsonContent, true); // 解析为数组

            // 合并加载的数据（防止键名缺失）
            if (is_array($loadedData)) {
                $data = array_merge($data, $loadedData);
            } else {
                echo "console.error('JSON 文件解析失败: {$jsonFilePath}');";
            }
        } else {
            echo "console.error('JSON 文件未找到或不可读: {$jsonFilePath}');";
        }

        // 将 PHP 数据转为 JSON 格式输出到 JS
        echo "const data = " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ";\n";
        ?>

        // ----------------------
        // JavaScript 渲染部分
        // ----------------------
        // 绑定页面标题
        document.getElementById('page-title').textContent = data.pageTitle;
        // 绑定导航栏 H1
        document.getElementById('nav-h1').textContent = data.navH1;

        // 绑定左侧第一个容器
        const leftFirstContainer = data.leftFirstContainer;
        document.getElementById('left-title').textContent = leftFirstContainer.title;
         document.getElementById('left-description').textContent = leftFirstContainer.description;

        // 动态生成右侧容器
        const rightContainers = document.getElementById('right-containers');
        data.rightContainers.forEach(container => {
            const containerDiv = document.createElement('div');
            containerDiv.className = 'container';
            containerDiv.innerHTML = `
                <div class="title">
                    
                    ${container.title}
                </div>
                <div class="content">
                    <p>${container.content}</p>
                    <a href="${container.buttonLink}" class="btn">${container.buttonText}</a>
                </div>
            `;
            rightContainers.appendChild(containerDiv);
        });
// 加载网站配置
        function loadConfig() {
            return fetch('/config/index/index.json')
                .then(response => response.json());
        }

        // 初始化页面
        async function init() {
            try {
                // 加载配置
                const config = await loadConfig();
                          window.addEventListener('scroll', handleScroll);  
 
                
                // 渲染社交媒体链接
                const socialLinksDiv = document.getElementById('social-links');
                socialLinksDiv.innerHTML = '';
                
                config.socialLinks.forEach(link => {
                    const a = document.createElement('a');
                    a.href = link.url;
                    a.className = `fab fa-${link.platform}`; // 自动生成图标类名
                    a.target = '_blank'; // 新窗口打开
                    socialLinksDiv.appendChild(a);
                });
            } catch (error) {
                console.error('加载配置失败:', error);
            }
        }
        // 侧边栏切换函数（示例）
function toggleSidebar() { const sidebar = document.getElementById("sidebar"); const hamburger = document.getElementById("hamburger");
sidebar.classList.toggle("open");

}

    </script>
</body>
</html>

