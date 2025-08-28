# 🚀 部署说明

本文档说明如何将 Telegram Bot PHP SDK 文档部署到 GitHub Pages。

## 📋 部署检查清单

### ✅ 已完成的工作

- [x] 创建完整的文档结构
- [x] 配置 docsify 框架
- [x] 设置自定义主题和样式
- [x] 编写用户指南文档
- [x] 创建 API 参考文档
- [x] 添加使用示例
- [x] 配置 GitHub Actions 自动部署
- [x] 验证所有文档链接
- [x] 设置自定义域名

### 📊 文档统计

- **📄 Markdown 文件**: 31 个
- **📝 总内容行数**: 4,646 行
- **📁 目录结构**: 12 个目录
- **🎨 样式文件**: 670 行 CSS
- **⚙️ 脚本文件**: 696 行 JavaScript

## 🔧 部署步骤

### 1. 提交代码到 GitHub

```bash
# 进入项目根目录
cd /path/to/telegram-sdk

# 添加所有文档文件
git add docs/

# 提交文档
git commit -m "📚 添加完整的文档站点

- 基于 docsify-themeable 的现代化文档系统
- 包含完整的用户指南和 API 参考
- 支持搜索、分页、代码高亮等功能
- 配置 GitHub Actions 自动部署
- 自定义主题和增强的用户体验

功能特性:
- 🚀 快速入门指南
- 📖 详细配置说明
- 🔧 API 参考文档
- 💡 使用示例
- 🔍 故障排除指南
- ⭐ 最佳实践"

# 推送到 GitHub
git push origin main
```

### 2. 启用 GitHub Pages

1. 访问 GitHub 仓库页面
2. 点击 **Settings** 标签
3. 在左侧菜单中找到 **Pages**
4. 在 **Source** 部分选择 **GitHub Actions**
5. 保存设置

### 3. 配置自定义域名（可选）

如果您有自定义域名：

1. 在 DNS 提供商处添加 CNAME 记录：
   ```
   docs.telegram-sdk.xbot.my CNAME your-username.github.io
   ```

2. 在 GitHub Pages 设置中添加自定义域名：
   - 在 **Custom domain** 字段输入: `docs.telegram-sdk.xbot.my`
   - 勾选 **Enforce HTTPS**

### 4. 等待部署完成

- GitHub Actions 将自动构建和部署文档
- 通常需要 2-5 分钟完成
- 可以在 **Actions** 标签查看部署进度

## 🌐 访问地址

部署完成后，文档将在以下地址可用：

- **GitHub Pages**: `https://your-username.github.io/telegram-sdk/`
- **自定义域名**: `https://docs.telegram-sdk.xbot.my/`

## 📱 功能特性

### 🎨 用户界面

- **响应式设计**: 完美支持桌面和移动设备
- **深色模式**: 支持系统深色模式自动切换
- **搜索功能**: 全文搜索，快速定位内容
- **导航增强**: 侧边栏、面包屑、分页导航

### 🔧 技术特性

- **代码高亮**: 支持 PHP、JavaScript、JSON、YAML 等
- **代码复制**: 一键复制代码块
- **图表支持**: Mermaid.js 绘制架构图
- **SEO 优化**: 完整的元数据和站点地图

### 📊 性能监控

- **Lighthouse CI**: 自动性能检测
- **构建统计**: 显示构建时间和状态
- **链接检查**: 自动验证内部链接

## 🔄 更新文档

### 自动更新

每当推送包含 `docs/` 目录变更的代码时，GitHub Actions 会自动：

1. 验证文档结构
2. 检查链接完整性
3. 构建静态站点
4. 部署到 GitHub Pages
5. 运行性能检测

### 手动触发

也可以手动触发部署：

1. 访问 GitHub 仓库的 **Actions** 标签
2. 选择 **Deploy Documentation** 工作流
3. 点击 **Run workflow**

## 🛠️ 本地开发

### 安装依赖

```bash
cd docs
npm install
```

### 启动开发服务器

```bash
# 启动本地服务器
npm run dev

# 或使用 docsify-cli
docsify serve . --port 3000
```

### 验证文档

```bash
# 运行验证脚本
./validate.sh

# 检查链接
npm run check-links

# 代码检查
npm run lint
```

## 📝 内容管理

### 添加新页面

1. 在相应目录创建 Markdown 文件
2. 更新 `_sidebar.md` 添加导航链接
3. 提交并推送代码

### 修改样式

- **CSS**: 编辑 `assets/css/custom.css`
- **JavaScript**: 编辑 `assets/js/custom.js`
- **配置**: 修改 `index.html` 中的 docsify 配置

### 添加插件

在 `index.html` 中添加插件脚本：

```html
<!-- 新插件 -->
<script src="//cdn.jsdelivr.net/npm/docsify-plugin-name"></script>
```

## 🔍 监控和维护

### GitHub Actions 监控

- **构建状态**: 检查 Actions 标签的工作流状态
- **部署日志**: 查看详细的构建和部署日志
- **错误通知**: 构建失败时会收到邮件通知

### 性能监控

- **Lighthouse 报告**: 每次部署后自动生成性能报告
- **加载时间**: 监控页面加载性能
- **搜索索引**: 确保搜索功能正常

### 定期维护

建议每月进行：

1. 检查链接有效性
2. 更新依赖包版本
3. 审查和优化内容
4. 备份重要配置

## 🐛 故障排除

### 部署失败

1. 检查 GitHub Actions 日志
2. 验证文档结构完整性
3. 确认所有链接有效
4. 检查 YAML 语法

### 访问问题

1. 确认 GitHub Pages 已启用
2. 检查自定义域名 DNS 配置
3. 等待 DNS 传播（最多 24 小时）
4. 验证 SSL 证书状态

### 搜索不工作

1. 清除浏览器缓存
2. 检查 JavaScript 控制台错误
3. 验证 docsify 配置

## 📞 获取支持

如遇到问题：

1. 查看 [GitHub Issues](https://github.com/xbot-my/telegram-sdk/issues)
2. 在 [讨论区](https://github.com/xbot-my/telegram-sdk/discussions) 提问
3. 联系维护团队

---

🎉 **恭喜！** 您的文档站点已成功配置并准备部署！