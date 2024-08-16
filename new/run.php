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

echo("basi_qkoto");

';
$simpleSyntax = '
$numberOne = 10;
$numberTwo = 20;

echo($numberTwo);

';

$lexer = new Lexer($ownSyntax);
$tokens = $lexer->tokenize();
//dd($tokens);

$parser = new Parser($tokens);
$statements = $parser->parse();

$interpreter = new Interpreter();
$interpreter->interpret($statements);

//dd($interpreter);
