<?php
// app/Controllers/AffiliateController.php

class AffiliateController extends Controller {
    
    public function dashboard() {
        $db = Database::getInstance();
        $userId = Auth::id();
        $user = Auth::user();
        
        // Get statistics
        $stats = [
            'total_clicks' => $db->fetchOne("SELECT COUNT(*) as total FROM affiliate_clicks WHERE affiliate_id = ?", [$userId])['total'],
            'total_referrals' => $db->fetchOne("SELECT COUNT(*) as total FROM users WHERE referred_by = ?", [$userId])['total'],
            'total_commissions' => $db->fetchOne("SELECT SUM(amount) as total FROM affiliate_commissions WHERE affiliate_id = ?", [$userId])['total'] ?? 0,
            'pending_commissions' => $db->fetchOne("SELECT SUM(amount) as total FROM affiliate_commissions WHERE affiliate_id = ? AND status = 'pending'", [$userId])['total'] ?? 0
        ];
        
        // Get recent commissions
        $recentCommissions = $db->fetchAll(
            "SELECT ac.*, u.name as referred_user_name, o.order_code
             FROM affiliate_commissions ac
             LEFT JOIN users u ON ac.referred_user_id = u.id
             LEFT JOIN orders o ON ac.order_id = o.id
             WHERE ac.affiliate_id = ?
             ORDER BY ac.created_at DESC
             LIMIT 20",
            [$userId]
        );
        
        // Generate affiliate link
        $affiliateLink = url('?ref=' . $user['referral_code']);
        
        $this->view('affiliate/dashboard', [
            'stats' => $stats,
            'recentCommissions' => $recentCommissions,
            'affiliateLink' => $affiliateLink,
            'referralCode' => $user['referral_code']
        ]);
    }
    
    public function commissions() {
        $db = Database::getInstance();
        $userId = Auth::id();
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $commissions = $db->fetchAll(
            "SELECT ac.*, u.name as referred_user_name, o.order_code
             FROM affiliate_commissions ac
             LEFT JOIN users u ON ac.referred_user_id = u.id
             LEFT JOIN orders o ON ac.order_id = o.id
             WHERE ac.affiliate_id = ?
             ORDER BY ac.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            [$userId]
        );
        
        $this->view('affiliate/commissions', [
            'commissions' => $commissions,
            'currentPage' => $page
        ]);
    }
}
