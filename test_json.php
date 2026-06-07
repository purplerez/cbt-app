<?php
// Manual store
$choicesRaw = ['text A', 'text B']; // This is what comes from validation if the form had choices[0], choices[1], etc., wait form has choices[1], choices[2]. Let's say form has choices[1]='A', choices[2]='B'
// In form, choices are sent as choices[1]=A, choices[2]=B.
// PHP receives them as an array [1 => 'A', 2 => 'B'].
$choicesRaw = [1 => 'text A', 2 => 'text B'];
$choicesStandardized = [];
$i = 1;
foreach ($choicesRaw as $c) {
    $choicesStandardized[(string)$i++] = $c;
}
echo "Manual: " . json_encode($choicesStandardized) . "\n";

// Word import
$parsedChoices = [1 => 'text A', 2 => 'text B']; // Built by WordQuestionImportService
// Sent to JS, serialized:
$jsonToJs = json_encode($parsedChoices);
// JS parses and stringifies, same string (mostly).
// PHP receives it:
$receivedChoices = json_decode($jsonToJs, true);
$filteredChoices = [];
foreach ($receivedChoices as $key => $choice) {
    if (!empty(trim($choice))) {
        $filteredChoices[$key] = trim($choice);
    }
}
echo "Word: " . json_encode($filteredChoices) . "\n";
