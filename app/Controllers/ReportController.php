<?php
namespace App\Controllers;
use App\Services\{ReportService,FinanceReport};
use App\Repositories\TransactionRepository;

class ReportController extends BaseController
{
    public function index(): void
    {
        $txRepo=new TransactionRepository();
        $rs=new ReportService($txRepo);
        $month=(int)($_GET['month']??date('n')); $year=(int)($_GET['year']??date('Y'));
        $summary=$rs->getSummaryByMonth($month,$year);
        $donutData=$rs->getByCategory($month,$year);
        $barData=$rs->getTrend(4);
        $chartJson=json_encode(['donut'=>$donutData,'bar'=>$barData],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
        $this->render('report/index',compact('summary','chartJson','month','year')+['pageTitle'=>"Báo cáo tháng {$month}/{$year}",'needChartJs'=>true]);
    }

    public function export(): void
    {
        $month=(int)($_GET['month']??date('n')); $year=(int)($_GET['year']??date('Y'));
        header('Content-Type: text/csv; charset=UTF-8');
        header(sprintf('Content-Disposition: attachment; filename="bao-cao-%04d-%02d.csv"',$year,$month));
        header('Cache-Control: no-cache'); header('Pragma: no-cache');
        (new FinanceReport(new TransactionRepository()))->exportCsv($month,$year);
        exit;
    }
}
