<?php

declare(strict_types=1);

namespace Core;

use RuntimeException;

/**
 * Front-controller router.
 *
 * Routes are registered with get()/post() and stored as:
 *   $routes[$method][$pattern] = [ControllerClass, action, [middlewares]]
 *
 * Patterns use :param syntax which compiles to named regex captures.
 * Example: '/admin/products/:id/edit' → '/admin/products/(?P<id>[^/]+)/edit'
 *
 * Middleware identifiers map to class names resolved in resolveMiddleware().
 */
class Router
{
    private array $routes = [];

    public function get(string $pattern, array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $pattern, $handler, $middleware);
    }

    public function post(string $pattern, array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $pattern, $handler, $middleware);
    }

    private function addRoute(string $method, string $pattern, array $handler, array $middleware): void
    {
        $this->routes[$method][$pattern] = [$handler[0], $handler[1], $middleware];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri    = $request->uri();

        $methodRoutes = $this->routes[$method] ?? [];

        foreach ($methodRoutes as $pattern => $config) {
            $regex = $this->compilePattern($pattern);

            if (!preg_match($regex, $uri, $matches)) {
                continue;
            }

            // Extract named captures only
            $params = array_filter(
                $matches,
                fn($k) => is_string($k),
                ARRAY_FILTER_USE_KEY
            );
            $request->setParams($params);

            [$controllerClass, $action, $middlewares] = $config;

            // Run middleware pipeline
            foreach ($middlewares as $id) {
                $mw = $this->resolveMiddleware($id);
                $mw->handle($request);
            }

            // Instantiate and call controller
            if (!class_exists($controllerClass)) {
                throw new RuntimeException("Controller not found: {$controllerClass}");
            }

            $controller = new $controllerClass();

            if (!method_exists($controller, $action)) {
                throw new RuntimeException("Action not found: {$controllerClass}::{$action}");
            }

            $controller->$action($request);
            return;
        }

        // No route matched
        Response::abort(404, 'Page not found.');
    }

    private function compilePattern(string $pattern): string
    {
        // Escape forward slashes in non-param segments and convert :param to named groups
        $regex = preg_replace('/:([a-zA-Z_][a-zA-Z0-9_]*)/', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $regex . '$#u';
    }

    private function resolveMiddleware(string $id): object
    {
        $map = [
            'auth'   => \App\Middleware\AuthMiddleware::class,
            'admin'  => \App\Middleware\AdminMiddleware::class,
            'seller' => \App\Middleware\SellerMiddleware::class,
            'store'  => \App\Middleware\StoreMiddleware::class,
            'csrf'   => \App\Middleware\CsrfMiddleware::class,
            'guest'  => \App\Middleware\GuestMiddleware::class,
        ];

        if (!isset($map[$id])) {
            throw new RuntimeException("Unknown middleware: {$id}");
        }

        return new $map[$id]();
    }
}
