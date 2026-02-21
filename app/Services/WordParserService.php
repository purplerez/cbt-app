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
        $structured = $this->extractStructuredContent($filePath);

        $this->log('========================================');
        $this->log('STARTING WORD DOCUMENT PARSING');
        $this->log('========================================');
        $this->log('File: ' . basename($filePath));
        $this->log('Structured elements: ' . count($structured));

        if (empty($structured)) {
            $this->log('ERROR: No content extracted', 'error');
            return [];
        }

        $debugLines = array_map(fn($e) => "[{$e['type']}] " . ($e['text'] ?? ''), $structured);
        file_put_contents(storage_path('logs/word_extracted_text.txt'), implode("\n", $debugLines));

        $questions     = [];
        $currentQ      = null;
        $lastOptLetter = null;

        $questionKeywords = [
            'bacalah',
            'perhatikan',
            'cermati',
            'cermatilah',
            'pilihlah',
            'manakah',
            'kutipan',
            'tentukan',
            'carilah',
            'arti',
            'topik',
        ];

        $endsWithMarker = function (string $text): bool {
            $text = rtrim($text);
            if ($text === '') return false;
            $last = mb_substr($text, -1);
            if ($last === '…' || $last === '?') return true;
            if (preg_match('/adalah[\s\.…]*$/iu', $text))  return true;
            if (preg_match('/yaitu[\s\.…]*$/iu', $text))   return true;
            if (preg_match('/berikut[\s\.…]*$/iu', $text)) return true;
            if (preg_match('/kecuali[\s\.…]*$/iu', $text)) return true;
            if (preg_match('/disebut[\s\.…]*$/iu', $text)) return true;
            if (preg_match('/\.\.\.$/', $text))             return true;
            return false;
        };

        $hasKeyword = function (string $text) use ($questionKeywords): bool {
            $lower = mb_strtolower($text);
            foreach ($questionKeywords as $kw) {
                $pos = mb_strpos($lower, $kw);
                if ($pos !== false && $pos < 80) return true;
            }
            return false;
        };

        $saveCurrentQ = function () use (&$questions, &$currentQ) {
            if ($currentQ === null) return;
            $optCount = 0;
            foreach (['option_a', 'option_b', 'option_c', 'option_d', 'option_e'] as $opt) {
                if (!empty($currentQ[$opt])) $optCount++;
            }
            if ($optCount >= 2) {
                $questions[] = $currentQ;
                $this->log("✓ Saved Q#{$currentQ['question_number']} with $optCount options");
            } else {
                $this->log("✗ Discarded Q#{$currentQ['question_number']}: only $optCount options");
            }
            $currentQ = null;
        };

        $inQuestionBody = false;
        $questionDone   = false;
        $lastQNumber    = 0;
        $n              = count($structured);

        for ($i = 0; $i < $n; $i++) {
            $el   = $structured[$i];
            $type = $el['type'];

            // ── IMAGE ─────────────────────────────────────────────────────
            if ($type === 'image') {
                if ($currentQ !== null) {
                    $savedPath = $el['saved_path'] ?? null;
                    if ($savedPath) {
                        if (!$questionDone && $lastOptLetter === null) {
                            $currentQ['image_path'] = $savedPath;
                            $this->log("  Image → question body");
                        } elseif ($lastOptLetter !== null) {
                            $currentQ['option_' . $lastOptLetter . '_image'] = $savedPath;
                            $this->log("  Image → option " . strtoupper($lastOptLetter));
                        }
                    }
                }
                continue;
            }

            // ── TEXT ──────────────────────────────────────────────────────
            $rawText  = $el['text'] ?? '';
            $htmlText = $el['html'] ?? htmlspecialchars($rawText, ENT_QUOTES, 'UTF-8');
            $line     = trim($rawText);

            if ($line === '') continue;

            // ── FIX SOAL 12: Concatenated options in ONE paragraph ────────
            // e.g. "a.(1)-(2)-(4)b.(2)-(1)c.(2)-(3)d.(2)-(4)e.(2)-(6)"
            // Options may be separated by \n (from w:br), so use /s flag for dot-all
            if (
                $currentQ !== null
                && preg_match('/^[aA][.\)]/u', $line)
                && preg_match('/[bB][.\)].*[cC][.\)]/su', $line)
            ) {
                $splitOpts = $this->splitConcatenatedOptions($line);
                if (count($splitOpts) >= 2) {
                    foreach ($splitOpts as $letter => $optPlain) {
                        $currentQ['option_' . $letter] = htmlspecialchars($optPlain, ENT_QUOTES, 'UTF-8');
                        $lastOptLetter = $letter;
                        $this->log("  Option (split) " . strtoupper($letter) . ": " . substr($optPlain, 0, 60));
                    }
                    $questionDone   = true;
                    $inQuestionBody = false;
                    continue;
                }
            }

            // ── LETTER OPTION (a. / b. / … / e.) ─────────────────────────
            if (preg_match('/^([A-Ea-e])[.\)]\s*(.+)/su', $line, $m)) {
                $letter  = strtolower($m[1]);
                $optText = trim($m[2]);
                $optHtml = trim(preg_replace('/^[A-Ea-e][.\)]\s*/u', '', $htmlText));

                if ($currentQ !== null && in_array($letter, ['a', 'b', 'c', 'd', 'e']) && $optText !== '') {
                    $currentQ['option_' . $letter] = $optHtml;
                    $lastOptLetter  = $letter;
                    $questionDone   = true;
                    $inQuestionBody = false;
                    $this->log("  Option " . strtoupper($letter) . ": " . substr($optText, 0, 60));
                    continue;
                }
            }

            // ── QUESTION NUMBER LINE ──────────────────────────────────────
            if (preg_match('/^(\d+)[.\)]\s+(.+)/su', $line, $m)) {
                $number  = (int) $m[1];
                $text    = trim($m[2]);
                $htmlRaw = trim(preg_replace('/^\d+[.\)]\s*/u', '', $htmlText));

                if (preg_match('/^(poin|nilai|score|bobot|skor|point)\b/iu', $text)) continue;

                // Sub-item / procedure step inside question body
                if (
                    $inQuestionBody && $number >= 1 && $number <= 20
                    && !$hasKeyword($text) && !$endsWithMarker($text)
                ) {
                    $currentQ['question_text'] .= "\n" . $htmlRaw;
                    $this->log("  Step → question body: " . substr($text, 0, 50));
                    continue;
                }

                $isNewQuestion = ($number > $lastQNumber)
                    || $hasKeyword($text)
                    || $endsWithMarker($text)
                    || (!$questionDone && strlen($text) > 80);

                if ($isNewQuestion) {
                    $saveCurrentQ();
                    $lastQNumber    = $number;
                    $lastOptLetter  = null;
                    $questionDone   = false;
                    $inQuestionBody = false;
                    $fullHtml       = $htmlRaw;

                    if (!$endsWithMarker($text)) {
                        $inQuestionBody = true;
                        $j = $i + 1;
                        while ($j < $n) {
                            $nxt     = $structured[$j];
                            $nxtRaw  = trim($nxt['text'] ?? '');
                            $nxtHtml = $nxt['html'] ?? htmlspecialchars($nxtRaw, ENT_QUOTES, 'UTF-8');

                            if ($nxt['type'] === 'image') break;
                            if ($nxtRaw === '') {
                                $j++;
                                continue;
                            }

                            if (preg_match('/^[A-Ea-e][.\)]\s*\S/u', $nxtRaw)) break;

                            if (preg_match('/^(\d+)[.\)]\s+(.+)/su', $nxtRaw, $nm)) {
                                $nxtNum = (int) $nm[1];
                                $nxtTxt = trim($nm[2]);
                                if (
                                    $nxtNum > $number
                                    && ($hasKeyword($nxtTxt) || strlen($nxtTxt) > 80 || $endsWithMarker($nxtTxt))
                                ) {
                                    break;
                                }
                            }

                            $fullHtml .= "\n" . $nxtHtml;
                            $i = $j;

                            if ($endsWithMarker($nxtRaw)) {
                                $questionDone   = true;
                                $inQuestionBody = false;
                                $j++;
                                break;
                            }
                            $j++;
                        }
                    } else {
                        $questionDone   = true;
                        $inQuestionBody = false;
                    }

                    $currentQ = [
                        'question_number' => $number,
                        'question_text'   => $fullHtml,
                        'option_a'        => null,
                        'option_a_image' => null,
                        'option_b'        => null,
                        'option_b_image' => null,
                        'option_c'        => null,
                        'option_c_image' => null,
                        'option_d'        => null,
                        'option_d_image' => null,
                        'option_e'        => null,
                        'option_e_image' => null,
                        'correct_answer'  => null,
                        'points'          => 1,
                        'image_path'      => null,
                    ];
                    $this->log("→ NEW Q#$number: " . substr($text, 0, 60));
                    continue;
                }
            }

            // ── PLAIN TEXT: append to active context ─────────────────────
            if ($currentQ !== null) {
                if ($questionDone && $lastOptLetter !== null) {
                    $currentQ['option_' . $lastOptLetter] .= "\n" . $htmlText;
                } elseif ($inQuestionBody || !$questionDone) {
                    $currentQ['question_text'] .= "\n" . $htmlText;
                    if ($endsWithMarker($line)) {
                        $questionDone   = true;
                        $inQuestionBody = false;
                    }
                }
            }

            if (preg_match('/(jawaban|answer|kunci):\s*([A-Ea-e])/iu', $line, $am)) {
                if ($currentQ !== null) $currentQ['correct_answer'] = strtoupper($am[2]);
            }
            if (preg_match('/(poin|nilai|score|bobot|skor|point):\s*(\d+)/iu', $line, $pm)) {
                if ($currentQ !== null) $currentQ['points'] = (int) $pm[2];
            }
        }

        $saveCurrentQ();

        usort($questions, fn($a, $b) => ($a['question_number'] ?? 0) - ($b['question_number'] ?? 0));

        $this->log('PARSING COMPLETE — Total: ' . count($questions));
        $this->log('Numbers: ' . implode(', ', array_column($questions, 'question_number')));

        foreach ($questions as &$q) {
            unset($q['question_number']);
        }

        return $questions;
    }

    // =========================================================================
    // Split concatenated options from one paragraph's plain text
    // Options may be on one line OR separated by \n (w:br line breaks)
    // e.g. "a.(1)-(2)-(4)b.(2)-(1)c.(2)-(3)d.(2)-(4)e.(2)-(6)"
    // OR:  "a.(1)-(2)-(4)\nb.(2)-(1)\nc.(2)-(3)\nd.(2)-(4)\ne.(2)-(6)"
    // Returns ['a' => 'text', 'b' => 'text', ...]
    // =========================================================================
    private function splitConcatenatedOptions(string $plainLine): array
    {
        $result    = [];
        $positions = [];

        // Normalize: collapse \n that appear between letter markers
        // Find all [a-e]. or [a-e]) positions in the full string (including across newlines)
        // Use /s flag is not needed here since we search full string
        preg_match_all('/(?<![a-zA-Z])([a-eA-E])[.\)]/u', $plainLine, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[1] as $match) {
            $letter = strtolower($match[0]);
            $offset = $match[1]; // byte offset
            if (in_array($letter, ['a', 'b', 'c', 'd', 'e'])) {
                $positions[] = ['letter' => $letter, 'offset' => $offset];
            }
        }

        if (count($positions) < 2) return [];

        for ($k = 0; $k < count($positions); $k++) {
            $letter = $positions[$k]['letter'];
            $start  = $positions[$k]['offset'];
            $end    = isset($positions[$k + 1]) ? $positions[$k + 1]['offset'] : strlen($plainLine);
            // Use substr (byte-based) to match PREG_OFFSET_CAPTURE byte offsets
            $chunk   = substr($plainLine, $start, $end - $start);
            // Strip leading "a." or "a)" prefix and trim whitespace/newlines
            $optText = trim(preg_replace('/^[a-eA-E][.\)]\s*/u', '', $chunk));
            if ($optText !== '') {
                $result[$letter] = $optText;
            }
        }

        return $result;
    }

    // =========================================================================
    // Extract structured content: text (with HTML bold/italic) + images in order
    // =========================================================================
    private function extractStructuredContent(string $filePath): array
    {
        $elements = [];

        try {
            $zip = new ZipArchive();
            if ($zip->open($filePath) !== true) return $elements;

            $documentXml  = $zip->getFromName('word/document.xml');
            $numberingXml = $zip->getFromName('word/numbering.xml');
            $relsXml      = $zip->getFromName('word/_rels/document.xml.rels');

            if (!$documentXml) {
                $zip->close();
                return $elements;
            }

            // ── Image map ─────────────────────────────────────────────────
            $imageMap = [];
            if ($relsXml) {
                $relsDom = new \DOMDocument();
                $relsDom->loadXML($relsXml);
                foreach ($relsDom->getElementsByTagName('Relationship') as $rel) {
                    if (str_contains($rel->getAttribute('Type'), '/image')) {
                        $imageMap[$rel->getAttribute('Id')] = 'word/' . $rel->getAttribute('Target');
                    }
                }
            }

            // ── Numbering format map ──────────────────────────────────────
            $numFmtMap = [];
            if ($numberingXml) {
                $numDom = new \DOMDocument();
                $numDom->loadXML($numberingXml);
                $numXp  = new \DOMXPath($numDom);
                $numXp->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

                $absFormats = [];
                foreach ($numXp->query('//w:abstractNum') as $absNum) {
                    $absId = $absNum->getAttribute('w:abstractNumId');
                    $absFormats[$absId] = [];
                    foreach ($numXp->query('.//w:lvl', $absNum) as $lvl) {
                        $ilvl    = $lvl->getAttribute('w:ilvl');
                        $fmtEl   = $numXp->query('.//w:numFmt',  $lvl);
                        $txtEl   = $numXp->query('.//w:lvlText', $lvl);
                        $fmt     = $fmtEl->length > 0 ? $fmtEl->item(0)->getAttribute('w:val') : '';
                        $lvlText = $txtEl->length  > 0 ? $txtEl->item(0)->getAttribute('w:val') : '';
                        $absFormats[$absId][$ilvl] = ['fmt' => $fmt, 'lvlText' => $lvlText];
                    }
                }

                foreach ($numXp->query('//w:num') as $num) {
                    $numId  = $num->getAttribute('w:numId');
                    $absRef = $numXp->query('.//w:abstractNumId', $num);
                    if ($absRef->length > 0) {
                        $absId = $absRef->item(0)->getAttribute('w:val');
                        $numFmtMap[$numId] = $absFormats[$absId] ?? [];
                    }
                }
            }

            // ── Parse document XML ────────────────────────────────────────
            $dom = new \DOMDocument();
            $dom->loadXML($documentXml);
            $xp  = new \DOMXPath($dom);
            $xp->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            $xp->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
            $xp->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

            // Auto-detect question numIds (decimal '%1.' with >= 5 occurrences)
            $numIdCounts = [];
            foreach ($xp->query('//w:p') as $p) {
                $numIdEl = $xp->query('.//w:numPr/w:numId', $p);
                $ilvlEl  = $xp->query('.//w:numPr/w:ilvl',  $p);
                if ($numIdEl->length === 0) continue;
                $nid  = $numIdEl->item(0)->getAttribute('w:val');
                $ilvl = $ilvlEl->length > 0 ? $ilvlEl->item(0)->getAttribute('w:val') : '0';
                $fmt  = $numFmtMap[$nid][$ilvl]['fmt']     ?? '';
                $lvlT = $numFmtMap[$nid][$ilvl]['lvlText'] ?? '';
                if ($fmt === 'decimal' && $lvlT === '%1.') {
                    $numIdCounts[$nid] = ($numIdCounts[$nid] ?? 0) + 1;
                }
            }
            $questionNumIds = array_keys(array_filter($numIdCounts, fn($c) => $c >= 5));
            $this->log('Question numIds: ' . implode(', ', $questionNumIds));

            // Process paragraphs in document order
            $globalQCounter = 0;
            $numIdCounters  = [];
            $optCounters    = [];

            foreach ($xp->query('//w:p') as $para) {

                // Images inside paragraph
                foreach ($xp->query('.//a:blip', $para) as $blip) {
                    $embedId = $blip->getAttribute('r:embed');
                    if (!isset($imageMap[$embedId])) continue;
                    $imgPath    = $imageMap[$embedId];
                    $imgContent = $zip->getFromName($imgPath);
                    if (!$imgContent) continue;
                    preg_match('/\.(png|jpg|jpeg|gif|bmp|webp)$/i', $imgPath, $extM);
                    $ext       = $extM[1] ?? 'png';
                    $savedPath = $this->saveImage($imgContent, $ext);
                    $elements[] = [
                        'type'       => 'image',
                        'content'    => $imgContent,
                        'extension'  => $ext,
                        'saved_path' => $savedPath,
                    ];
                }

                // Numbering
                $numIdEl = $xp->query('.//w:numPr/w:numId', $para);
                $ilvlEl  = $xp->query('.//w:numPr/w:ilvl',  $para);
                $numId   = $numIdEl->length > 0 ? $numIdEl->item(0)->getAttribute('w:val') : null;
                $ilvl    = $ilvlEl->length  > 0 ? $ilvlEl->item(0)->getAttribute('w:val')  : '0';
                $fmt     = $numId ? ($numFmtMap[$numId][$ilvl]['fmt']     ?? '') : '';
                $lvlText = $numId ? ($numFmtMap[$numId][$ilvl]['lvlText'] ?? '') : '';

                $prefix = '';
                if ($numId !== null) {
                    if (in_array($numId, $questionNumIds)) {
                        $numIdCounters[$numId] = ($numIdCounters[$numId] ?? 0) + 1;
                        $globalQCounter++;
                        $prefix = $globalQCounter . '. ';
                    } elseif ($fmt === 'lowerLetter') {
                        $optCounters[$numId] = ($optCounters[$numId] ?? 0) + 1;
                        $prefix = chr(96 + $optCounters[$numId]) . '. ';
                    } elseif ($fmt === 'decimal' && str_contains($lvlText, '(')) {
                        $numIdCounters[$numId] = ($numIdCounters[$numId] ?? 0) + 1;
                        $prefix = '(' . $numIdCounters[$numId] . ') ';
                    } elseif ($fmt === 'decimal') {
                        $numIdCounters[$numId] = ($numIdCounters[$numId] ?? 0) + 1;
                        $prefix = $numIdCounters[$numId] . '. ';
                    }
                }

                // Build plain + HTML from runs
                $plainText = '';
                $htmlText  = '';

                foreach ($xp->query('.//w:r', $para) as $run) {
                    $rPrQ = $xp->query('.//w:rPr', $run);
                    $tEl  = $xp->query('.//w:t',   $run);
                    if ($tEl->length === 0) continue;
                    $t = $tEl->item(0)->textContent;
                    if ($t === '') continue;

                    $isBold   = false;
                    $isItalic = false;

                    if ($rPrQ->length > 0) {
                        $rPrNode = $rPrQ->item(0);
                        // Use direct child 'w:b' (not descendant './/w:b') to avoid false positives
                        // w:b present + val not "0"/"false" = bold ON
                        // w:b absent = not bold
                        $bNodes = $xp->query('w:b', $rPrNode);
                        if ($bNodes->length > 0) {
                            $bVal   = $bNodes->item(0)->getAttribute('w:val');
                            $isBold = ($bVal !== '0' && $bVal !== 'false');
                        }
                        $iNodes = $xp->query('w:i', $rPrNode);
                        if ($iNodes->length > 0) {
                            $iVal     = $iNodes->item(0)->getAttribute('w:val');
                            $isItalic = ($iVal !== '0' && $iVal !== 'false');
                        }
                    }

                    $plainText .= $t;
                    $escaped    = htmlspecialchars($t, ENT_QUOTES, 'UTF-8');

                    if ($isBold && $isItalic) {
                        $htmlText .= "<strong><em>$escaped</em></strong>";
                    } elseif ($isBold) {
                        $htmlText .= "<strong>$escaped</strong>";
                    } elseif ($isItalic) {
                        $htmlText .= "<em>$escaped</em>";
                    } else {
                        $htmlText .= $escaped;
                    }
                }

                // Explicit line-breaks inside paragraph
                foreach ($xp->query('.//w:br', $para) as $br) {
                    $brType = $br->getAttribute('w:type');
                    if ($brType === '' || $brType === 'textWrapping') {
                        $htmlText  .= '<br>';
                        $plainText .= "\n";
                    }
                }

                $plainFull = trim($prefix . $plainText);
                $htmlFull  = trim(htmlspecialchars($prefix, ENT_QUOTES, 'UTF-8') . $htmlText);

                if ($plainFull === '') continue;

                $elements[] = [
                    'type' => 'text',
                    'text' => $plainFull,
                    'html' => $htmlFull,
                ];
            }

            $zip->close();
        } catch (\Exception $e) {
            $this->log('Error in extractStructuredContent: ' . $e->getMessage(), 'error');
        }

        return $elements;
    }

    private function saveImage(string $imageContent, string $extension): string
    {
        $fileName        = 'question_' . time() . '_' . Str::random(10) . '.' . $extension;
        $destinationPath = 'questions/images/' . $fileName;
        Storage::disk('public')->put($destinationPath, $imageContent);
        $this->log("Saved image: $destinationPath");
        return $destinationPath;
    }

    private function log(string $message, string $level = 'info'): void
    {
        match ($level) {
            'error'   => Log::error($message),
            'warning' => Log::warning($message),
            'debug'   => Log::debug($message),
            default   => Log::info($message),
        };
    }
}
