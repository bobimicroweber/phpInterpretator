<?php

class Interpreter {
    private $globalScope = [];

    public function interpret($statements) {
        foreach ($statements as $statement) {
            $this->evaluate($statement);
        }
    }

    private function evaluate($node) {
        switch ($node['type']) {
            case 'Class':
                $this->evaluateClass($node);
                break;
            case 'FunctionCall':
                $this->evaluateFunctionCall($node);
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

    private function evaluateFunctionCall($node) {
        if ($node['name'] === 'echo') {
            foreach ($node['arguments'] as $arg) {
                if ($arg['type'] === 'Variable') {
                    echo $this->globalScope[$arg['name']] . "\n";
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
                // Currently, just printing a message; you'd handle the method body here.
                echo "Calling method $methodName on $variable\n";
                return;
            }
        }

        throw new Exception("Method $methodName is not defined in class $className");
    }
}
