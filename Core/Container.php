<?php

declare(strict_types=1);

namespace Core;

use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 * Dependency Injection Container
 *
 * A simple DI container for managing class dependencies and shared instances.
 * Supports automatic dependency resolution and singleton/shared instances.
 *
 * @package Core
 * @author  Prima Yoga
 */
class Container
{
    /**
     * @var array<string, mixed> Registered services and their definitions.
     */
    private array $services = [];

    /**
     * @var array<string, object> Shared instances.
     */
    private array $instances = [];

    /**
     * Register a service in the container.
     *
     * @param string $name The service name or interface.
     * @param mixed $definition The service definition (class name, closure, or instance).
     * @param bool $shared Whether to share the same instance across requests.
     * @return self Returns the container for method chaining.
     */
    public function set(string $name, mixed $definition, bool $shared = false): self
    {
        $this->services[$name] = [
            'definition' => $definition,
            'shared' => $shared,
        ];
        return $this;
    }

    /**
     * Get a service instance from the container.
     *
     * @param string $name The service name or interface.
     * @return mixed The service instance.
     * @throws \Exception If the service cannot be resolved.
     */
    public function get(string $name): mixed
    {
        // Return shared instance if available
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        if (!isset($this->services[$name])) {
            throw new \Exception("Service '{$name}' not registered in container.");
        }

        $definition = $this->services[$name]['definition'];
        $shared = $this->services[$name]['shared'];

        $instance = $this->resolve($definition);

        if ($shared) {
            $this->instances[$name] = $instance;
        }

        return $instance;
    }

    /**
     * Check if a service is registered.
     *
     * @param string $name The service name.
     * @return bool True if the service is registered, false otherwise.
     */
    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }

    /**
     * Resolve a service definition into an instance.
     *
     * @param mixed $definition The service definition.
     * @return mixed The resolved instance.
     * @throws \Exception If the definition cannot be resolved.
     */
    private function resolve(mixed $definition): mixed
    {
        if (is_callable($definition)) {
            return $definition($this);
        }

        if (is_object($definition)) {
            return $definition;
        }

        if (is_string($definition) && class_exists($definition)) {
            return $this->resolveClass($definition);
        }

        throw new \Exception("Cannot resolve service definition.");
    }

    /**
     * Resolve a class into an instance using reflection for dependency injection.
     *
     * @param string $className The class name to instantiate.
     * @return object The class instance.
     * @throws \Exception If the class cannot be instantiated.
     */
    private function resolveClass(string $className): object
    {
        try {
            $reflection = new ReflectionClass($className);
            $constructor = $reflection->getConstructor();

            if ($constructor === null) {
                return new $className();
            }

            $parameters = $constructor->getParameters();
            $dependencies = [];

            foreach ($parameters as $parameter) {
                $dependencies[] = $this->resolveDependency($parameter);
            }

            return $reflection->newInstanceArgs($dependencies);
        } catch (ReflectionException $e) {
            throw new \Exception("Failed to instantiate '{$className}': " . $e->getMessage());
        }
    }

    /**
     * Resolve a single dependency for a class parameter.
     *
     * @param ReflectionParameter $parameter The parameter to resolve.
     * @return mixed The resolved dependency.
     * @throws \Exception If the dependency cannot be resolved.
     */
    private function resolveDependency(ReflectionParameter $parameter): mixed
    {
        $type = $parameter->getType();

        if ($type === null) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }
            throw new \Exception("Cannot resolve parameter '{$parameter->getName()}' without type hint or default value.");
        }

        $typeName = $type->getName();

        // Check if it's a built-in type
        if (in_array($typeName, ['int', 'string', 'bool', 'float', 'array', 'callable'])) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }
            throw new \Exception("Cannot resolve built-in type '{$typeName}' for parameter '{$parameter->getName()}'.");
        }

        // Try to get from container
        if ($this->has($typeName)) {
            return $this->get($typeName);
        }

        // Check if it's an interface and we have a concrete implementation
        foreach ($this->services as $name => $service) {
            if (is_string($service['definition']) && is_subclass_of($service['definition'], $typeName)) {
                return $this->get($name);
            }
        }

        throw new \Exception("Cannot resolve dependency '{$typeName}' for parameter '{$parameter->getName()}'.");
    }
}
