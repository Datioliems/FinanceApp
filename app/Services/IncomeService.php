<?php
namespace App\Services;
use App\Models\IncomeTransaction;
use App\Repositories\TransactionRepository;

class IncomeService
{
    public function __construct(private TransactionRepository $txRepo) {}

    public function add(array $data): void
    {
        $tx = new IncomeTransaction(
            categoryId: (int)($data['category_id']??0),
            amount:     (float)($data['amount']??0),
            transDate:  $data['trans_date']??'',
            note:       trim($data['note']??''),
        );
        $tx->process();
    }

    public function update(int $id, array $data): void
    {
        $amount=(float)($data['amount']??0);
        if ($amount<=0) throw new \InvalidArgumentException('Số tiền phải lớn hơn 0.');
        if (!$this->txRepo->update($id,[
            'category_id'=>(int)($data['category_id']??0),
            'amount'=>$amount,'note'=>trim($data['note']??''),
            'trans_date'=>$data['trans_date']??'','type'=>'income',
        ])) throw new \RuntimeException('Không tìm thấy giao dịch.');
    }

    public function delete(int $id): void
    {
        if (!$this->txRepo->deleteById($id))
            throw new \RuntimeException('Không tìm thấy giao dịch.');
    }
}
