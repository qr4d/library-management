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
        $query = "
            SELECT b.*, s.name as shelf_name, 
                   GROUP_CONCAT(DISTINCT bm.type) as marks,
                   (SELECT COUNT(*) FROM book_mark WHERE isbn = b.isbn AND type = 'like') as like_count,
                   (SELECT COUNT(*) FROM book_mark WHERE isbn = b.isbn AND type = 'dislike') as dislike_count,
                   (SELECT COUNT(*) FROM book_mark WHERE isbn = b.isbn AND type = 'favorite') as favorite_count
            FROM book b
            LEFT JOIN shelf s ON b.shelf_id = s.id
            LEFT JOIN book_mark bm ON b.isbn = bm.isbn 
                AND bm.user_id = :user_id
            WHERE 1=1
        ";
        
        $params = [':user_id' => $filters['user_id'] ?? null];

        if (!empty($filters['shelf_id'])) {
            $query .= " AND b.shelf_id = :shelf_id";
            $params[':shelf_id'] = $filters['shelf_id'];
        }

        if (!empty($filters['keyword'])) {
            $query .= " AND (b.title LIKE :keyword OR b.author LIKE :keyword OR b.isbn LIKE :keyword)";
            $params[':keyword'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['mark_type'])) {
            $query .= " AND EXISTS (
                SELECT 1 FROM book_mark bm2 
                WHERE bm2.isbn = b.isbn 
                AND bm2.user_id = :mark_user_id 
                AND bm2.type = :mark_type
            )";
            $params[':mark_user_id'] = $filters['user_id'];
            $params[':mark_type'] = $filters['mark_type'];
        }

        $query .= " GROUP BY b.id";

        // 修改获取总数的查询
        $countQuery = "
            SELECT COUNT(DISTINCT b.id) as total 
            FROM book b
            LEFT JOIN shelf s ON b.shelf_id = s.id
            LEFT JOIN book_mark bm ON b.isbn = bm.isbn 
                AND bm.user_id = :user_id
            WHERE 1=1
        ";

        if (!empty($filters['shelf_id'])) {
            $countQuery .= " AND b.shelf_id = :shelf_id";
        }

        if (!empty($filters['keyword'])) {
            $countQuery .= " AND (b.title LIKE :keyword OR b.author LIKE :keyword OR b.isbn LIKE :keyword)";
        }

        if (!empty($filters['mark_type'])) {
            $countQuery .= " AND EXISTS (
                SELECT 1 FROM book_mark bm2 
                WHERE bm2.isbn = b.isbn 
                AND bm2.user_id = :mark_user_id 
                AND bm2.type = :mark_type
            )";
        }

        // 执行总数查询
        $stmt = $this->pdo->prepare($countQuery);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $totalResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $totalResult['total'] ?? 0;

        // 添加分页和排序
        $page = max(1, intval($filters['page'] ?? 1));
        $pageSize = max(6, min(90, intval($filters['page_size'] ?? 12))); // 修改这行
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

    public function addMark($userId, $isbn, $type) {
        $stmt = $this->pdo->prepare('
            INSERT INTO book_mark (user_id, isbn, type) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP
        ');
        return $stmt->execute([$userId, $isbn, $type]);
    }

    public function removeMark($userId, $isbn, $type) {
        $stmt = $this->pdo->prepare('
            DELETE FROM book_mark 
            WHERE user_id = ? AND isbn = ? AND type = ?
        ');
        return $stmt->execute([$userId, $isbn, $type]);
    }

    public function getBookMarks($bookId, $userId) {
        $stmt = $this->pdo->prepare('
            SELECT type 
            FROM book_mark 
            WHERE book_id = ? AND user_id = ?
        ');
        $stmt->execute([$bookId, $userId]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'type');
    }

    public function getMarkCounts($isbn) {
        $stmt = $this->pdo->prepare('
            SELECT type, COUNT(*) as count
            FROM book_mark
            WHERE isbn = ?
            GROUP BY type
        ');
        $stmt->execute([$isbn]);
        $counts = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $counts[$row['type']] = $row['count'];
        }
        return $counts;
    }
}