<?php
namespace App\Services;
use App\Models\ExpenseTransaction;
use App\Repositories\{TransactionRepository,BudgetRepository};

class ExpenseService
{
    private BudgetService $budgetService;

    public function __construct(private TransactionRepository $txRepo)
    {
        $this->budgetService = new BudgetService(new BudgetRepository(), $txRepo);
    }

    public function add(array $data): ?string
    {
        $tx = new ExpenseTransaction(
            categoryId: (int)($data['category_id']??0),
            amount:     (float)($data['amount']??0),
            transDate:  $data['trans_date']??'',
            note:       trim($data['note']??''),
        );
        $tx->setBudgetService($this->budgetService);
        $tx->process();
        return $tx->getBudgetAlert();
    }

    public function update(int $id, array $data): void
    {
        $amount=(float)($data['amount']??0);
        if ($amount<=0) throw new \InvalidArgumentException('Số tiền phải lớn hơn 0.');
        if (!$this->txRepo->update($id,[
            'category_id'=>(int)($data['category_id']??0),
            'amount'=>$amount,'note'=>trim($data['note']??''),
            'trans_date'=>$data['trans_date']??'','type'=>'expense',
        ])) throw new \RuntimeException('Không tìm thấy giao dịch.');
    }

    public function delete(int $id): void
    {
        if (!$this->txRepo->deleteById($id))
            throw new \RuntimeException('Không tìm thấy giao dịch.');
    }
}
