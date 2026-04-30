<?php
namespace App\Services;
use App\Repositories\TransactionRepository;

class ReportService
{
    public function __construct(private TransactionRepository $txRepo) {}

    public function getSummaryByMonth(int $month, int $year): array
    {
        $raw=$this->txRepo->getSummaryByMonth($month,$year);
        $income=(float)($raw['income']??0); $expense=(float)($raw['expense']??0);
        return ['income'=>$income,'expense'=>$expense,'balance'=>$income-$expense];
    }

    public function getByCategory(int $month, int $year): array
    {
        $rows=$this->txRepo->getExpenseByCategory($month,$year);
        $labels=[]; $data=[]; $colors=[];
        $defaults=['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#C9CBCF'];
        $i=0;
        foreach($rows as $r){
            $labels[]=$r['category_name']; $data[]=(float)$r['total'];
            $colors[]=$r['color']?:$defaults[$i%count($defaults)]; $i++;
        }
        return ['labels'=>$labels,'datasets'=>[['data'=>$data,'backgroundColor'=>$colors,'hoverOffset'=>6]]];
    }

    public function getTrend(int $weeks): array
    {
        $labels=[]; $income=[]; $expense=[];
        for($i=$weeks-1;$i>=0;$i--){
            $start=new \DateTime("-{$i} weeks monday this week");
            $end=new \DateTime("-{$i} weeks sunday this week");
            $rows=$this->txRepo->findByDateRange($start,$end);
            $inc=0.0; $exp=0.0;
            foreach($rows as $r){ if($r['type']==='income') $inc+=(float)$r['amount']; else $exp+=(float)$r['amount']; }
            $labels[]='Tuần '.$start->format('W'); $income[]=$inc; $expense[]=$exp;
        }
        return ['labels'=>$labels,'datasets'=>[
            ['label'=>'Thu nhập','data'=>$income,'backgroundColor'=>'#22c55e','borderRadius'=>4],
            ['label'=>'Chi tiêu','data'=>$expense,'backgroundColor'=>'#ef4444','borderRadius'=>4],
        ]];
    }
}
