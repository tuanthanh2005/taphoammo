<?php
// app/Core/Controller.php

class Controller {
    
    protected function view($view, $data = []) {
        extract($data);
        
        $viewFile = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View not found: {$view}");
        }
    }
    
    protected function redirect($url) {
        header("Location: " . $url);
        exit;
    }
    
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
