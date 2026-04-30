<?php
namespace App\Services;
use App\Models\Budget;
use App\Repositories\{BudgetRepository, BudgetRepositoryInterface, TransactionRepository};

class BudgetService
{
    public function __construct(
        private BudgetRepositoryInterface $budgetRepo,
        private TransactionRepository     $txRepo
    ) {}

    public function checkAlert(int $categoryId, int $month, int $year): ?string
    {
        $spent  = $this->txRepo->getSumByCategory($categoryId, $month, $year);
        $budget = $this->budgetRepo->findByCategoryAndMonth($categoryId, $month, $year);
        if (!$budget || !$budget->isExceeded($spent)) return null;
        $pct      = round($budget->getUsagePercent($spent), 1);
        $limit    = number_format($budget->getLimitAmount(), 0, ',', '.');
        $spentFmt = number_format($spent, 0, ',', '.');
        return "⚠️ Đã chi {$spentFmt}đ / {$limit}đ ({$pct}%) ngân sách tháng này!";
    }

    public function setLimit(int $categoryId, float $limitAmount, int $month, int $year, int $alertThreshold=80): bool
    {
        if ($limitAmount<=0) throw new \InvalidArgumentException('Hạn mức phải lớn hơn 0.');
        return $this->budgetRepo->upsert([
            'category_id'=>$categoryId,'limit_amount'=>$limitAmount,
            'alert_threshold'=>$alertThreshold,'month'=>$month,'year'=>$year,
        ]);
    }

    public function getBudgetSummary(int $month, int $year): array
    {
        $rows=$this->budgetRepo->findByMonth($month,$year);
        return array_map(function(array $row) use ($month,$year) {
            $budget=Budget::fromArray($row);
            $spent=$this->txRepo->getSumByCategory($row['category_id'],$month,$year);
            return array_merge($row,[
                'spent'=>$spent,'pct'=>$budget->getUsagePercent($spent),
                'status_class'=>$budget->getStatusClass($spent),
                'is_exceeded'=>$budget->isExceeded($spent),
            ]);
        }, $rows);
    }
}
