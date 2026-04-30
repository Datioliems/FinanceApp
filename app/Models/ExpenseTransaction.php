<?php
namespace App\Models;
use App\Repositories\TransactionRepository;
use App\Services\BudgetService;

class ExpenseTransaction extends Transaction
{
    private ?BudgetService $budgetService = null;
    private ?string $budgetAlert          = null;

    public function getType(): string { return 'expense'; }
    public function setBudgetService(BudgetService $s): void { $this->budgetService = $s; }
    public function getBudgetAlert(): ?string { return $this->budgetAlert; }

    protected function validate(): void
    {
        if ($this->amount <= 0)
            throw new \InvalidArgumentException('Số tiền chi tiêu phải lớn hơn 0.');
        if ($this->categoryId <= 0)
            throw new \InvalidArgumentException('Vui lòng chọn danh mục.');
        if (empty(trim($this->transDate)))
            throw new \InvalidArgumentException('Ngày giao dịch không được để trống.');
        if ($this->transDate > date('Y-m-d'))
            throw new \InvalidArgumentException('Không được nhập giao dịch trong tương lai.');
    }

    protected function save(): void
    {
        $this->id = (new TransactionRepository())->save($this->toArray());
    }

    protected function notify(): void
    {
        if ($this->budgetService === null) return;
        $month = (int)date('n', strtotime($this->transDate));
        $year  = (int)date('Y', strtotime($this->transDate));
        $this->budgetAlert = $this->budgetService->checkAlert(
            $this->categoryId, $month, $year
        );
        error_log(sprintf('[Expense] id=%d | cat=%d | amount=%.2f | alert=%s',
            $this->id??0, $this->categoryId, $this->amount, $this->budgetAlert??'none'));
    }
}
