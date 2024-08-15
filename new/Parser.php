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

        $visibility = 'public';
        $current = $this->tokens[$this->position];
        if (isset($current['type']) && $current['type'] == 'IDENT') {
            $visibility = $current['value'];
            $this->position++;
        }

        $this->consume('CLASS');

        $className = $this->consume('IDENT')['value'];

        $this->consume('LBRACE');

        $methods = [];
        $variables = [];

        while ($this->currentToken()['type'] !== 'RBRACE') {
            $current = $this->currentToken();
            if (
                ($current['type'] == 'FUNCTION') ||
                ($current['type'] == 'IDENT' && $this->tokens[$this->position + 1]['type'] == 'FUNCTION')
            ) {
                $methods[] = $this->parseMethod();
            } else {
                $variables[] = $this->parseVariable();
            }
        }

        $this->consume('RBRACE');

        return [
            'type' => 'Class',
            'name' => $className,
            'methods' => $methods,
            'variables' => $variables,
            'visibility' => $visibility
        ];
    }

    private function parseVariable()
    {
        $this->consume('DOLAR');
        $variableName = $this->consume('IDENT')['value'];
        $this->consume('EQUAL');

        if ($this->currentToken()['type'] == 'DQUOTE') {
            // Variable Strings
            $this->consume('DQUOTE');

            $variableValue = '';
            while ($this->currentToken()['type'] !== 'DQUOTE') {
                $variableValue .= $this->consume('IDENT')['value'];
            }
            $this->consume('DQUOTE');
            $this->consume('SEMI');

            return ['type' => 'Variable', 'name' => $variableName, 'value' => $variableValue];
        }


    }
    private function parseMethod() {

        $methodVisibility = 'public';

        if ($this->currentToken()['type'] == 'IDENT') {
            $methodVisibility = $this->currentToken()['value'];
            $this->position++;
        }

        $this->consume('FUNCTION');
        $methodName = $this->consume('IDENT')['value'];

        $this->consume('LPAREN');
        $this->consume('RPAREN');

        $this->consume('LBRACE');
        $this->consume('RBRACE');

        return [
            'type' => 'Method',
            'name' => $methodName,
            'visibility' => $methodVisibility
        ];
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
