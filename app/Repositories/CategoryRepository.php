<?php
namespace App\Repositories;

// CHECKPOINT C: Không có user_id

class CategoryRepository extends BaseRepository
{
    protected function getTable(): string { return 'categories'; }

    public function findAll(): array
    {
        return $this->db->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
    }

    public function findByType(string $type): array
    {
        $stmt=$this->db->prepare(
            "SELECT * FROM categories WHERE type=? OR type='both' ORDER BY name ASC"
        );
        $stmt->execute([$type]); return $stmt->fetchAll();
    }

    public function findByName(string $name): ?array
    {
        $stmt=$this->db->prepare('SELECT * FROM categories WHERE name=? LIMIT 1');
        $stmt->execute([$name]); return $stmt->fetch() ?: null;
    }

    public function save(array $data): int
    {
        $stmt=$this->db->prepare(
            'INSERT INTO categories (name,type,icon,color) VALUES (:name,:type,:icon,:color)'
        );
        $stmt->execute([
            ':name'=>$data['name'],':type'=>$data['type']??'both',
            ':icon'=>$data['icon']??null,':color'=>$data['color']??null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateById(int $id, array $data): bool
    {
        $stmt=$this->db->prepare(
            'UPDATE categories SET name=:name,type=:type,icon=:icon,color=:color WHERE id=:id'
        );
        $stmt->execute([
            ':name'=>$data['name'],':type'=>$data['type']??'both',
            ':icon'=>$data['icon']??null,':color'=>$data['color']??null,':id'=>$id,
        ]);
        return $stmt->rowCount()>0;
    }

    public function deleteById(int $id): bool
    {
        $stmt=$this->db->prepare('DELETE FROM categories WHERE id=?');
        $stmt->execute([$id]); return $stmt->rowCount()>0;
    }
}
