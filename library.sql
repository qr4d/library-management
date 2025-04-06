CREATE DATABASE IF NOT EXISTS library DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE library;

-- 书架表
CREATE TABLE shelf (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL COMMENT '书架名称',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 图书表  
CREATE TABLE book (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL COMMENT '书名',
    author VARCHAR(100) COMMENT '作者',
    isbn VARCHAR(13) COMMENT 'ISBN号',
    publisher VARCHAR(100) COMMENT '出版社',
    pubdate VARCHAR(20) COMMENT '出版日期',
    category VARCHAR(50) COMMENT '分类',
    summary TEXT COMMENT '简介',
    image_url VARCHAR(200) COMMENT '封面图片URL',
    shelf_id INT COMMENT '所属书架ID',
    douban_id VARCHAR(20) COMMENT '豆瓣ID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (shelf_id) REFERENCES shelf(id)
);

-- 用户表
CREATE TABLE user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE COMMENT '用户名',
    password VARCHAR(255) NOT NULL COMMENT '密码hash',
    role TINYINT(1) NOT NULL DEFAULT 0 COMMENT '角色:0普通用户,1普通管理员,2用户管理员',
    status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '状态:1启用,0禁用',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 操作日志表
CREATE TABLE operation_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL COMMENT '操作用户ID',
    action VARCHAR(50) NOT NULL COMMENT '操作类型',
    target_type VARCHAR(20) NOT NULL COMMENT '目标类型:book/shelf/user',
    target_id INT NOT NULL COMMENT '目标ID',
    detail TEXT COMMENT '详细信息',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id)
);

-- 图书标记表
CREATE TABLE book_mark (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL COMMENT '用户ID',
    isbn VARCHAR(13) NOT NULL COMMENT 'ISBN',
    type VARCHAR(10) NOT NULL COMMENT '标记类型:like/dislike/favorite',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id),
    UNIQUE KEY user_book_mark (user_id, isbn, type)
);

-- 重新生成密码哈希
-- 添加默认管理员账号 admin/admin123
INSERT INTO user (username, password, role) VALUES 
('admin', '$2y$10$A4i7RrxL9pS9W4s5OKqCZOT3IgwumGYz3NXpXAKOs4GwEf7zq5qeO', 2);