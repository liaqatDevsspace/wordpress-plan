<?php

// simple function
function sayHello()
{
    echo "Hello!";
}

sayHello();

//  function with parameter
function doubleIt($num)
{
    return $num * 2;
}

echo "<p>" . doubleIt(5) . "</p>";

// Anonymous functions
$welcome = function () {
    echo "<p>Welcome to my website!</p>";
};

$welcome();


$count = 0;

// closures
$printCount = function () use ($count) {
    echo "<p>$count</p>";
};
$printCount();

// variable functions

$varFun = "sayHello";

$varFun();


function printSomething()
{
    global $count;
    echo "<p>$count</p>";
}
printSomething();
