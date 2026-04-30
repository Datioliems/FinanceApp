<?php
// ============================================================
// ROUTES — Checkpoint C (scope đề bài, không có Auth)
// ============================================================
use App\Controllers\{DashboardController,TransactionController,BudgetController,CategoryController,ReportController};

$router->get('/',          [DashboardController::class, 'index']);
$router->get('/dashboard', [DashboardController::class, 'index']);

$router->get('/transactions',              [TransactionController::class, 'index']);
$router->get('/transactions/create',       [TransactionController::class, 'create']);
$router->post('/transactions',             [TransactionController::class, 'store']);
$router->get('/transactions/{id}/edit',    [TransactionController::class, 'edit']);
$router->post('/transactions/{id}',        [TransactionController::class, 'update']);
$router->post('/transactions/{id}/delete', [TransactionController::class, 'destroy']);

$router->get('/categories',              [CategoryController::class, 'index']);
$router->post('/categories',             [CategoryController::class, 'store']);
$router->get('/categories/{id}/edit',    [CategoryController::class, 'edit']);
$router->post('/categories/{id}',        [CategoryController::class, 'update']);
$router->post('/categories/{id}/delete', [CategoryController::class, 'destroy']);

$router->get('/budget',              [BudgetController::class, 'index']);
$router->post('/budget',             [BudgetController::class, 'setLimit']);
$router->post('/budget/{id}/delete', [BudgetController::class, 'destroy']);

$router->get('/report',        [ReportController::class, 'index']);
$router->get('/report/export', [ReportController::class, 'export']);
