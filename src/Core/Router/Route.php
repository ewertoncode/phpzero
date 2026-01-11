<?php

namespace PhpZero\Core\Router;

use PhpZero\Core\Attributes\ArrayOf;

class Route
{
    #[ArrayOf(RouteDefinition::class)]
    private array $routes = [];

    /**
     * Adiciona uma rota ao router
     */
    public function addRoute(string $method, string $path, string $controller, string $action): void
    {
        // Extrai parâmetros da rota (ex: {id}, {userId})
        $paramNames = [];
        $pattern = preg_replace_callback(
            '/\{([a-zA-Z0-9_]+)\}/',
            function ($matches) use (&$paramNames) {
                $paramNames[] = $matches[1];
                return '([^/]+)';
            },
            $path
        );

        // Escapa barras e adiciona delimitadores
        $pattern = '#^' . str_replace('/', '\/', $pattern) . '$#';

        // Cria objeto RouteDefinition ao invés de array
        $this->routes[] = new RouteDefinition(
            method: strtoupper($method),
            path: $path,
            pattern: $pattern,
            paramNames: $paramNames,
            controller: $controller,
            action: $action
        );
    }

    /**
     * Encontra a rota correspondente à URL
     */
    public function match(string $method, string $path): ?array
    {
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            // Usa métodos da classe RouteDefinition
            if (!$route->matchesMethod($method)) {
                continue;
            }

            $params = $route->matchesPath($path);
            if ($params !== null) {
                return [
                    'controller' => $route->controller,
                    'action' => $route->action,
                    'params' => $params,
                ];
            }
        }

        return null;
    }
}