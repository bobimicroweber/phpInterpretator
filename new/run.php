<?php
include 'vendor/autoload.php';
include 'Lexer.php';
include 'Parser.php';

$ownSyntax = '

class Dog { 
    
    $description = "Snoop Dogg";
    $name = "Dog";
    $color = "Brown";
    $location = "Home";
    
    private function bark() {
        
    }
    
    public function run() {
        
    }
    
    public function eat() {
        
    }
    
}



';

$lexer = new Lexer($ownSyntax);
$tokens = $lexer->tokenize();
//dd($tokens);

$parser = new Parser($tokens);
$ast = $parser->parse();
//dd($ast);

