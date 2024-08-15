<?php
include 'vendor/autoload.php';
include 'Lexer.php';
include 'Parser.php';
include 'Interpreter.php';

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

$dog = new Dog();


';

$lexer = new Lexer($ownSyntax);
$tokens = $lexer->tokenize();
//dd($tokens);

$parser = new Parser($tokens);
$statements = $parser->parse();
//dd($statements);

$interpreter = new Interpreter();
$interpreter->interpret($statements);

dd($interpreter);
