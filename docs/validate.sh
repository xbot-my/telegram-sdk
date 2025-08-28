#!/bin/bash

# 文档验证脚本
echo "🔍 开始验证 Telegram Bot PHP SDK 文档..."
echo "================================================"

# 进入文档目录
cd "$(dirname "$0")" || exit 1

# 验证结果
errors=0
warnings=0

# 1. 检查必要文件
echo "📋 检查必要文件..."
required_files=(
    "index.html"
    "README.md"
    "_sidebar.md"
    "_navbar.md"
    "_coverpage.md"
    ".nojekyll"
    "package.json"
    "CNAME"
)

for file in "${required_files[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✅ $file"
    else
        echo "  ❌ $file (缺失)"
        ((errors++))
    fi
done

# 2. 检查目录结构
echo ""
echo "📁 检查目录结构..."
required_dirs=(
    "guide"
    "api"
    "assets/css"
    "assets/js"
    "assets/images"
    "examples"
    "troubleshooting"
    "best-practices"
)

for dir in "${required_dirs[@]}"; do
    if [ -d "$dir" ]; then
        echo "  ✅ $dir/"
    else
        echo "  ❌ $dir/ (缺失)"
        ((errors++))
    fi
done

# 3. 检查指南文件
echo ""
echo "📖 检查指南文件..."
guide_files=(
    "guide/README.md"
    "guide/installation.md"
    "guide/quick-start.md"
    "guide/configuration.md"
    "guide/deployment.md"
)

for file in "${guide_files[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✅ $file"
    else
        echo "  ❌ $file (缺失)"
        ((errors++))
    fi
done

# 4. 检查API文档
echo ""
echo "📋 检查API文档..."
api_files=(
    "api/README.md"
    "api/bot-manager.md"
)

for file in "${api_files[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✅ $file"
    else
        echo "  ❌ $file (缺失)"
        ((errors++))
    fi
done

# 5. 检查资源文件
echo ""
echo "🎨 检查资源文件..."
asset_files=(
    "assets/css/custom.css"
    "assets/js/custom.js"
)

for file in "${asset_files[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✅ $file"
    else
        echo "  ❌ $file (缺失)"
        ((errors++))
    fi
done

# 6. 检查文件内容
echo ""
echo "📝 检查文件内容..."

# 检查index.html是否包含必要配置
if grep -q "docsify" index.html && grep -q "window.\$docsify" index.html; then
    echo "  ✅ index.html 包含 docsify 配置"
else
    echo "  ❌ index.html 缺少 docsify 配置"
    ((errors++))
fi

# 检查CSS文件是否不为空
if [ -s "assets/css/custom.css" ]; then
    echo "  ✅ custom.css 不为空"
else
    echo "  ⚠️  custom.css 为空或很小"
    ((warnings++))
fi

# 检查JS文件是否不为空
if [ -s "assets/js/custom.js" ]; then
    echo "  ✅ custom.js 不为空"
else
    echo "  ⚠️  custom.js 为空或很小"
    ((warnings++))
fi

# 7. 检查内部链接
echo ""
echo "🔗 检查内部链接..."
broken_links=0

# 简单的内部链接检查
while IFS= read -r line; do
    file=$(echo "$line" | cut -d: -f1)
    link=$(echo "$line" | grep -o '\[.*\](.*\.md)' | sed 's/.*(\(.*\))/\1/')
    
    if [[ "$link" =~ ^\.\./ ]]; then
        # 处理相对路径
        target_dir=$(dirname "$file")
        target_file="$target_dir/$link"
        target_file=$(echo "$target_file" | sed 's|/\./|/|g' | sed 's|[^/]*/\.\./||g')
    else
        # 处理同目录文件
        target_dir=$(dirname "$file")
        target_file="$target_dir/$link"
    fi
    
    if [ ! -f "$target_file" ]; then
        echo "  ❌ 损坏的链接: $file -> $link"
        ((broken_links++))
    fi
done < <(grep -r "\[.*\](.*\.md)" . --include="*.md" | grep -v "http")

if [ $broken_links -eq 0 ]; then
    echo "  ✅ 未发现损坏的内部链接"
else
    echo "  ⚠️  发现 $broken_links 个可能损坏的链接"
    ((warnings+=broken_links))
fi

# 8. 统计信息
echo ""
echo "📊 文档统计..."
total_md_files=$(find . -name "*.md" | wc -l | tr -d ' ')
total_lines=$(find . -name "*.md" -exec cat {} \; | wc -l | tr -d ' ')
total_dirs=$(find . -type d | wc -l | tr -d ' ')

echo "  📄 Markdown 文件: $total_md_files"
echo "  📝 总行数: $total_lines"
echo "  📁 目录数: $total_dirs"

# 9. 检查GitHub Actions配置
echo ""
echo "🚀 检查GitHub Actions..."
if [ -f "../.github/workflows/docs.yml" ]; then
    echo "  ✅ GitHub Actions 工作流存在"
else
    echo "  ❌ GitHub Actions 工作流缺失"
    ((errors++))
fi

# 10. 最终结果
echo ""
echo "================================================"
echo "🏁 验证完成"
echo ""

if [ $errors -eq 0 ] && [ $warnings -eq 0 ]; then
    echo "🎉 所有检查通过！文档已准备就绪。"
    echo ""
    echo "📚 接下来的步骤:"
    echo "  1. 提交代码到 GitHub"
    echo "  2. 启用 GitHub Pages"
    echo "  3. 配置自定义域名（可选）"
    echo "  4. 等待自动部署完成"
    exit 0
elif [ $errors -eq 0 ]; then
    echo "⚠️  验证完成，有 $warnings 个警告"
    echo "📝 文档可以部署，但建议修复警告项目"
    exit 0
else
    echo "❌ 验证失败，发现 $errors 个错误和 $warnings 个警告"
    echo "🔧 请修复错误后重新验证"
    exit 1
fi