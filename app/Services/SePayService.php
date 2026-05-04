<?php
// app/Services/SePayService.php

class SePayService {
    private $apiKey;
    private $merchantId;
    private $isSandbox;
    private $baseUrl = 'https://my.sepay.vn/api';

    public function __construct() {
        $db = Database::getInstance();
        $settings = $db->fetchAll("SELECT key_name, value FROM settings WHERE key_name LIKE 'sepay_%'");
        $config = [];
        foreach ($settings as $s) {
            $config[$s['key_name']] = $s['value'];
        }

        $this->apiKey = $config['sepay_api_key'] ?? '';
        $this->merchantId = $config['sepay_merchant_id'] ?? '';
        $this->isSandbox = ($config['sepay_mode'] ?? 'sandbox') === 'sandbox';
    }

    public function createCheckout($data) {
        $endpoint = "{$this->baseUrl}/checkout/create";
        
        $payload = [
            'merchant_id' => $this->merchantId,
            'order_id' => $data['order_id'],
            'amount' => $data['amount'],
            'description' => $data['description'],
            'return_url' => $data['return_url'] ?? '',
            'cancel_url' => $data['cancel_url'] ?? '',
            'webhook_url' => $data['webhook_url'] ?? '',
        ];

        return $this->callApi($endpoint, $payload);
    }

    private function callApi($endpoint, $payload) {
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => 'CURL Error: ' . $error];
        }

        return json_decode($response, true);
    }
}
