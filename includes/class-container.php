<?php
if (!defined('ABSPATH')) exit;

final class AI_Community_Container {
    use AI_Community_Singleton;

    private array $services = [];
    private array $instances = [];
    private array $definitions = [];
    
    public function register(string $name, string $class, array $dependencies = []): void {
        if (isset($this->services[$name])) {
            throw new Exception("Service '{$name}' is already registered.");
        }
        if (!class_exists($class)) {
            throw new Exception("Class '{$class}' does not exist for service '{$name}'.");
        }
        $this->services[$name] = $class;
        $this->definitions[$name] = ['class' => $class, 'dependencies' => $dependencies];
    }

    public function get(string $name) {
        if (!isset($this->services[$name])) {
            throw new Exception("Service '{$name}' is not registered.");
        }
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        $definition = $this->definitions[$name];
        $dependencies = [];
        foreach ($definition['dependencies'] as $dep_name) {
            $dependencies[] = $this->get($dep_name);
        }

        $class = $definition['class'];
        $this->instances[$name] = new $class(...$dependencies);
        
        return $this->instances[$name];
    }
}
