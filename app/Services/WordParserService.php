<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class WordParserService
{
    public function parseWordDocument($filePath)
    {
        // Try simple text extraction first
        $allText = $this->extractAllTextSimple($filePath);

        $this->log('========================================');
        $this->log('STARTING WORD DOCUMENT PARSING');
        $this->log('========================================');
        $this->log('File: ' . basename($filePath));
        $this->log('Total text length: ' . strlen($allText) . ' characters');

        if (empty($allText)) {
            $this->log('ERROR: No text extracted from Word document', 'error');
            return [];
        }

        // Save full extracted text to file for debugging
        $debugFile = storage_path('logs/word_extracted_text.txt');
        file_put_contents($debugFile, $allText);
        $this->log("Full extracted text saved to: $debugFile");

        $questions = [];
        $currentQuestion = null;
        $inOptionsSection = false;

        // Split by lines
        $lines = explode("\n", $allText);
        $this->log('Total lines to process: ' . count($lines));

        // Question indicator keywords
        $questionKeywords = [
            'bacalah',
            'perhatikan',
            'cermati',
            'cermatilah',
            'pilihlah',
            'manakah',
            'berikut',
            'kutipan',
            'tentukan',
            'carilah',
            'arti',
            'topik',    // e.g. "Topik Karya Tulis Ilmiah : ..."
        ];

        // Track the last letter-option parsed so numbered continuation lines
        // (e.g. "10) Lengkapi biodata...") can be appended to that option
        $lastOptionLetter = null;

        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);

            if (empty($line)) {
                continue;
            }

            // Check if this line starts a NEW QUESTION
            // A new question has: number + (dot/paren) + (long text OR keyword OR ends with question marker)
            if (preg_match('/^(\d+)[\.\)]\s+(.+)/s', $line, $matches)) {
                $number = intval($matches[1]);
                $text = trim($matches[2]);
                $textLength = strlen($text);

                // Skip point lines
                if (preg_match('/^(poin|nilai|score|bobot|skor|point)/i', $text)) {
                    continue;
                }

                // Check if this looks like a QUESTION (not an option)
                $hasKeyword = false;
                foreach ($questionKeywords as $keyword) {
                    if (stripos($text, $keyword) !== false && stripos($text, $keyword) < 80) {
                        $hasKeyword = true;
                        break;
                    }
                }

                $endsWithMarker = (
                    substr($line, -1) === '…' ||
                    substr($line, -1) === '?' ||
                    preg_match('/adalah[\.…\s]*$/iu', $line) ||
                    preg_match('/yaitu[\.…\s]*$/iu', $line) ||
                    preg_match('/\.\.\.$/', $line) ||           // ends with ...
                    preg_match('/tersebut adalah/i', $line) ||  // tambahan ini
                    preg_match('/berikut (adalah|ini)/i', $line) // tambahan ini
                );

                // DECISION: Is this a QUESTION start or an OPTION?
                $isNewQuestion = false;

                if ($inOptionsSection && $textLength < 100 && !$hasKeyword && $number >= 2 && $number <= 6) {
                    // We're in options section, short text, no keyword, number 2-6 = OPTION
                    if ($currentQuestion !== null) {
                        $optionLetter = chr(96 + ($number - 1)); // 2→a, 3→b, 4→c, 5→d, 6→e
                        if ($optionLetter >= 'a' && $optionLetter <= 'e') {
                            $currentQuestion['option_' . $optionLetter] = $text;
                            $this->log("  Option " . strtoupper($optionLetter) . " (from #$number): " . substr($text, 0, 50));
                        }
                    }
                } else if ($textLength > 80 || $hasKeyword || $endsWithMarker || !$inOptionsSection) {
                    // This looks like a NEW QUESTION
                    $isNewQuestion = true;
                    $inOptionsSection = false;
                    $lastOptionLetter = null;

                    // Save previous question if it has enough options
                    if ($currentQuestion !== null) {
                        $optCount = 0;
                        foreach (['option_a', 'option_b', 'option_c', 'option_d', 'option_e'] as $opt) {
                            if (!empty($currentQuestion[$opt])) $optCount++;
                        }

                        if ($optCount >= 2) {
                            $questions[] = $currentQuestion;
                            $this->log("✓ Saved Q#" . $currentQuestion['question_number'] . " with $optCount options");
                        } else {
                            $this->log("✗ Discarded Q#" . $currentQuestion['question_number'] . ": only $optCount options");
                        }
                    }

                    // Start new question - read ALL lines until we hit question marker
                    $this->log("→ NEW QUESTION #$number");
                    $fullQuestionText = $text;

                    // Keep reading lines until we find the question marker (ends with … or ?)
                    if (!$endsWithMarker) {
                        for ($j = $i + 1; $j < count($lines); $j++) {
                            $nextLine = trim($lines[$j]);
                            if (empty($nextLine)) continue;

                            // Check if next line is an option (letter-based), allow no space after period
                            if (preg_match('/^([A-Ea-e])[\.\)]\s*\S/', $nextLine)) {
                                // Found options, stop here
                                break;
                            }

                            // Check if next line is a numbered line that could be another question
                            if (preg_match('/^(\d+)[\.\)]\s+(.+)/', $nextLine, $nextMatch)) {
                                $nextNum = intval($nextMatch[1]);
                                $nextText = trim($nextMatch[2]);
                                $nextHasKeyword = false;
                                foreach ($questionKeywords as $kw) {
                                    if (stripos($nextText, $kw) !== false && stripos($nextText, $kw) < 80) {
                                        $nextHasKeyword = true;
                                        break;
                                    }
                                }

                                // If it's a big number with keyword, it's probably next question
                                if (strlen($nextText) > 100 || $nextHasKeyword) {
                                    break;
                                }
                            }

                            // Append this line to question text
                            $fullQuestionText .= "\n" . $nextLine;
                            $i = $j; // Move index forward

                            // Check if we found the marker
                            if (
                                substr($nextLine, -1) === '…' || substr($nextLine, -1) === '?' ||
                                preg_match('/adalah[\.…\s]*$/i', $nextLine) ||
                                preg_match('/yaitu[\.…\s]*$/i', $nextLine)
                            ) {
                                $inOptionsSection = true;
                                break;
                            }
                        }
                    } else {
                        $inOptionsSection = true;
                    }

                    $currentQuestion = [
                        'question_number' => $number,
                        'question_text' => $fullQuestionText,
                        'option_a' => null,
                        'option_a_image' => null,
                        'option_b' => null,
                        'option_b_image' => null,
                        'option_c' => null,
                        'option_c_image' => null,
                        'option_d' => null,
                        'option_d_image' => null,
                        'option_e' => null,
                        'option_e_image' => null,
                        'correct_answer' => null,
                        'points' => 1,
                        'image_path' => null,
                    ];

                    $this->log("  Question text length: " . strlen($fullQuestionText));
                } else {
                    // Numbered line that is neither a sub-option (2-6) nor a clear new question.
                    // Treat it as a continuation of the last parsed letter-option (e.g. "10) ..."
                    // that follows "a. 4) ..." in multi-part option format).
                    if ($inOptionsSection && $currentQuestion !== null && $lastOptionLetter !== null) {
                        $currentQuestion['option_' . $lastOptionLetter] .= "\n" . $text;
                        $this->log("  Continued Option " . strtoupper($lastOptionLetter) . ": " . substr($text, 0, 50));
                    }
                }
            }
            // Check for LETTER-based options (a., b., c., d., e.)
            elseif (preg_match('/^([A-Ea-e])[\.\.\)]\s*(.+)/i', $line, $matches)) {
                if ($currentQuestion !== null) {
                    $optionLetter = strtolower($matches[1]);
                    $optionText = trim($matches[2]);

                    // Check if multiple options are concatenated on one line (e.g., "a.text1b.text2c.text3")
                    if (!empty($optionText) && preg_match('/[B-Eb-e][\.\.\)]/i', $optionText)) {
                        // Split concatenated options
                        preg_match_all('/([A-Ea-e])[\.\.\)]\s*(.*?)(?=[A-Ea-e][\.\.\)]|$)/is', $line, $allOpts, PREG_SET_ORDER);
                        if (count($allOpts) >= 2) {
                            foreach ($allOpts as $opt) {
                                $letter = strtolower($opt[1]);
                                $text = trim($opt[2]);
                                if (in_array($letter, ['a', 'b', 'c', 'd', 'e']) && !empty($text)) {
                                    $currentQuestion['option_' . $letter] = $text;
                                    $inOptionsSection = true;
                                    $lastOptionLetter = $letter;
                                    $this->log("  Option (split) " . strtoupper($letter) . ": " . substr($text, 0, 50));
                                }
                            }
                        } else {
                            // Fallback: single option
                            if (in_array($optionLetter, ['a', 'b', 'c', 'd', 'e']) && !empty($optionText)) {
                                $currentQuestion['option_' . $optionLetter] = $optionText;
                                $inOptionsSection = true;
                                $lastOptionLetter = $optionLetter;
                                $this->log("  Option " . strtoupper($optionLetter) . ": " . substr($optionText, 0, 50));
                            }
                        }
                    } else {
                        if (in_array($optionLetter, ['a', 'b', 'c', 'd', 'e']) && !empty($optionText)) {
                            $currentQuestion['option_' . $optionLetter] = $optionText;
                            $inOptionsSection = true;
                            $lastOptionLetter = $optionLetter;
                            $this->log("  Option " . strtoupper($optionLetter) . ": " . substr($optionText, 0, 50));
                        }
                    }
                }
            }
            // Check for answer key
            elseif (preg_match('/(jawaban|answer|kunci):\s*([A-Ea-e])/i', $line, $matches)) {
                if ($currentQuestion !== null) {
                    $currentQuestion['correct_answer'] = strtoupper($matches[2]);
                    $this->log("  Answer: " . $matches[2]);
                }
            }
            // Check for points
            elseif (preg_match('/(poin|nilai|score|bobot|skor|point):\s*(\d+)/i', $line, $matches)) {
                if ($currentQuestion !== null) {
                    $currentQuestion['points'] = intval($matches[2]);
                }
            }
        }

        // Save last question
        if ($currentQuestion !== null) {
            $optCount = 0;
            foreach (['option_a', 'option_b', 'option_c', 'option_d', 'option_e'] as $opt) {
                if (!empty($currentQuestion[$opt])) $optCount++;
            }

            if ($optCount >= 2) {
                $questions[] = $currentQuestion;
                $this->log("✓ Saved last Q#" . $currentQuestion['question_number'] . " with $optCount options");
            }
        }

        // Sort by question number
        usort($questions, function ($a, $b) {
            return ($a['question_number'] ?? 0) - ($b['question_number'] ?? 0);
        });

        $this->log('========================================');
        $this->log('PARSING COMPLETE');
        $this->log('Total questions: ' . count($questions));

        $questionNumbers = array_map(function ($q) {
            return $q['question_number'] ?? '?';
        }, $questions);
        $this->log('Question numbers: ' . implode(', ', $questionNumbers));
        $this->log('========================================');

        // Remove question_number field
        foreach ($questions as &$question) {
            unset($question['question_number']);
        }

        // Extract and assign images
        try {
            $this->extractAndAssignImages($filePath, $questions);
        } catch (\Exception $e) {
            $this->log('Error extracting images: ' . $e->getMessage(), 'error');
        }

        return $questions;
    }

    /**
     * Extract document structure with text and images in order
     */
    private function extractDocumentStructure($filePath)
    {
        $structure = [];

        try {
            $zip = new ZipArchive();

            if ($zip->open($filePath) !== true) {
                return $structure;
            }

            // Get document.xml content
            $documentXml = $zip->getFromName('word/document.xml');

            if (!$documentXml) {
                $zip->close();
                return $structure;
            }

            // Parse XML
            $dom = new \DOMDocument();
            $dom->loadXML($documentXml);
            $xpath = new \DOMXPath($dom);

            // Register namespaces
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
            $xpath->registerNamespace('pic', 'http://schemas.openxmlformats.org/drawingml/2006/picture');
            $xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

            // Get document relationships to map images
            $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
            $imageMap = [];

            if ($relsXml) {
                $relsDom = new \DOMDocument();
                $relsDom->loadXML($relsXml);
                $relsXpath = new \DOMXPath($relsDom);
                $relationships = $relsXpath->query('//Relationship[@Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image"]');

                foreach ($relationships as $rel) {
                    $id = $rel->getAttribute('Id');
                    $target = $rel->getAttribute('Target');
                    $imageMap[$id] = 'word/' . $target;
                }
            }

            // Get all paragraphs in order
            $paragraphs = $xpath->query('//w:p');

            foreach ($paragraphs as $paragraph) {
                // Check for text
                $textNodes = $xpath->query('.//w:t', $paragraph);
                $text = '';
                foreach ($textNodes as $textNode) {
                    $text .= $textNode->textContent;
                }

                if (!empty(trim($text))) {
                    $structure[] = [
                        'type' => 'text',
                        'content' => $text,
                    ];
                }

                // Check for images in this paragraph
                $drawings = $xpath->query('.//w:drawing', $paragraph);

                foreach ($drawings as $drawing) {
                    $blips = $xpath->query('.//a:blip', $drawing);

                    foreach ($blips as $blip) {
                        $embed = $blip->getAttribute('r:embed');

                        if (isset($imageMap[$embed])) {
                            $imagePath = str_replace('\\', '/', $imageMap[$embed]);
                            $imageContent = $zip->getFromName($imagePath);

                            if ($imageContent) {
                                // Get extension from path
                                preg_match('/\.(png|jpg|jpeg|gif|bmp)$/i', $imagePath, $matches);
                                $extension = $matches[1] ?? 'png';

                                $structure[] = [
                                    'type' => 'image',
                                    'content' => $imageContent,
                                    'extension' => $extension,
                                ];

                                $this->log("Found image: " . $imagePath);
                            }
                        }
                    }
                }
            }

            $zip->close();
        } catch (\Exception $e) {
            $this->log('Error extracting document structure: ' . $e->getMessage(), 'error');
        }

        return $structure;
    }

    /**
     * Save image to storage
     */
    private function saveImage($imageContent, $extension)
    {
        $fileName = 'question_' . time() . '_' . Str::random(10) . '.' . $extension;
        $destinationPath = 'questions/images/' . $fileName;

        Storage::disk('public')->put($destinationPath, $imageContent);

        $this->log("Saved image: " . $destinationPath);

        return $destinationPath;
    }

    /**
     * Extract images from DOCX and intelligently assign to questions/options
     */
    private function extractAndAssignImages($filePath, &$questions)
    {
        if (count($questions) === 0) {
            return;
        }

        $zip = new ZipArchive();

        if ($zip->open($filePath) !== true) {
            $this->log('Cannot open ZIP file for image extraction', 'error');
            return;
        }

        try {
            // Read document.xml and document.xml.rels to map images
            $documentXml = $zip->getFromName('word/document.xml');
            $relsXml = $zip->getFromName('word/_rels/document.xml.rels');

            if (!$documentXml || !$relsXml) {
                $this->log('Cannot read document XML or relationships', 'error');
                $zip->close();
                return;
            }

            // Parse relationships to build image ID map
            $imageMap = [];
            $relsDoc = new \DOMDocument();
            $relsDoc->loadXML($relsXml);

            $relationships = $relsDoc->getElementsByTagName('Relationship');
            foreach ($relationships as $rel) {
                $type = $rel->getAttribute('Type');
                if (strpos($type, '/image') !== false) {
                    $id = $rel->getAttribute('Id');
                    $target = $rel->getAttribute('Target');
                    $imageMap[$id] = 'word/' . $target;
                }
            }

            $this->log('Found ' . count($imageMap) . ' image relationships');

            if (count($imageMap) === 0) {
                $zip->close();
                return;
            }

            // Parse document.xml to find images
            $doc = new \DOMDocument();
            $doc->loadXML($documentXml);
            $xpath = new \DOMXPath($doc);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
            $xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

            // Extract all text and image references in order
            $elements = [];
            $paragraphs = $xpath->query('//w:p');

            foreach ($paragraphs as $paragraph) {
                // Get text
                $textNodes = $xpath->query('.//w:t', $paragraph);
                $text = '';
                foreach ($textNodes as $textNode) {
                    $text .= $textNode->textContent;
                }

                if (!empty(trim($text))) {
                    $elements[] = ['type' => 'text', 'content' => trim($text)];
                }

                // Get images in this paragraph
                $blips = $xpath->query('.//a:blip', $paragraph);
                foreach ($blips as $blip) {
                    $embedId = $blip->getAttribute('r:embed');
                    if (isset($imageMap[$embedId])) {
                        $imagePath = $imageMap[$embedId];
                        $imageContent = $zip->getFromName($imagePath);

                        if ($imageContent) {
                            preg_match('/\.(png|jpg|jpeg|gif|bmp)$/i', $imagePath, $matches);
                            $extension = $matches[1] ?? 'png';

                            $elements[] = [
                                'type' => 'image',
                                'content' => $imageContent,
                                'extension' => $extension,
                            ];
                        }
                    }
                }
            }

            $this->log('Extracted ' . count($elements) . ' elements (text + images)');

            // Now assign images to questions based on proximity
            $currentQuestionIndex = -1;
            $lastOption = null;

            foreach ($elements as $element) {
                if ($element['type'] === 'text') {
                    $text = $element['content'];

                    // Check if this is a new question
                    if (preg_match('/^(\d+)\.?\s+/', $text)) {
                        $currentQuestionIndex++;
                        $lastOption = null;
                    }
                    // Check if this is an option
                    elseif (preg_match('/^([A-D])\.?\s+/i', $text, $matches)) {
                        $lastOption = strtolower($matches[1]);
                    }
                } elseif ($element['type'] === 'image' && $currentQuestionIndex >= 0 && $currentQuestionIndex < count($questions)) {
                    // Save image
                    $imagePath = $this->saveImage($element['content'], $element['extension']);

                    // Assign to the appropriate location
                    if ($lastOption !== null && isset($questions[$currentQuestionIndex]['option_' . $lastOption])) {
                        $questions[$currentQuestionIndex]['option_' . $lastOption . '_image'] = $imagePath;
                        $this->log("Assigned image to Q" . ($currentQuestionIndex + 1) . " option " . strtoupper($lastOption));
                    } elseif ($lastOption === null) {
                        $questions[$currentQuestionIndex]['image_path'] = $imagePath;
                        $this->log("Assigned image to Q" . ($currentQuestionIndex + 1) . " question");
                    }
                }
            }

            $zip->close();
        } catch (\Exception $e) {
            $this->log('Error in extractAndAssignImages: ' . $e->getMessage(), 'error');
            $zip->close();
        }
    }

    /**
     * Log helper function
     */
    private function log($message, $level = 'info')
    {
        // Use proper Log facade calls
        switch ($level) {
            case 'error':
                Log::error($message);
                break;
            case 'warning':
                Log::warning($message);
                break;
            case 'debug':
                Log::debug($message);
                break;
            default:
                Log::info($message);
                break;
        }
    }

    private function extractAllTextSimple($filePath)
    {
        $text = '';

        try {
            $zip = new ZipArchive();

            if ($zip->open($filePath) === true) {
                $content = $zip->getFromName('word/document.xml');
                $numberingXml = $zip->getFromName('word/numbering.xml');

                if ($content && $numberingXml) {
                    // ====================================================
                    // STEP 1: Parse numbering.xml untuk mendapatkan format
                    // ====================================================
                    $numDom = new \DOMDocument();
                    $numDom->loadXML($numberingXml);
                    $numXpath = new \DOMXPath($numDom);
                    $numXpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

                    // Build abstractNumId -> {ilvl -> {fmt, lvlText}}
                    $abstractNumFormats = [];
                    $abstractNums = $numXpath->query('//w:abstractNum');
                    foreach ($abstractNums as $abstractNum) {
                        $absId = $abstractNum->getAttribute('w:abstractNumId');
                        $abstractNumFormats[$absId] = [];
                        $levels = $numXpath->query('.//w:lvl', $abstractNum);
                        foreach ($levels as $lvl) {
                            $ilvl = $lvl->getAttribute('w:ilvl');
                            $numFmt = $numXpath->query('.//w:numFmt', $lvl);
                            $lvlText = $numXpath->query('.//w:lvlText', $lvl);
                            $fmt = $numFmt->length > 0 ? $numFmt->item(0)->getAttribute('w:val') : '';
                            $txt = $lvlText->length > 0 ? $lvlText->item(0)->getAttribute('w:val') : '';
                            $abstractNumFormats[$absId][$ilvl] = ['fmt' => $fmt, 'lvlText' => $txt];
                        }
                    }

                    // Build numId -> abstractNumId
                    $numIdToAbsId = [];
                    $nums = $numXpath->query('//w:num');
                    foreach ($nums as $num) {
                        $numId = $num->getAttribute('w:numId');
                        $absRef = $numXpath->query('.//w:abstractNumId', $num);
                        if ($absRef->length > 0) {
                            $numIdToAbsId[$numId] = $absRef->item(0)->getAttribute('w:val');
                        }
                    }

                    // Helper: dapatkan format untuk numId+ilvl
                    $getFormat = function ($numId, $ilvl) use ($numIdToAbsId, $abstractNumFormats) {
                        $absId = $numIdToAbsId[$numId] ?? null;
                        if ($absId === null) return ['fmt' => '', 'lvlText' => ''];
                        return $abstractNumFormats[$absId][$ilvl] ?? ['fmt' => '', 'lvlText' => ''];
                    };

                    // ====================================================
                    // STEP 2: Tentukan numId mana yang merupakan soal utama
                    // Soal utama = numId=1 (decimal, %1.)
                    // Kita deteksi secara otomatis: numId dengan fmt=decimal
                    // dan lvlText=%1. yang memiliki jumlah count terbesar
                    // (atau hardcode numId=1 jika dokumen selalu sama)
                    // ====================================================
                    $QUESTION_NUM_ID = '1'; // numId untuk soal utama

                    // ====================================================
                    // STEP 3: Parse document.xml dengan numbering-aware
                    // ====================================================
                    $dom = new \DOMDocument();
                    $dom->loadXML($content);
                    $xpath = new \DOMXPath($dom);
                    $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

                    $paragraphs = $xpath->query('//w:p');
                    $paragraphTexts = [];

                    // Counters per-numId untuk tracking urutan
                    $numIdCounters = []; // numId -> counter (untuk soal)
                    $optionCounters = []; // numId -> counter huruf (untuk pilihan)

                    $currentQuestionNumId = null; // numId aktif soal terakhir

                    foreach ($paragraphs as $paragraph) {
                        $paragraphText = '';

                        // Cek apakah ada numbering
                        $numPrNodes = $xpath->query('.//w:numPr', $paragraph);
                        $hasNumbering = $numPrNodes->length > 0;

                        $numId = null;
                        $ilvl = null;
                        $fmt = '';
                        $lvlText = '';

                        if ($hasNumbering) {
                            $ilvlNodes = $xpath->query('.//w:numPr/w:ilvl', $paragraph);
                            $numIdNodes = $xpath->query('.//w:numPr/w:numId', $paragraph);

                            if ($ilvlNodes->length > 0 && $numIdNodes->length > 0) {
                                $ilvl = $ilvlNodes->item(0)->getAttribute('w:val');
                                $numId = $numIdNodes->item(0)->getAttribute('w:val');
                                $formatInfo = $getFormat($numId, $ilvl);
                                $fmt = $formatInfo['fmt'];
                                $lvlText = $formatInfo['lvlText'];
                            }
                        }

                        if ($numId !== null) {
                            if ($numId === $QUESTION_NUM_ID) {
                                // === INI ADALAH SOAL ===
                                if (!isset($numIdCounters[$numId])) {
                                    $numIdCounters[$numId] = 0;
                                }
                                $numIdCounters[$numId]++;
                                $questionNumber = $numIdCounters[$numId];
                                $paragraphText = $questionNumber . '. ';
                                $currentQuestionNumId = null; // reset option tracking

                            } elseif ($fmt === 'lowerLetter') {
                                // === INI ADALAH PILIHAN JAWABAN (a, b, c, d, e) ===
                                if (!isset($optionCounters[$numId])) {
                                    $optionCounters[$numId] = 0;
                                }
                                $optionCounters[$numId]++;
                                $letterIndex = $optionCounters[$numId];
                                $optionLetter = chr(96 + $letterIndex); // 1→a, 2→b, dst
                                $paragraphText = $optionLetter . '. ';
                            } elseif ($fmt === 'decimal' && strpos($lvlText, '(') !== false) {
                                // === INI ADALAH SUB-ITEM BACAAN (1), (2), (3)... ===
                                if (!isset($numIdCounters[$numId])) {
                                    $numIdCounters[$numId] = 0;
                                }
                                $numIdCounters[$numId]++;
                                $itemNumber = $numIdCounters[$numId];
                                $paragraphText = '(' . $itemNumber . ') ';
                            } elseif ($fmt === 'decimal') {
                                // === DECIMAL LAIN (sub-numbering lainnya) ===
                                if (!isset($numIdCounters[$numId])) {
                                    $numIdCounters[$numId] = 0;
                                }
                                $numIdCounters[$numId]++;
                                $itemNumber = $numIdCounters[$numId];
                                $paragraphText = $itemNumber . '. ';
                            }
                        }

                        // Ambil teks paragraf
                        $textNodes = $xpath->query('.//w:t', $paragraph);
                        foreach ($textNodes as $textNode) {
                            $paragraphText .= $textNode->textContent;
                        }

                        $paragraphText = trim($paragraphText);
                        if (!empty($paragraphText)) {
                            $paragraphTexts[] = $paragraphText;
                        }
                    }

                    $text = implode("\n", $paragraphTexts);
                    $text = html_entity_decode($text);

                    $this->log('Extracted ' . count($paragraphTexts) . ' paragraphs');
                }

                $zip->close();
            }
        } catch (\Exception $e) {
            $this->log('Error: ' . $e->getMessage(), 'error');
        }

        return $text;
    }
}
