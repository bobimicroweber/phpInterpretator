<?php

class Parser {
    private $tokens;
    private $position = 0;

    public function __construct($tokens) {
        $this->tokens = $tokens;
    }

    public function parse() {
        $statements = [];
        while ($this->currentToken()['type'] !== 'EOF') {
            $statements[] = $this->parseStatement();
        }
        return $statements;
    }

    private function parseStatement() {
        if ($this->currentToken()['type'] === 'CLASS') {
            return $this->parseClass();
        } elseif ($this->currentToken()['type'] === 'DOLLAR') {
            return $this->parseAssignment();
        }
        throw new Exception("Unknown statement");
    }

    private function parseAssignment() {

        $this->consume('DOLLAR');
        $variableName = $this->consume('IDENT')['value'];
        $this->consume('ASSIGN');
        $newObject = $this->parseNewObject();
        $this->consume('SEMI');

        return ['type' => 'Assignment', 'variable' => $variableName, 'value' => $newObject];
    }

    private function parseNewObject() {
        $this->consume('NEW');
        $className = $this->consume('IDENT')['value'];
        $this->consume('LPAREN');
        $this->consume('RPAREN');
        return ['type' => 'NewObject', 'className' => $className];
    }

    private function parseMethodCall($variable) {
        $this->consume('ARROW');
        $methodName = $this->consume('IDENT')['value'];
        $this->consume('LPAREN');
        $this->consume('RPAREN');
        $this->consume('SEMI');
        return ['type' => 'MethodCall', 'variable' => $variable, 'method' => $methodName];
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
        $this->consume('DOLLAR');
        $variableName = $this->consume('IDENT')['value'];
        $this->consume('ASSIGN');

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
