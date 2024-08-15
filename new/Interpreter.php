<?php

class Interpreter {
    private $classes = [];

    public function interpret($ast) {
        if ($ast['type'] === 'Class') {
            $this->defineClass($ast);
        }
    }

    private function defineClass($classNode) {
        $className = $classNode['name'];
        $methods = [];

        foreach ($classNode['methods'] as $methodNode) {
            $methodName = $methodNode['name'];
            $methods[$methodName] = function() use ($methodName) {
                echo "Method $methodName called\n";
            };
        }

        $this->classes[$className] = $methods;
    }

    public function instantiate($className) {
        if (!isset($this->classes[$className])) {
            throw new Exception("Class $className not defined");
        }

        return new ObjectInstance($this->classes[$className]);
    }
}

class ObjectInstance {
    private $methods;

    public function __construct($methods) {
        $this->methods = $methods;
    }

    public function call($methodName) {
        if (!isset($this->methods[$methodName])) {
            throw new Exception("Method $methodName not found");
        }

        $method = $this->methods[$methodName];
        $method();
    }
}
