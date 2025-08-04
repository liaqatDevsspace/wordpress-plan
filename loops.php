<?php
// while loop
$count = 0;
echo "<ul>";
while ($count < 5) {
    echo "<li>count:$count</li>";
    $count++;
}
echo "</ul>";

// do while loop
$count = 10;
do {
    echo "<p>count:$count</p>";
} while ($count < 10);


//for loop
echo "<ul>";

for ($i = 0; $i < 10; $i++) {
    echo "<li>count:$i</li>";
}
echo "</ul>";


//for each loop
$fruits = array("apple", "banana", "cherry");
foreach ($fruits as $fruit) echo "$fruit, ";
echo "etc......";
