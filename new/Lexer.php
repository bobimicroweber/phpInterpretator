<?php

class Lexer {
    private $input;
    private $position = 0;
    private $tokens = [];

    public function __construct($input) {
        $this->input = $input;
    }

    public function tokenize() {
        $tokens = [];
        $length = strlen($this->input);

        while ($this->position < $length) {
            $char = $this->input[$this->position];

            if (ctype_space($char)) {
                $this->position++;
                continue;
            }

            if ($char === '{') {
                $tokens[] = ['type' => 'LBRACE', 'value' => '{'];
            } elseif ($char === '}') {
                $tokens[] = ['type' => 'RBRACE', 'value' => '}'];
            } elseif ($char === '(') {
                $tokens[] = ['type' => 'LPAREN', 'value' => '('];
            } elseif ($char === ')') {
                $tokens[] = ['type' => 'RPAREN', 'value' => ')'];
            } elseif ($char === ';') {
                $tokens[] = ['type' => 'SEMI', 'value' => ';'];
            } elseif (preg_match('/[a-zA-Z_]\w*/', $char, $matches)) {
                $value = $this->consumeIdentifier();
                $type = $this->determineType($value);
                $tokens[] = ['type' => $type, 'value' => $value];
            } else {
                throw new Exception("Unknown character: $char");
            }

            $this->position++;
        }

        $tokens[] = ['type' => 'EOF', 'value' => ''];
        return $tokens;
    }

    private function consumeIdentifier() {
        $start = $this->position;
        while ($this->position < strlen($this->input) && ctype_alnum($this->input[$this->position])) {
            $this->position++;
        }
        return substr($this->input, $start, $this->position - $start);
    }

    private function determineType($value) {
        switch ($value) {
            case 'class':
                return 'CLASS';
            case 'function':
                return 'FUNCTION';
            case 'new':
                return 'NEW';
            default:
                return 'IDENT';
        }
    }
}
