<?php
namespace App\Controllers;
use App\Services\BudgetService;
use App\Repositories\{BudgetRepository,CategoryRepository,TransactionRepository};
use App\Helpers\{CsrfTokenManager,FlashMessage};

class BudgetController extends BaseController
{
    private BudgetService $budgetService;
    private CategoryRepository $catRepo;

    public function __construct()
    {
        $this->catRepo=$new=new CategoryRepository();
        $this->budgetService=new BudgetService(new BudgetRepository(),new TransactionRepository());
    }

    public function index(): void
    {
        $month=(int)($_GET['month']??date('n'));
        $year=(int)($_GET['year']??date('Y'));
        $summary=$this->budgetService->getBudgetSummary($month,$year);
        $cats=$this->catRepo->findByType('expense');
        $csrf=CsrfTokenManager::generate();
        $this->render('budget/index',compact('summary','cats','csrf','month','year')+['pageTitle'=>'Ngân sách']);
    }

    public function setLimit(): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token']??'')) {
            FlashMessage::set('danger','Phiên hết hạn.'); $this->redirect(BASE_URL.'/budget');
        }
        CsrfTokenManager::invalidate();
        try {
            $this->budgetService->setLimit(
                (int)($_POST['category_id']??0),(float)($_POST['limit_amount']??0),
                (int)($_POST['month']??date('n')),(int)($_POST['year']??date('Y')),
                (int)($_POST['alert_threshold']??80)
            );
            FlashMessage::set('success','Đã cập nhật ngân sách.');
        } catch (\InvalidArgumentException $e) {
            FlashMessage::set('danger',$e->getMessage());
        }
        $m=$_POST['month']??date('n'); $y=$_POST['year']??date('Y');
        $this->redirect(BASE_URL."/budget?month={$m}&year={$y}");
    }

    public function destroy(string $id): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token']??'')) {
            FlashMessage::set('danger','Phiên hết hạn.'); $this->redirect(BASE_URL.'/budget');
        }
        CsrfTokenManager::invalidate();
        (new BudgetRepository())->deleteById((int)$id);
        FlashMessage::set('success','Đã xoá hạn mức.');
        $this->redirect(BASE_URL.'/budget');
    }
}
