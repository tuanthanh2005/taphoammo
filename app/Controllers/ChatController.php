<?php
// app/Controllers/ChatController.php

class ChatController extends Controller {
    private $conversationModel;
    private $messageModel;
    private $userModel;

    public function __construct() {
        $this->conversationModel = new Conversation();
        $this->messageModel = new Message();
        $this->userModel = new User();
    }

    public function sendMessage() {
        // Prevent any output before JSON
        ob_start();
        
        try {
            if (!Auth::check()) {
                ob_end_clean();
                return $this->json(['success' => false, 'message' => 'Bạn cần đăng nhập']);
            }
            
            // Verify CSRF token
            try {
                CSRF::check();
            } catch (Exception $e) {
                ob_end_clean();
                return $this->json(['success' => false, 'message' => 'CSRF token không hợp lệ. Vui lòng refresh trang.']);
            }

            $senderId = Auth::id();
            $conversationId = $_POST['conversation_id'] ?? null;
            $recipientId = $_POST['recipient_id'] ?? ($_POST['seller_id'] ?? null);
            $message = trim($_POST['message'] ?? '');

            $attachmentPath = null;
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $upload = Helper::uploadFile($_FILES['attachment'], 'chat');
                if ($upload['success']) {
                    $attachmentPath = $upload['path'];
                } else {
                    ob_end_clean();
                    return $this->json(['success' => false, 'message' => $upload['message']]);
                }
            }

            if (empty($message) && !$attachmentPath) {
                ob_end_clean();
                return $this->json(['success' => false, 'message' => 'Tin nhắn không được để trống']);
            }
            if (empty($message) && $attachmentPath) {
                $message = '[Tệp đính kèm]';
            }

            // Find or create conversation
            if ($conversationId) {
                $conversation = $this->conversationModel->find($conversationId);
                if (!$conversation || ($conversation['buyer_id'] != $senderId && $conversation['seller_id'] != $senderId)) {
                    ob_end_clean();
                    return $this->json(['success' => false, 'message' => 'Cuộc trò chuyện không hợp lệ']);
                }
            } elseif ($recipientId) {
                if ($senderId == $recipientId) {
                    ob_end_clean();
                    return $this->json(['success' => false, 'message' => 'Bạn không thể tự nhắn tin cho chính mình']);
                }
                
                // Buyer initiates chat with seller
                $buyerId = $senderId;
                $sellerId = $recipientId;
                
                $conversation = $this->conversationModel->findOrCreate($buyerId, $sellerId);
                $conversationId = $conversation['id'];
            } else {
                ob_end_clean();
                return $this->json(['success' => false, 'message' => 'Thiếu thông tin người nhận']);
            }

            // Create message
            $msgId = $this->messageModel->create([
                'conversation_id' => $conversation['id'],
                'sender_id' => $senderId,
                'message' => $message,
                'attachment' => $attachmentPath
            ]);

            if ($msgId) {
                // Update conversation last message
                $isBuyer = ($senderId == $conversation['buyer_id']);
                $this->conversationModel->updateLastMessage($conversation['id'], $message, $isBuyer);
                
                // Telegram Notification
                try {
                    $recipientIdFinal = $isBuyer ? $conversation['seller_id'] : $conversation['buyer_id'];
                    $recipientUser = $this->userModel->find($recipientIdFinal);
                    
                    if (!empty($recipientUser['telegram_chat_id'])) {
                        // Kiểm tra nếu người nhận đang online (hoạt động trong 5 phút qua) thì không gửi Telegram
                        $isOnline = false;
                        if (!empty($recipientUser['last_active_at'])) {
                            $isOnline = (time() - strtotime($recipientUser['last_active_at'])) < 300; // 5 phút
                        }

                        if (!$isOnline) {
                            $senderUser = Auth::user();
                            $msg = "💬 <b>TIN NHẮN MỚI TỪ " . e($senderUser['name']) . "</b>\n";
                            $msg .= "Nội dung: " . e($message) . "\n";
                            $msg .= "Vui lòng truy cập hệ thống để trả lời.";
                            Helper::sendTelegramMessage($recipientUser['telegram_chat_id'], $msg);
                        }
                    }
                } catch (Exception $e) {
                    error_log("Telegram Notify Chat Error: " . $e->getMessage());
                }

                // Return updated messages
                $messages = $this->messageModel->getMessagesForConversation($conversation['id']);
                
                ob_end_clean();
                return $this->json([
                    'success' => true,
                    'message' => 'Tin nhắn đã được gửi',
                    'messages' => $messages,
                    'current_user_id' => $senderId
                ]);
            }

            ob_end_clean();
            return $this->json(['success' => false, 'message' => 'Lỗi khi gửi tin nhắn']);
            
        } catch (Exception $e) {
            ob_end_clean();
            error_log('Chat error: ' . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }

    public function getMessages() {
        if (!Auth::check()) {
            return $this->json(['success' => false, 'message' => 'Bạn cần đăng nhập']);
        }

        $sellerId = $_GET['seller_id'] ?? null;
        if (!$sellerId) {
            return $this->json(['success' => false, 'message' => 'Thiếu ID người bán']);
        }

        $buyerId = Auth::id();
        $conversation = $this->conversationModel->findOrCreate($buyerId, $sellerId);
        
        $messages = $this->messageModel->getMessagesForConversation($conversation['id']);
        
        // Mark as read
        $isBuyer = ($buyerId == $conversation['buyer_id']);
        $this->conversationModel->resetUnreadCount($conversation['id'], $isBuyer);
        $this->messageModel->markAsRead($conversation['id'], $buyerId);

        $seller = $this->userModel->find($sellerId);
        $isOnline = false;
        if ($seller && $seller['last_active_at']) {
            $isOnline = (time() - strtotime($seller['last_active_at'])) < 300;
        }
        if ((int)$sellerId === Helper::getSystemUserId()) $isOnline = true;

        return $this->json([
            'success' => true,
            'messages' => $messages,
            'current_user_id' => $buyerId,
            'is_online' => $isOnline,
            'last_active_at' => $seller ? $seller['last_active_at'] : null
        ]);
    }

    public function sellerIndex() {
        if (!Auth::check()) {
            Helper::redirect(url('/login'));
            return;
        }
        
        // Check if user is seller or admin
        $user = Auth::user();
        if (!$user || !in_array($user['role'], ['seller', 'admin'])) {
            Helper::redirect(url('/'));
            return;
        }

        $userId = Auth::id();
        $conversations = $this->conversationModel->getConversationsForUser($userId);

        return $this->view('seller/chat', [
            'title' => 'Quản lý tin nhắn',
            'conversations' => $conversations
        ]);
    }

    public function userIndex() {
        if (!Auth::check()) {
            Helper::redirect(url('/login'));
        }

        $userId = Auth::id();
        $conversations = $this->conversationModel->getConversationsForUser($userId);

        return $this->view('seller/chat', [
            'title' => 'Tin nhắn của tôi',
            'conversations' => $conversations
        ]);
    }

    public function getConversationMessages() {
        if (!Auth::check()) {
            return $this->json(['success' => false]);
        }

        $id = $_GET['id'] ?? null;
        if (!$id) return $this->json(['success' => false]);

        $userId = Auth::id();
        $systemUserId = Helper::getSystemUserId();
        
        if ($id === 'npc') {
            $conversation = $this->conversationModel->findOrCreate($userId, $systemUserId);
            $id = $conversation['id'];
        } else {
            $conversation = $this->conversationModel->find($id);
        }

        if (!$conversation || ($conversation['buyer_id'] != $userId && $conversation['seller_id'] != $userId)) {
            return $this->json(['success' => false]);
        }

        $messages = $this->messageModel->getMessagesForConversation($id);
        
        // Mark as read
        $isBuyer = ($userId == $conversation['buyer_id']);
        $this->conversationModel->resetUnreadCount($id, $isBuyer);
        $this->messageModel->markAsRead($id, $userId);

        $otherId = ($userId == $conversation['buyer_id']) ? $conversation['seller_id'] : $conversation['buyer_id'];
        $otherUser = $this->userModel->find($otherId);
        $isOnline = false;
        if ($otherUser && $otherUser['last_active_at']) {
            $isOnline = (time() - strtotime($otherUser['last_active_at'])) < 300;
        }
        if ((int)$otherId === $systemUserId) $isOnline = true;

        return $this->json([
            'success' => true,
            'messages' => $messages,
            'current_user_id' => $userId,
            'is_online' => $isOnline,
            'last_active_at' => $otherUser ? $otherUser['last_active_at'] : null
        ]);
    }

    public function getChatList() {
        if (!Auth::check()) {
            return $this->json(['success' => false]);
        }

        $userId = Auth::id();
        $conversations = $this->conversationModel->getConversationsForUser($userId);

        return $this->json([
            'success' => true,
            'conversations' => $conversations
        ]);
    }

    public function checkNotifications() {
        if (!Auth::check()) {
            return $this->json(['success' => false]);
        }
        
        $userId = Auth::id();
        $unreadCount = $this->conversationModel->getTotalUnread($userId);
        $systemUserId = Helper::getSystemUserId();
        
        // Kiểm tra xem có tin nhắn chưa đọc từ NPC (User ID 1) không
        $db = Database::getInstance();
        $npcMessage = $db->fetchOne(
            "SELECT m.id 
             FROM messages m 
             JOIN conversations c ON m.conversation_id = c.id 
             WHERE (c.buyer_id = ? OR c.seller_id = ?) 
             AND m.sender_id = ? 
             AND m.is_read = 0 
             ORDER BY m.created_at DESC LIMIT 1",
            [$userId, $userId, $systemUserId]
        );

        return $this->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'has_new_npc_message' => !!$npcMessage,
            'last_npc_message_id' => $npcMessage['id'] ?? null
        ]);
    }
}
