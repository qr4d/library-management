# 个人图书管理系统

一个基于 PHP + MySQL 的个人图书管理系统,支持扫码录入、图书分类、书架管理等功能。

## 功能特点

### 图书管理
- 通过 ISBN 扫码快速录入图书
- 支持豆瓣图书搜索和 ISBN 查询
- 图书分类和书架管理
- 图书信息批量导入导出

### 权限管理
- 多级用户权限(普通用户/普通管理员/用户管理员)
- 操作日志记录
- 用户行为追踪

### 其他特性
- 响应式设计,支持PC端和移动端
- 前后端分离架构
- RESTful API 接口

## 技术栈

- 前端：Bootstrap 5 + jQuery
- 后端：PHP 7.4+ 
- 数据库：MySQL 5.7+
- API：豆瓣图书 API

## 安装步骤

1. 克隆仓库
```bash
git clone https://github.com/qr4d/library-management.git
```
2. 配置数据库
复制 config/database.example.php 为 config/database.php
修改数据库配置信息
```php
<?php
return [
    'host' => 'localhost',
    'dbname' => 'library',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset' => 'utf8mb4'
];
```
3. 配置API密钥
复制 config/apikey.example.php 为 config/apikey.php
设置你的豆瓣API密钥
```php
<?php
return [
    'douban' => 'your_douban_apikey'
];
```
4. 导入数据库
mysql -u username -p < library.sql
5. 配置 Web 服务器
将网站根目录指向项目目录
确保 PHP 有读写权限
6. 初始账号
默认管理员账号: admin
默认密码: admin123

## 项目结构

├── api/            # API接口
├── config/         # 配置文件
├── css/           # 样式文件
├── js/            # JavaScript文件
│   ├── components/    # 公共组件
│   └── pages/        # 页面脚本
├── models/        # 数据模型
└── tools/         # 工具脚本

## API 接口
### 图书搜索
豆瓣搜索: https://api.douban.com/v2/book/search
ISBN查询: https://api.douban.com/v2/book/isbn/{isbn}
## 开发团队
@qr4d

## License
MIT License