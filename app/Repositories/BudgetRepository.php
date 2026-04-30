<?php
namespace App\Repositories;

// CHECKPOINT C: Không có user_id

interface BudgetRepositoryInterface
{
    public function findByMonth(int $month, int $year): array;
    public function findByCategoryAndMonth(int $categoryId, int $month, int $year): ?\App\Models\Budget;
    public function upsert(array $data): bool;
    public function deleteById(int $id): bool;
}

class BudgetRepository extends BaseRepository implements BudgetRepositoryInterface
{
    protected function getTable(): string { return 'budgets'; }

    public function findByMonth(int $month, int $year): array
    {
        $stmt=$this->db->prepare(
            'SELECT b.*,c.name AS category_name,c.color AS category_color,c.icon AS category_icon
             FROM budgets b JOIN categories c ON b.category_id=c.id
             WHERE b.month=:m AND b.year=:y ORDER BY c.name ASC'
        );
        $stmt->execute([':m'=>$month,':y'=>$year]); return $stmt->fetchAll();
    }

    public function findByCategoryAndMonth(int $categoryId, int $month, int $year): ?\App\Models\Budget
    {
        $stmt=$this->db->prepare(
            'SELECT * FROM budgets WHERE category_id=:cid AND month=:m AND year=:y LIMIT 1'
        );
        $stmt->execute([':cid'=>$categoryId,':m'=>$month,':y'=>$year]);
        $row=$stmt->fetch(); return $row ? \App\Models\Budget::fromArray($row) : null;
    }

    public function upsert(array $data): bool
    {
        $stmt=$this->db->prepare(
            'INSERT INTO budgets (category_id,limit_amount,alert_threshold,month,year)
             VALUES (:cid,:limit,:threshold,:m,:y)
             ON DUPLICATE KEY UPDATE limit_amount=VALUES(limit_amount),alert_threshold=VALUES(alert_threshold)'
        );
        $stmt->execute([
            ':cid'=>$data['category_id'],':limit'=>$data['limit_amount'],
            ':threshold'=>$data['alert_threshold']??80,
            ':m'=>$data['month'],':y'=>$data['year'],
        ]);
        return $stmt->rowCount()>0;
    }

    public function deleteById(int $id): bool
    {
        $stmt=$this->db->prepare('DELETE FROM budgets WHERE id=?');
        $stmt->execute([$id]); return $stmt->rowCount()>0;
    }
}
