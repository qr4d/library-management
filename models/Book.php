<?php
class Book {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // 添加图书
    public function add($data) {
        // 处理作者数组
        $author = is_array($data['author']) ? implode(', ', $data['author']) : $data['author'];
        
        // 设置默认值
        $defaultData = [
            'title' => '',
            'author' => '',
            'isbn' => '',
            'publisher' => '',
            'pubdate' => '',
            'category' => '',
            'summary' => '',
            'image' => '',
            'shelf_id' => null,
            'douban_id' => ''
        ];

        // 合并数据，使用默认值
        $data = array_merge($defaultData, [
            'title' => $data['title'],
            'author' => $author,
            'isbn' => $data['isbn13'] ?? ($data['isbn10'] ?? ''),
            'publisher' => $data['publisher'] ?? '',
            'pubdate' => $data['pubdate'] ?? '',
            'category' => $data['category'] ?? '',
            'summary' => $data['summary'] ?? '',
            'image' => $data['image'] ?? '',
            'shelf_id' => $data['shelf_id'] ?? null,
            'douban_id' => $data['id'] ?? ''
        ]);

        $stmt = $this->pdo->prepare('
            INSERT INTO book (
                title, author, isbn, publisher, pubdate,
                category, summary, image_url, shelf_id, douban_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        return $stmt->execute([
            $data['title'],
            $data['author'],
            $data['isbn'],
            $data['publisher'],
            $data['pubdate'],
            $data['category'],
            $data['summary'],
            $data['image'],
            $data['shelf_id'],
            $data['douban_id']
        ]);
    }
    
    // 获取书架下的图书
    public function getByShelf($shelfId) {
        $stmt = $this->pdo->prepare('SELECT * FROM book WHERE shelf_id = ?');
        $stmt->execute([$shelfId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function update($data) {
        $stmt = $this->pdo->prepare("UPDATE books SET title = :title WHERE id = :id");
        return $stmt->execute([
            ':title' => $data['title'] ?? null,
            ':id' => $data['id']
        ]);
    }

    // 减少重复查询,增加连接查询
    public function search($filters) {
        // 构建基础查询
        $query = "
            SELECT b.*, s.name as shelf_name 
            FROM book b
            LEFT JOIN shelf s ON b.shelf_id = s.id
            WHERE 1=1
        ";
        $params = [];
        $countParams = [];

        // 添加过滤条件
        if (!empty($filters['shelf_id'])) {
            $query .= " AND b.shelf_id = :shelf_id";
            $params[':shelf_id'] = $filters['shelf_id'];
            $countParams[':shelf_id'] = $filters['shelf_id'];
        }

        if (!empty($filters['keyword'])) {
            $query .= " AND (b.title LIKE :keyword OR b.author LIKE :keyword OR b.isbn LIKE :keyword)";
            $params[':keyword'] = '%' . $filters['keyword'] . '%';
            $countParams[':keyword'] = '%' . $filters['keyword'] . '%';
        }

        // 获取总数
        $countQuery = str_replace('b.*, s.name as shelf_name', 'COUNT(*) as total', $query);
        $stmt = $this->pdo->prepare($countQuery);
        foreach ($countParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // 添加分页和排序
        $page = max(1, intval($filters['page'] ?? 1));
        $pageSize = max(10, min(100, intval($filters['page_size'] ?? 20)));
        $offset = ($page - 1) * $pageSize;

        $query .= " ORDER BY b.created_at DESC LIMIT :offset, :limit";
        $params[':offset'] = $offset;
        $params[':limit'] = $pageSize;

        // 执行查询
        $stmt = $this->pdo->prepare($query);
        foreach ($params as $key => $value) {
            if ($key === ':offset' || $key === ':limit') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total
        ];
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare('DELETE FROM book WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}