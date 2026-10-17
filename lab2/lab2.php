<?php

// Lab 2: PHP Expressions and Control Structures

echo '<h1>Lab 2A: Expressions and Control Structures</h1>';

// 1. Expressions and Operators
echo '<h2>1. Expressions and Operators</h2>';

$x = -5;

// TODO: Write an expression that calculates:
// y = 3(abs(2x) + 4)
$y = 3 * (abs(2 * $x) + 4);

echo "The value of y is: $y<br>";

// 2. Boolean Comparisons
echo '<h2>2. Comparisons</h2>';

// TODO: Use var_dump() to compare:
var_dump(2 > 1);     // true  
var_dump(2 == 3);    // false   
var_dump("5" == 5);  // true
var_dump("5" === 5); // false
echo '<br>';

// 3. Unary Operators
echo '<h2>3. Unary Operators</h2>';

$c = 5;
$d = -$c; // Unary minus
$e = +$c; // Unary plus
$f = ++$c; // Pre-increment

// TODO: Create variables that demonstrate unary minus, unary plus,
// and pre-increment. Display the results.
echo "d: $d<br>";
echo "e: $e<br>";
echo "f: $f<br>";

// 4. Operator Precedence
echo '<h2>4. Operator Precedence</h2>';

// TODO: Compare the results of these two expressions:
$a = 2 + 3 * 4; 
$b = (2 + 3) * 4;

echo "a: $a<br>"; // 14
echo "b: $b<br>"; // 20

// 5. Assignment Associativity
echo '<h2>5. Assignment Associativity</h2>';

$level = $score = $time = 0;

echo "level: $level<br>";
echo "score: $score<br>";
echo "time: $time<br>";

// 6. Equality and Assignment
echo '<h2>6. Equality and Assignment</h2>';

// TODO: Create variables with equal and unequal values.
// Use var_dump() to compare them with == and ===.

$a = 5;
$b = 5;
var_dump($a == $b); //true
var_dump($a === $b); //true
$c = 10;
$d = 20;
var_dump($c == $d); //false
var_dump($c === $d); //false

// 7. Logical Operators
echo '<h2>7. Logical Operators</h2>';
// TODO: Use var_dump() to demonstrate &&, ||, and !.

$a = 1;
$b = 0;
$c = 1;

var_dump($a && $b);
var_dump($a || $b);
var_dump(!$a);
var_dump(!$c);

//8. Scholarship Decision with if
echo '<h2>8. Scholarship Decision with if</h2>';

$gpa = 3.9;

if ($gpa >= 3.7) {
    echo "Student gets scholarship";
}
echo '<br>';


// 9. Scholarship Decision with if...else
echo '<h2>9. Scholarship Decision with if...else</h2>';

$gpa = 3.5;

if($gpa >= 3.7) {
    echo "Student gets scholarship";
} else {
    echo "Student does not get scholarship";
}
echo '<br>';

// TODO: Use if...else to display whether the student receives a scholarship.

// 10. Scholarship Decision with if...elseif...else
echo '<h2>10. Scholarship Decision with if...elseif...else</h2>';

// TODO: Use if...elseif...else to display:
// Full scholarship for GPA 3.7 or higher
// Partial scholarship for GPA 3.5 through 3.69
// No scholarship otherwise

$gpa = 3.59;

if($gpa >= 3.7){
    echo "Student gets full scholarship";
} elseif ($gpa >= 3.5){
    echo "Student gets partial scholarship";
} else{
    echo "Student does not get scholarship";
}
echo '<br>';

// 11. Access Status
echo '<h2>11. Access Status</h2>';

$status = 'pending';

// TODO: Use a switch statement to display a message for:
// approved, pending, and all other statuses

switch ($status) {
    case 'approved':
        echo "Access granted";
        break;
    case 'pending':
        echo "Review in progress";
        break;
    default:
        echo "Access denied";
}
echo '<br>';

// 12. While Loop
echo '<h2>12. While Loop</h2>';

$fuel = 10;

// TODO: Create a while loop.
// Display "There is enough fuel" while fuel is greater than 1.
// Decrease fuel during each repetition.

while ($fuel > 1) {
    echo "There is enough fuel<br>";
    $fuel--;
}
echo '<br>';

// 13. Multiplication Table
echo '<h2>13. Multiplication Table</h2>';

// TODO: Create a for loop that displays the 12-times table
// from 1 through 12.

for ($count = 1; $count <= 12; $count++){
    echo $count . " times 12 is " . ($count * 12) . "<br>";
}
echo '<br>';
