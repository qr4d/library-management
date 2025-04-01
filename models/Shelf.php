<?php
class Shelf {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // 添加书架
    public function add($name) {
        $stmt = $this->pdo->prepare('INSERT INTO shelf (name) VALUES (?)');
        return $stmt->execute([$name]);
    }
    
    // 获取所有书架
    public function getAll() {
        $stmt = $this->pdo->query('
            SELECT s.*, COUNT(b.id) as book_count
            FROM shelf s
            LEFT JOIN book b ON b.shelf_id = s.id 
            GROUP BY s.id
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 删除书架
    public function delete($id) {
        $stmt = $this->pdo->prepare('DELETE FROM shelf WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function getNameById($id) {
        $stmt = $this->pdo->prepare('SELECT name FROM shelves WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new Exception("Shelf with ID {$id} not found.");
        }

        return $result['name'];
    }

    public function moveBooks($sourceShelfId, $targetShelfId) {
        $stmt = $this->pdo->prepare('UPDATE books SET shelf_id = :targetShelfId WHERE shelf_id = :sourceShelfId');
        $stmt->execute([
            ':targetShelfId' => $targetShelfId,
            ':sourceShelfId' => $sourceShelfId
        ]);
    }

    /**
     * Deletes all books associated with a specific shelf ID.
     *
     * @param int $shelfId The ID of the shelf whose books should be deleted.
     * @return void
     */
    public function deleteBooks($shelfId) {
        $stmt = $this->pdo->prepare('DELETE FROM books WHERE shelf_id = :shelf_id');
        $stmt->execute(['shelf_id' => $shelfId]);
    }
}