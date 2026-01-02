<?php
require 'vendor/autoload.php';

use App\Services\WordTemplateService;

try {
    $service = new WordTemplateService();
    $phpWord = $service->generateTemplate();

    $filename = 'test-template-' . date('Y-m-d-His') . '.docx';
    $tmpFile = 'storage/app/' . uniqid() . '.docx';

    $phpWord->save($tmpFile);

    echo "Success! File saved to: " . $tmpFile;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    echo "\nFile: " . $e->getFile();
    echo "\nLine: " . $e->getLine();
    echo "\n\nStacktrace:\n";
    echo $e->getTraceAsString();
}
?>
