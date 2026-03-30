<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\TextRun;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class WordQuestionImportService
{
    private $tempImagePaths = [];

    /**
     * Parse Word document and extract questions with answer keys.
     * Uses a block-based approach: Jawaban: / Poin: lines are end-of-block markers.
     */
    public function parseWordFile($filePath)
    {
        try {
            $this->extractImagesFromDocx($filePath);
        } catch (\Exception $e) {
            // Silently ignore image extraction errors
        }

        // Collect all non-empty paragraph strings from the docx
        $paragraphs = $this->collectParagraphs($filePath);

        return $this->parseBlockBased($paragraphs);
    }

    // -----------------------------------------------------------------------
    // Paragraph collection
    // -----------------------------------------------------------------------

    /**
     * Collect all non-empty text paragraphs from the docx, trying PhpWord
     * first and falling back to raw XML.
     */
    private function collectParagraphs($filePath)
    {
        try {
            return $this->collectWithPhpWord($filePath);
        } catch (\Exception $e) {
            return $this->collectWithXml($filePath);
        }
    }

    private function collectWithPhpWord($filePath)
    {
        $phpWord = IOFactory::load($filePath);
        $lines = [];

        foreach ($phpWord->getSections() as $section) {
            $elements = $section->getElements();
            if (empty($elements)) {
                throw new \Exception('PhpWord returned empty elements');
            }

            foreach ($elements as $element) {
                $text = $this->extractElementText($element);
                $text = trim($text);
                if ($text !== '') {
                    $lines[] = $text;
                }
            }
        }

        return $lines;
    }

    private function collectWithXml($filePath)
    {
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
        $lines = [];

        foreach ($paragraphs as $paragraph) {
            $textNodes = $xpath->query('.//w:t', $paragraph);
            $text = '';
            foreach ($textNodes as $node) {
                $text .= $node->nodeValue;
            }
            $text = trim($text);
            if ($text !== '') {
                $lines[] = $text;
            }
        }

        return $lines;
    }

    // -----------------------------------------------------------------------
    // Block-based parser
    // -----------------------------------------------------------------------

    /**
     * Parse paragraphs into question blocks.
     *
     * Document structure (per question):
     *   [question text]          ← one or more lines (first line = main question)
     *   [choice 1 text]          ← subsequent plain lines = choices (no prefix needed)
     *   [choice 2 text]
     *   ...
     *   Jawaban: X / Jawaban: X,Y,Z   ← detected answer marker
     *   Poin: N                        ← detected points marker
     *
     * We accumulate lines until we hit a Jawaban: line, at which point we
     * know we have a complete block.
     */
    private function parseBlockBased(array $lines)
    {
        $questions = [];
        $block = [];          // accumulates lines for the current question block
        $pendingAnswer = null; // last parsed answer letters
        $pendingPoints = null; // last parsed points value
        $questionNumber = 0;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($this->isAnswerLine($line)) {
                $pendingAnswer = $this->parseAnswerLetters($line);
                continue;
            }

            if ($this->isPointsLine($line)) {
                $pendingPoints = $this->parsePointsValue($line);

                // Flush the block: we now have all data for this question
                if (!empty($block)) {
                    $questionNumber++;
                    $questions[] = $this->buildQuestion(
                        $questionNumber,
                        $block,
                        $pendingAnswer ?? [],
                        $pendingPoints ?? 1
                    );
                    $block = [];
                }
                $pendingAnswer = null;
                $pendingPoints = null;
                continue;
            }

            // Regular line — accumulate into block.
            // Strip leading numbering like "1." / "1)" / "A." / "A)" if present
            $block[] = $this->stripLeadingLabel($line);
        }

        // Handle trailing block without a Poin: line
        if (!empty($block)) {
            $questionNumber++;
            $questions[] = $this->buildQuestion(
                $questionNumber,
                $block,
                $pendingAnswer ?? [],
                $pendingPoints ?? 1
            );
        }

        \Log::info('WordQuestionImportService: parsed ' . count($questions) . ' questions (block-based)');

        return $questions;
    }

    /**
     * Build a question array from accumulated lines.
     *
     * The first line is the question text.
     * Remaining lines are choices (indexed 1, 2, 3, …).
     */
    private function buildQuestion($number, array $block, array $answerLetters, $points)
    {
        $questionText = array_shift($block);
        $choices = [];
        $idx = 1;
        foreach ($block as $choiceLine) {
            if (trim($choiceLine) !== '') {
                $choices[$idx] = trim($choiceLine);
                $idx++;
            }
        }

        // Convert answer letters (A, B, C …) to numeric indices (1, 2, 3 …)
        $answerKey = array_map(function ($letter) {
            return ord(strtoupper(trim($letter))) - ord('A') + 1;
        }, $answerLetters);

        $questionType = $this->detectQuestionType(count($choices), count($answerKey));

        \Log::info("WordParser Q{$number}", [
            'question_text' => substr($questionText, 0, 60),
            'choice_count'  => count($choices),
            'answer_key'    => $answerKey,
            'points'        => $points,
            'type'          => $questionType,
        ]);

        return [
            'question_text' => $questionText,
            'question_html' => '',
            'choices'       => $choices,
            'choice_html'   => [],
            'answer_key'    => $answerKey,
            'question_type' => $questionType,
            'points'        => $points,
        ];
    }

    // -----------------------------------------------------------------------
    // Line type detection helpers
    // -----------------------------------------------------------------------

    /** Matches: "Jawaban: A"  "Jawaban: A,B,C"  "Jawaban: B, D, E" */
    private function isAnswerLine($text)
    {
        return (bool) preg_match('/^Jawaban\s*:\s*/i', trim($text));
    }

    /** Matches: "Poin: 2"  "Poin:  3" */
    private function isPointsLine($text)
    {
        return (bool) preg_match('/^Poin\s*:\s*/i', trim($text));
    }

    /** Extract answer letters from "Jawaban: A" or "Jawaban: B, D, E" */
    private function parseAnswerLetters($text)
    {
        $text = preg_replace('/^Jawaban\s*:\s*/i', '', trim($text));
        // Split on comma or comma+space
        $parts = preg_split('/\s*,\s*/', $text);
        $letters = [];
        foreach ($parts as $p) {
            $p = strtoupper(trim($p));
            if (preg_match('/^[A-E]$/', $p)) {
                $letters[] = $p;
            }
        }
        return $letters;
    }

    /** Extract numeric points from "Poin: 2" or "Poin:  3" */
    private function parsePointsValue($text)
    {
        if (preg_match('/^Poin\s*:\s*(\d+(?:\.\d+)?)/i', trim($text), $m)) {
            return floatval($m[1]);
        }
        return 1;
    }

    /**
     * Strip leading numbering or letter labels that may appear in some files.
     * Handles: "1. text", "1) text", "A. text", "A) text"
     */
    private function stripLeadingLabel($text)
    {
        // "1. " or "1) "
        $text = preg_replace('/^\d+[\.\)]\s+/', '', $text);
        // "A. " or "A) "
        $text = preg_replace('/^[A-E][\.\)]\s+/', '', $text);
        return $text;
    }

    // -----------------------------------------------------------------------
    // Question type detection
    // -----------------------------------------------------------------------

    /**
     * Detect question type:
     * 0 = PG (single choice)
     * 1 = PG Kompleks (multiple answers)
     * 2 = Benar/Salah (2 choices, single answer)
     * 3 = Essay (no choices)
     */
    private function detectQuestionType($choiceCount, $answerCount)
    {
        if ($choiceCount === 0) {
            return 3; // Essay
        }
        if ($choiceCount === 2 && $answerCount <= 1) {
            return 2; // Benar/Salah
        }
        if ($answerCount > 1) {
            return 1; // PG Kompleks
        }
        return 0; // PG
    }

    // -----------------------------------------------------------------------
    // Image extraction (unchanged)
    // -----------------------------------------------------------------------

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
                $stat     = $zip->statIndex($i);
                $fileName = $stat['name'];

                if (strpos($fileName, 'word/media/') === 0) {
                    $fileContent = $zip->getFromIndex($i);
                    $targetPath  = $tempDir . '/' . basename($fileName);
                    file_put_contents($targetPath, $fileContent);
                    $this->tempImagePaths[basename($fileName)] = $targetPath;
                }
            }

            $zip->close();
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    // -----------------------------------------------------------------------
    // PhpWord element text extraction helper
    // -----------------------------------------------------------------------

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

    // -----------------------------------------------------------------------
    // Public helpers (used by controller / other code)
    // -----------------------------------------------------------------------

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

            $ext         = pathinfo($imageFileName, PATHINFO_EXTENSION);
            $newFilename = 'question_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

            return Storage::disk('public')->putFileAs('question_images', $tempPath, $newFilename);
        } catch (\Exception $e) {
            return null;
        }
    }

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
