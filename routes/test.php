<?php

// Simple test route - add this to routes/web.php temporarily

Route::get('/test-word-template', function () {
    try {
        $service = new \App\Services\WordTemplateService();
        $phpWord = $service->generateTemplate();

        $filename = 'template-soal-test-' . date('Y-m-d-His') . '.docx';
        return response()->streamDownload(function () use ($phpWord) {
            $phpWord->save('php://output');
        }, $filename);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
