<?php

namespace PhpZero\Core\Container;

use ReflectionClass;
use ReflectionParameter;
use PhpZero\Core\Container\ContainerException;

class Container
{
    private array $bindings = [];
    private array $instances = [];

    public function bind(string $abstract, string|callable $concrete, bool $singleton = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton,
        ];
    }

    public function singleton(string $abstract, string|callable $concrete): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function make(string $abstract): mixed
    {
        // Se já existe instância singleton, retorna ela
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Se não tem binding, tenta criar diretamente
        if (!isset($this->bindings[$abstract])) {
            $instance = $this->resolve($abstract);
            
            // Se não é singleton, retorna direto
            return $instance;
        }

        $binding = $this->bindings[$abstract];
        $concrete = $binding['concrete'];

        // Se é callable, executa
        if (is_callable($concrete)) {
            $instance = $concrete($this);
        } else {
            // Se é string, resolve
            $instance = $this->resolve($concrete);
        }

        // Se é singleton, armazena
        if ($binding['singleton']) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * REFLECTION: Inspeciona a classe e resolve dependências
     */
    private function resolve(string $class): object
    {
        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new ContainerException("Classe $class não pode ser instanciada");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependencies[] = $this->resolveParameter($parameter);
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * REFLECTION: Descobre o tipo do parâmetro e resolve
     */
    private function resolveParameter(ReflectionParameter $parameter): mixed
    {
        $type = $parameter->getType();

        if ($type === null) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }
            throw new ContainerException(
                "Não foi possível resolver parâmetro {$parameter->getName()}"
            );
        }

        // Se não é built-in (é uma classe), resolve do container
        if (!$type->isBuiltin()) {
            $typeName = $type->getName();
            
            // Verifica se tem binding para essa interface/classe
            if (isset($this->bindings[$typeName])) {
                return $this->make($typeName);
            }
            
            // Se não tem binding, tenta criar diretamente (recursão!)
            return $this->make($typeName);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new ContainerException(
            "Não foi possível resolver parâmetro {$parameter->getName()}"
        );
    }
}