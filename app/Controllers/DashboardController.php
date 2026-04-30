<?php
namespace App\Controllers;
use App\Services\{ReportService,FinanceReport};
use App\Repositories\TransactionRepository;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $txRepo=new TransactionRepository();
        $rs=new ReportService($txRepo);
        $month=(int)date('n'); $year=(int)date('Y');
        $summary=$rs->getSummaryByMonth($month,$year);
        $donutData=$rs->getByCategory($month,$year);
        $barData=$rs->getTrend(4);
        $chartJson=json_encode(['donut'=>$donutData,'bar'=>$barData],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
        $this->render('dashboard/index',compact('summary','chartJson','month','year')+['pageTitle'=>'Dashboard','needChartJs'=>true]);
    }
}
