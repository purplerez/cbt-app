<?php

require 'vendor/autoload.php';

$newAnswers = ["1" => "A", "2" => "B"];
$existingAnswers = [];

foreach ($newAnswers as $qId => $ans) {
    $existingAnswers[(string)$qId] = $ans;
}

$obj = empty($existingAnswers) ? new \stdClass() : (object)$existingAnswers;

echo "As object: ";
var_dump($obj);

echo "\nJSON encoded: ";
echo json_encode($obj) . "\n";

echo "\nIs array cast updating dirty attributes correctly in Laravel?\n";
// Let's simulate Laravel cast
$original = json_encode(['1' => 'A']);
$decoded = json_decode($original, true); // [1 => 'A']

$decoded['2'] = 'B';
$obj2 = (object)$decoded;
echo "New JSON: " . json_encode($obj2) . "\n";

