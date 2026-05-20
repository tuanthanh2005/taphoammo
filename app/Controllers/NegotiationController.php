<?php
// app/Controllers/NegotiationController.php

class NegotiationController extends Controller
{
    private $roomModel;
    private $userModel;

    public function __construct()
    {
        $this->roomModel = new NegotiationRoom();
        $this->userModel = new User();
    }

    /* =================== ADMIN =================== */

    public function adminIndex()
    {
        $status = $_GET['status'] ?? null;
        $rooms = $this->roomModel->getRoomsForAdmin(null, $status);

        $this->view('admin/negotiations', [
            'rooms' => $rooms,
            'currentStatus' => $status,
            'pageTitle' => 'Phòng đàm phán',
        ]);
    }

    public function adminCreate()
    {
        if (!isset($_POST) || empty($_POST)) {
            return $this->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        }

        try {
            CSRF::check();
        } catch (Exception $e) {
            return $this->json(['success' => false, 'message' => 'CSRF token không hợp lệ']);
        }

        $buyerId = (int)($_POST['buyer_id'] ?? 0);
        $sellerId = (int)($_POST['seller_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $topic = trim($_POST['topic'] ?? '');
        $disputeId = !empty($_POST['dispute_id']) ? (int)$_POST['dispute_id'] : null;

        if (!$buyerId || !$sellerId || !$title) {
            Session::setFlash('error', 'Vui lòng điền đầy đủ thông tin (khách, seller, tiêu đề)');
            $this->redirect('/admin/negotiations');
            return;
        }

        $buyer = $this->userModel->find($buyerId);
        $seller = $this->userModel->find($sellerId);
        if (!$buyer || !$seller) {
            Session::setFlash('error', 'Không tìm thấy user hoặc seller');
            $this->redirect('/admin/negotiations');
            return;
        }

        $adminId = Auth::id();
        $roomId = $this->roomModel->createRoom($adminId, $buyerId, $sellerId, $title, $topic, $disputeId);

        // System welcome message
        $welcome = "🏛️ <b>PHÒNG ĐÀM PHÁN ĐƯỢC TẠO</b>\n";
        $welcome .= "Chủ đề: " . $title . "\n";
        if ($topic) $welcome .= "Nội dung: " . $topic . "\n";
        $welcome .= "Vui lòng trao đổi văn minh, hợp tác để giải quyết vấn đề.";
        $this->roomModel->addMessage($roomId, $adminId, 'system', $welcome, null, 1);

        // Notifications
        $this->notifyParticipants($roomId, $title, $topic, $buyer, $seller);

        Session::setFlash('success', 'Đã tạo phòng đàm phán #' . $roomId);
        $this->redirect('/admin/negotiations/' . $roomId);
    }

    public function adminDetail($id)
    {
        $room = $this->roomModel->findWithDetails($id);
        if (!$room) {
            Session::setFlash('error', 'Không tìm thấy phòng đàm phán');
            $this->redirect('/admin/negotiations');
            return;
        }

        $messages = $this->roomModel->getMessages($id);
        $this->roomModel->resetUnread($id, 'admin');

        $this->view('admin/negotiation_detail', [
            'room' => $room,
            'messages' => $messages,
            'pageTitle' => 'Đàm phán #' . $id,
        ]);
    }

    public function adminClose($id)
    {
        try { CSRF::check(); } catch (Exception $e) { $this->redirect('/admin/negotiations/' . $id); return; }

        $status = $_POST['status'] ?? 'resolved';
        if (!in_array($status, ['resolved', 'closed', 'open'])) $status = 'closed';

        $this->roomModel->setStatus($id, $status);

        $msg = $status === 'resolved' ? '✅ Phòng đàm phán đã được đánh dấu GIẢI QUYẾT bởi admin.'
            : ($status === 'closed' ? '🔒 Phòng đàm phán đã được ĐÓNG bởi admin.' : '🔓 Phòng được mở lại bởi admin.');
        $this->roomModel->addMessage($id, Auth::id(), 'system', $msg, null, 1);

        Session::setFlash('success', 'Cập nhật trạng thái thành công');
        $this->redirect('/admin/negotiations/' . $id);
    }

    /** API: search users by name/username/email */
    public function searchUsers()
    {
        $q = trim($_GET['q'] ?? '');
        $role = $_GET['role'] ?? '';
        if (mb_strlen($q) < 1) return $this->json(['success' => true, 'users' => []]);

        $where = "(name LIKE ? OR username LIKE ? OR email LIKE ?) AND status != 'banned'";
        $params = ["%{$q}%", "%{$q}%", "%{$q}%"];
        if ($role === 'seller') {
            $where .= " AND role = 'seller'";
        } elseif ($role === 'buyer') {
            $where .= " AND role IN ('user','buyer','seller')";
        }

        $users = Database::getInstance()->fetchAll(
            "SELECT id, name, username, email, avatar, role FROM users WHERE {$where} ORDER BY id DESC LIMIT 15",
            $params
        );
        return $this->json(['success' => true, 'users' => $users]);
    }

    /* =================== ROOM ACCESS (any participant) =================== */

    public function show($id)
    {
        $room = $this->roomModel->findWithDetails($id);
        if (!$room) {
            Session::setFlash('error', 'Không tìm thấy phòng');
            $this->redirect('/');
            return;
        }

        $userId = Auth::id();
        $role = $this->roleOf($room, $userId);
        if (!$role) {
            Session::setFlash('error', 'Bạn không có quyền truy cập phòng này');
            $this->redirect('/');
            return;
        }

        $messages = $this->roomModel->getMessages($id);
        $this->roomModel->resetUnread($id, $role);

        $this->view('shared/negotiation_room', [
            'room' => $room,
            'messages' => $messages,
            'role' => $role,
            'pageTitle' => 'Phòng đàm phán #' . $id,
        ]);
    }

    /** API: send message into room */
    public function sendMessage($id)
    {
        if (!Auth::check()) return $this->json(['success' => false, 'message' => 'Cần đăng nhập']);
        try { CSRF::check(); } catch (Exception $e) { return $this->json(['success' => false, 'message' => 'CSRF lỗi']); }

        $room = $this->roomModel->find($id);
        if (!$room) return $this->json(['success' => false, 'message' => 'Phòng không tồn tại']);

        $userId = Auth::id();
        $role = $this->roleOf($room, $userId);
        if (!$role || $role === 'system') return $this->json(['success' => false, 'message' => 'Không có quyền']);

        if ($room['status'] !== 'open') {
            return $this->json(['success' => false, 'message' => 'Phòng đã đóng, không thể gửi tin']);
        }

        $message = trim($_POST['message'] ?? '');
        $attachmentPath = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $upload = Helper::uploadFile($_FILES['attachment'], 'chat');
            if ($upload['success']) $attachmentPath = $upload['path'];
        }
        if ($message === '' && !$attachmentPath) {
            return $this->json(['success' => false, 'message' => 'Tin nhắn trống']);
        }
        if ($message === '' && $attachmentPath) $message = '[Tệp đính kèm]';

        $this->roomModel->addMessage($id, $userId, $role, $message, $attachmentPath, 0);
        $messages = $this->roomModel->getMessages($id);

        return $this->json([
            'success' => true,
            'messages' => $messages,
            'current_user_id' => $userId,
            'role' => $role,
        ]);
    }

    public function getMessagesApi($id)
    {
        if (!Auth::check()) return $this->json(['success' => false]);
        $room = $this->roomModel->find($id);
        if (!$room) return $this->json(['success' => false]);
        $userId = Auth::id();
        $role = $this->roleOf($room, $userId);
        if (!$role) return $this->json(['success' => false]);

        $messages = $this->roomModel->getMessages($id);
        $this->roomModel->resetUnread($id, $role);

        return $this->json([
            'success' => true,
            'messages' => $messages,
            'current_user_id' => $userId,
            'role' => $role,
            'room_status' => $room['status'],
        ]);
    }

    /* =================== Helpers =================== */

    private function roleOf($room, $userId)
    {
        $userId = (int)$userId;
        if ((int)$room['admin_id'] === $userId || Auth::user()['role'] === 'admin') return 'admin';
        if ((int)$room['buyer_id'] === $userId) return 'buyer';
        if ((int)$room['seller_id'] === $userId) return 'seller';
        return null;
    }

    private function notifyParticipants($roomId, $title, $topic, $buyer, $seller)
    {
        $notification = new Notification();
        $url = url('/negotiation/' . $roomId);

        // Seller - in-app
        $notification->send(
            $seller['id'],
            '🏛️ Bạn được mời vào phòng đàm phán',
            "Admin đã tạo phòng đàm phán: \"{$title}\". Vui lòng vào trao đổi để giải quyết vấn đề. Link: {$url}",
            'warning'
        );

        // Buyer - in-app
        $notification->send(
            $buyer['id'],
            '🏛️ Phòng đàm phán đã được mở',
            "Admin đã mở phòng đàm phán: \"{$title}\". Vui lòng vào trao đổi cùng seller. Link: {$url}",
            'info'
        );

        // Telegram for seller
        try {
            if (!empty($seller['telegram_chat_id'])) {
                $msg = "🏛️ <b>BẠN ĐƯỢC MỜI VÀO PHÒNG ĐÀM PHÁN</b>\n\n";
                $msg .= "Chủ đề: <b>" . htmlspecialchars($title) . "</b>\n";
                if ($topic) $msg .= "Nội dung: " . htmlspecialchars(mb_strimwidth($topic, 0, 200, '...')) . "\n";
                $msg .= "\n⚠️ Vui lòng đăng nhập và vào phòng để trao đổi cùng khách hàng và admin.\n";
                $msg .= "👉 {$url}";
                Helper::sendTelegramMessage($seller['telegram_chat_id'], $msg);
            }
        } catch (Exception $e) {
            error_log('Negotiation telegram seller: ' . $e->getMessage());
        }

        // Telegram for buyer
        try {
            if (!empty($buyer['telegram_chat_id'])) {
                $msg = "🏛️ <b>PHÒNG ĐÀM PHÁN ĐÃ ĐƯỢC MỞ</b>\n\n";
                $msg .= "Chủ đề: <b>" . htmlspecialchars($title) . "</b>\n";
                $msg .= "Admin đã mở phòng để bạn trao đổi với seller.\n";
                $msg .= "👉 {$url}";
                Helper::sendTelegramMessage($buyer['telegram_chat_id'], $msg);
            }
        } catch (Exception $e) {
            error_log('Negotiation telegram buyer: ' . $e->getMessage());
        }

        // System chat message reminder via NPC to seller
        try {
            $reminder = "⚠️ <b>NHẮC NHỞ TỪ HỆ THỐNG</b>\n\n"
                . "Bạn đang được mời vào phòng đàm phán \"{$title}\". Vui lòng vào trao đổi để giải quyết vấn đề về hàng hóa/dịch vụ.\n"
                . "👉 Truy cập: " . url('/negotiation/' . $roomId);
            Helper::sendSystemMessage($seller['id'], $reminder);
        } catch (Exception $e) {}
    }
}
