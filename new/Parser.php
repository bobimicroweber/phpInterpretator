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

        $current = $this->currentToken();

        if ($current['type'] === 'CLASS') {
            return $this->parseClass();
        } elseif ($current['type'] === 'DOLLAR') {
            return $this->parseVariable();
        } elseif ($current['type'] === 'IDENT') {
            return $this->parseIdentifier();
        }

        throw new Exception("Unknown statement: " . $current['type']. ' ' . $current['value']);
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

    private function parseIdentifier()
    {
        // Parse function echo();
        if ($this->currentToken()['type'] == 'IDENT' && $this->tokens[$this->position + 1]['type'] == 'LPAREN') {

            $functionName = $this->currentToken()['value'];
            $this->consume('IDENT');
            $this->consume('LPAREN');

            $functionArguments = $this->parseFunctionArguments();

            $this->consume('RPAREN');
            $this->consume('SEMI');


            return [
                'type' => 'FunctionCall',
                'name' => $functionName,
                'arguments' => $functionArguments
            ];
        }

    }

    private function parseFunctionArguments() {
        $arguments = [];

        /**
         * $numberOne, $numberTwo
         */
        while(true) {
            if ($this->currentToken()['type'] == 'DOLLAR') {

                $this->consume('DOLLAR');
                $variableName = $this->consume('IDENT')['value'];

                $arguments[] = [
                    'type' => 'Variable',
                    'name' => $variableName
                ];

                if ($this->currentToken()['type'] == 'COMMA') {
                    $this->consume('COMMA');
                } else {
                    break;
                }

            } else if ($this->currentToken()['type'] == 'NUMBER') {

                $arguments[] = [
                    'type' => 'Number',
                    'value' => $this->consume('NUMBER')['value']
                ];

                if ($this->currentToken()['type'] == 'COMMA') {
                    $this->consume('COMMA');
                } else {
                    break;
                }

            } else if ($this->currentToken()['type'] == 'DQUOTE') {

                $this->consume('DQUOTE');
                $arguments[] = [
                    'type' => 'String',
                    'value' => $this->consume('IDENT')['value']
                ];
                $this->consume('DQUOTE');

                if ($this->currentToken()['type'] == 'COMMA') {
                    $this->consume('COMMA');
                } else {
                    break;
                }

            } else {
                break;
            }
        }

        return $arguments;
    }

    private function parseVariable()
    {
        $this->consume('DOLLAR'); // $
        $variableName = $this->consume('IDENT')['value'];//text
        $this->consume('ASSIGN'); // =

        /**
         * Assign new object to variable
         */
        if ($this->currentToken()['type'] == 'NEW') {
            $this->consume('NEW');
            $className = $this->consume('IDENT')['value'];
            $this->consume('LPAREN');
            $this->consume('RPAREN');
            $this->consume('SEMI');
            return ['type' => 'Variable', 'name' => $variableName, 'value' => ['type' => 'NewObject', 'className' => $className]];
        }

        /**
         * Assign string to variable
         * $name = "John";
         */
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

        /**
         * Assign number to variable
         * $number = 10;
         */
        if ($this->currentToken()['type'] == 'NUMBER') {
            $variableValue = $this->currentToken()['value'];
            $this->consume('NUMBER');
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
