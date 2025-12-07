<?php

namespace App\Core;

use App\Utils\ApiError;

class Router
{
    private $routes = [];

    /**
     * Register a GET route
     * @param string $path
     * @param callable|array $handler
     * @param array $middlewares
     */
    public function get($path, $handler, $middlewares = [])
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    /**
     * Register a POST route
     * @param string $path
     * @param callable|array $handler
     * @param array $middlewares
     */
    public function post($path, $handler, $middlewares = [])
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    /**
     * Register a PATCH route
     * @param string $path
     * @param callable|array $handler
     * @param array $middlewares
     */
    public function patch($path, $handler, $middlewares = [])
    {
        $this->addRoute('PATCH', $path, $handler, $middlewares);
    }

    /**
     * Register a DELETE route
     * @param string $path
     * @param callable|array $handler
     * @param array $middlewares
     */
    public function delete($path, $handler, $middlewares = [])
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    /**
     * Internal method to add route to the list
     */
    private function addRoute($method, $path, $handler, $middlewares)
    {
        // Convert route params (e.g., /users/:id) to Regex (e.g., /users/(?P<id>[^/]+))
        $pattern = preg_replace('/\:([a-zA-Z0-9_]+)/', '(?P<$1>[^/]+)', $path);
        $pattern = "#^" . $pattern . "$#";

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    /**
     * Dispatch the request to the appropriate route handler
     * @param Request $request
     * @throws ApiError
     */
    public function dispatch(Request $request)
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // --- Handle Subdirectory Execution ---
        // Calculate the directory of the running script (e.g., /my-project/public)
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        
        // Normalize slashes for Windows compatibility
        $scriptDir = str_replace('\\', '/', $scriptDir);

        // If the URI starts with the script directory (and it's not root), strip it
        if ($scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        }

        // Ensure URI starts with /
        if ($uri === '' || $uri === false) {
            $uri = '/';
        }

        // Remove trailing slash if not root
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }

        // Iterate through registered routes
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                
                // Extract named parameters from regex match
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                $request->setParams($params);

                // Execute Middlewares
                foreach ($route['middlewares'] as $middleware) {
                    // Middleware receives the request object
                    call_user_func($middleware, $request);
                }

                // Execute Controller Handler
                call_user_func($route['handler'], $request);
                return;
            }
        }

        throw new ApiError(404, 'Not found');
    }
}