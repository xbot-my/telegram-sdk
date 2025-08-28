// 文档增强脚本
(function() {
    'use strict';

    // 等待 DOM 加载完成
    document.addEventListener('DOMContentLoaded', function() {
        initializeDocEnhancements();
    });

    // 等待 docsify 完全加载
    window.$docsify = window.$docsify || {};
    
    // 添加插件
    window.$docsify.plugins = [].concat(
        window.$docsify.plugins || [],
        [
            docEnhancementPlugin,
            codeEnhancementPlugin,
            navigationEnhancementPlugin,
            performanceMonitorPlugin
        ]
    );

    /**
     * 初始化文档增强功能
     */
    function initializeDocEnhancements() {
        // 添加返回顶部按钮
        addBackToTopButton();
        
        // 添加阅读进度条
        addReadingProgress();
        
        // 添加键盘快捷键支持
        addKeyboardShortcuts();
        
        // 添加主题切换
        addThemeToggle();
        
        // 初始化代码复制功能增强
        enhanceCodeBlocks();
        
        // 添加图片懒加载
        addImageLazyLoading();
        
        console.log('📚 文档增强功能已初始化');
    }

    /**
     * 文档增强插件
     */
    function docEnhancementPlugin(hook, vm) {
        hook.mounted(function() {
            // 添加页面加载完成标记
            document.body.classList.add('doc-loaded');
        });

        hook.beforeEach(function(content) {
            // 处理内容增强
            return enhanceContent(content);
        });

        hook.afterEach(function(html, next) {
            // 处理渲染后的 HTML
            next(enhanceRenderedHTML(html));
        });

        hook.doneEach(function() {
            // 页面渲染完成后的处理
            updatePageMetadata();
            highlightCurrentSection();
            addExternalLinkIcons();
        });
    }

    /**
     * 代码增强插件
     */
    function codeEnhancementPlugin(hook, vm) {
        hook.doneEach(function() {
            // 增强代码块
            enhanceCodeBlocks();
            
            // 添加代码运行示例
            addCodeExamples();
            
            // 代码块语言标签
            addCodeLanguageLabels();
        });
    }

    /**
     * 导航增强插件
     */
    function navigationEnhancementPlugin(hook, vm) {
        hook.doneEach(function() {
            // 更新面包屑导航
            updateBreadcrumb();
            
            // 高亮当前页面
            highlightCurrentPage();
            
            // 添加上下页导航
            addPageNavigation();
        });
    }

    /**
     * 性能监控插件
     */
    function performanceMonitorPlugin(hook, vm) {
        let startTime;
        
        hook.beforeEach(function() {
            startTime = performance.now();
        });
        
        hook.doneEach(function() {
            const loadTime = performance.now() - startTime;
            console.log(`📊 页面加载时间: ${loadTime.toFixed(2)}ms`);
            
            // 可选：发送性能数据到分析服务
            if (window.gtag) {
                gtag('event', 'page_load_time', {
                    value: Math.round(loadTime),
                    custom_parameter: vm.route.path
                });
            }
        });
    }

    /**
     * 增强内容处理
     */
    function enhanceContent(content) {
        // 添加警告框支持
        content = content.replace(/> \[!(NOTE|TIP|WARNING|DANGER)\]/g, function(match, type) {
            const typeMap = {
                'NOTE': 'info',
                'TIP': 'success', 
                'WARNING': 'warning',
                'DANGER': 'error'
            };
            return `> [!${typeMap[type] || 'info'}]`;
        });

        // 添加徽章支持
        content = content.replace(/\[!(\w+)\]/g, '<span class="feature-tag">$1</span>');

        // 添加 API 方法标记
        content = content.replace(/\[!(GET|POST|PUT|DELETE)\]/g, function(match, method) {
            return `<span class="api-method ${method.toLowerCase()}">${method}</span>`;
        });

        return content;
    }

    /**
     * 增强渲染后的 HTML
     */
    function enhanceRenderedHTML(html) {
        // 添加表格响应式包装
        html = html.replace(/<table/g, '<div class="table-wrapper"><table');
        html = html.replace(/<\/table>/g, '</table></div>');

        // 为外部链接添加图标
        html = html.replace(/<a href="https?:\/\/[^"]*"[^>]*>/g, function(match) {
            if (!match.includes('class=')) {
                return match.replace('>', ' class="external-link">');
            }
            return match;
        });

        return html;
    }

    /**
     * 添加返回顶部按钮
     */
    function addBackToTopButton() {
        const button = document.createElement('button');
        button.className = 'back-to-top';
        button.innerHTML = '↑';
        button.title = '返回顶部';
        button.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border: none;
            border-radius: 50%;
            background: var(--theme-color);
            color: white;
            font-size: 20px;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;

        document.body.appendChild(button);

        // 滚动事件
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                button.style.opacity = '1';
                button.style.visibility = 'visible';
            } else {
                button.style.opacity = '0';
                button.style.visibility = 'hidden';
            }
        });

        // 点击事件
        button.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    /**
     * 添加阅读进度条
     */
    function addReadingProgress() {
        const progressBar = document.createElement('div');
        progressBar.className = 'reading-progress';
        progressBar.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: var(--theme-color);
            z-index: 9999;
            transition: width 0.3s ease;
        `;

        document.body.appendChild(progressBar);

        window.addEventListener('scroll', function() {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            progressBar.style.width = scrolled + '%';
        });
    }

    /**
     * 添加键盘快捷键支持
     */
    function addKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K 打开搜索
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.querySelector('.search input');
                if (searchInput) {
                    searchInput.focus();
                }
            }
            
            // ESC 关闭搜索
            if (e.key === 'Escape') {
                const searchInput = document.querySelector('.search input');
                if (searchInput && document.activeElement === searchInput) {
                    searchInput.blur();
                }
            }
            
            // 左右箭头键导航
            if (e.key === 'ArrowLeft' && e.altKey) {
                const prevBtn = document.querySelector('.pagination-item--previous');
                if (prevBtn) prevBtn.click();
            }
            
            if (e.key === 'ArrowRight' && e.altKey) {
                const nextBtn = document.querySelector('.pagination-item--next');
                if (nextBtn) nextBtn.click();
            }
        });
    }

    /**
     * 添加主题切换
     */
    function addThemeToggle() {
        const toggle = document.createElement('button');
        toggle.className = 'theme-toggle';
        toggle.innerHTML = '🌙';
        toggle.title = '切换主题';
        toggle.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            background: var(--sidebar-background);
            border: 2px solid var(--border-color);
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
        `;

        document.body.appendChild(toggle);

        const isDark = localStorage.getItem('theme') === 'dark';
        if (isDark) {
            document.body.classList.add('dark-theme');
            toggle.innerHTML = '☀️';
        }

        toggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-theme');
            const isDarkMode = document.body.classList.contains('dark-theme');
            toggle.innerHTML = isDarkMode ? '☀️' : '🌙';
            localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
        });
    }

    /**
     * 增强代码块
     */
    function enhanceCodeBlocks() {
        const codeBlocks = document.querySelectorAll('pre[data-lang]');
        codeBlocks.forEach(function(block) {
            // 添加行号
            if (!block.querySelector('.line-numbers')) {
                addLineNumbers(block);
            }
            
            // 添加代码折叠功能
            if (block.textContent.split('\n').length > 20) {
                addCodeFolding(block);
            }
        });
    }

    /**
     * 添加行号
     */
    function addLineNumbers(codeBlock) {
        const code = codeBlock.querySelector('code');
        if (!code) return;

        const lines = code.textContent.split('\n');
        const lineNumbersDiv = document.createElement('div');
        lineNumbersDiv.className = 'line-numbers';
        lineNumbersDiv.style.cssText = `
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 40px;
            background: rgba(0,0,0,0.05);
            border-right: 1px solid var(--border-color);
            padding: 16px 8px;
            font-size: 12px;
            line-height: 1.5;
            color: var(--text-color-light);
            user-select: none;
        `;

        codeBlock.style.position = 'relative';
        codeBlock.style.paddingLeft = '50px';

        lines.forEach(function(_, index) {
            const lineNumber = document.createElement('div');
            lineNumber.textContent = index + 1;
            lineNumbersDiv.appendChild(lineNumber);
        });

        codeBlock.insertBefore(lineNumbersDiv, code);
    }

    /**
     * 添加代码折叠
     */
    function addCodeFolding(codeBlock) {
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'code-fold-toggle';
        toggleBtn.innerHTML = '折叠';
        toggleBtn.style.cssText = `
            position: absolute;
            top: 8px;
            right: 60px;
            background: var(--theme-color);
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            z-index: 10;
        `;

        codeBlock.style.position = 'relative';
        codeBlock.appendChild(toggleBtn);

        let isCollapsed = false;
        const originalHeight = codeBlock.scrollHeight;

        toggleBtn.addEventListener('click', function() {
            if (isCollapsed) {
                codeBlock.style.height = originalHeight + 'px';
                codeBlock.style.overflow = 'auto';
                toggleBtn.innerHTML = '折叠';
            } else {
                codeBlock.style.height = '200px';
                codeBlock.style.overflow = 'hidden';
                toggleBtn.innerHTML = '展开';
            }
            isCollapsed = !isCollapsed;
        });
    }

    /**
     * 添加代码示例
     */
    function addCodeExamples() {
        const phpBlocks = document.querySelectorAll('pre[data-lang="php"]');
        phpBlocks.forEach(function(block) {
            if (block.textContent.includes('// 示例')) {
                addRunButton(block);
            }
        });
    }

    /**
     * 添加运行按钮
     */
    function addRunButton(codeBlock) {
        const runBtn = document.createElement('button');
        runBtn.className = 'code-run-btn';
        runBtn.innerHTML = '▶️ 运行示例';
        runBtn.style.cssText = `
            position: absolute;
            top: 8px;
            right: 120px;
            background: #28a745;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            z-index: 10;
        `;

        codeBlock.appendChild(runBtn);

        runBtn.addEventListener('click', function() {
            // 这里可以集成 PHP 在线运行环境
            alert('此功能需要集成 PHP 在线运行环境');
        });
    }

    /**
     * 添加代码语言标签
     */
    function addCodeLanguageLabels() {
        const codeBlocks = document.querySelectorAll('pre[data-lang]');
        codeBlocks.forEach(function(block) {
            const lang = block.getAttribute('data-lang');
            if (lang && !block.querySelector('.lang-label')) {
                const label = document.createElement('span');
                label.className = 'lang-label';
                label.textContent = lang.toUpperCase();
                label.style.cssText = `
                    position: absolute;
                    top: 8px;
                    right: 12px;
                    background: var(--theme-color);
                    color: white;
                    padding: 2px 8px;
                    border-radius: 4px;
                    font-size: 11px;
                    font-weight: bold;
                    z-index: 10;
                `;
                
                block.style.position = 'relative';
                block.appendChild(label);
            }
        });
    }

    /**
     * 更新页面元数据
     */
    function updatePageMetadata() {
        // 更新页面标题
        const h1 = document.querySelector('h1');
        if (h1) {
            document.title = h1.textContent + ' - Telegram Bot PHP SDK';
        }

        // 添加面包屑导航到标题
        updatePageTitle();
    }

    /**
     * 更新页面标题
     */
    function updatePageTitle() {
        const path = location.hash.replace('#/', '');
        const parts = path.split('/').filter(Boolean);
        
        if (parts.length > 0) {
            const breadcrumbs = parts.map(part => 
                part.replace(/-/g, ' ')
                    .replace(/\b\w/g, l => l.toUpperCase())
            ).join(' > ');
            
            const titleElement = document.querySelector('h1');
            if (titleElement && !titleElement.querySelector('.breadcrumb')) {
                const breadcrumbSpan = document.createElement('span');
                breadcrumbSpan.className = 'breadcrumb';
                breadcrumbSpan.style.cssText = `
                    font-size: 0.6em;
                    color: var(--text-color-light);
                    display: block;
                    margin-bottom: 0.5em;
                `;
                breadcrumbSpan.textContent = breadcrumbs;
                titleElement.insertBefore(breadcrumbSpan, titleElement.firstChild);
            }
        }
    }

    /**
     * 高亮当前章节
     */
    function highlightCurrentSection() {
        const headers = document.querySelectorAll('h2, h3, h4');
        const sections = Array.from(headers).map(header => ({
            element: header,
            offset: header.offsetTop
        }));

        function updateActiveSection() {
            const scrollTop = window.pageYOffset;
            const current = sections.reverse().find(section => 
                scrollTop >= section.offset - 100
            );
            
            if (current) {
                // 移除所有活跃状态
                document.querySelectorAll('.active-section').forEach(el => 
                    el.classList.remove('active-section')
                );
                
                // 添加当前活跃状态
                current.element.classList.add('active-section');
            }
        }

        window.addEventListener('scroll', throttle(updateActiveSection, 100));
    }

    /**
     * 添加外部链接图标
     */
    function addExternalLinkIcons() {
        const externalLinks = document.querySelectorAll('a[href^="http"]:not([href*="' + location.hostname + '"])');
        externalLinks.forEach(function(link) {
            if (!link.querySelector('.external-icon')) {
                const icon = document.createElement('span');
                icon.className = 'external-icon';
                icon.innerHTML = ' 🔗';
                icon.style.fontSize = '0.8em';
                link.appendChild(icon);
                link.setAttribute('target', '_blank');
                link.setAttribute('rel', 'noopener noreferrer');
            }
        });
    }

    /**
     * 添加图片懒加载
     */
    function addImageLazyLoading() {
        const images = document.querySelectorAll('img');
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(function(img) {
            imageObserver.observe(img);
        });
    }

    /**
     * 节流函数
     */
    function throttle(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * 防抖函数
     */
    function debounce(func, wait, immediate) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                timeout = null;
                if (!immediate) func(...args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func(...args);
        };
    }

    /**
     * 添加页面导航
     */
    function addPageNavigation() {
        // 这个功能由 docsify-pagination 插件提供
        // 这里可以添加额外的导航增强
    }

    /**
     * 更新面包屑导航
     */
    function updateBreadcrumb() {
        // 由于 docsify 的单页应用特性，面包屑主要在标题中显示
    }

    /**
     * 高亮当前页面
     */
    function highlightCurrentPage() {
        const currentPath = location.hash.replace('#/', '');
        const sidebarLinks = document.querySelectorAll('.sidebar a');
        
        sidebarLinks.forEach(function(link) {
            link.classList.remove('active');
            const href = link.getAttribute('href');
            if (href && href.replace('#/', '') === currentPath) {
                link.classList.add('active');
                // 确保父级菜单展开
                let parent = link.parentElement;
                while (parent && parent.classList.contains('sidebar-nav')) {
                    if (parent.querySelector('.collapse')) {
                        parent.querySelector('.collapse').classList.add('show');
                    }
                    parent = parent.parentElement;
                }
            }
        });
    }

    // 在控制台输出欢迎信息
    console.log(`
    🤖 Telegram Bot PHP SDK 文档
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    📚 欢迎来到 Telegram Bot PHP SDK 文档！
    🔗 GitHub: https://github.com/xbot-my/telegram-sdk
    📖 文档: https://docs.telegram-sdk.com
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    
    🔧 快捷键:
    • Ctrl/Cmd + K: 打开搜索
    • Alt + ←/→: 上一页/下一页
    • ESC: 关闭搜索
    
    💡 提示: 文档支持深色模式，点击右上角切换按钮即可。
    `);

})();