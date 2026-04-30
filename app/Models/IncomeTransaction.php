<?php
namespace App\Models;
use App\Repositories\TransactionRepository;

class IncomeTransaction extends Transaction
{
    public function getType(): string { return 'income'; }

    protected function validate(): void
    {
        if ($this->amount <= 0)
            throw new \InvalidArgumentException('Số tiền thu nhập phải lớn hơn 0.');
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
        error_log(sprintf('[Income] id=%d | cat=%d | amount=%.2f | date=%s',
            $this->id??0, $this->categoryId, $this->amount, $this->transDate));
    }
}
