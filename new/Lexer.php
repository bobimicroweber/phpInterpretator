<?php

class Lexer {
    private $input;
    private $position = 0;
    private $tokens = [];

    const TOKEN_LBRACE = 'LBRACE';
    const TOKEN_RBRACE = 'RBRACE';
    const TOKEN_LPAREN = 'LPAREN';
    const TOKEN_RPAREN = 'RPAREN';
    const TOKEN_SEMI = 'SEMI';
    const TOKEN_CLASS = 'CLASS';
    const TOKEN_FUNCTION = 'FUNCTION';
    const TOKEN_NEW = 'NEW';
    const TOKEN_IDENT = 'IDENT';
    const TOKEN_EOF = 'EOF';
    const TOKEN_DOLLAR = 'DOLLAR';

    const TOKEN_DASH = 'DASH';

    const TOKEN_GT = 'GT';
    const TOKEN_LT = 'LT';

    const TOKEN_DQUOTE = 'DQUOTE';
    const TOKEN_SQUOTE = 'SQUOTE';

    const TOKEN_EXMARK = 'EXMARK';

    const TOKEN_ASSIGN = 'ASSIGN';

    const TOKEN_STRING = 'STRING';

    const TOKEN_SLASH = 'SLASH';

    const TOKEN_PLUS = 'PLUS';

    const TOKEN_COMMA = 'COMMA';

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
                $tokens[] = ['type' => self::TOKEN_LBRACE, 'value' => '{'];
            }
            elseif ($char === '}') {
                $tokens[] = ['type' => self::TOKEN_RBRACE, 'value' => '}'];
            }
            elseif ($char === '(') {
                $tokens[] = ['type' => self::TOKEN_LPAREN, 'value' => '('];
            }
            elseif ($char === ')') {
                $tokens[] = ['type' => self::TOKEN_RPAREN, 'value' => ')'];
            }
            elseif ($char === ';') {
                $tokens[] = ['type' => self::TOKEN_SEMI, 'value' => ';'];
            }
            elseif ($char === '$') {
                $tokens[] = ['type' => self::TOKEN_DOLLAR, 'value' => '$'];
            } elseif ($char === '-') {
                $tokens[] = ['type' => self::TOKEN_DASH, 'value' => '-'];
            } elseif ($char === '>') {
                $tokens[] = ['type' => self::TOKEN_GT, 'value' => '>'];
            } elseif ($char === '<') {
                $tokens[] = ['type' => self::TOKEN_LT, 'value' => '<'];
            } elseif ($char === '"') {
                $tokens[] = ['type' => self::TOKEN_DQUOTE, 'value' => '"'];
            } elseif ($char === "'") {
                $tokens[] = ['type' => self::TOKEN_SQUOTE, 'value' => "'"];
            } elseif ($char === '!') {
                $tokens[] = ['type' => self::TOKEN_EXMARK, 'value' => '!'];
            } elseif ($char === '=') {
                $tokens[] = ['type' => self::TOKEN_ASSIGN, 'value' => '='];
            } elseif ($char === '/') {
                $tokens[] = ['type' => self::TOKEN_SLASH, 'value' => '/'];
            }
            elseif ($char === '+') {
                $tokens[] = ['type' => self::TOKEN_PLUS, 'value' => '+'];
            } elseif ($char === ',') {
                $tokens[] = ['type' => self::TOKEN_COMMA, 'value' => ','];
            }
            elseif (ctype_alpha($char) || $char === '_') {
                $value = $this->consumeIdentifier();
                $type = $this->determineType($value);
                $tokens[] = ['type' => $type, 'value' => $value];
                continue;
            }
            else if (ctype_digit($char)) {
                $value = $this->consumeNumber();
                $tokens[] = ['type' => 'NUMBER', 'value' => $value];
                continue;
            }
            else {
                throw new Exception("Unknown character: $char");
            }

            $this->position++;
        }

        $tokens[] = ['type' => 'EOF', 'value' => ''];
        return $tokens;
    }

    private function consumeIdentifier() {
        $start = $this->position;
        while ($this->position < strlen($this->input) && (ctype_alnum($this->input[$this->position]) || $this->input[$this->position] === '_')) {
            $this->position++;
        }
        return substr($this->input, $start, $this->position - $start);
    }

    public function consumeNumber() {
        $start = $this->position;
        while ($this->position < strlen($this->input) && ctype_digit($this->input[$this->position])) {
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
