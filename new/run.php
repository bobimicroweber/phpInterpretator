<?php
include 'vendor/autoload.php';
include 'Lexer.php';
include 'Parser.php';

$ownSyntax = '

class Dog { 
    function bark() { 
    
    }
}

';

$lexer = new Lexer($ownSyntax);
$tokens = $lexer->tokenize();
dd($tokens);



$parser = new Parser($tokens);
$ast = $parser->parse();

dd($ast);

