<?php

namespace PhpZero\Core\Router;

/**
 * Representa uma definição de rota
 */
class RouteDefinition
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly string $pattern,
        public readonly array $paramNames,
        public readonly string $controller,
        public readonly string $action
    ) {
        if(!in_array(strtoupper($method), ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'])) {
            throw new \InvalidArgumentException("Método HTTP inválido: $method");
        }
    }

    /**
     * Verifica se o método HTTP corresponde
     */
    public function matchesMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    /**
     * Verifica se o path corresponde e retorna parâmetros
     */
    public function matchesPath(string $path): ?array
    {
        if (preg_match($this->pattern, $path, $matches)) {
            // Remove o primeiro match (é a string completa)
            array_shift($matches);

            // Combina nomes dos parâmetros com valores
            $params = [];
            foreach ($this->paramNames as $index => $paramName) {
                $params[$paramName] = $matches[$index] ?? null;
            }

            return $params;
        }

        return null;
    }

    public function __serialize(): array
    {
        return [
            'method' => $this->method,
            'path' => $this->path,
            'pattern' => $this->pattern,
            'paramNames' => $this->paramNames,
            'controller' => $this->controller,
            'action' => $this->action,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->method = $data['method'];
        $this->path = $data['path'];
        $this->pattern = $data['pattern'];
        $this->paramNames = $data['paramNames'] ?? [];
        $this->controller = $data['controller'];
        $this->action = $data['action'];
    }
}