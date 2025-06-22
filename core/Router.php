<?php

class Router {
    private $routes = [];
    private $basePath = '';

    public function __construct($basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }

    public function get($route, $callback) {
        $this->addRoute('GET', $route, $callback);
    }

    public function post($route, $callback) {
        $this->addRoute('POST', $route, $callback);
    }

    public function put($route, $callback) {
        $this->addRoute('PUT', $route, $callback);
    }

    public function delete($route, $callback) {
        $this->addRoute('DELETE', $route, $callback);
    }

    private function addRoute($method, $route, $callback) {
        $route = $this->basePath . '/' . trim($route, '/');
        $route = $route ?: '/';
        $this->routes[$method][$route] = $callback;
    }

    public function dispatch() {
        echo '<pre>';
        print_r ($_SERVER);
        echo '<pre>';
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'])['path'];
        
        // Remove query parameters
        $uri = strtok($uri, '?');
        
        // Check for exact match first
        if (isset($this->routes[$method][$uri])) {
            return $this->executeCallback($this->routes[$method][$uri]);
        }

        // Check for dynamic routes
        foreach ($this->routes[$method] ?? [] as $route => $callback) {
            $pattern = $this->convertToRegex($route);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                return $this->executeCallback($callback, $matches);
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo "404 - Page Not Found";
    }

    private function convertToRegex($route) {
        // Convert {id} to capturing group
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
        return '#^' . $pattern . '$#';
    }

    private function executeCallback($callback, $params = []) {
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }

        if (is_string($callback)) {
            // Format: "ControllerName@methodName"
            list($controller, $method) = explode('@', $callback);
            $controllerClass = "App\\Controllers\\{$controller}";
            
            if (class_exists($controllerClass)) {
                $instance = new $controllerClass();
                if (method_exists($instance, $method)) {
                    return call_user_func_array([$instance, $method], $params);
                }
            }
        }

        throw new Exception("Invalid callback or controller method not found");
    }

    public function redirect($url, $statusCode = 302) {
        http_response_code($statusCode);
        header('Location: ' . $url);
        exit;
    }
}
