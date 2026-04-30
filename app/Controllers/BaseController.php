<?php
namespace App\Controllers;

abstract class BaseController
{
    protected function render(string $view, array $data=[]): void
    {
        extract($data, EXTR_SKIP);
        $file=BASE_PATH.'/app/Views/'.$view.'.php';
        if (!file_exists($file)) throw new \RuntimeException("View không tồn tại: {$view}");
        require $file;
    }

    protected function redirect(string $url): never
    {
        header('Location: '.$url); exit;
    }

    // Checkpoint C: không có session/user → trả 0 mặc định
    // Tất cả nơi gọi currentUserId() sẽ được thay bằng gọi trực tiếp
    protected function currentUserId(): int { return 0; }
}
