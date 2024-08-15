<?php

class Parser {
    private $tokens;
    private $position = 0;

    public function __construct($tokens) {
        $this->tokens = $tokens;
    }

    public function parse() {
        $classNode = $this->parseClass();
        return $classNode;
    }

    private function parseClass() {
        $this->consume('CLASS');
        $className = $this->consume('IDENT')['value'];
        $this->consume('LBRACE');
        $methods = [];

        while ($this->currentToken()['type'] !== 'RBRACE') {
            $methods[] = $this->parseMethod();
        }

        $this->consume('RBRACE');
        return ['type' => 'Class', 'name' => $className, 'methods' => $methods];
    }

    private function parseMethod() {
        $this->consume('FUNCTION');
        $methodName = $this->consume('IDENT')['value'];
        $this->consume('LPAREN');
        $this->consume('RPAREN');
        $this->consume('LBRACE');
        $this->consume('RBRACE');
        return ['type' => 'Method', 'name' => $methodName];
    }

    private function consume($expectedType) {
        $token = $this->tokens[$this->position];
        if ($token['type'] !== $expectedType) {
            throw new Exception("Expected $expectedType, got {$token['type']}");
        }
        $this->position++;
        return $token;
    }

    private function currentToken() {
        return $this->tokens[$this->position];
    }
}
