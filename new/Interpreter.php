<?php

class Interpreter {
    private $globalScope = [];

    public function interpret($statements) {
        foreach ($statements as $statement) {
            $this->evaluate($statement);
        }
    }

    private function evaluate($node, $object = null) {

        switch ($node['type']) {
            case 'Class':
                $this->evaluateClass($node);
                break;
            case 'FunctionCall':
                $this->evaluateFunctionCall($node, $object);
                break;
            case 'Variable':
                $this->evaluateVariable($node);
                break;
            case 'NewObject':
                return $this->evaluateNewObject($node);
            case 'MethodCall':
                $this->evaluateMethodCall($node);
                break;
            default:
                throw new Exception("Unknown node type: " . $node['type']);
        }
    }

    private function evaluateClass($node) {
        // Store class definition in global scope
        $this->globalScope[$node['name']] = $node;
    }

    private function evaluateFunctionCall($node, $object = null) {

        if ($node['name'] === 'echo') {
            if (isset($node['arguments'])) {
                foreach ($node['arguments'] as $arg) {
                    if ($arg['type'] === 'PropertyCall') {
                        $propertyFound = false;
                        if (isset($this->globalScope[$object['__class']]['variables'])) {
                            foreach ($this->globalScope[$object['__class']]['variables'] as $variable) {
                                if ($variable['name'] === $arg['property']) {
                                    $propertyFound = true;
                                    echo $variable['value'];
                                }
                            }
                        }
                        if (!$propertyFound) {
                            throw new Exception("Property " . $arg['property'] . " is not defined in class " . $object['__class']);
                        }
                    }
                    if ($arg['type'] === 'Variable') {
                        if (!isset($this->globalScope[$arg['name']])) {
                            throw new Exception("Variable " . $arg['name'] . " is not defined");
                        }
                        if (is_string($this->globalScope[$arg['name']])) {
                            echo $this->globalScope[$arg['name']] . "\n";
                        } else {
                            var_dump($this->globalScope[$arg['name']]);
                        }
                    }
                    if ($arg['type'] === 'String') {
                        echo $arg['value'] . "\n";
                    }
                    if ($arg['type'] === 'Number') {
                        echo $arg['value'] . "\n";
                    }
                }
            }
        } else {
            throw new Exception("Unknown function: " . $node['name']);
        }
    }

    private function evaluateVariable($node) {
        if (isset($node['value']['type']) && $node['value']['type'] === 'NewObject') {
            $this->globalScope[$node['name']] = $this->evaluateNewObject($node['value']);
        } else {
            $this->globalScope[$node['name']] = $node['value'];
        }
    }

    private function evaluateNewObject($node) {
        $className = $node['className'];

        if (!isset($this->globalScope[$className])) {
            throw new Exception("Class $className is not defined");
        }

        // Creating a simple object representation
        return ['__class' => $className, '__properties' => []];
    }

    private function evaluateMethodCall($node) {
        $variable = $node['variable'];

        if (!isset($this->globalScope[$variable])) {
            throw new Exception("Variable $variable is not defined");
        }

        $object = $this->globalScope[$variable];
        $className = $object['__class'];
        $methodName = $node['method'];

        if (!isset($this->globalScope[$className])) {
            throw new Exception("Class $className is not defined");
        }

        $classDef = $this->globalScope[$className];

        foreach ($classDef['methods'] as $method) {
            if ($method['name'] === $methodName) {
                // Execute the method body
                foreach ($method['body'] as $statement) {
                    $this->evaluate($statement, $object);
                }
                return;
            }
        }

        throw new Exception("Method $methodName is not defined in class $className");
    }
}
