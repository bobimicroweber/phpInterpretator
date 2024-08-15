<?php

class Interpreter {
    private $classes = [];
    private $variables = [];

    public function interpret($statements) {

        foreach ($statements as $statement) {
            switch ($statement['type']) {
                case 'Class':
                    $this->defineClass($statement);
                    break;
                case 'Assignment':
                    $this->assignVariable($statement);
                    break;
                case 'MethodCall':
                    $this->callMethod($statement);
                    break;
            }
        }
    }

    private function defineClass($classNode) {
        $className = $classNode['name'];
        $methods = [];

        foreach ($classNode['methods'] as $methodNode) {
            $methodName = $methodNode['name'];
            $methods[$methodName] = function($instance) use ($methodName) {
                echo "Method $methodName called on " . $instance->getClassName() . "\n";
            };
        }

        $this->classes[$className] = [
            'name' => $className,
            'methods' => $methods
        ];
    }

    private function assignVariable($assignmentNode) {
        $variableName = $assignmentNode['variable'];
        $newObjectNode = $assignmentNode['value'];
        $this->variables[$variableName] = $this->instantiate($newObjectNode['className']);
    }

    private function instantiate($className) {
        if (!isset($this->classes[$className])) {
            throw new Exception("Class $className not defined");
        }

        return new ObjectInstance($className, $this->classes[$className]['methods']);
    }

    private function callMethod($methodCallNode) {
        $variableName = $methodCallNode['variable'];
        $methodName = $methodCallNode['method'];

        if (!isset($this->variables[$variableName])) {
            throw new Exception("Variable \$$variableName not defined");
        }

        $object = $this->variables[$variableName];
        $object->call($methodName);
    }
}

class ObjectInstance {
    private $className;
    private $methods;

    public function __construct($className, $methods) {
        $this->className = $className;
        $this->methods = $methods;
    }

    public function getClassName() {
        return $this->className;
    }

    public function call($methodName) {
        if (!isset($this->methods[$methodName])) {
            throw new Exception("Method $methodName not found in class " . $this->className);
        }

        $method = $this->methods[$methodName];
        $method($this);
    }
}
