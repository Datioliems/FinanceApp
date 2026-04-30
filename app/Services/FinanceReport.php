<?php
namespace App\Services;
use App\Repositories\TransactionRepository;

class FinanceReport
{
    public function __construct(private TransactionRepository $repo) {}

    public function generateMonthly(int $month, int $year): array
    {
        $s=$this->repo->getSummaryByMonth($month,$year);
        $income=(float)($s['income']??0); $expense=(float)($s['expense']??0);
        return ['income'=>$income,'expense'=>$expense,'balance'=>$income-$expense,'month'=>$month,'year'=>$year];
    }

    public function generateRange(string $startDate, string $endDate): array
    {
        $s=$this->repo->getSummaryByRange($startDate,$endDate);
        $income=(float)($s['income']??0); $expense=(float)($s['expense']??0);
        return ['income'=>$income,'expense'=>$expense,'balance'=>$income-$expense];
    }

    public function getByCategory(int $month, int $year): array
    {
        $rows=$this->repo->getExpenseByCategory($month,$year);
        $labels=[]; $data=[]; $colors=[];
        $defaults=['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#C9CBCF'];
        $i=0;
        foreach($rows as $row){
            $labels[]=$row['category_name']; $data[]=(float)$row['total'];
            $colors[]=$row['color']?:$defaults[$i%count($defaults)]; $i++;
        }
        return compact('labels','data','colors');
    }

    public function getTrend(int $weeks): array
    {
        $labels=[]; $income=[]; $expense=[];
        for($i=$weeks-1;$i>=0;$i--){
            $start=new \DateTime("-{$i} weeks monday this week");
            $end=new \DateTime("-{$i} weeks sunday this week");
            $rows=$this->repo->findByDateRange($start,$end);
            $inc=0.0; $exp=0.0;
            foreach($rows as $r){ if($r['type']==='income') $inc+=(float)$r['amount']; else $exp+=(float)$r['amount']; }
            $labels[]='Tuần '.$start->format('W'); $income[]=$inc; $expense[]=$exp;
        }
        return compact('labels','income','expense');
    }

    public function exportCsv(int $month, int $year): void
    {
        $rows=$this->repo->findByMonth($month,$year);
        echo "\xEF\xBB\xBF";
        $out=fopen('php://output','w');
        fputcsv($out,['Ngày','Loại','Danh mục','Số tiền (đ)','Ghi chú']);
        foreach($rows as $r){
            fputcsv($out,[
                $r['trans_date'],
                $r['type']==='income'?'Thu nhập':'Chi tiêu',
                $r['category_name'],
                number_format((float)$r['amount'],0,',','.'),
                $r['note']??'',
            ]);
        }
        fclose($out);
    }
}
