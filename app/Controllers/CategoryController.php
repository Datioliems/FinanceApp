<?php
namespace App\Controllers;
use App\Repositories\CategoryRepository;
use App\Helpers\{CsrfTokenManager,FlashMessage};

class CategoryController extends BaseController
{
    private CategoryRepository $catRepo;
    public function __construct() { $this->catRepo=new CategoryRepository(); }

    public function index(): void
    {
        $cats=$this->catRepo->findAll();
        $csrf=CsrfTokenManager::generate();
        $this->render('categories/index',compact('cats','csrf')+['pageTitle'=>'Danh mục']);
    }

    public function store(): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token']??'')) {
            FlashMessage::set('danger','Phiên hết hạn.'); $this->redirect(BASE_URL.'/categories');
        }
        CsrfTokenManager::invalidate();
        $name=trim($_POST['name']??'');
        if (strlen($name)<2) { FlashMessage::set('danger','Tên danh mục phải có ít nhất 2 ký tự.'); $this->redirect(BASE_URL.'/categories'); }
        if ($this->catRepo->findByName($name)) { FlashMessage::set('warning',"Danh mục \"{$name}\" đã tồn tại."); $this->redirect(BASE_URL.'/categories'); }
        $this->catRepo->save(['name'=>$name,'type'=>$_POST['type']??'both','icon'=>trim($_POST['icon']??'')?:null,'color'=>trim($_POST['color']??'')?:null]);
        FlashMessage::set('success',"Đã tạo danh mục \"{$name}\".");
        $this->redirect(BASE_URL.'/categories');
    }

    public function edit(string $id): void
    {
        $cat=$this->catRepo->findById((int)$id);
        if (!$cat) { FlashMessage::set('danger','Không tìm thấy danh mục.'); $this->redirect(BASE_URL.'/categories'); }
        $this->render('categories/edit',['cat'=>$cat,'csrf'=>CsrfTokenManager::generate(),'pageTitle'=>'Sửa danh mục']);
    }

    public function update(string $id): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token']??'')) {
            FlashMessage::set('danger','Phiên hết hạn.'); $this->redirect(BASE_URL.'/categories');
        }
        CsrfTokenManager::invalidate();
        $this->catRepo->updateById((int)$id,['name'=>trim($_POST['name']??''),'type'=>$_POST['type']??'both','icon'=>trim($_POST['icon']??'')?:null,'color'=>trim($_POST['color']??'')?:null]);
        FlashMessage::set('success','Đã cập nhật danh mục.');
        $this->redirect(BASE_URL.'/categories');
    }

    public function destroy(string $id): void
    {
        if (!CsrfTokenManager::validate($_POST['csrf_token']??'')) {
            FlashMessage::set('danger','Phiên hết hạn.'); $this->redirect(BASE_URL.'/categories');
        }
        CsrfTokenManager::invalidate();
        try {
            $this->catRepo->deleteById((int)$id);
            FlashMessage::set('success','Đã xoá danh mục.');
        } catch (\PDOException $e) {
            if (str_starts_with($e->getCode(),'23')) FlashMessage::set('danger','Không thể xoá — danh mục đang có giao dịch.');
            else FlashMessage::set('danger','Có lỗi xảy ra.');
        }
        $this->redirect(BASE_URL.'/categories');
    }
}
