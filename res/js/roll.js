// 滚动动画核心逻辑
        function handleScroll() {
            const left = document.querySelector('.left');
            const right = document.querySelector('.right');
            const threshold = 600; // 滚动阈值

            if (window.scrollY > threshold) {
                left.classList.add('left-hidden');
                right.classList.add('right-wide');
            } else {
                left.classList.remove('left-hidden');
                right.classList.remove('right-wide');
                // 恢复stylespc.css的固定比例（重要！）
                left.style.flexBasis = '33.333%'; 
                right.style.flexBasis = '66.667%'; 
            }
        }
