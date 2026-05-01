<?php
// app/Core/Router.php

class Router {
    private $routes = [];
    private $middlewares = [];
    
    public function get($path, $callback, $middlewares = []) {
        $this->addRoute('GET', $path, $callback, $middlewares);
    }
    
    public function post($path, $callback, $middlewares = []) {
        $this->addRoute('POST', $path, $callback, $middlewares);
    }
    
    private function addRoute($method, $path, $callback, $middlewares = []) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback,
            'middlewares' => $middlewares
        ];
    }
    
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];
        
        // Remove query string
        if (false !== $pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        
        // Decode URL
        $requestUri = rawurldecode($requestUri);
        
        // Remove trailing slash except for root
        if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
            $requestUri = rtrim($requestUri, '/');
        }
        
        // Try to match routes
        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }
            
            $pattern = $this->convertToRegex($route['path']);
            
            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);
                
                // Run middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $middlewareInstance = new $middleware();
                    if (!$middlewareInstance->handle()) {
                        return;
                    }
                }
                
                // Execute callback
                if (is_array($route['callback'])) {
                    $controller = new $route['callback'][0]();
                    $method = $route['callback'][1];
                    call_user_func_array([$controller, $method], $matches);
                } else {
                    call_user_func_array($route['callback'], $matches);
                }
                
                return;
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        echo '<!DOCTYPE html>
<html>
<head>
    <title>404 Not Found</title>
    <style>
        body { font-family: Arial; padding: 50px; text-align: center; background: #f5f5f5; }
        h1 { color: #dc3545; }
        p { color: #666; }
        a { color: #28a745; text-decoration: none; }
    </style>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The page you are looking for does not exist.</p>
    <p><a href="/">← Back to Home</a></p>
</body>
</html>';
    }
    
    private function convertToRegex($path) {
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $path);
        // Convert {param} to regex capture group (allow more characters including spaces and unicode)
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $pattern);
        return '#^' . $pattern . '$#u'; // Added 'u' flag for unicode support
    }
}
