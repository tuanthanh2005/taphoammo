<?php
$c = file_get_contents('d:/aicuatoi/app/Controllers/AdminController.php');
$code = <<<EOT
    public function disputes() {
        require_once __DIR__ . '/../Models/Dispute.php';
        \$disputeModel = new Dispute();
        
        \$page = \$_GET['page'] ?? 1;
        \$status = \$_GET['status'] ?? '';
        
        \$disputes = \$disputeModel->getAllDisputes(\$page, 50, \$status);
        \$counts = \$disputeModel->countByStatus();
        
        \$this->view('admin/disputes', [
            'disputes' => \$disputes,
            'counts' => \$counts,
            'currentPage' => \$page,
            'currentStatus' => \$status
        ]);
    }

    public function resolveDispute(\$id) {
        if (\$_SERVER['REQUEST_METHOD'] !== 'POST') {
            \$this->redirect('/admin/disputes');
            return;
        }

        CSRF::check();
        
        \$decision = \$_POST['decision'] ?? '';
        \$refundAmount = (float)(\$_POST['refund_amount'] ?? 0);
        \$penaltyAmount = (float)(\$_POST['penalty_amount'] ?? 0);
        \$adminNote = trim(\$_POST['admin_note'] ?? '');

        require_once __DIR__ . '/../Services/DisputeService.php';
        \$disputeService = new DisputeService();
        \$result = \$disputeService->resolveDispute(Auth::id(), \$id, \$decision, \$refundAmount, \$penaltyAmount, \$adminNote);

        if (\$result['success']) {
            Session::setFlash('success', 'Đã xử lý khiếu nại.');
        } else {
            Session::setFlash('error', \$result['message']);
        }

        \$this->redirect('/admin/disputes');
    }
    
    public function products() {
EOT;
$c = str_replace('    public function products() {', $code, $c);
file_put_contents('d:/aicuatoi/app/Controllers/AdminController.php', $c);
