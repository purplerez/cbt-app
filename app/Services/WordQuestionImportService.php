<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\TextRun;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class WordQuestionImportService
{
    private $tempImagePaths = [];
    private $answerKeys = [];

    /**
     * Parse Word document and extract questions with answer keys
     */
    public function parseWordFile($filePath)
    {
        try {
            $this->extractImagesFromDocx($filePath);
            return $this->parseWithPhpWord($filePath);
        } catch (\Exception $e) {
            return $this->parseWithXml($filePath);
        }
    }

    /**
     * Extract images from DOCX file
     */
    private function extractImagesFromDocx($filePath)
    {
        try {
            $zip = new ZipArchive();
            if (!$zip->open($filePath)) {
                return;
            }

            $tempDir = storage_path('app/temp_images_' . uniqid());
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $fileName = $stat['name'];

                if (strpos($fileName, 'word/media/') === 0) {
                    $fileContent = $zip->getFromIndex($i);
                    $targetPath = $tempDir . '/' . basename($fileName);
                    file_put_contents($targetPath, $fileContent);
                    $this->tempImagePaths[basename($fileName)] = $targetPath;
                }
            }

            $zip->close();
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    /**
     * Parse Word file using PhpWord library
     */
    private function parseWithPhpWord($filePath)
    {
        $phpWord = IOFactory::load($filePath);
        $questions = [];
        $allText = '';

        foreach ($phpWord->getSections() as $section) {
            $elements = $section->getElements();

            if (empty($elements)) {
                throw new \Exception('PhpWord returned empty elements');
            }

            $currentQuestion = null;

            foreach ($elements as $element) {
                $text = $this->extractElementText($element);

                if (empty(trim($text))) {
                    continue;
                }

                // Collect all text for answer key parsing
                $allText .= $text . "\n";

                // Check if this is an answer key line
                if ($this->isAnswerKeyLine($text)) {
                    $this->parseAnswerKeyLine($text);
                    continue;
                }

                if ($this->isQuestionStart($text)) {
                    if ($currentQuestion && !empty($currentQuestion['question_text'])) {
                        $questions[] = $currentQuestion;
                    }

                    $questionNumber = $this->extractQuestionNumber($text);
                    $currentQuestion = [
                        'question_number' => $questionNumber,
                        'question_text' => $this->cleanText($text),
                        'choices' => [],
                        'question_images' => [],
                    ];
                } elseif ($currentQuestion && $this->isChoice($text)) {
                    $this->addChoiceFromText($currentQuestion, $text);
                }
            }

            if ($currentQuestion && !empty($currentQuestion['question_text'])) {
                $questions[] = $currentQuestion;
            }
        }

        return $this->processQuestions($questions);
    }

    /**
     * Parse Word file by reading XML directly
     */
    private function parseWithXml($filePath)
    {
        $questions = [];

        try {
            $zip = new ZipArchive();
            if (!$zip->open($filePath)) {
                throw new \Exception('Cannot open Word file');
            }

            $documentXml = $zip->getFromName('word/document.xml');
            $zip->close();

            if (!$documentXml) {
                return [];
            }

            $dom = new \DOMDocument();
            $dom->loadXML($documentXml);
            $xpath = new \DOMXPath($dom);

            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

            $paragraphs = $xpath->query('//w:p');

            $currentQuestion = null;

            foreach ($paragraphs as $paragraph) {
                $text = $this->extractTextFromXmlElement($xpath, $paragraph);

                if (empty(trim($text))) {
                    continue;
                }

                // Check if this is an answer key line
                if ($this->isAnswerKeyLine($text)) {
                    $this->parseAnswerKeyLine($text);
                    continue;
                }

                if ($this->isQuestionStart($text)) {
                    if ($currentQuestion && !empty($currentQuestion['question_text'])) {
                        $questions[] = $currentQuestion;
                    }

                    $questionNumber = $this->extractQuestionNumber($text);
                    $currentQuestion = [
                        'question_number' => $questionNumber,
                        'question_text' => $this->cleanText($text),
                        'choices' => [],
                        'question_images' => [],
                    ];
                } elseif ($currentQuestion && $this->isChoice($text)) {
                    $this->addChoiceFromText($currentQuestion, $text);
                }
            }

            if ($currentQuestion && !empty($currentQuestion['question_text'])) {
                $questions[] = $currentQuestion;
            }

            return $this->processQuestions($questions);
        } catch (\Exception $e) {
            throw new \Exception('Error parsing Word file: ' . $e->getMessage());
        }
    }

    /**
     * Check if text is an answer key line: JAWAB:1=A atau JAWAB:2=A,B,C
     */
    private function isAnswerKeyLine($text)
    {
        $trimmed = trim($text);
        return preg_match('/^JAWAB:\d+=[A-D](,[A-D])*$/i', $trimmed) === 1;
    }

    /**
     * Parse answer key line: JAWAB:1=A atau JAWAB:2=A,B,C
     */
    private function parseAnswerKeyLine($text)
    {
        $trimmed = trim($text);
        if (preg_match('/^JAWAB:(\d+)=([A-D](,[A-D])*)/i', $trimmed, $matches)) {
            $questionNumber = intval($matches[1]);
            $answers = explode(',', strtoupper($matches[2]));

            // Convert letter answers to numeric (A=1, B=2, C=3, D=4)
            $numericAnswers = array_map(function($letter) {
                return ord(trim($letter)) - ord('A') + 1;
            }, $answers);

            $this->answerKeys[$questionNumber] = $numericAnswers;
        }
    }

    /**
     * Extract question number from text
     */
    private function extractQuestionNumber($text)
    {
        if (preg_match('/^(\d+)[\.\)]/', trim($text), $matches)) {
            return intval($matches[1]);
        }
        return null;
    }

    /**
     * Extract text from XML element
     */
    private function extractTextFromXmlElement($xpath, $paragraph)
    {
        $textNodes = $xpath->query('.//w:t', $paragraph);
        $text = '';

        foreach ($textNodes as $node) {
            $text .= $node->nodeValue;
        }

        return $text;
    }

    /**
     * Extract text from various element types
     */
    private function extractElementText($element)
    {
        $text = '';

        if ($element instanceof \PhpOffice\PhpWord\Element\Paragraph) {
            foreach ($element->getElements() as $run) {
                if ($run instanceof TextRun) {
                    $text .= ($run->getText() ?? '');
                } elseif (method_exists($run, 'getText')) {
                    $text .= ($run->getText() ?? '');
                }
            }
        } elseif (method_exists($element, 'getText')) {
            $text = ($element->getText() ?? '');
        }

        return $text;
    }

    /**
     * Check if text is a question start
     */
    private function isQuestionStart($text)
    {
        $trimmed = trim($text);
        return preg_match('/^\d+[\.\)]\s+/', $trimmed) === 1 && strlen($trimmed) > 3;
    }

    /**
     * Check if text is a choice
     */
    private function isChoice($text)
    {
        $trimmed = trim($text);
        return preg_match('/^[A-D][\.\)]\s+/', $trimmed) === 1;
    }

    /**
     * Add choice from text
     */
    private function addChoiceFromText(&$question, $text)
    {
        $trimmed = trim($text);

        if (preg_match('/^([A-D])[\.\)]\s+(.*)$/', $trimmed, $matches)) {
            $key = $matches[1];
            $choiceText = trim($matches[2]);
        } else {
            return;
        }

        $numericKey = $this->keyToNumeric($key);
        $question['choices'][$numericKey] = $choiceText;
    }

    /**
     * Convert letter key to numeric
     */
    private function keyToNumeric($key)
    {
        $key = strtoupper(trim($key));
        return ord($key) - ord('A') + 1;
    }

    /**
     * Clean text
     */
    private function cleanText($text)
    {
        $text = preg_replace('/^\d+[\.\)]\s+/', '', trim($text));
        return trim($text);
    }

    /**
     * Process questions and attach answer keys
     */
    private function processQuestions($questions)
    {
        return array_map(function ($question) {
            $question['question_type'] = $this->detectQuestionType($question);
            $question['question_text'] = $this->cleanText($question['question_text']);
            $question['choices'] = $this->normalizeChoiceKeys($question['choices']);

            // Attach answer key from parsed data
            $questionNumber = $question['question_number'] ?? null;
            if ($questionNumber && isset($this->answerKeys[$questionNumber])) {
                $question['answer_key'] = $this->answerKeys[$questionNumber];
            } else {
                $question['answer_key'] = [];
            }

            unset($question['question_number']);
            return $question;
        }, $questions);
    }

    /**
     * Detect question type
     */
    private function detectQuestionType($question)
    {
        $choiceCount = count($question['choices']);

        if ($choiceCount === 0) {
            return 3; // Essay
        }

        if ($choiceCount === 2) {
            return 2; // True/False
        }

        return 0; // Single choice
    }

    /**
     * Normalize choice keys
     */
    private function normalizeChoiceKeys($choices)
    {
        if (empty($choices)) {
            return [];
        }

        $normalized = [];
        $index = 1;

        foreach ($choices as $choice) {
            $normalized[$index] = $choice;
            $index++;
        }

        return $normalized;
    }

    /**
     * Save extracted image to public storage
     */
    public function saveExtractedImage($imageFileName)
    {
        if (!isset($this->tempImagePaths[$imageFileName])) {
            return null;
        }

        try {
            $tempPath = $this->tempImagePaths[$imageFileName];

            if (!file_exists($tempPath)) {
                return null;
            }

            $ext = pathinfo($imageFileName, PATHINFO_EXTENSION);
            $newFilename = 'question_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

            $savedPath = Storage::disk('public')->putFileAs('question_images', $tempPath, $newFilename);

            return $savedPath;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Clean up temporary image files
     */
    public function cleanupTempImages()
    {
        foreach ($this->tempImagePaths as $tempPath) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }

        $tempDirs = glob(storage_path('app/temp_images_*'), GLOB_ONLYDIR);
        foreach ($tempDirs as $dir) {
            array_map('unlink', glob("$dir/*.*"));
            @rmdir($dir);
        }
    }
}
