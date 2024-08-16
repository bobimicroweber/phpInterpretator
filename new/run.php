<?php
include 'vendor/autoload.php';
include 'Lexer.php';
include 'Parser.php';
include 'Interpreter.php';

$ownSyntax = '


class Cat { 
    
    $description = "Doga Cat";
    $name = "Cat";
    $color = "Brown";
    $location = "Home";
    
    private function meow() {
       echo($this->name);
       echo($this->color); 
    }
    
}

class Dog { 
    
    $description = "Snoop Dogg";
    $name = "Dog";
    $color = "Brown";
    $location = "Home";
    
    private function bark() {
       echo($this->name);
       echo($this->color); 
    }
    
}

$dog = new Dog();
$dog->bark();


$cat = new Cat();
$cat->meow();

';
$simpleSyntax = '
$numberOne = 10;
$numberTwo = 20;

echo("qko");

';

$lexer = new Lexer($ownSyntax);
$tokens = $lexer->tokenize();
//dd($tokens);

$parser = new Parser($tokens);
$statements = $parser->parse();
//dd($statements);

$interpreter = new Interpreter();
$interpreter->interpret($statements);

//dd($interpreter);
