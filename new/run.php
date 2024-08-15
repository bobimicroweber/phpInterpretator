<?php
include 'vendor/autoload.php';
include 'Lexer.php';
include 'Parser.php';

$ownSyntax = '

public class Dog { 
    
    var $name;
    
    function bark() { 
      echo "Woof!";
    }
    
    function eat() { 
        echo "Nom nom nom";
    }
    
    function getName() { 
        return $this->name;
    }
    
}

';

$lexer = new Lexer($ownSyntax);
$tokens = $lexer->tokenize();

$parser = new Parser($tokens);
$ast = $parser->parse();

dd($ast);

