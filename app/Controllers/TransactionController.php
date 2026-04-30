<?php
namespace App\Controllers;
use App\Services\{IncomeService,ExpenseService,FinanceReport};
use App\Repositories\{TransactionRepository,CategoryRepository};
use App\Helpers\{CsrfTokenManager,FlashMessage,Paginator};

class TransactionController extends BaseController
{
    private IncomeService         $incomeService;
    private ExpenseService        $expenseService;
    private FinanceReport         $report;
    private TransactionRepository $txRepo;
    private CategoryRepository    $catRepo;

    public function __construct()
    {
        $this->txRepo         = new TransactionRepository();
        $this->catRepo        = new CategoryRepository();
        $this->incomeService  = new IncomeService($this->txRepo);
        $this->expenseService = new ExpenseService($this->txRepo);
        $this->report         = new FinanceReport($this->txRepo);
    }

    public function index(): void
    {
        $page       = max(1,(int)($_GET['page']??1));
        $startDate  = $_GET['start_date']??date('Y-m-01');
        $endDate    = $_GET['end_date']??date('Y-m-t');
        $filterType = $_GET['filter_type']??'';
        $sort       = $_GET['sort']??'date_desc';
        $catFilter  = (int)($_GET['category_id']??0);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$startDate)) $startDate=date('Y-m-01');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$endDate))   $endDate=date('Y-m-t');
        if (!in_array($filterType,['income','expense',''],true)) $filterType='';

        $total=$this->txRepo->countFiltered($filterType,$startDate,$endDate,$catFilter);
        $pager=new Paginator($total,10,$page);
        $items=$this->txRepo->findFiltered($filterType,$sort,$startDate,$endDate,
            $pager->getPerPage(),$pager->getOffset(),$catFilter);

        $dailySummary=$this->txRepo->getDailySummary($startDate,$endDate);
        $summary=$this->report->generateRange($startDate,$endDate);
        $cats=$this->catRepo->findAll();

        $this->render('transactions/index',compact(
            'items','pager','filterType','sort','startDate','endDate',
            'catFilter','cats','dailySummary','summary'
        )+['pageTitle'=>'Giao dịch']);
    }

    public function create(): void
    {
        $incCats=$this->catRepo->findByType('income');
        $expCats=$this->catRepo->findByType('expense');
        $this->render('transactions/create',[
            'incCats'=>$incCats,'expCats'=>$expCats,
            'csrf'=>CsrfTokenManager::generate(),
            'today'=>date('Y-m-d'),'pageTitle'=>'Thêm giao dịch',
        ]);
    }

    public function store(): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token']??'')) {
            FlashMessage::set('danger','Phiên hết hạn.'); $this->redirect(BASE_URL.'/transactions/create');
        }
        CsrfTokenManager::invalidate();

        if (($_POST['trans_date']??'')>date('Y-m-d')) {
            FlashMessage::set('danger','Không được nhập giao dịch trong tương lai.');
            $this->redirect(BASE_URL.'/transactions/create');
        }

        $type=($_POST['type']??'');
        try {
            if ($type==='income') {
                $this->incomeService->add($_POST);
                FlashMessage::set('success','Đã lưu thu nhập.');
            } elseif ($type==='expense') {
                $alert=$this->expenseService->add($_POST);
                FlashMessage::set($alert?'warning':'success',$alert??'Đã lưu chi tiêu.');
            } else {
                FlashMessage::set('danger','Loại giao dịch không hợp lệ.');
                $this->redirect(BASE_URL.'/transactions/create');
            }
            $this->redirect(BASE_URL.'/transactions');
        } catch (\InvalidArgumentException $e) {
            FlashMessage::set('danger',$e->getMessage());
            $this->redirect(BASE_URL.'/transactions/create');
        }
    }

    public function edit(string $id): void
    {
        $tx=$this->txRepo->findById((int)$id);
        if (!$tx) { FlashMessage::set('danger','Không tìm thấy giao dịch.'); $this->redirect(BASE_URL.'/transactions'); }
        $incCats=$this->catRepo->findByType('income');
        $expCats=$this->catRepo->findByType('expense');
        $this->render('transactions/edit',[
            'tx'=>$tx,'incCats'=>$incCats,'expCats'=>$expCats,
            'csrf'=>CsrfTokenManager::generate(),
            'today'=>date('Y-m-d'),'pageTitle'=>'Sửa giao dịch',
        ]);
    }

    public function update(string $id): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token']??'')) {
            FlashMessage::set('danger','Phiên hết hạn.'); $this->redirect(BASE_URL.'/transactions');
        }
        CsrfTokenManager::invalidate();

        if (($_POST['trans_date']??'')>date('Y-m-d')) {
            FlashMessage::set('danger','Không được nhập giao dịch trong tương lai.');
            $this->redirect(BASE_URL.'/transactions/'.(int)$id.'/edit');
        }

        $tx=$this->txRepo->findById((int)$id);
        if (!$tx) { FlashMessage::set('danger','Không tìm thấy giao dịch.'); $this->redirect(BASE_URL.'/transactions'); }

        $type=$tx['type'];
        if ($_POST['type']??'' !== $type) {
            FlashMessage::set('danger','Không thể đổi loại giao dịch khi sửa.');
            $this->redirect(BASE_URL.'/transactions/'.(int)$id.'/edit');
        }

        try {
            if ($type==='income') $this->incomeService->update((int)$id,array_merge($_POST,['type'=>'income']));
            else                  $this->expenseService->update((int)$id,array_merge($_POST,['type'=>'expense']));
            FlashMessage::set('success','Đã cập nhật giao dịch.');
        } catch (\Exception $e) {
            FlashMessage::set('danger',$e->getMessage());
        }
        $this->redirect(BASE_URL.'/transactions');
    }

    public function destroy(string $id): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token']??'')) {
            FlashMessage::set('danger','Phiên hết hạn.'); $this->redirect(BASE_URL.'/transactions');
        }
        CsrfTokenManager::invalidate();
        try {
            $this->txRepo->deleteById((int)$id);
            FlashMessage::set('success','Đã xoá giao dịch.');
        } catch (\Exception $e) {
            FlashMessage::set('danger',$e->getMessage());
        }
        $this->redirect(BASE_URL.'/transactions');
    }
}
