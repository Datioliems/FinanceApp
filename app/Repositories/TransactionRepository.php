<?php
namespace App\Repositories;

// CHECKPOINT C: Không có user_id — ứng dụng 1 người dùng

class TransactionRepository extends BaseRepository
{
    protected function getTable(): string { return 'transactions'; }

    public function findFiltered(
        string $type='', string $sort='date_desc',
        string $startDate='', string $endDate='',
        int $limit=15, int $offset=0, int $categoryId=0
    ): array {
        $orderMap = [
            'date_desc'=>'t.trans_date DESC, t.id DESC','date_asc'=>'t.trans_date ASC, t.id ASC',
            'amount_desc'=>'t.amount DESC','amount_asc'=>'t.amount ASC',
        ];
        $sql = 'SELECT t.*, c.name AS category_name, c.color AS category_color
                FROM transactions t JOIN categories c ON t.category_id=c.id WHERE 1=1';
        $params = [];
        if ($type!=='')       { $sql.=' AND t.type=:type';           $params[':type']=$type; }
        if (!empty($startDate)){ $sql.=' AND t.trans_date>=:start';  $params[':start']=$startDate; }
        if (!empty($endDate))  { $sql.=' AND t.trans_date<=:end';    $params[':end']=$endDate; }
        if ($categoryId>0)     { $sql.=' AND t.category_id=:cat';    $params[':cat']=$categoryId; }
        $sql.=" ORDER BY ".($orderMap[$sort]??$orderMap['date_desc'])." LIMIT :limit OFFSET :offset";
        $stmt=$this->db->prepare($sql);
        foreach($params as $k=>$v) $stmt->bindValue($k,$v);
        $stmt->bindValue(':limit',$limit,\PDO::PARAM_INT);
        $stmt->bindValue(':offset',$offset,\PDO::PARAM_INT);
        $stmt->execute(); return $stmt->fetchAll();
    }

    public function countFiltered(string $type='', string $startDate='', string $endDate='', int $categoryId=0): int
    {
        $sql='SELECT COUNT(*) FROM transactions WHERE 1=1'; $params=[];
        if ($type!=='')        { $sql.=' AND type=:type';           $params[':type']=$type; }
        if (!empty($startDate)){ $sql.=' AND trans_date>=:start';   $params[':start']=$startDate; }
        if (!empty($endDate))  { $sql.=' AND trans_date<=:end';     $params[':end']=$endDate; }
        if ($categoryId>0)     { $sql.=' AND category_id=:cat';     $params[':cat']=$categoryId; }
        $stmt=$this->db->prepare($sql); $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getDailySummary(string $startDate='', string $endDate=''): array
    {
        $sql="SELECT trans_date,
                     SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS income,
                     SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense,
                     SUM(CASE WHEN type='income'  THEN amount ELSE -amount END) AS balance,
                     COUNT(*) AS total_tx
              FROM transactions WHERE 1=1"; $params=[];
        if (!empty($startDate)){ $sql.=' AND trans_date>=:start'; $params[':start']=$startDate; }
        if (!empty($endDate))  { $sql.=' AND trans_date<=:end';   $params[':end']=$endDate; }
        $sql.=' GROUP BY trans_date ORDER BY trans_date DESC';
        $stmt=$this->db->prepare($sql); $stmt->execute($params); return $stmt->fetchAll();
    }

    public function getSummaryByRange(string $startDate, string $endDate): array
    {
        $sql="SELECT SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS income,
                     SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense
              FROM transactions WHERE 1=1"; $params=[];
        if (!empty($startDate)){ $sql.=' AND trans_date>=:start'; $params[':start']=$startDate; }
        if (!empty($endDate))  { $sql.=' AND trans_date<=:end';   $params[':end']=$endDate; }
        $stmt=$this->db->prepare($sql); $stmt->execute($params);
        return $stmt->fetch() ?: ['income'=>0,'expense'=>0];
    }

    public function getSumByCategory(int $categoryId, int $month, int $year): float
    {
        $stmt=$this->db->prepare(
            "SELECT COALESCE(SUM(amount),0) FROM transactions
             WHERE category_id=:cid AND type='expense'
               AND MONTH(trans_date)=:m AND YEAR(trans_date)=:y"
        );
        $stmt->execute([':cid'=>$categoryId,':m'=>$month,':y'=>$year]);
        return (float)$stmt->fetchColumn();
    }

    public function getSummaryByMonth(int $month, int $year): array
    {
        $stmt=$this->db->prepare(
            'SELECT type,COALESCE(SUM(amount),0) AS total FROM transactions
             WHERE MONTH(trans_date)=:m AND YEAR(trans_date)=:y GROUP BY type'
        );
        $stmt->execute([':m'=>$month,':y'=>$year]);
        $r=['income'=>0.0,'expense'=>0.0];
        foreach($stmt->fetchAll() as $row) $r[$row['type']]=(float)$row['total'];
        return $r;
    }

    public function getExpenseByCategory(int $month, int $year): array
    {
        $stmt=$this->db->prepare(
            "SELECT c.name AS category_name, c.color, SUM(t.amount) AS total
             FROM transactions t JOIN categories c ON t.category_id=c.id
             WHERE t.type='expense' AND MONTH(t.trans_date)=:m AND YEAR(t.trans_date)=:y
             GROUP BY t.category_id ORDER BY total DESC"
        );
        $stmt->execute([':m'=>$month,':y'=>$year]); return $stmt->fetchAll();
    }

    public function findByMonth(int $month, int $year): array
    {
        $stmt=$this->db->prepare(
            'SELECT t.trans_date, t.type, c.name AS category_name, t.amount, t.note
             FROM transactions t JOIN categories c ON t.category_id=c.id
             WHERE MONTH(t.trans_date)=:m AND YEAR(t.trans_date)=:y
             ORDER BY t.trans_date ASC, t.id ASC'
        );
        $stmt->execute([':m'=>$month,':y'=>$year]); return $stmt->fetchAll();
    }

    public function findByDateRange(\DateTime $from, \DateTime $to): array
    {
        $stmt=$this->db->prepare(
            'SELECT * FROM transactions WHERE trans_date>=:from AND trans_date<=:to ORDER BY trans_date ASC'
        );
        $stmt->execute([':from'=>$from->format('Y-m-d'),':to'=>$to->format('Y-m-d')]);
        return $stmt->fetchAll();
    }

    public function save(array $data): int
    {
        $stmt=$this->db->prepare(
            'INSERT INTO transactions (category_id,type,amount,note,trans_date)
             VALUES (:cid,:type,:amount,:note,:date)'
        );
        $stmt->execute([
            ':cid'=>$data['category_id'],':type'=>$data['type'],
            ':amount'=>$data['amount'],':note'=>$data['note']??'',':date'=>$data['trans_date'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql='UPDATE transactions SET category_id=:cid,amount=:amount,note=:note,trans_date=:date,updated_at=NOW()';
        $p=[':cid'=>$data['category_id'],':amount'=>$data['amount'],
            ':note'=>$data['note']??'',':date'=>$data['trans_date']];
        if (isset($data['type'])) { $sql.=',type=:type'; $p[':type']=$data['type']; }
        $sql.=' WHERE id=:id'; $p[':id']=$id;
        $stmt=$this->db->prepare($sql); $stmt->execute($p);
        return $stmt->rowCount()>0;
    }

    public function deleteById(int $id): bool
    {
        $stmt=$this->db->prepare('DELETE FROM transactions WHERE id=?');
        $stmt->execute([$id]); return $stmt->rowCount()>0;
    }
}
